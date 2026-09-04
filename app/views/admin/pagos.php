<?php require_once "../app/views/layouts/header.php"; ?>
<div class="container py-4">
    <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver</a>
    <h3 class="mb-4">Pagos Pendientes de Confirmación</h3>

    <?php if (!empty($pagos)): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-value text-warning"><?= count($pagos) ?></div>
                    <div class="kpi-label">Pagos por revisar</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-value text-success">Bs <?= number_format(array_sum(array_column($pagos, 'monto')), 2) ?></div>
                    <div class="kpi-label">Monto total pendiente</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="table mb-0">
            <thead>
                <tr><th>Profesional</th><th>Tipo</th><th>Plan</th><th>Monto</th><th>Comprobante</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
                    <td>
                        <span class="badge bg-<?= $p['tipo_transaccion'] === 'MEMBRESIA_MENSUAL' ? 'primary' : 'info text-white' ?>">
                            <?= $p['tipo_transaccion'] === 'MEMBRESIA_MENSUAL' ? 'Membresía' : 'Tokens' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($p['nombre_plan'] ?? '—') ?></td>
                    <td class="fw-bold">Bs <?= number_format($p['monto'], 2) ?></td>
                    <td><code class="small"><?= htmlspecialchars($p['codigo_comprobante']) ?></code></td>
                    <td class="text-end">
                        <form method="POST" action="<?= BASE_URL ?>/admin/confirmarPago" class="d-inline">
                            <input type="hidden" name="id_transaccion" value="<?= $p['id_transaccion'] ?>">
                            <button class="btn btn-sm btn-success">Confirmar</button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/admin/rechazarPago" class="d-inline">
                            <input type="hidden" name="id_transaccion" value="<?= $p['id_transaccion'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($pagos)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-circle-check"></i>
                <p>No hay pagos pendientes. Todo al día.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once "../app/views/layouts/footer.php"; ?>