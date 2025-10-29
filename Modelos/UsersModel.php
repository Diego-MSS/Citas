<?php
// Modelos/UsersModel.php

class UsersModel
{
    /** Comprueba si ya existe un usuario con ese email (login) */
    public static function existsByEmail(PDO $db, string $email): bool
    {
        $st = $db->prepare('SELECT 1 FROM usuario WHERE login = :email LIMIT 1');
        $st->execute([':email' => $email]);
        return (bool) $st->fetchColumn();
    }

    /** Crea un usuario y devuelve su ID */
    public static function create(PDO $db, string $nombre, string $email, string $passwordHash): int
    {
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

    /** Obtiene un usuario por email (útil para login) */
    public static function findByEmail(PDO $db, string $email): ?array
    {
        $st = $db->prepare('SELECT id, login, pass, nombre FROM usuario WHERE login = :email LIMIT 1');
        $st->execute([':email' => $email]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }

    /** (Opcional) Obtener por ID */
    public static function findById(PDO $db, int $id): ?array
    {
        $st = $db->prepare('SELECT id, login, nombre FROM usuario WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }
}
