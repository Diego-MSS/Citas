<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'App de Citas' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="container py-4">
    <?= $content ?? '' ?> 
  </main>

  <?php include __DIR__ . '/footer.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
?>

<!-- Modal Agendar Cita -->
<div class="modal fade" id="modalAgendar" tabindex="-1" aria-labelledby="modalAgendarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- pequeño -->
    <form class="modal-content" id="formAgendar" method="POST" action="/citas/nueva">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgendarLabel">Agendar cita</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <!-- Fecha con calendario -->
        <div class="mb-3">
          <label class="form-label">Fecha</label>
          <input type="text" class="form-control" id="inputFecha" placeholder="Elige una fecha" required>
          <input type="hidden" name="fecha" id="fechaISO"> <!-- YYYY-MM-DD real -->
        </div>

        <!-- Horas -->
        <div class="mb-3">
          <label class="form-label d-block">Hora</label>
          <div id="gridHoras" class="d-grid" style="grid-template-columns: repeat(3, 1fr); gap: .5rem;">
            <!-- aquí se pintan los botones -->
          </div>
          <input type="hidden" name="slot_id" id="slotIdSeleccionado" required>
        </div>

        <!-- Asunto -->
        <div class="mb-2">
          <label class="form-label">Asunto</label>
          <input type="text" class="form-control" name="asunto" id="inputAsunto" placeholder="Motivo de la cita" required>
        </div>

        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnAgendar" disabled>Agendar</button>
      </div>
    </form>
  </div>
</div>


<!-- Flatpickr (calendario) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
  // Abre el modal desde cualquier parte (navbar, botón, etc.)
  function abrirAgendar(fechaPreseleccionada = null) {
    // Reset form
    document.getElementById('formAgendar').reset();
    document.getElementById('gridHoras').innerHTML = '';
    document.getElementById('slotIdSeleccionado').value = '';
    document.getElementById('btnAgendar').disabled = true;

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalAgendar'));
    modal.show();

    // Flatpickr
    if (window._fp) { window._fp.destroy(); }
    window._fp = flatpickr('#inputFecha', {
      locale: 'es',
      dateFormat: 'd/m/Y',
      altInput: false,
      minDate: 'today',
      defaultDate: fechaPreseleccionada || null,
      onChange: function(selectedDates, dateStr, instance) {
        const iso = selectedDates.length ? selectedDates[0].toISOString().slice(0,10) : '';
        document.getElementById('fechaISO').value = iso;
        cargarHoras(iso);
      }
    });

    // Si ya venía fecha preseleccionada (por ejemplo, desde la agenda)
    if (fechaPreseleccionada) {
      const d = new Date(fechaPreseleccionada);
      document.getElementById('fechaISO').value = d.toISOString().slice(0,10);
      cargarHoras(d.toISOString().slice(0,10));
    }
  }

  // Pide al backend los slots del día y pinta la rejilla
  async function cargarHoras(fechaISO) {
  const grid = document.getElementById('gridHoras');
  grid.innerHTML = '<div class="text-center text-muted py-2" style="grid-column: 1 / -1;">Cargando horas...</div>';

  const r = await fetch('/api/slots?date=' + encodeURIComponent(fechaISO));
  const data = await r.json(); // [{slot_id, time, available}, ...]

  grid.innerHTML = '';
  data.forEach(s => {
    const label = s.time.slice(0,5); // "HH:MM"
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn ' + (s.available ? 'btn-outline-primary' : 'btn-outline-secondary');
    btn.textContent = label;
    btn.disabled = !s.available;

    btn.addEventListener('click', () => {
      grid.querySelectorAll('button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('slotIdSeleccionado').value = s.slot_id; // <-- enviamos el FK
      toggleSubmit();
    });

    grid.appendChild(btn);
  });

  toggleSubmit();
}


  function toggleSubmit() {
    const fecha = document.getElementById('fechaISO').value;
    const hora  = document.getElementById('slotIdSeleccionado').value;
    const asunto= document.getElementById('inputAsunto').value.trim();
    document.getElementById('btnAgendar').disabled = !(fecha && hora && asunto);
  }

  document.getElementById('inputAsunto').addEventListener('input', toggleSubmit);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>