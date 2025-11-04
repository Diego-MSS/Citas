<?php 
require_once __DIR__ . '/../Modelos/UsersModel.php';

class AuthController{
    public function registerForm(){
        $db = DB::getInstance();
        $errores = [];

        //El usuario envia los datos
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $nombre=trim($_POST['nombre'] ?? '');
            $login=trim($_POST['email'] ?? '');
            $pass=trim($_POST['pass'] ?? '');
            $confirmar=trim($_POST['confirmar'] ?? '');

            if($nombre === ''|| $login===''||$pass===''){
                $errores[]="Todos los campos son obligatorios.";
            }
            if(!filter_var($login,FILTER_VALIDATE_EMAIL)){
                $errores[]="El email no es valido.";
            }
            if($pass !== $confirmar){
                $errores[]="Las contraseñas no coinciden.";
            }

            $stmt = $db->prepare("select id from usuario where login = :email");
            $stmt ->execute([':email'=> $login]);
            if($stmt->fetch()){
                $errores[]="Ya existe ese usuario";
            }
            if(empty($errores)){
                $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                $usuarioID = UsersModel::create($db, $nombre, $login, $pass_hash);

                if(session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['usuario_id'] = $usuarioID;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_email'] = $login;

                header('Location: /agenda');
                exit;
            }
        }
        $title = 'Crear cuenta';
        include VIEWS_PATH . '\registrar.php';
    }
    public function loginForm(){
        $db = DB::getInstance();
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $login = trim($_POST['email'] ?? '');
            $pass = trim($_POST['pass'] ?? '');

            if ($login === '' || $pass === ''){
                $errores[] = 'Debe rellenar todos los campos';
            }
            if(!filter_var($login,FILTER_VALIDATE_EMAIL)){
                $errores[]="El email no es valido.";
            }
            $stmt=$db->prepare("SELECT * FROM usuario where login = :email");
            $stmt->execute([':email'=> $login]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario && password_verify($pass, $usuario['pass'])){
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];

                header('Location: /agenda');
                exit;
            }
        }
        $title = 'Login';
        include VIEWS_PATH . '/login.php';
    }
    
    public function buscarUsuario(){
        $title = "Buscar Usuario";
        $q = trim($_GET['q'] ?? '');
        $resultados = [];
        if($q !== ''){
            $resultados = UsersModel::buscar($q);
        }
        include VIEWS_PATH . '/buscar.php';
    }
}