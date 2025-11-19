<?php

class CitasModel{

    /**
     * Nombre: getByUsuario()
     * Recibe: el id del usuario y un array con los filtros que desea el usuario.
     * Devuelve: un array con los datos devueltos por la consulta.
     * Descripcion:
     *      ->Preparamos la consulta
     *      ->En caso de que haya filtros se añaden a la consulta
     *      ->Lanzamos la consutla cambiando los datos.
     */
    public static function getByUsuario($id, array $fitros): array {
        $db = DB::getInstance();
        $sql= 'select c.id, c.fecha, s.hora, c.asunto, c.estado
                            from CITA c 
                            join SLOTS as s on c.hora = s.id
                             where usuario = :id';
        $p =[":id"=>$id];
        if (!empty($fitros['desde'])){
            $sql .= " and c.fecha >= :d";
            $p[":d"]=$filtros['desde'];
        }
        if(!empty($fitros['hasta'])){
            $sql .= " and c.fecha <= :h";
            $p[":h"]=$fitros['hasta'];
        }
        if(!empty($fitros['estado'])){
            $sql .= " and c.estado = :e";
            $p[":e"]=$fitros['estado'];
        }
        if (!empty($fitros['q'])){ 
            $sql .= " AND c.asunto LIKE :q"; 
            $p[':q']="%{$fitros['q']}%"; 
        }
        $sql .= " ORDER BY c.fecha DESC, s.hora DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($p);

        $citas = $stmt -> fetchALL(PDO::FETCH_ASSOC); 

        return $citas;
    }

