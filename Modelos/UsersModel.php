<?php
// Modelos/UsersModel.php

class UsersModel
{
    /**
     * Nombre: existsByEmail()
     * Recibe: un tipo string
     * Devuelve: Un boleano en caso de que exista un usuario con ese mismo email.
     * Descripcion:
     *  ->Se prepara una consulta para evitar el SQL Inyection
     *  ->Se inserta el email que queremos comprobar.
     *  ->Devuevle un boleano para identificar si hay un usuario con ese email o no.
     */
    public static function existsByEmail(string $email): bool
    {
        $db = DB::getInstance();

        $st = $db->prepare('SELECT 1 FROM USUARIO WHERE login = :email LIMIT 1');
        $st->execute([':email' => $email]);
        return (bool) $st->fetchColumn();
    }

    /**
     * Nombre: create()
     * Recibe: 3 variables de tipo string con los datos que se van a guardar 
     * Devuelve: El id del ultimo 'insert' que se ha metido en la base de datos.
     * Descripcion:
     *      ->Recibe los parametros que ya estan validados
     *      ->Prepara la consulta
     *      ->Los inserta en la base de daots.
     */
    public static function create(string $nombre, string $email, string $passwordHash): int
    {
        $db = DB::getInstance();
        $st = $db->prepare('
            INSERT INTO USUARIO (login, pass, nombre)
            VALUES (:login, :pass, :nombre)
        ');
        $st->execute([
            ':login'  => $email,
            ':pass'   => $passwordHash,
            ':nombre' => $nombre,
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Nombre: buscar()
     * Recibe: una variable de tipo string con el nombre a buscar.
     * Devuelve: Una lista de los usuario
     * Descripcion:
     *  ->Recibe los parametros ya validados.
     *  ->Prepara la consulta 
     *  ->Cambia los datos por los recibidos
     *  ->Lanza la consulta a la base de datos.
     */
    public static function buscar(string $q, int $limit = 20){
        $db = DB::getInstance();
        $sql = 'SELECT id, nombre 
            FROM USUARIO
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

    /**
     * Nombre: getUsuario()
     * Recibe: el email del usuario
     * Devuelve: un array con los datos con el usuario.
     * Descripcion:
     *      ->Recibe el string con los datos ya validados del controlador.
     *      ->Prepara la consulta 
     *      ->Lanza la consulta a la base de datos cambiando los datos.
     */
    public static function getUsuario(string $email){
        $db = DB::getInstance();

        $sql = 'SELECT * 
                FROM USUARIO
                WHERE login = :email';
        $usuario = $db ->prepare($sql);
        $usuario->execute([
            ':email'=>$email
        ]);
        return $usuario->fetch(PDO::FETCH_ASSOC);
    }
}
