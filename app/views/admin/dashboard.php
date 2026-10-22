<?php
require __DIR__ . '/../layouts/header.php';

$tarjetas = [
    ['clientes',            'fa-users',          'Clientes registrados',  false],
    ['tecnicos_aprobados',  'fa-user-check',     'Técnicos aprobados',    false],
    ['solicitudes_activas', 'fa-bolt',           'Solicitudes en curso',  false],
    ['finalizadas_mes',     'fa-flag-checkered', 'Finalizadas este mes',  false],
    ['ingresos_mes',        'fa-coins',          'Ingresos del mes (Bs)', true],
    ['solicitudes_total',   'fa-clipboard-list', 'Solicitudes totales',   false],
];
$totalEstados = max(1, array_sum($estados));
$colorEstado  = ['PENDIENTE' => 'warning', 'ACEPTADA' => 'info', 'EN_CAMINO' => 'primary', 'EN_PROCESO' => 'primary', 'FINALIZADA' => 'success', 'CANCELADA' => 'secondary'];
?>

<div class="container py-4">
    <h1 class="fx-page-title h3 mb-3">Panel de administración</h1>

        <?php if ((int) $ind['tecnicos_pendientes'] > 0 || (int) $ind['pagos_pendientes'] > 0 || (int) $ind['categorias_pendientes'] > 0): ?>
        <div class="row g-3 mb-4">
            <?php if ((int) $ind['tecnicos_pendientes'] > 0): ?>
                <div class="col-md-6"><a class="fx-quick" href="<?= BASE_URL ?>/admin/revisionTecnicos">
                    <span class="ico"><i class="fa-solid fa-user-clock"></i></span>
                    <span><span class="t d-block"><?= (int) $ind['tecnicos_pendientes'] ?> técnico(s) esperan validación</span><small>Revisar documentos</small></span>
                </a></div>
            <?php endif; ?>
            <?php if ((int) $ind['pagos_pendientes'] > 0): ?>
                <div class="col-md-6"><a class="fx-quick" href="<?= BASE_URL ?>/admin/revisionPagos">
                    <span class="ico"><i class="fa-solid fa-money-check-dollar"></i></span>
                    <span><span class="t d-block"><?= (int) $ind['pagos_pendientes'] ?> pago(s) por confirmar</span><small>Revisar comprobantes</small></span>
                </a></div>
            <?php endif; ?>
                        <?php if ((int) $ind['categorias_pendientes'] > 0): ?>
                <div class="col-md-6"><a class="fx-quick" href="<?= BASE_URL ?>/admin/categorias">
                    <span class="ico"><i class="fa-solid fa-tags"></i></span>
                    <span><span class="t d-block"><?= (int) $ind['categorias_pendientes'] ?> especialidad(es) nueva(s) por aprobar</span><small>Sugeridas por técnicos al registrarse</small></span>
                </a></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <?php foreach ($tarjetas as [$k, $icono, $texto, $dinero]): ?>
            <div class="col-6 col-lg-4"><div class="fx-stat d-flex align-items-center gap-3">
                <span class="fx-quick" style="padding:0;border:0;width:auto;pointer-events:none;"><span class="ico"><i class="fa-solid <?= $icono ?>"></i></span></span>
                <div><div class="num"><?= $dinero ? number_format((float) $ind[$k], 0) : (int) $ind[$k] ?></div><small><?= $texto ?></small></div>
            </div></div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <h2 class="h5 fw-bold mb-2">Solicitudes por estado</h2>
            <div class="card"><div class="card-body">
                <?php if (!$estados): ?><div class="text-muted small">Aún no hay solicitudes.</div><?php endif; ?>
                <?php foreach ($estados as $estado => $total): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1"><span><?= Catalogo::etiquetaEstado($estado) ?></span><strong><?= (int) $total ?></strong></div>
                        <div class="progress" style="height:8px;"><div class="progress-bar bg-<?= $colorEstado[$estado] ?? 'secondary' ?>" style="width: <?= round($total * 100 / $totalEstados) ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div></div>
        </div>

        <div class="col-lg-7">
            <h2 class="h5 fw-bold mb-2">Últimas solicitudes</h2>
            <div class="card"><div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Código</th><th>Cliente</th><th>Técnico</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($ultimas as $s): ?>
                        <tr>
                            <td class="small"><?= Security::e($s['codigo_seguimiento']) ?><div class="text-muted"><?= date('d/m H:i', strtotime($s['fecha_solicitud'])) ?></div></td>
                            <td class="small"><?= Security::e($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?><div class="text-muted"><?= Security::e($s['nombre_categoria'] ?? '') ?></div></td>
                            <td class="small"><?= $s['prof_nombre'] ? Security::e($s['prof_nombre'] . ' ' . $s['prof_apellido']) : '<span class="text-muted">Sin asignar</span>' ?></td>
                            <td><?= Catalogo::badgeEstado($s['estado_servicio']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$ultimas): ?><tr><td colspan="4" class="text-center text-muted py-4">Sin solicitudes todavía.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>