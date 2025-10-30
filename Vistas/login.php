<?php ob_start() ?>
 <h1 class="mb-4">Log in</h1>
 <?php if(!empty($errores)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach($errores as $error): ?>
                <li>
                    <?= htmlspecialchars($error)?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method= "POST" action= "/login" class="mb-3">
    <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input 
        type="email"
        class="form-control"
        id ="email"
        name="email"
        value="<?php htmlspecialchars($_POST['email'] ?? '')?>"
        require>
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

  <button type="submit" class="btn btn-primary w-100">Entrar</button>
</form>

<p class="mt-3 text-center">
  ¿No tienes cuenta? <a href="/registrar">Registrese aquí</a>.
</p>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';