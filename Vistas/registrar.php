<?php ob_start(); ?>

<h1 class="mb-4">Crear cuenta</h1>

<?php if (!empty($errores)): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/registrar" class="mt-3">
  <div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input
      type="text"
      class="form-control"
      id="nombre"
      name="nombre"
      value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
      required
    >
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Correo electrónico</label>
    <input
      type="email"
      class="form-control"
      id="email"
      name="email"
      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
      required
    >
  </div>

  <div class="mb-3">
    <label for="pass" class="form-label">Contraseña</label>
    <input
      type="password"
      class="form-control"
      id="pass"
      name="pass"
      required
    >
  </div>

  <div class="mb-3">
    <label for="confirmar" class="form-label">Confirmar contraseña</label>
    <input
      type="password"
      class="form-control"
      id="confirmar"
      name="confirmar"
      required
    >
  </div>
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">

  <button type="submit" class="btn btn-primary w-100">Registrarse</button>
</form>

<p class="mt-3 text-center">
  ¿Ya tienes una cuenta? <a href="/login">Inicia sesión aquí</a>.
</p>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';
