<?php
$_ENV = parse_ini_file(__DIR__. '/.env');

class DB{
    private static $instance = null;
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = NEW PDO("mysql:host= {$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}", $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [PDO::ALTER_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return self::$instance;
    }
}


