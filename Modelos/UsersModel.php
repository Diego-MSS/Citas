<?php
// Modelos/UsersModel.php

class UsersModel
{
    /** Comprueba si ya existe un usuario con ese email (login) */
    public static function existsByEmail(string $email): bool
    {
        $db = DB::getInstance();

        $st = $db->prepare('SELECT 1 FROM usuario WHERE login = :email LIMIT 1');
        $st->execute([':email' => $email]);
        return (bool) $st->fetchColumn();
    }

    /** Crea un usuario y devuelve su ID */
    public static function create(string $nombre, string $email, string $passwordHash): int
    {
        $db = DB::getInstance();
        $st = $db->prepare('
            INSERT INTO usuario (login, pass, nombre)
            VALUES (:login, :pass, :nombre)
        ');
        $st->execute([
            ':login'  => $email,
            ':pass'   => $passwordHash,
            ':nombre' => $nombre,
        ]);
        return (int) $db->lastInsertId();
    }
    public static function buscar(string $q, int $limit = 20){
        $db = DB::getInstance();
        $sql = 'SELECT id, nombre 
            FROM usuario
            where nombre LIKE :q1 or login LIKE :q2
            order by nombre asc
            limit :lim';
        $st = $db->prepare($sql);
        $like = '%'.$q.'%';
        $st->bindValue(':q1', $like, PDO::PARAM_STR);
        $st->bindValue(':q2', $like, PDO::PARAM_STR);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getUsuario(string $email){
        $db = DB::getInstance();

        $sql = 'SELECT * 
                FROM usuario
                WHERE login = :email';
        $usuario = $db ->prepare($sql);
        $usuario->execute([
            ':email'=>$email
        ]);
        return $usuario->fetch(PDO::FETCH_ASSOC);
    }
}
