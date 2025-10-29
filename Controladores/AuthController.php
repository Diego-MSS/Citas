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
}