<?php ob_start(); ?>
<h2 class="mb-3">Buscar usuarios</h2>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-8">
    <input type="text" class="form-control" name="q" placeholder="Nombre o correo"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
  </div>
  <div class="col-md-4">
    <button class="btn btn-primary">Buscar</button>
  </div>
</form>

<?php if (isset($resultados)): ?>
  <?php if (empty($resultados) && ($q !== '')): ?>
    <div class="alert alert-warning">No se encontraron usuarios.</div>
  <?php elseif (!empty($resultados)): ?>
    <div class="list-group">
      <?php foreach ($resultados as $u): ?>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
           href="/agenda-publica?user=<?= (int)$u['id'] ?>">
          <span><?= htmlspecialchars($u['nombre']) ?></span>
          <span class="badge bg-secondary">Ver agenda</span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/layout/main.php'; ?>
