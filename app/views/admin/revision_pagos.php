<?php
require __DIR__ . '/../layouts/header.php';
$tabs = ['PENDIENTE' => 'Pendientes', 'CONFIRMADO' => 'Confirmados', 'FALLIDO' => 'Rechazados', 'TODOS' => 'Todos'];
$claseEstado = ['PENDIENTE' => ['En revisión', 'warning'], 'CONFIRMADO' => ['Confirmado', 'success'], 'FALLIDO' => ['Rechazado', 'danger']];
?>

<div class="container py-4" style="max-width: 1000px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="fx-page-title h3 mb-0">Pagos de técnicos</h1>
        <span class="badge text-bg-warning fs-6"><?= (int) $resumen['pagos'] ?> pendientes</span>
    </div>

    <div class="fx-filter d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($tabs as $k => $t): ?>
            <a href="<?= BASE_URL ?>/admin/revisionPagos?estado=<?= $k ?>" class="btn btn-sm <?= $filtro === $k ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $t ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$pagos): ?>
        <div class="text-center text-muted py-5"><i class="fa-regular fa-circle-check fs-1 d-block mb-2"></i>No hay pagos en esta lista.</div>
    <?php endif; ?>

    <div class="d-grid gap-3">
        <?php foreach ($pagos as $p):
            [$txt, $cls] = $claseEstado[$p['estado_pago']] ?? [$p['estado_pago'], 'secondary'];
            $concepto = $p['tipo_transaccion'] === 'PAQUETE_TOKENS'
                ? (int) $p['tokens_comprados'] . ' tokens (paquete)'
                : 'Plan ' . Catalogo::nombrePlan($p['nombre_plan'] ?? ''); ?>
            <div class="card fx-job <?= $p['estado_pago'] === 'PENDIENTE' ? 'nueva' : ($p['estado_pago'] === 'CONFIRMADO' ? 'fin' : '') ?>"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <div class="fw-bold fs-5"><?= Security::e($p['nombre'] . ' ' . $p['apellido']) ?></div>
                        <div class="small text-muted"><?= Security::e($p['correo']) ?> · <?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge text-bg-<?= $cls ?> mb-1"><?= $txt ?></span>
                        <div class="fw-bold fs-5 text-primary">Bs <?= number_format((float) $p['monto'], 2) ?></div>
                    </div>
                </div>

                <div class="row g-2 my-2 small">
                    <div class="col-md-4"><span class="text-muted">Compra:</span> <strong><?= Security::e($concepto) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted">Método:</span> <?= Security::e($p['metodo_pago']) ?></div>
                    <div class="col-md-4"><span class="text-muted">Código:</span> <strong><?= Security::e($p['codigo_comprobante']) ?></strong></div>
                </div>

                <?php if (!empty($p['foto_comprobante'])): ?>
                    <a class="btn btn-outline-primary btn-sm mb-2" target="_blank" rel="noopener" href="<?= BASE_URL ?>/admin/verComprobante/<?= (int) $p['id_transaccion'] ?>"><i class="fa-regular fa-image me-1"></i>Ver comprobante</a>
                <?php else: ?>
                    <div class="small text-muted mb-2"><i class="fa-regular fa-image me-1"></i>No adjuntó foto del comprobante.</div>
                <?php endif; ?>

                <?php if ($p['estado_pago'] === 'PENDIENTE'): ?>
                    <form method="post" action="<?= BASE_URL ?>/admin/decidirPagoSuscripcion" class="d-flex flex-wrap gap-2 m-0">
                        <?= Security::campo() ?>
                        <input type="hidden" name="id_transaccion" value="<?= (int) $p['id_transaccion'] ?>">
                        <input type="text" name="observacion" class="form-control form-control-sm" style="max-width:320px" placeholder="Motivo (obligatorio si rechazas)" maxlength="250">
                        <button name="decision" value="CONFIRMAR" class="btn btn-primary btn-sm" onclick="return confirm('¿Confirmar este pago y aplicarlo a la cuenta?');"><i class="fa-solid fa-check me-1"></i>Confirmar</button>
                        <button name="decision" value="RECHAZAR" class="btn btn-outline-danger btn-sm">Rechazar</button>
                    </form>
                <?php elseif (!empty($p['observacion'])): ?>
                    <div class="small text-muted"><i class="fa-solid fa-comment me-1"></i><?= Security::e($p['observacion']) ?></div>
                <?php endif; ?>
            </div></div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>