    /**
     * Nombre: getSlots()
     * Recibe: La fecha en la que buscar los slots.
     * Devuelve: un array con los slots libres 
     * Descripcion:
     *      ->Preparamos la consulta
     *      ->Lanzamos la consulta con los datos 
     *      ->Validamos los datos recibidos por la consulta y los devolvemos al controlador.
     */
    public static function getSlots($fecha){
        $db = DB::getInstance();

        $st = $db->prepare("SELECT s.hora as time, s.id as slot_id,
             CASE WHEN c.hora IS NULL THEN 1 ELSE 0 END AS available
            FROM SLOTS s
            LEFT JOIN CITA c
                ON c.hora = s.id AND c.fecha = :fecha
            ORDER BY s.hora ASC
            ");
        $st->execute([":fecha"=>$fecha]);
        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach($row as $r){
            $r['slot_id']   = (int)$r['slot_id'];
            $r['available'] = (bool)$r['available'];
            if (isset($r['time'])) $r['time'] = substr($r['time'], 0, 5);
        }
        return $row;
    }
    /**
     * Nombre: comprobarSlot()
     * Recibe: el id del slot a comprobar
     * Devuelve: la columna recibida por la consulta.
     * Descripcion:
     *      ->Preparamos la consulta para evitar el SQL Inyection
     *      ->Lanzamos la consulta pero introduciendo los datos recibidos por el controlador.
     *      ->Devolvemos los datos de la consulta.
     */
    public static function comprobarSlot($slotId){
        $db = DB::getInstance();

        $st = $db -> prepare("SELECT COUNT(*) FROM SLOTS WHERE id = :id");
        $st->execute([":id"=>$slotId]);

        return $st->fetchColumn() > 0;
    }

    /**
     * Nombre: slotDisponible()
     * Recibe: el id del slot a comprobar y la fecha donde se encuentra el slot
     * Devuelve: Si el slot seleccionado esta libre o no
     * Descripcion:
     *      ->Prepara la consulta para evitar el SQL Inyection
     *      ->Ejecutamos la consulta con los datos que hemos recibido.
     *      ->Devolvemos un boleano para indicar si el slot esta libre o si no.
     */
    public static function slotDisponible($slotId, $fecha): bool{
        $db = DB::getInstance();

        $st= $db->prepare("SELECT COUNT(*) FROM CITA WHERE hora = :horaid AND fecha =:fecha");
        $st->execute([":horaid"=> $slotId, ":fecha" => $fecha]);
        
        return (int)$st ->fetchColumn() === 0;
    }

    /**
     * Nombre: crearCita()
     * Recibe: El id del usuario, la fecha, el id del slot y el asunto de la cita.
     * Devuelve: Nada
     * Descripcion:
     *      ->Preparamos la consulta para evitar el SQL Inyeccion.
     *      ->Lanzamos la consulta con los datos ya validados.
     */
    public static function crearCita($usuarioId, $fecha, $slotId, $asunto){
        $db = DB::getInstance();

        $st=$db->prepare("INSERT INTO CITA (usuario, fecha, hora, asunto, estado) VALUE ( :u, :f, :h, :a, 'RESERVADA')");

        $st->execute([ 
            ":u"=>$usuarioId,
            ":f"=>$fecha,
            ":h"=>$slotId,
            ":a"=>$asunto
        ]);
    }


    /**
     * Nombre: getAgenda()
     * Recibe: el id del usuario, la fecha de inicio de la semana, la fecha de fin de la semana.
     * Devuelve: un array con las citas que tiene el usuario durante esas fechas.
     * Descripcion: 
     *      ->Se prepara la consulta para evitar el SQL Inyection
     *      ->Se ejecuta la consulta con los datos ya validados en el controlador.
     *      ->Se formatean los datos de las hora para que aparezcan de manera que queremos.
     */
    public static function getAgenda(string $from, string $to, int $uid){
        $db=DB::getInstance();
        $sql="SELECT c.id, c.fecha, s.hora time, c.asunto, c.estado 
            from CITA c 
            join SLOTS s on s.id = c.hora
            where c.usuario = :u 
            and c.fecha between :f and :t 
            order by c.fecha, s.hora";
        $st= $db->prepare($sql);
        $st->execute([
            ':u' => $uid,
            ':f' => $from,
            ':t' => $to
        ]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r) $r['time'] = substr($r['time'],0,5);
        return $rows;
    }


    /**
     * Nombre: Cancelar()
     * Recibe: El id de la cita que vamos a eliminar y el id del usuario que desea eliminar la cita.
     * Devuelve: Un mensaje de que en caso de error, aparezca un mensaje de error y en caso de exito, un mensaje de exito.
     * Descripcion:
     *      ->Se busca la cita mediante una llamada a otra funcion
     *      ->En caso de que no exista, se manda un error.
     *      ->En caso de que exita y ya este cancelada, se manda un error.
     *      ->En caos de que exista y no este cancelada:
     *          ->Se prepara la consulta para evitar el SQL Inyection
     *          ->Se ejecuta la consulta con los datos recibidos del controlador.
     *          ->En caso de que se ejecute con exito, se manda un mensaje de SUCCESS
     *          ->En caso de que haya un error, se manda un mensaje de ERROR.
     */
    public static function cancelar($citaId, $usuarioId): ?array{
        $db = DB::getInstance();

        $cita = self::getCita($citaId, $usuarioId);
        if(!$cita){
            return ['ok' => false, 'msg' =>'Cita no encontrada o no pertenece al usuario.'];
        }
        if($cita['estado' === 'CANCELADA']){
            return['ok' => false, 'msg'=> 'La cita ya estaba cancelada'];
        }
        $st = $db ->prepare("UPDATE CITA SET estado = 'CANCELADA' where id = :id and usuario = :u");
        $st -> execute([":id" =>$citaId, ":u" => $usuarioId]);

        if($st->rowCount()> 0){
            return ['ok' => true, 'msg'=> 'La cita ha sido cancelada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'No se pudo cancelar la cita correctamente'];
    }

    /**
     * Nombre: getCita()
     * Recibe: el id de la cita y el id del usuario
     * Devuelve: Un array con los datos de la cita.
     * Descripcion:
     *      ->Se prepara la consulta para evitar el SQL Inyection
     *      ->Se ejecuta la consulta con los datos previamente validados.
     *      ->Se devuelve un array de los datos de la consulta o null en caso de que no se encuentren.
     */
    public static function getCita($citaId, $usuarioId): array{
        $db = DB::getInstance();
        $sql="SELECT c.id, c.usuario, c.fecha, s.hora, c.estado
            FROM CITA c
            join SLOTS s on c.hora = s.id
            where c.id = :id
            and c.usuario = :u
            limit 1";
        $st=$db->prepare($sql);
        $st -> execute([":id" => $citaId, ":u" => $usuarioId]);
        return $st->fetch(PDO::FETCH_ASSOC) ? :null;
    }
}