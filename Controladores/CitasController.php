<?php
require_once __DIR__ . '/../Modelos/CitasModel.php';
/** Este es el controlador de Citas. Lo que hace que se conecten las paginas que necesiten datos de citas con los propios datos de las citas */

class CitasController{
  /**
   * Nombre: index()
   * Recibe: Nada
   * Devuelve: Nada
   * Descripcion: Saca los parametros de la semana en la que estamos, y el id del usuario que se guarda en $_SESSION al empezar la sesion. Y los envia a traves de la funcion getAgenda del MOdelo de Citas. 
   */
    public function index(){
      $uid = $_SESSION['usuario_id'];
      $monday = date('Y-m-d', strtotime('monday this week'));
      $sunday = date('Y-m-d', strtotime('sunday this week'));
      $citas = CitasModel::getAgenda($monday, $sunday, $uid);
      require VIEWS_PATH . '/agenda.php';
    }

    /**
     * Nombre: agendaJson()
     * Recibe: Los dias donde comienza y termina la semana mediante un metodo GET
     * Devuelve: Nada
     * Descripcion: Es al igual que el index() solo que los guarda en un JSON para que el JavaScript los pueda leer sin tener que volver a lanzar la peticion del GET y asi tener una pagina mas dinamica.
     */
    public function agendaJson(){
      header('Content-Type: application/json');
      $uid = $_SESSION['usuario_id'];
      $from = $_GET['from'] ?? date('Y-m-d');
      $to = $_GET['to'] ?? date('Y-m-d');
      echo json_encode (CitasModel::getAgenda($from, $to, $uid));
    }
    
    /**
     * Nombre: citas()
     * Recibe: la variable $id desde el front-controller. Y los datos de los filtros por los que quiere buscar la cita el usuario mediante un metodo GET
     * Devuelve: Nada
     * Descripcion: recibe el id del usuario y los filtros en un metodo POST para mostrar las citas, o en su totalidad a falta de filtros o las citas que esten dentro del filtro del usuario.
     */

    public function citas($id){
        $fitros = [
          'desde' => $_GET['desde'] ?? '',
          'hasta' => $_GET['hasta'] ?? '',
          'estado' => $_GET['estado'] ?? '',
          'q' => $_GET['q'] ?? ''
        ];
        $citas = CitasModel::getByUsuario($id, $fitros);
        require VIEWS_PATH . '/citas.php';
    }

    /**
     * Nombre: slotsJson()
     * Recibe: La fecha en la que buscar los slots libres mediante un metodo GET
     * Devuelve: Nada
     * Descripcion: Mediante la fecha que seleccione el usuario, esta funcion llama a una funcion preescrita para recibir los parametros de los slots libres. Al fina esta funcion crea un Json para que javascript cree una API a este archivo y que de manera dinamica y sin tener que realizxar mas de una peticion GET pueda mostrar los slots libres.
     */
    public function slotsJson() {
    header('Content-Type: application/json; charset=utf-8');

    $fecha = $_GET['date'] ?? '';
    // Validación simple
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode([]); return;
    }
    $slots = CitasModel::getSlots($fecha);
    echo json_encode($slots);
    exit;
}


/**
 * Nombre: crear()
 * Recibe: Mediante el metodo POST desde el lado del cliente esta funcion recibe los datos para crear una cita.
 * Devuelve: En caso de que se cumpla y se cree, un FLASH para que aparezca un modal con SUCCESS y en caso de que no se cumpla, aparecera un modal con DANGER
 * Descripcion:
 *  ->Comprueba que la sesion esta iniciada y si no la crea. 
 *  ->Comprueba el token csrf para que no se produzcan ataques mediante enlaces externos.
 *  ->Recibe el id del usuario de la variable $_SESSION, la fecha, el slot y el asunto los recibe mediante un metodo POST del formulario de crear la cita.
 *  ->Comprueba que no ha habido ningun error, en cuanto a fecha, hora y que el asunto no se quede vacio.
 *  ->Verifica que la hora esta libre y que este.
 *  ->Si hay errores los muestra 
 *  ->En caso de que no haya errores, crea la cita y lanza el modal SUCCESS.
 */
