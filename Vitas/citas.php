<?php ob_start(); ?>

<h1 class='mb-3'>Todas mis citas</h1>

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
        <?php if (empty($citas)):?>
            <tr><td colspan="4" class="text text-center"> No tienes citas.</td></tr>
        <?php else: ?>
            <?php foreach($citas as $cita): ?>
                <tr>
                    <td><?= htmlspecialchars(substr($c['hora'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) ?></td>
                    <td><?= htmlspecialchars($c['asunto']) ?></td>
                    <td><?= htmlspecialchars($c['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <tbody>
</table>
<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layout/main.php';