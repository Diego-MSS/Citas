<?php
require_once __DIR__ . '/../Modelos/CitasModel.php';

class CitasController{
    public function index(){

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
}