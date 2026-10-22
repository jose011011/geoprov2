<?php
require __DIR__ . '/../layouts/header.php';

$total = 0.0;
foreach ($solicitudes as $s) { $total += (float) $s['precio_acordado']; }
$cantidad = count($solicitudes);
$promedio = $cantidad ? $total / $cantidad : 0;
?>

<div class="container py-4" style="max-width: 960px;">
    <h1 class="fx-page-title h3 mb-3">Historial de trabajos</h1>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="fx-stat"><div class="num"><?= $cantidad ?></div><small>Trabajos finalizados</small></div></div>
        <div class="col-md-4"><div class="fx-stat"><div class="num">Bs <?= number_format($total, 0) ?></div><small>Total cobrado</small></div></div>
        <div class="col-md-4"><div class="fx-stat"><div class="num">Bs <?= number_format($promedio, 0) ?></div><small>Promedio por trabajo</small></div></div>
    </div>

    <?php if (!$solicitudes): ?>
        <div class="text-center text-muted py-5"><i class="fa-regular fa-clipboard fs-1 d-block mb-2"></i>Todavía no finalizaste ningún trabajo.</div>
    <?php else: ?>
        <div class="card"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Fecha</th><th>Cliente</th><th>Zona</th><th class="text-end">Cobro</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($solicitudes as $s): $fecha = $s['fecha_finalizacion'] ?? $s['fecha_solicitud']; ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d/m/Y', strtotime($fecha)) ?><div class="small text-muted"><?= Security::e($s['codigo_seguimiento']) ?></div></td>
                        <td><?= Security::e($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></td>
                        <td class="small text-muted"><?= Security::e($s['zona']) ?></td>
                        <td class="text-end fw-semibold">Bs <?= number_format((float) $s['precio_acordado'], 2) ?></td>
                        <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/solicitud/detalle/<?= (int) $s['id_solicitud'] ?>">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>