<?php
require __DIR__ . '/../layouts/header.php';
$nombresMes = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
$maxIng  = max(1, ...array_column($meses, 'ingresos'));
$maxCat  = max(1, ...array_map(fn($c) => (int) $c['total'], $porCategoria ?: [['total' => 1]]));
?>

<div class="container py-4" style="max-width: 1000px;">
    <h1 class="fx-page-title h3 mb-3">Reportes</h1>

    <h2 class="h5 fw-bold mb-2">Últimos 6 meses</h2>
    <div class="card mb-4"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Mes</th><th class="text-center">Solicitudes</th><th class="text-center">Finalizadas</th><th class="text-end">Servicios cobrados (Bs)</th><th style="width:28%">Ingresos FixGeo (Bs)</th></tr></thead>
            <tbody>
            <?php foreach (array_reverse($meses) as $m): [$anio, $mes] = explode('-', $m['periodo']); ?>
                <tr>
                    <td class="fw-semibold"><?= $nombresMes[$mes] ?? $mes ?> <?= $anio ?></td>
                    <td class="text-center"><?= $m['creadas'] ?></td>
                    <td class="text-center"><?= $m['finalizadas'] ?></td>
                    <td class="text-end"><?= number_format($m['volumen'], 0) ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;"><div class="progress-bar" style="width: <?= round($m['ingresos'] * 100 / $maxIng) ?>%"></div></div>
                            <strong class="small"><?= number_format($m['ingresos'], 0) ?></strong>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>

    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h5 fw-bold mb-2">Técnicos con más trabajos</h2>
            <div class="card"><div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Técnico</th><th class="text-center">Trabajos</th><th class="text-end">Cobrado (Bs)</th></tr></thead>
                    <tbody>
                    <?php foreach ($top as $t): ?>
                        <tr>
                            <td><div class="fw-semibold"><?= Security::e($t['nombre'] . ' ' . $t['apellido']) ?></div>
                                <div class="small text-muted"><?= Security::e($t['nombre_categoria']) ?> · <?= Catalogo::estrellas((float) $t['estrellas']) ?></div></td>
                            <td class="text-center"><?= (int) $t['trabajos'] ?></td>
                            <td class="text-end"><?= number_format((float) $t['cobrado'], 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$top): ?><tr><td colspan="3" class="text-center text-muted py-4">Aún no hay trabajos finalizados.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="col-lg-5">
            <h2 class="h5 fw-bold mb-2">Solicitudes por categoría</h2>
            <div class="card"><div class="card-body">
                <?php foreach ($porCategoria as $c): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1"><span><?= Security::e($c['nombre_categoria']) ?></span><strong><?= (int) $c['total'] ?></strong></div>
                        <div class="progress" style="height:8px;"><div class="progress-bar" style="width: <?= round((int) $c['total'] * 100 / $maxCat) ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>