<?php ob_start(); ?>

<h2 class="mb-3">Agenda pública del centro</h2>
<p class="text-muted mb-3">
  Aquí puedes ver las horas <strong>libres</strong> y <strong>ocupadas</strong> del centro de forma anónima.
</p>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="small text-muted" id="rangoSemana"></div>
  <div class="btn-group">
    <button class="btn btn-outline-secondary btn-sm" id="btnPrev">← Semana</button>
    <button class="btn btn-outline-secondary btn-sm" id="btnToday">Hoy</button>
    <button class="btn btn-outline-secondary btn-sm" id="btnNext">Semana →</button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-bordered align-middle text-center" id="tablaAgendaPublica">
    <thead class="table-light">
      <tr id="headerRow">
        <th style="width:110px;">Hora</th>
        <th data-day="0">Lunes</th>
        <th data-day="1">Martes</th>
        <th data-day="2">Miércoles</th>
        <th data-day="3">Jueves</th>
        <th data-day="4">Viernes</th>
      </tr>
    </thead>
    <tbody id="bodyAgenda"></tbody>
  </table>
</div>

<script>
(function () {
  function ymd(date) {
    const d = new Date(date);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  function fmtES(dateISO) {
    const [y, m, d] = dateISO.split('-');
    return `${d}/${m}/${y}`;
  }

  function getMonday(date = new Date()) {
    const d = new Date(date);
    const day = d.getDay(); // 0=Dom,1=Lun,...6=Sáb
    const diff = (day === 0 ? -6 : 1 - day);
    d.setDate(d.getDate() + diff);
    d.setHours(0,0,0,0);
    return d;
  }

  function buildTimes() {
    const out = [];
    let h = 9, min = 0;
    while (true) {
      out.push(`${String(h).padStart(2,'0')}:${String(min).padStart(2,'0')}`);
      if (h === 17 && min === 30) break;
      min += 30;
      if (min === 60) { min = 0; h++; }
    }
    return out;
  }

  const TIMES = buildTimes();
  let currentMonday = getMonday(new Date());

  function renderSkeleton() {
    const tbody = document.getElementById('bodyAgenda');
    tbody.innerHTML = '';

    TIMES.forEach(time => {
      const tr = document.createElement('tr');

      const th = document.createElement('th');
      th.textContent = time;
      tr.appendChild(th);

      for (let col = 0; col < 5; col++) {
        const td = document.createElement('td');
        td.dataset.dayIndex = String(col);
        td.dataset.time = time;
        td.textContent = 'Cargando...';
        td.className = 'text-muted';
        tr.appendChild(td);
      }

      tbody.appendChild(tr);
    });
  }

  function renderHeaderDates() {
    const ths = document.querySelectorAll('#headerRow th[data-day]');

    const start = new Date(currentMonday);
    const end = new Date(currentMonday);
    end.setDate(end.getDate() + 4);

    document.getElementById('rangoSemana').textContent =
      `Semana: ${fmtES(ymd(start))} - ${fmtES(ymd(end))}`;

    ths.forEach(th => {

      if(!th.dataset.base)th.dataset.base = th.textContent.trim();


      const idx = parseInt(th.dataset.day, 10);
      const d = new Date(currentMonday);
      d.setDate(d.getDate() + idx);
      const dateISO = ymd(d);

      th.innerHTML = `${th.dataset.base}<br><small class="text-muted">${fmtES(dateISO)}</small>`;
    });
  }

  function paintCell(td, status) {
    td.className = 'fw-semibold';
    if (status === 'occupied') {
      td.classList.add('bg-danger','text-white');
      td.textContent = 'Ocupada';
    } else if (status === 'past') {
      td.className = 'bg-light text-muted';
      td.textContent = 'Pasada';
    } else {
      td.classList.add('bg-success','text-white');
      td.textContent = 'Libre';
    }
  }

  async function loadWeek() {
    renderHeaderDates();

    // 1 petición para toda la semana (si tu backend lo soporta)
    const from = ymd(currentMonday);
    const toDate = new Date(currentMonday);
    toDate.setDate(toDate.getDate() + 4);
    const to = ymd(toDate);

    const r = await fetch(`/api/agenda-publica?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
    const payload = await r.json();
    const occupied = payload.occupied || [];

    // Map "YYYY-MM-DD|HH:MM" => true
    const occSet = new Set(occupied.map(x => `${x.fecha}|${(x.time||'').slice(0,5)}`));

    // Hoy para "pasadas"
    const now = new Date();
    const todayISO = ymd(now);

    for (let dayIndex = 0; dayIndex < 5; dayIndex++) {
      const d = new Date(currentMonday);
      d.setDate(d.getDate() + dayIndex);
      const dateISO = ymd(d);

      TIMES.forEach(time => {
        const td = document.querySelector(`#bodyAgenda td[data-day-index="${dayIndex}"][data-time="${time}"]`);
        if (!td) return;

        const key = `${dateISO}|${time}`;
        const isOccupied = occSet.has(key);

        // Pasada solo si es hoy y la hora ya pasó
        let isPast = false;
        if (dateISO === todayISO) {
          const [hh, mm] = time.split(':').map(Number);
          const slotDT = new Date(now);
          slotDT.setHours(hh, mm, 0, 0);
          isPast = slotDT < now;
        }

        if (isPast) paintCell(td, 'past');
        else if (isOccupied) paintCell(td, 'occupied');
        else paintCell(td, 'free');
      });
    }
  }

  document.getElementById('btnPrev').addEventListener('click', () => {
    currentMonday.setDate(currentMonday.getDate() - 7);
    loadWeek();
  });

  document.getElementById('btnNext').addEventListener('click', () => {
    currentMonday.setDate(currentMonday.getDate() + 7);
    loadWeek();
  });

  document.getElementById('btnToday').addEventListener('click', () => {
    currentMonday = getMonday(new Date());
    loadWeek();
  });

  // INIT
  renderSkeleton();
  loadWeek();

})();
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';
?>
