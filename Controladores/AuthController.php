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

        //El usuario envia los datos
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
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

                header('Location: /agenda');
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
        $db = DB::getInstance();
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $login = trim($_POST['email'] ?? '');
            $pass = trim($_POST['pass'] ?? '');

            if ($login === '' || $pass === ''){
                $errores[] = 'Debe rellenar todos los campos.';
            }
            if(!filter_var($login,FILTER_VALIDATE_EMAIL)){
                $errores[]="El email no es valido.";
            }
            if(!(UsersModel::existsByEmail($login))){
                $errores[]="El usuario debe estar registrado en la app con anterioridad.";
            }
            $usuario = UsersModel::getUsuario($login);

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
    
    /**
     * Nombre: buscarUsuario()
     * Recibe: Mediante el formulario de busqueda de usuarios, recibe el nombre del usuario a buscar.
     * Devuelve: Nada.
     * Descripcion:
     *      ->Recibe el nombre a buscar,
     *      ->Lanza la consulta con el nombre 
     *      ->Muestra los resultados en la vista.
     */
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