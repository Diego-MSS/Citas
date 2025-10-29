<?php
// C:\Users\diego\Desktop\Citas\config\db.php

$envPath = dirname(__DIR__) . '/.env'; // lee .env desde la raíz del proyecto
$_ENV = parse_ini_file($envPath);

class DB {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (!self::$instance) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_DATABASE']
            );

            self::$instance = new PDO(
                $dsn,
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // <- aquí el fix
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }
        return self::$instance;
    }
}