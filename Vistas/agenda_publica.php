<?php ob_start(); ?>
<h2 class="mb-3">Agenda pública</h2>

<form method="GET" class="row g-2 mb-3">
  <input type="hidden" name="user" value="<?= (int)($_GET['user'] ?? 0) ?>">
  <div class="col-md-4">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
  </div>
  <div class="col-md-4 d-flex align-items-end">
    <button class="btn btn-primary">Aplicar</button>
  </div>
</form>

<?php if (empty($_GET['user'])): ?>
  <div class="alert alert-info">Busca un usuario y entra desde “Ver agenda”.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>Fecha</th><th>Hora</th><th>Asunto</th><th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($citas)): ?>
          <tr><td colspan="4" class="text-muted">No hay citas en el rango seleccionado.</td></tr>
        <?php else: foreach ($citas as $c): ?>
          <tr>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) ?></td>
            <td><?= htmlspecialchars($c['time']) ?></td>
            <td><?= htmlspecialchars($c['asunto']) ?></td>
            <td>
              <?php $color = $c['estado']==='CANCELADA'?'danger':($c['estado']==='COMPLETADA'?'success':'secondary'); ?>
              <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($c['estado']) ?></span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/layout/main.php'; ?>
