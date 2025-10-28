<?php
require 'CITAS/config/db.php';
require 'CITAS/Controladores';

define('VIEWS_PATH',__DIR__.'/CITAS/Vitas');
session_start();
function requireLogin(){
    if(!isset($_SESSION['usuario_id'])){
        header('Location: /login');
        exit;
    }
}
$uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

switch($uri){
    case'/':
        //Vista de la pagina principal, como se hace ya que no necesito ningun controlador para hacer una landing page
        require VIEWS_PATH. '/landing.php';
        break;
    case '/login':
            $controller = new AuthController();
            $controller->loginForm();
        break;
    case '/registrar':
            $controller = new AuthController();
            $controller->registerForm();
        break;
    case '/agenda':
        requireLogin();
        $controller = new CitasController();
        $controller -> index();
        break;
    case '/perfil':
        requireLogin();
        $controller = new AuthController();
        $controller -> perfil();
        break;
    
}