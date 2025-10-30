<?php
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '\config\db.php';
require_once BASE_PATH . '\Controladores\AuthController.php';
require_once BASE_PATH . '\Controladores\CitasController.php';

define('VIEWS_PATH',BASE_PATH . '\Vistas');

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
        require VIEWS_PATH. '\landing.php';
        break;
    case '/login':
        //Vista del login de la app
            $controller = new AuthController();
            $controller->loginForm();
        break;
    case '/registrar':
        //Vista de la pagina de registrar de la app
            $controller = new AuthController();
            $controller->registerForm();
        break;
    case '/agenda':
        //Vista de la agenda de la app. Requiere loguearse para verla
        requireLogin();
        $controller = new CitasController();
        $controller -> index();
        break;
    case '/perfil':
        //Vista del perfil de cada persona que 
        requireLogin();
        $id = $_SESSION['usuario_id'];
        $controller = new AuthController();
        $controller -> perfil($id);
        break;
    case '/buscar':
        //Vista de la pagina que utilizaran las personas para ver sus citas sin estar logueadas
        require VIEWS_PATH. '/buscar.php';
        break;
    case '/citas':
        requireLogin();
        $id = $_SESSION['usuario_id'];
        $controller = new CitasController();
        $controller -> citas($id);
        break;
    case '/logout':
        session_start();
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    case '/api/slots':
        requireLogin();
        $fechas = new CitasController();
        $fechas->slotsJson();
        break;
    case '/citas/nueva':
        requireLogin();
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $citas = new CitasController();
            $citas -> crear();
        }else{
            header('Location: /agenda');exit;
        }
        break;
}