<?php ob_start(); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Mi agenda semanal</h2>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" id="btnAnterior">← Semana anterior</button>
      <button class="btn btn-outline-secondary btn-sm" id="btnHoy">Hoy</button>
      <button class="btn btn-outline-secondary btn-sm" id="btnSiguiente">Semana siguiente →</button>
      <button class="btn btn-success btn-sm ms-auto" onclick="abrirAgendar()">+ Nueva cita</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center mb-0">
      <thead class="table-light">
        <tr id="diasSemana"><!-- se rellena por JS --></tr>
      </thead>
      <tbody id="citasSemana"><!-- se rellena por JS --></tbody>
    </table>
  </div>

  <div id="msgSinCitas" class="text-center text-muted py-4 d-none">
    No tienes citas esta semana.
  </div>
</div>

<script>
  // --- Helpers locales (no dependemos de otros ficheros) ---
  function ymdLocal(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
  }
  function lunesDe(d) {
    const res = new Date(d);
    const day = res.getDay();           // 0=Dom..6=Sáb
    const diff = (day === 0 ? -6 : 1 - day);
    res.setDate(res.getDate() + diff);  // mover al lunes
    res.setHours(0,0,0,0);
    return res;
  }

  // --- Estado ---
  let fechaInicioSemana = lunesDe(new Date());

  document.addEventListener('DOMContentLoaded', () => {
    try {
      renderSemana();
      cargarAgenda();

      document.getElementById('btnAnterior').addEventListener('click', () => moverSemana(-7));
      document.getElementById('btnSiguiente').addEventListener('click', () => moverSemana(+7));
      document.getElementById('btnHoy').addEventListener('click', () => {
        fechaInicioSemana = lunesDe(new Date());
        renderSemana();
        cargarAgenda();
      });
    } catch (e) {
      console.error('Error inicializando agenda:', e);
    }
  });

  function moverSemana(deltaDias) {
    fechaInicioSemana.setDate(fechaInicioSemana.getDate() + deltaDias);
    renderSemana();
    cargarAgenda();
  }

  function renderSemana() {
    const thead = document.getElementById('diasSemana');
    thead.innerHTML = '';
    const opciones = { weekday: 'short', day: 'numeric', month: 'short' };

    for (let i = 0; i < 7; i++) {
      const d = new Date(fechaInicioSemana);
      d.setDate(d.getDate() + i);

      const th = document.createElement('th');
      th.dataset.fecha = ymdLocal(d);
      th.textContent = d.toLocaleDateString('es-ES', opciones);
      thead.appendChild(th);
    }

    // limpia cuerpo mientras llegan datos
    document.getElementById('citasSemana').innerHTML = '';
    document.getElementById('msgSinCitas').classList.add('d-none');
  }

  async function cargarAgenda() {
    const desde = ymdLocal(fechaInicioSemana);
    const hastaD = new Date(fechaInicioSemana);
    hastaD.setDate(hastaD.getDate() + 6);
    const hasta = ymdLocal(hastaD);

    try {
      const res = await fetch(`/api/agenda?from=${desde}&to=${hasta}`);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const citas = await res.json();
      pintarCitasSemana(Array.isArray(citas) ? citas : []);
    } catch (e) {
      console.error('Error cargando agenda:', e);
      pintarCitasSemana([]); // al menos pinta cabecera
    }
  }

  function pintarCitasSemana(citas) {
    const tbody = document.getElementById('citasSemana');
    tbody.innerHTML = '';

    // mapear días visibles
    const dias = Array.from(document.querySelectorAll('#diasSemana th')).map(th => th.dataset.fecha);
    const porDia = {};
    dias.forEach(f => porDia[f] = []);
    citas.forEach(c => { if (porDia[c.fecha]) porDia[c.fecha].push(c); });

    const maxFilas = Math.max(1, ...Object.values(porDia).map(arr => arr.length));

    for (let fila = 0; fila < maxFilas; fila++) {
      const tr = document.createElement('tr');
      dias.forEach(f => {
        const td = document.createElement('td');
        const c = porDia[f][fila];
        if (c) {
          const horaHM = (c.time || '').slice(0,5);
          let color = 'secondary';
          if (c.estado === 'RESERVADA') color = 'primary';
          else if (c.estado === 'CANCELADA') color = 'danger';
          else if (c.estado === 'COMPLETADA') color = 'success';

          if (c.estado === 'CANCELADA'){
            td.innerHTML = `
            <div class="fw-semibold">${horaHM}</div>
            <div class="small text-muted">${c.asunto || ''}</div>
            <span class="badge bg-${color}">${c.estado}</span>
            
          `;
          }else{
            td.innerHTML = `
            <div class="fw-semibold">${horaHM}</div>
            <div class="small text-muted">${c.asunto || ''}</div>
            <span class="badge bg-${color}">${c.estado}</span>
            <div class="mt-2">
              <form method="POST" action="/citas/cancelar" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
                <input type="hidden" name="id" value="${c.id}">
                <button class="btn btn-sm btn-outline-danger onclick="return confirm('¿Seguro que quieres anular esta cita?');"">Anular</button>
              </form>
            </div>
          `;
          }
          
        }
        tr.appendChild(td);
      });
      tbody.appendChild(tr);
    }

    document.getElementById('msgSinCitas').classList.toggle('d-none', citas.length > 0);
  }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';
