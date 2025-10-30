<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['usuario_id']);
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand fw-bold text-primary" href="/">Clínica Salud+</a>

    <!-- Botón para pantallas pequeñas -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCitas" aria-controls="navbarCitas" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCitas">
      <!-- Enlaces principales -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= $_SERVER['REQUEST_URI'] === '/' ? ' active' : '' ?>" href="/">Inicio</a>
        </li>

        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link<?= $_SERVER['REQUEST_URI'] === '/agenda' ? ' active' : '' ?>" href="/agenda">Mi agenda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= $_SERVER['REQUEST_URI'] === '/citas' ? ' active' : '' ?>" href="/citas">Mis citas</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="abrirAgendar(); return false;">Agendar cita</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link<?= $_SERVER['REQUEST_URI'] === '/buscar' ? ' active' : '' ?>" href="/buscar">Buscar citas</a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Acciones usuario -->
      <ul class="navbar-nav ms-auto">
        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              👤 <?= htmlspecialchars($usuarioNombre) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
              <li><a class="dropdown-item" href="/perfil">Ver perfil</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout">Cerrar sesión</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-primary me-2" href="/login">Iniciar sesión</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary" href="/registrar">Registrarse</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
