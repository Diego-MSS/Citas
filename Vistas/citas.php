<?php ob_start(); ?>

<h1 class='mb-3 text text-center'>Todas mis citas</h1>
<button class="btn btn-primary btn-sm" onclick="abrirAgendar()">+ Nueva cita</button>

<?php if(empty($citas)):?>
    <div class="alert alert-danger">
        <span class="text text-center mb-3">No tienes citas.</span>
    </div>
<?php else: ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Fecha</th>
                <th>Asunto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach($citas as $cita): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($cita['fecha']))) ?></td>
                        <td><?= htmlspecialchars($cita['asunto']) ?></td>
                        <td><?= htmlspecialchars($cita['estado']) ?></td>
                        <td>
                            <?php if ($cita['estado'] !== 'CANCELADA'): ?>
                                <form method="POST" action="/citas/cancelar" class="d-inline">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$cita['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Anular esta cita?');">
                                    Anular
                                </button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-danger">CANCELADA</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
        <tbody>
    </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';