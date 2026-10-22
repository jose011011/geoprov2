<?php
require __DIR__ . '/../layouts/header.php';

$r    = $datos['resumen'];
$ing  = $datos['ingresos'];
$cal  = $datos['calidad'];
$tasa = (int) $r['creadas'] > 0 ? round($r['finalizadas'] * 100 / $r['creadas']) : 0;
$fecha = fn(string $f) => date('d/m/Y', strtotime($f));
$nombreCategoria = 'Todas las categorías';
foreach ($categorias as $c) {
    if ((int) $c['id_categoria'] === $idCategoria) { $nombreCategoria = $c['nombre_categoria']; }
}
$q = '&categoria=' . (int) $idCategoria;
$vinculo = fn(string $d, string $h) => BASE_URL . '/admin/informe?desde=' . $d . '&hasta=' . $h . $q;
$rangos = [
    'Este mes'        => [date('Y-m-01'), date('Y-m-d')],
    'Mes pasado'      => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('first day of last month'))],
    'Últimos 30 días' => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
    'Este año'        => [date('Y-01-01'), date('Y-m-d')],
];
$etiquetaPago = ['PENDIENTE' => 'En revisión', 'CONFIRMADO' => 'Confirmado', 'FALLIDO' => 'Rechazado'];
?>

