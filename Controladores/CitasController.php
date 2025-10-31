<?php
require_once __DIR__ . '/../Modelos/CitasModel.php';

class CitasController{
    public function index(){
      $uid = $_SESSION['usuario_id'];
      $monday = date('Y-m-d', strtotime('monday this week'));
      $sunday = date('Y-m-d', strtotime('sunday this week'));
      $citas = CitasModel::getAgenda($monday, $sunday, $uid);
      require VIEWS_PATH . '/agenda.php';
    }
    public function agendaJson(){
      header('Content-Type: application/json');
      $uid = $_SESSION['usuario_id'];
      $from = $_GET['from'] ?? date('Y-m-d');
      $to = $_GET['to'] ?? date('Y-m-d');
      echo json_encode (CitasModel::getAgenda($from, $to, $uid));
    }
    public function citas($id){
        $citas = CitasModel::getByUsuario($id);
        require VIEWS_PATH . '/citas.php';
    }
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
}