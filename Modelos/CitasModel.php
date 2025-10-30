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
}