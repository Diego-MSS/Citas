<?php ob_start(); ?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">Tu salud, siempre bajo control</h1>
        <p class="lead text-muted">
          Consulta tus próximas citas médicas, gestiona tus recordatorios y lleva el control de tus visitas al centro de salud desde una sola aplicación.
        </p>

        <div class="d-flex gap-3 mt-4">
          <a href="/registrar" class="btn btn-primary btn-lg">Crear cuenta</a>
          <a href="/login" class="btn btn-outline-secondary btn-lg">Iniciar sesión</a>
        </div>

        <p class="small mt-3 text-muted">
          ¿Tienes una cita pendiente? <a href="/buscar">Consulta aquí</a> con tu código de cita.
        </p>
      </div>

      <div class="col-lg-6 text-center">
        <img src="/assets/img/landing-illustration.svg" alt="Gestión de citas médicas" class="img-fluid rounded shadow-sm">
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-4">Todo lo que necesitas en una sola app</h2>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="mb-3 text-primary fs-2">📅</div>
            <h3 class="h5">Consulta tus citas</h3>
            <p class="text-muted mb-0">Visualiza tus próximas citas médicas con fecha, hora y centro de salud asignado.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="mb-3 text-success fs-2">🔔</div>
            <h3 class="h5">Recibe recordatorios</h3>
            <p class="text-muted mb-0">Activa notificaciones para no olvidar tus próximas visitas médicas o revisiones periódicas.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="mb-3 text-info fs-2">📈</div>
            <h3 class="h5">Historial de citas</h3>
            <p class="text-muted mb-0">Lleva el control de tus visitas anteriores y consulta los resultados de cada una.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <img src="/assets/img/agenda-preview.png" alt="Agenda de citas" class="img-fluid rounded shadow-sm">
      </div>
      <div class="col-lg-6">
        <h2 class="h3 mb-3">Organiza tu agenda médica fácilmente</h2>
        <p class="text-muted mb-4">
          Añade nuevas citas, cancela o reprograma tus visitas médicas desde tu perfil personal. 
          Todo sincronizado con tu centro de salud.
        </p>
        <a href="/registrar" class="btn btn-primary">Empieza ahora</a>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container text-center">
    <h2 class="mb-3">¿Cómo funciona?</h2>
    <div class="row justify-content-center g-4 mt-4">
      <div class="col-md-3">
        <div class="p-3 border rounded h-100">
          <div class="fs-2 mb-2 text-primary">🧑‍💻</div>
          <h3 class="h6">1. Crea tu cuenta</h3>
          <p class="text-muted small">Regístrate con tu correo electrónico y accede a tu panel personal.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 border rounded h-100">
          <div class="fs-2 mb-2 text-success">📅</div>
          <h3 class="h6">2. Consulta tus citas</h3>
          <p class="text-muted small">Visualiza tus próximas citas y consulta la información de cada una.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 border rounded h-100">
          <div class="fs-2 mb-2 text-info">🔔</div>
          <h3 class="h6">3. Recibe recordatorios</h3>
          <p class="text-muted small">Activa notificaciones para no perder ninguna cita importante.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 text-center bg-primary text-white">
  <div class="container">
    <h2 class="h3 mb-3">Tu salud al alcance de un clic</h2>
    <p class="mb-4 text-white-50">Regístrate gratis y controla tus citas médicas fácilmente.</p>
    <a href="/registrar" class="btn btn-light btn-lg">Comenzar ahora</a>
  </div>
</section>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';