public function crear() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    // CSRF
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Token CSRF inválido');
    }

    $usuarioId = (int)$_SESSION['usuario_id'];
    $fecha = $_POST['fecha'] ?? '';
    $slotId    = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;
    $asunto= trim($_POST['asunto'] ?? '');
    $errores = [];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $errores[] = 'Fecha inválida';
    if ($slotId <= 0) $errores[] = 'Hora inválida';
    if ($asunto === '') $errores[] = 'Asunto requerido';

    // Verifica que la hora exista como slot y esté libre

    if (!CitasModel::comprobarSlot($slotId)) $errores[] = 'La hora seleccionada no está disponible';
    if (!CitasModel::slotDisponible($slotId, $fecha)) $errores[] = 'La hora ya está ocupada';

    if ($errores) {
          $_SESSION['flash'] =[
            'tipo' => 'danger',
            'titulo' => 'Error al crear la cita',
            'mensaje' => implode('<br>', $errores)
          ];
          header('Location: /citas'); exit;
    }

    // Insertar cita
    CitasModel::crearCita($usuarioId, $fecha, $slotId, $asunto);
    
    $_SESSION['flash']=[
      'tipo' => 'success',
      'titulo' => '¡Cita creada!',
      'mensaje' => 'Tu cita se ha registrado correctamente.'
    ];

    header('Location: /citas'); exit;
  }

  /**
   * Nombre: cancelar()
   * Recibe: Mediante el formulario POST de anular cita, recibe el id de la cita.
   * Devuelve: Un modal flash.
   * Descripcion: 
   *      ->La funcion recibe por el formulario POST el id de la cita.
   *      ->En caso de que la cita no exita, la funcion devuelve un modal de error.
   *      ->En caso de que exita, se hace una llamada al modelo para que se cancele la cita pasandole el id de la cita.
   */
  public function cancelar(){

    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        $_SESSION['flash'] = [
            'tipo' => 'danger',
            'titulo' => 'Acción no permitida',
            'mensaje' => 'Token CSRF inválido.'
        ];
        header('Location: /citas'); exit;
    }

    $userId = $_SESSION['usuario_id'];
    $citaId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if($citaId<=0){
      $_SESSION['flash'] = [
        'tipo' => 'danger',
        'titulo' => 'Error',
        'mensaje' => 'Identificador de la cita invalido.'
      ];
      header('Location: /agenda');
    }
    $res = CitasModel::cancelar($citaId, $userId);
    
    $_SESSION['flash'] = [
      'tipo' => $res['ok'] ? 'success' : 'danger',
      'titulo'=> $res['ok'] ? 'Cita anulada' : 'No se pudo anular',
      'mensaje' => $res['msg']
    ];

    header('Location: /agenda');
    exit;
  }
  
  /**
   * Nombre: agendaPublica()
   * Recibe: Mediante la busqueda de usuarios, recibe el id del usuario al que quiere ver la agenda.
   * Devuelve: Nada.
   * Descripcion: 
   *    ->Se recibe el usuario mediante el formulario get
   *    ->Se guardan los dias del dia en el que estamos, cuando empieza la semana en la que estamos y cuando termina.
   *    ->Se hace una llamada a la funcion getAgenda() con los parametros de la semana para que aparezca en la vista la agenda del usuario.
   */
  public function agendaPublica() {
    // Lee userId y rango (si no hay rango, semana actual)
    $userId = (int)($_GET['user'] ?? 0);

    // Fechas por defecto: semana actual (lunes a domingo)
    $hoy = new DateTime();
    $dow = (int)$hoy->format('N'); // 1=Mon..7=Sun
    $monday = (clone $hoy)->modify('-'.($dow-1).' day');
    $sunday = (clone $monday)->modify('+6 day');

    $from = $_GET['from'] ?? $monday->format('Y-m-d');
    $to   = $_GET['to']   ?? $sunday->format('Y-m-d');

    $citas = [];
    if ($userId > 0) {
        $citas = CitasModel::getAgenda($from, $to, $userId);
    }
    $title = 'Agenda pública';
    include VIEWS_PATH . '/agenda_publica.php';
}

/**
 * Nombre: agendaPublicaJson
 * Recibe: El id del usuario, el dia que empieza la semana, y el dia que termina.
 * Devuelve: Un Json con las citas del usuario.
 * Descripcion: 
 *      ->Saca y guarda en un Json las citas del usuario para que el programa no tenga que hacer muchas peticiones y se sature.
 *      ->Esto hace que la vista sea mas dinamica y que no se sature el servidor con miles de peticiones y sentencias sql.
 * 
 */
public function agendaPublicaJson() {
    header('Content-Type: application/json; charset=utf-8');

    $userId = (int)($_GET['user'] ?? 0);
    $from = $_GET['from'] ?? date('Y-m-d');
    $to   = $_GET['to']   ?? date('Y-m-d');

    if ($userId <= 0) { echo json_encode([]); return; }

    echo json_encode(CitasModel::getAgenda($from, $to, $userId));
}
}