<style>
@media print {
    @page { size: A4; margin: 14mm; }
    body { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 11px; }
    .fx-navbar, .fx-subnav, .no-print, footer, .alert { display: none !important; }
    main, .container { max-width: 100% !important; padding: 0 !important; }
    .card, .fx-stat { box-shadow: none !important; border: 1px solid #cfd6e4 !important; break-inside: avoid; }
    tr { break-inside: avoid; } h2 { break-after: avoid; }
}
.fx-informe-cabecera { border-bottom: 3px solid var(--fx-blue); }
</style>

<div class="container py-4" style="max-width: 1000px;">

    <div class="no-print mb-4">
        <h1 class="fx-page-title h3 mb-3">Informe del sistema</h1>
        <form method="get" action="<?= BASE_URL ?>/admin/informe" class="row g-2 align-items-end mb-2">
            <div class="col-6 col-md-2"><label class="form-label small text-muted mb-0">Desde</label><input type="date" name="desde" class="form-control" value="<?= Security::e($desde) ?>" max="<?= date('Y-m-d') ?>" required></div>
            <div class="col-6 col-md-2"><label class="form-label small text-muted mb-0">Hasta</label><input type="date" name="hasta" class="form-control" value="<?= Security::e($hasta) ?>" max="<?= date('Y-m-d') ?>" required></div>
            <div class="col-12 col-md-4"><label class="form-label small text-muted mb-0">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['id_categoria'] ?>" <?= $idCategoria === (int) $c['id_categoria'] ? 'selected' : '' ?>><?= Security::e($c['nombre_categoria']) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-6 col-md-2 d-grid"><button class="btn btn-primary">Actualizar</button></div>
            <div class="col-6 col-md-2 d-grid"><button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="fa-solid fa-file-pdf me-1"></i>Descargar PDF</button></div>
        </form>
        <div class="d-flex flex-wrap gap-2 align-items-center small">
            <?php foreach ($rangos as $texto => [$d, $h]): ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $vinculo($d, $h) ?>"><?= $texto ?></a>
            <?php endforeach; ?>
            <span class="text-muted ms-2">Excel:</span>
            <a class="btn btn-sm btn-link" href="<?= BASE_URL ?>/admin/informeCsv?tipo=solicitudes&desde=<?= $desde ?>&hasta=<?= $hasta . $q ?>"><i class="fa-solid fa-file-csv me-1"></i>Solicitudes</a>
            <a class="btn btn-sm btn-link" href="<?= BASE_URL ?>/admin/informeCsv?tipo=pagos&desde=<?= $desde ?>&hasta=<?= $hasta . $q ?>"><i class="fa-solid fa-file-csv me-1"></i>Pagos</a>
        </div>
        <div class="alert alert-info mt-3 small mb-0"><i class="fa-solid fa-circle-info me-1"></i>Pulsa <strong>Descargar PDF</strong> y en la ventana de impresión elige <strong>"Guardar como PDF"</strong> como destino.</div>
    </div>

    <div id="informe">
        <div class="fx-informe-cabecera d-flex flex-wrap justify-content-between align-items-end gap-2 pb-2 mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="fx-brand-mark"><i class="fa-solid fa-location-dot"></i></span>
                <div>
                    <div class="fs-3 fw-bold lh-1">Fix<span style="color:var(--fx-blue)">Geo</span> · Informe del sistema</div>
                    <div class="text-muted">Periodo: <strong><?= $fecha($desde) ?></strong> al <strong><?= $fecha($hasta) ?></strong> · Categoría: <strong><?= Security::e($nombreCategoria) ?></strong></div>
                </div>
            </div>
            <div class="text-end small text-muted">Generado el <?= date('d/m/Y H:i') ?><br>por <?= Security::e($generadoPor) ?></div>
        </div>

        <h2 class="h5 fw-bold mb-2">1. Resumen</h2>
        <div class="row g-2 mb-3">
            <?php foreach ([
                [(int) $r['creadas'],                                              'Solicitudes creadas'],
                [(int) $r['finalizadas'],                                          'Finalizadas (' . $tasa . '%)'],
                [(int) $r['canceladas'],                                           'Canceladas'],
                ['Bs ' . number_format((float) $datos['cobrado']['monto'], 0),     'Cobrado por los técnicos'],
                ['Bs ' . number_format((float) $ing['total'], 0),                  $idCategoria ? 'Pagos de técnicos de la categoría' : 'Ingresos de FixGeo'],
                [($datos['altas']['clientes'] === null ? '—' : (int) $datos['altas']['clientes']) . ' / ' . (int) $datos['altas']['tecnicos'], 'Clientes / técnicos nuevos'],
            ] as [$valor, $texto]): ?>
                <div class="col-4"><div class="fx-stat"><div class="num" style="font-size:1.4rem"><?= $valor ?></div><small><?= $texto ?></small></div></div>
            <?php endforeach; ?>
        </div>
        <p class="small text-muted mb-4">
            Pagos confirmados: Bs <?= number_format((float) $ing['planes'], 2) ?> por planes y Bs <?= number_format((float) $ing['tokens'], 2) ?> por tokens sueltos (<?= (int) $ing['pagos'] ?> pagos).
            Calidad: <?= (int) $cal['total'] > 0
                ? number_format((float) $cal['general'], 1) . ' / 5 de valoración general, ' . number_format((float) $cal['puntualidad'], 1) . ' de puntualidad y ' . number_format((float) $cal['calidad'], 1) . ' de calidad del trabajo (' . (int) $cal['total'] . ' calificaciones).'
                : 'sin calificaciones en este periodo.' ?>
            <?php if ($idCategoria): ?> Los clientes nuevos no se filtran por categoría.<?php endif; ?>
        </p>

        <div class="row g-4 mb-4">
            <div class="col-6">
                <h2 class="h5 fw-bold mb-2">2. Solicitudes por estado</h2>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Estado</th><th class="text-end">Cantidad</th></tr></thead>
                    <tbody>
                    <?php foreach ($datos['estados'] as $estado => $total): ?>
                        <tr><td><?= Catalogo::etiquetaEstado($estado) ?></td><td class="text-end"><?= (int) $total ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$datos['estados']): ?><tr><td colspan="2" class="text-muted">Sin solicitudes en el periodo.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-6">
                <h2 class="h5 fw-bold mb-2">3. Por macrodistrito</h2>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Zona</th><th class="text-end">Solicitudes</th></tr></thead>
                    <tbody>
                    <?php foreach ($datos['porMacro'] as $m): ?>
                        <tr><td><?= Security::e(Catalogo::macroEtiqueta($m['macrodistrito'])) ?></td><td class="text-end"><?= (int) $m['total'] ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$datos['porMacro']): ?><tr><td colspan="2" class="text-muted">Sin datos.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 class="h5 fw-bold mb-2">4. Por categoría de servicio</h2>
        <table class="table table-sm mb-4">
            <thead><tr><th>Categoría</th><th class="text-end">Solicitudes</th><th class="text-end">Finalizadas</th><th class="text-end">Cobrado (Bs)</th></tr></thead>
            <tbody>
            <?php foreach ($datos['porCategoria'] as $c): ?>
                <tr><td><?= Security::e($c['nombre_categoria']) ?></td><td class="text-end"><?= (int) $c['solicitudes'] ?></td><td class="text-end"><?= (int) $c['finalizadas'] ?></td><td class="text-end"><?= number_format((float) $c['cobrado'], 2) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$datos['porCategoria']): ?><tr><td colspan="4" class="text-muted">Sin datos.</td></tr><?php endif; ?>
            </tbody>
        </table>

        <h2 class="h5 fw-bold mb-2">5. Técnicos destacados</h2>
        <table class="table table-sm mb-4">
            <thead><tr><th>Técnico</th><th>Especialidad</th><th class="text-end">Trabajos</th><th class="text-end">Cobrado (Bs)</th><th class="text-end">Valoración</th></tr></thead>
            <tbody>
            <?php foreach ($datos['top'] as $t): ?>
                <tr><td><?= Security::e($t['nombre'] . ' ' . $t['apellido']) ?></td><td><?= Security::e($t['nombre_categoria']) ?></td><td class="text-end"><?= (int) $t['trabajos'] ?></td><td class="text-end"><?= number_format((float) $t['cobrado'], 2) ?></td><td class="text-end"><?= number_format((float) $t['estrellas'], 1) ?> / 5</td></tr>
            <?php endforeach; ?>
            <?php if (!$datos['top']): ?><tr><td colspan="5" class="text-muted">No hubo trabajos finalizados en el periodo.</td></tr><?php endif; ?>
            </tbody>
        </table>

        <h2 class="h5 fw-bold mb-2">6. Pagos del periodo <small class="text-muted fw-normal">(hasta 100)</small></h2>
        <table class="table table-sm mb-4">
            <thead><tr><th>Fecha</th><th>Técnico</th><th>Compra</th><th class="text-end">Monto (Bs)</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($datos['pagos'] as $p): ?>
                <tr>
                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($p['fecha_pago'])) ?></td>
                    <td><?= Security::e($p['nombre'] . ' ' . $p['apellido']) ?></td>
                    <td><?= $p['tipo_transaccion'] === 'PAQUETE_TOKENS' ? (int) $p['tokens_comprados'] . ' tokens' : 'Plan ' . Security::e(Catalogo::nombrePlan($p['nombre_plan'] ?? '')) ?></td>
                    <td class="text-end"><?= number_format((float) $p['monto'], 2) ?></td>
                    <td><?= $etiquetaPago[$p['estado_pago']] ?? Security::e($p['estado_pago']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$datos['pagos']): ?><tr><td colspan="5" class="text-muted">Sin pagos en el periodo.</td></tr><?php endif; ?>
            </tbody>
        </table>

        <div class="text-center small text-muted border-top pt-2">FixGeo · Servicios técnicos en La Paz y El Alto · Documento generado automáticamente por el sistema</div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>