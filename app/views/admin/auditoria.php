<?php require_once "../app/views/layouts/header.php"; ?>
<div class="container py-4">
    <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver</a>
    <h3 class="mb-4">Auditoría del Sistema</h3>
    <table class="table table-sm bg-white">
        <thead><tr><th>Fecha</th><th>Responsable</th><th>Acción</th><th>Tabla</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td class="small"><?= date('d/m/Y H:i', strtotime($log['fecha_evento'])) ?></td>
                <td class="small"><?= htmlspecialchars($log['responsable']) ?></td>
                <td class="small"><span class="badge bg-secondary"><?= htmlspecialchars($log['accion']) ?></span></td>
                <td class="small"><?= htmlspecialchars($log['tabla_afectada']) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($log['ip_origen']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" class="text-center text-muted">Sin registros de auditoría todavía.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once "../app/views/layouts/footer.php"; ?>