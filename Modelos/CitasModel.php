<?php

class CitasModel{
    public static function getByUsuario($id){
        $db = DB::getInstance();

        $stmt=$db->prepare('select c.fecha, s.hora, c.asunto, c.estado from cita c join slots as s on c.hora = s.id where usuario = :id ORDER BY fecha DESC');
        $stmt->bindParam(':id', $id);
        $stmt->execute();

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
}