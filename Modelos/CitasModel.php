<?php

class CitasModel{
    public static function getByUsuario($id, array $fitros): array {
        $db = DB::getInstance();
        $sql= 'select c.id, c.fecha, s.hora, c.asunto, c.estado
                            from cita c 
                            join slots as s on c.hora = s.id
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

    public static function getSlots($fecha){
        $db = DB::getInstance();

        $st = $db->prepare("SELECT s.hora as time, s.id as slot_id,
             CASE WHEN c.hora IS NULL THEN 1 ELSE 0 END AS available
            FROM slots s
            LEFT JOIN cita c
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

    public static function comprobarSlot($slotId){
        $db = DB::getInstance();

        $st = $db -> prepare("SELECT COUNT(*) FROM slots WHERE id = :id");
        $st->execute([":id"=>$slotId]);

        return $st->fetchColumn() > 0;
    }

    public static function slotDisponible($slotId, $fecha): bool{
        $db = DB::getInstance();

        $st= $db->prepare("SELECT COUNT(*) FROM cita WHERE hora = :horaid AND fecha =:fecha");
        $st->execute([":horaid"=> $slotId, ":fecha" => $fecha]);
        
        return (int)$st ->fetchColumn() === 0;
    }

    public static function crearCita($usuarioId, $fecha, $slotId, $asunto){
        $db = DB::getInstance();

        $st=$db->prepare("INSERT INTO cita (usuario, fecha, hora, asunto, estado) VALUE ( :u, :f, :h, :a, 'RESERVADA')");

        $st->execute([ 
            ":u"=>$usuarioId,
            ":f"=>$fecha,
            ":h"=>$slotId,
            ":a"=>$asunto
        ]);
    }

    public static function getAgenda(string $from, string $to, int $uid){
        $db=DB::getInstance();
        $sql="SELECT c.id, c.fecha, s.hora time, c.asunto, c.estado 
            from cita c 
            join slots s on s.id = c.hora
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

    public static function cancelar($citaId, $usuarioId): ?array{
        $db = DB::getInstance();

        $cita = self::getCita($citaId, $usuarioId);
        if(!$cita){
            return ['ok' => false, 'msg' =>'Cita no encontrada o no pertenece al usuario.'];
        }
        if($cita['estado' === 'CANCELADA']){
            return['ok' => false, 'msg'=> 'La cita ya estaba cancelada'];
        }
        $st = $db ->prepare("UPDATE cita SET estado = 'CANCELADA' where id = :id and usuario = :u");
        $st -> execute([":id" =>$citaId, ":u" => $usuarioId]);

        if($st->rowCount()> 0){
            return ['ok' => true, 'msg'=> 'La cita ha sido cancelada correctamente.'];
        }
        return ['ok' => false, 'msg' => 'No se pudo cancelar la cita correctamente'];
    }
    public static function getCita($citaId, $usuarioId): array{
        $db = DB::getInstance();
        $sql="SELECT c.id, c.usuario, c.fecha, s.hora, c.estado
            FROM cita c
            join slots s on c.hora = s.id
            where c.id = :id
            and c.usuario = :u
            limit 1";
        $st=$db->prepare($sql);
        $st -> execute([":id" => $citaId, ":u" => $usuarioId]);
        return $st->fetch(PDO::FETCH_ASSOC) ? :null;
    }
}