<?php
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/Controladores/AuthController.php';
require_once BASE_PATH . '/Controladores/CitasController.php';

define('VIEWS_PATH',BASE_PATH . '/Vistas');

date_default_timezone_set('Europe/Madrid');

session_start();
function requireLogin(){
    if(!isset($_SESSION['usuario_id'])){
        header('Location: /login', true, 303);
        exit;
    }
}

function requireGuest(): void{
    if(session_status() === PHP_SESSION_NONE ) session_start();

    if(!empty($_SESSION['usuario_id'])){
        $_SESSION['flash'] = [
            'tipo' => 'info',
            'titulo' => 'Sesion ya iniciada',
            'mensaje' => 'Ya has iniciado sesion. Para cambiar de cuenta o registrar una nueva, primero debe cerrar sesion.'
        ];
        header('Location: /agenda', true, 303);
        exit; 
    }
}
$uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

switch($uri){
    case'/':
        //Vista de la pagina principal, como se hace ya que no necesito ningun controlador para hacer una landing page

        if(!empty($_SESSION['usuario_id'])){
            header('Location: /agenda', true, 303);
            exit;
        }

        require VIEWS_PATH. '/landing.php';
        break;
    case '/login':
        //Vista del login de la app
            requireGuest();
            $controller = new AuthController();
            $controller->loginForm();
        break;
    case '/registrar':
        //Vista de la pagina de registrar de la app
            requireGuest();
            $controller = new AuthController();
            $controller->registerForm();
        break;
    case '/agenda':
        //Vista de la agenda de la app. Requiere loguearse para verla
        requireLogin();
        $controller = new CitasController();
        $controller -> index();
        break;
    case'/api/agenda':
        requireLogin();
        $controller = new CitasController();
        $controller->agendaJson();
        break;
    case '/perfil':
        //Vista del perfil de cada persona que 
        requireLogin();
        $id = $_SESSION['usuario_id'];
        $controller = new AuthController();
        $controller -> perfil($id);
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
        header('Location: /', true, 303);
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
            header('Location: /agenda', true, 303);exit;
        }
        break;
    case '/citas/cancelar':
        requireLogin();
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $controller = new CitasController();
            $controller -> cancelar();
        }else{
            header('Location: /agenda', true, 303);
            exit;
        }
        break;
    case '/agenda-publica':
        $controller = new CitasController();
        $controller -> agendaPublica();
        break;
    case '/api/agenda-publica':
        $controller = new CitasController();
        $controller -> agendaPublicaJson();
        break;
}