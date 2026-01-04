<?php 
require_once __DIR__ . '/../Modelos/UsersModel.php';

class AuthController{

    /**
     * Nombre: registerForm()
     * Recibe: Mediante el formulario de registro la funcion recibe el nobre, el email, la contraseña y la contraseña repetida para confirmar que es igual.
     * Devuelve: Un modal, en caso de que todos los datos sean corectos sera de metodo SUCCESS y en caso de que haya algun problema DANGER.
     * Descripcion:
     *      ->Recibe los datos del usuario que se quiere registrar en nuestra app
     *      ->Comprueba que los datos sean correctos y validos.
     *      ->Si no hay errores al comprobar los datos los guarda en la base de datos mediante una llamada de la funcion del modelo.
     *      ->En caso de que haya errores, vuelves a la pagina de registro y se marcan los errores que se han cometido al hacer el registro.
     */
    public function registerForm(){
        $db = DB::getInstance();
        $errores = [];
        if(session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }


        //El usuario envia los datos
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $token = $_POST['csrf'] ?? '';
            if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
                $_SESSION['flash'] = [
                    'tipo' => 'danger',
                    'titulo' => 'Solicitud inválida',
                    'mensaje' => 'Token CSRF inválido.'
                ];
            header('Location: /registrar', true, 303);
            exit;
            }

            $nombre=trim($_POST['nombre'] ?? '');
            $login=trim($_POST['email'] ?? '');
            $pass=trim($_POST['pass'] ?? '');
            $confirmar=trim($_POST['confirmar'] ?? '');
            
            //Comprobacion de los datos
            if($nombre === ''|| $login===''||$pass===''){
                $errores[]="Todos los campos son obligatorios.";
            }
            if(!filter_var($login,FILTER_VALIDATE_EMAIL)){
                $errores[]="El email no es valido.";
            }
            if($pass !== $confirmar){
                $errores[]="Las contraseñas no coinciden.";
            }
            if(UsersModel::existsByEmail($login)){
                $errores[]="Ya existe ese usuario.";
            }
            //Si no hay errores al comprobar los datos, se crea el usuario
            if(empty($errores)){
                $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                $usuarioID = UsersModel::create($nombre, $login, $pass_hash);

                //Pasamos los datos del usuario a la variable $_SESSION para que pueda entrar en las demas areas.
                if(session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['usuario_id'] = $usuarioID;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_email'] = $login;

                header('Location: /agenda', true, 303);
                exit;
            }else{
                $_SESSION['flash'] = [
                    'tipo' => 'danger',
                    'titulo' => 'Error al crear el usuario',
                    'mensaje' => implode('<br>', $errores)
                ];
                header('Location: /registrar', true, 303);
                exit;
            }
        }
        $title = 'Crear cuenta';
        include VIEWS_PATH . '/registrar.php';
    }

    /**
     * Nombre: loginForm()
     * Recibe: Mediante el formulario de autentificacion de la app recibe el usuario y la contraseña para poder entrar.
     * Devuelve: En caso de error,una lista con los errores cometidos por el usuario.
     * Descripcion:
     *      ->Se piden los datos del usuario.
     *      ->Se comprueban si los datos son correctos y validos.
     *      ->En caso de que sean correcto se redirige a una de las paginas.
     */
    public function loginForm(){
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    $errores = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){

        // ✅ CSRF solo en POST
        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            $_SESSION['flash'] = [
                'tipo' => 'danger',
                'titulo' => 'Solicitud inválida',
                'mensaje' => 'Token CSRF inválido.'
            ];
            header('Location: /login', true, 303);
            exit;
        }

        $login = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['pass'] ?? '');

        if ($login === '' || $pass === ''){
            $errores[] = 'Debe rellenar todos los campos.';
        }
        if(!filter_var($login, FILTER_VALIDATE_EMAIL)){
            $errores[] = "El email no es valido.";
        }
        if(!(UsersModel::existsByEmail($login))){
            $errores[] = "El usuario debe estar registrado en la app con anterioridad.";
        }

        if(!empty($errores)){
            $_SESSION['flash']= [
                'tipo' => 'danger',
                'titulo' => 'Error de login',
                'mensaje' => implode('<br>', $errores)
            ];
            header('Location: /login', true, 303);
            exit;
        }

        $usuario = UsersModel::getUsuario($login);

        if ($usuario && password_verify($pass, $usuario['pass'])){
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            // (opcional pero recomendable)
            $_SESSION['csrf'] = bin2hex(random_bytes(16));

            header('Location: /agenda', true, 303);
            exit;
        } else {
            $_SESSION['flash']= [
                'tipo' => 'danger',
                'titulo' => 'Error de login',
                'mensaje' => 'Credenciales incorrectas.'
            ];
            header('Location: /login', true, 303);
            exit;
        }
    }

    $title = 'Login';
    include VIEWS_PATH . '/login.php';
    }
}