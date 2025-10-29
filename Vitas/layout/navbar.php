
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= $isLoggedIn ? 'ver-reservas.php' : 'login.php' ?>">TurnoFácil</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link <?= $current == 'ver-reservas.php' ? 'active' : '' ?>" href="ver-reservas.php">Citas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current == 'mis-servicios.php' ? 'active' : '' ?>" href="mis-servicios.php">Servicios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current == 'horario.php' ? 'active' : '' ?>" href="horario.php">Horario</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current == 'clientes.php' ? 'active' : '' ?>" href="clientes.php">Clientes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php">Cerrar sesión</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link <?= $current == 'login.php' ? 'active' : '' ?>" href="login.php">Iniciar sesión</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>