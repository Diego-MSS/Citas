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

    $db = DB::getInstance();

    // Obtenemos TODOS los slots y vemos cuáles están ocupados en esa fecha
    // Tabla slot: id, time (HH:MM:SS)
    // Tabla cita: usuario, fecha (YYYY-MM-DD), hora (HH:MM:SS)
    $sql = "
      SELECT s.hora, s.id as slot_id,
             CASE WHEN c.hora IS NULL THEN 1 ELSE 0 END AS available
      FROM slots s
      LEFT JOIN cita c
        ON c.hora = s.id AND c.fecha = :fecha
      ORDER BY s.hora ASC
    ";
    $st = $db->prepare($sql);
    $st->execute([':fecha' => $fecha]);

    $out = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $out[] = [
            'slot_id' => (int)$row['slot_id'],
          'time' => $row['hora'],
          'available' => (bool)$row['available'],
        ];
    }
    echo json_encode($out);
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

    $db = DB::getInstance();

    // Verifica que la hora exista como slot y esté libre
    $st = $db->prepare("SELECT COUNT(*) FROM slots WHERE id = :id");
    $st->execute([':id' => $slotId]);
    if (!$st->fetchColumn()) $errores[] = 'La hora seleccionada no está disponible';

    $st = $db->prepare("SELECT COUNT(*) FROM cita WHERE fecha = :f AND hora = :t");
    $st->execute([':f' => $fecha, ':t' => $slotId]);
    if ($st->fetchColumn()) $errores[] = 'La hora ya está ocupada';

    if ($errores) {
        $_SESSION['errores'] = $errores;
        header('Location: /agenda'); exit;
    }

    // Insertar cita
    $ins = $db->prepare("
      INSERT INTO cita (usuario, fecha, hora, asunto, estado)
      VALUES (:u, :f, :t, :a, 'RESERVADA')
    ");
    $ins->execute([
      ':u' => $usuarioId,
      ':f' => $fecha,
      ':t' => $slotId,
      ':a' => $asunto,
    ]);

    header('Location: /citas'); exit;
}
}