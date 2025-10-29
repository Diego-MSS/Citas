<?php

class CitasModel{
    public static function getByUsuario($id){
        $db = DB::getInstance();

        $stmt=$db->prepare('select fecha, hora, asunto, estado from cita where usuario = :id ORDER BY fecha DESC');
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $citas = $stmt -> fetchALL(PDO::FETCH_ASSOC);

        return $citas;
    }
}