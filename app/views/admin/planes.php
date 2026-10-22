<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 980px;">
    <h1 class="fx-page-title h3 mb-1">Planes y precios</h1>
    <p class="text-muted mb-1"><strong>Espera de cascada</strong>: minutos que tarda cada plan en <em>ver y recibir aviso</em> de una solicitud abierta. 0 = al instante (Premium).</p>
    <p class="text-muted">El precio de un token suelto se calcula con el plan de pago de tokens limitados: <strong>precio ÷ tokens</strong>. Usa <strong>999</strong> tokens para "ilimitados".</p>

    <div class="row g-3">
        <?php foreach ($planes as $p): $esGratis = (int) $p['id_plan'] === 1; ?>
            <div class="col-12"><div class="card"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold fs-5"><?= Security::e(Catalogo::nombrePlan($p['nombre_plan'])) ?></div>
                    <small class="text-muted"><?= (int) $p['tecnicos'] ?> técnico(s) con este plan</small>
                </div>
                <form method="post" action="<?= BASE_URL ?>/admin/guardarPlan" class="row g-2 align-items-end">
                    <?= Security::campo() ?>
                    <input type="hidden" name="id_plan" value="<?= (int) $p['id_plan'] ?>">
                    <div class="col-md-2"><label class="form-label small text-muted mb-0">Precio (Bs)</label>
                        <input type="number" name="precio" class="form-control form-control-sm" min="0" max="9999" step="0.5" value="<?= Security::e($p['precio']) ?>" <?= $esGratis ? 'readonly' : '' ?> required></div>
                    <div class="col-md-2"><label class="form-label small text-muted mb-0">Tokens que da</label>
                        <input type="number" name="tokens" class="form-control form-control-sm" min="1" max="9999" value="<?= (int) $p['tokens_otorgados'] ?>" required></div>
                    <div class="col-md-2"><label class="form-label small text-muted mb-0">Espera cascada (min)</label>
                        <input type="number" name="retraso" class="form-control form-control-sm" min="0" max="120" value="<?= (int) $p['retraso_cascada_min'] ?>" required></div>
                    <div class="col-md-3"><label class="form-label small text-muted mb-0">Descripción</label>
                        <input name="descripcion" class="form-control form-control-sm" maxlength="500" value="<?= Security::e($p['descripcion'] ?? '') ?>"></div>
                    <div class="col-md-2"><label class="form-label small text-muted mb-0">Estado</label>
                        <select name="estado" class="form-select form-select-sm" <?= $esGratis ? 'disabled' : '' ?>>
                            <option value="1" <?= (int) $p['estado'] === 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (int) $p['estado'] === 0 ? 'selected' : '' ?>>Oculto</option>
                        </select></div>
                    <div class="col-md-1 d-grid"><button class="btn btn-primary btn-sm">Guardar</button></div>
                </form>
            </div></div></div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>