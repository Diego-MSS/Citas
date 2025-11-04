<?php ob_start(); ?>

<h1 class='mb-3 text text-center'>Todas mis citas</h1>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Estado</label>
    <select class="form-select" name="estado">
      <option value="">Todos</option>
      <?php foreach (['RESERVADA','CANCELADA','COMPLETADA'] as $est): ?>
        <option value="<?= $est ?>" <?= (($_GET['estado'] ?? '')===$est)?'selected':'' ?>><?= $est ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Buscar asunto</label>
    <input type="text" class="form-control" name="q" placeholder="p. ej. revisión" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </div>
  <div class="col-12 d-flex gap-2 mt-2">
    <button class="btn btn-primary">Filtrar</button>
    <a class="btn btn-outline-secondary" href="/citas">Limpiar</a>
    <button type="button" class="btn btn-success ms-auto" onclick="abrirAgendar()">+ Nueva cita</button>
  </div>
</form>

<?php if(empty($citas)):?>
    <div class="alert alert-danger">
        <span class="text text-center mb-3">No tienes citas.</span>
    </div>
<?php else: ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Fecha</th>
                <th>Asunto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach($citas as $cita): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($cita['fecha']))) ?></td>
                        <td><?= htmlspecialchars($cita['asunto']) ?></td>
                        <td><?= htmlspecialchars($cita['estado']) ?></td>
                        <td>
                            <?php if ($cita['estado'] !== 'CANCELADA'): ?>
                                <form method="POST" action="/citas/cancelar" class="d-inline">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$cita['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Anular esta cita?');">
                                    Anular
                                </button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-danger">CANCELADA</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
        <tbody>
    </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';