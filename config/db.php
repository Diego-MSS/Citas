<?php
$_ENV = parse_ini_file(__DIR__. '/.env');

$dsn = "mysql:host= {$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}";
$options = [PDO::ALTER_ERRMODE => PDO::ERRMODE_EXCEPTION];

try{
    $pdo = NEW PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $options);
}catch (PDOException $e) {
        die("Error de conexion: ".$e->getMessage());
}

