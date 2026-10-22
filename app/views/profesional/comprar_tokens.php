<?php
require __DIR__ . '/../layouts/header.php';

$planesPago = array_values(array_filter($planes, fn($p) => (int) $p['id_plan'] > 1 && (int) ($p['estado'] ?? 1) === 1));
$esDePago   = (int) $perfil['id_plan'] > 1;
$hayQr      = is_file(__DIR__ . '/../../../public/img/qr-pago.png');
$bloqueado  = $motivoBloqueo !== null;
$sinTokensSueltos = $ilimitado || $precioToken <= 0;
$estadoPago = ['PENDIENTE' => ['En revisión', 'warning'], 'CONFIRMADO' => ['Confirmado', 'success'], 'FALLIDO' => ['Rechazado', 'danger']];
$concepto   = fn(array $t): string => $t['tipo_transaccion'] === 'PAQUETE_TOKENS'
    ? (int) $t['tokens_comprados'] . ' tokens'
    : 'Plan ' . Catalogo::nombrePlan($t['nombre_plan'] ?? '');
?>

<div class="container py-4" style="max-width: 920px;">
    <h1 class="fx-page-title h3 mb-1">Tokens y planes</h1>
    <p class="text-muted">Cada trabajo que aceptas usa 1 token. <strong>Puedes comprar en cualquier momento</strong>: los tokens nuevos se suman a los que ya tienes.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="fx-stat"><div class="num"><?= $ilimitado ? '∞' : (int) $perfil['tokens_disponibles'] ?></div><small>Tokens disponibles</small></div></div>
        <div class="col-md-4"><div class="fx-stat"><div class="fw-bold fs-4 text-primary"><?= Security::e(Catalogo::nombrePlan($perfil['nombre_plan'])) ?></div><small>Tu plan actual</small></div></div>
        <div class="col-md-4"><div class="fx-stat">
            <?php if ($esDePago && !empty($perfil['fin_suscripcion'])): ?>
                <div class="fw-bold fs-4 <?= $vencida ? 'text-danger' : '' ?>"><?= date('d/m/Y', strtotime($perfil['fin_suscripcion'])) ?></div><small><?= $vencida ? 'Venció el' : 'Vence el' ?></small>
            <?php else: ?>
                <div class="fw-bold fs-4">—</div><small>Sin vencimiento</small>
            <?php endif; ?>
        </div></div>
    </div>

    <?php if ($bloqueado): ?>
        <div class="alert alert-<?= $pendiente ? 'info' : 'warning' ?> d-flex gap-3">
            <i class="fa-solid <?= $pendiente ? 'fa-hourglass-half' : 'fa-lock' ?> fs-4"></i>
            <div>
                <div class="fw-bold mb-1">Por ahora no puedes enviar un nuevo pago</div>
                <div><?= Security::e($motivoBloqueo) ?></div>
                <?php if ($pendiente): ?>
                    <div class="small mt-2">Pago en revisión: <strong><?= Security::e($concepto($pendiente)) ?></strong> · Bs <?= number_format((float) $pendiente['monto'], 2) ?>
                        · código <?= Security::e($pendiente['codigo_comprobante']) ?> · enviado el <?= date('d/m/Y H:i', strtotime($pendiente['fecha_pago'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/profesional/registrarPago" enctype="multipart/form-data">
        <?= Security::campo() ?>
        <input type="hidden" name="tipo_compra" id="tipoCompra" value="PLAN">
        <fieldset <?= $bloqueado ? 'disabled' : '' ?>>

            <h2 class="h5 fw-bold mb-3">1. ¿Qué quieres comprar?</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <button type="button" class="fx-mode active" data-modo="PLAN">
                        <span class="ico"><i class="fa-solid fa-layer-group"></i></span>
                        <span><span class="fw-bold d-block">Plan mensual</span><small class="text-muted">Más tokens, mejor posición y vencimiento a 30 días.</small></span>
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="fx-mode" data-modo="PAQUETE" <?= $sinTokensSueltos ? 'disabled' : '' ?>>
                        <span class="ico"><i class="fa-solid fa-coins"></i></span>
                        <span><span class="fw-bold d-block">Solo tokens</span>
                        <small class="text-muted">
                            <?php if ($ilimitado): ?>No lo necesitas: tu plan Premium incluye tokens ilimitados.
                            <?php elseif ($precioToken <= 0): ?>No disponible por ahora.
                            <?php else: ?>De 10 en 10, a Bs <?= number_format($precioToken, 2) ?> cada uno. Sin cambiar de plan.<?php endif; ?>
                        </small></span>
                    </button>
                </div>
            </div>

            <div id="grupoPlan" class="row g-3 mb-4">
                <?php foreach ($planesPago as $p): $ilim = (int) $p['tokens_otorgados'] >= 999; ?>
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="id_plan" id="plan<?= (int) $p['id_plan'] ?>" value="<?= (int) $p['id_plan'] ?>" autocomplete="off">
                        <label class="fx-plan-card" for="plan<?= (int) $p['id_plan'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold fs-5"><?= Security::e(Catalogo::nombrePlan($p['nombre_plan'])) ?></span>
                                <?php if ((int) $p['posicionamiento_destacado'] === 1): ?><span class="badge text-bg-primary"><i class="fa-solid fa-crown me-1"></i>Destacado</span><?php endif; ?>
                            </div>
                            <div class="precio my-2">Bs <?= number_format((float) $p['precio'], 0) ?> <small class="fs-6 fw-normal text-muted">/ mes</small></div>
                            <div class="fw-semibold mb-1"><i class="fa-solid fa-coins me-1 text-warning"></i><?= $ilim ? 'Tokens ilimitados' : (int) $p['tokens_otorgados'] . ' tokens al mes' ?></div>
                            <div class="small text-muted"><?= Security::e($p['descripcion'] ?? '') ?></div>
                        </label>
                    </div>
                <?php endforeach; ?>
                <?php if ($esDePago && !$vencida): ?>
                    <div class="col-12 small text-muted"><i class="fa-solid fa-circle-info me-1"></i>Si renuevas tu plan actual antes de que venza, se suman 30 días a tu vencimiento.</div>
                <?php endif; ?>
            </div>

            <div id="grupoPaquete" class="mb-4 d-none">
                <div class="card"><div class="card-body">
                    <label class="form-label fw-semibold" for="cantidad_tokens">¿Cuántos tokens necesitas?</label>
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-6">
                            <select class="form-select form-select-lg" id="cantidad_tokens" name="cantidad_tokens" data-unitario="<?= number_format($precioToken, 6, '.', '') ?>">
                                <?php for ($n = ProfesionalPanel::PAQUETE_MIN; $n <= ProfesionalPanel::PAQUETE_MAX; $n += ProfesionalPanel::PAQUETE_PASO): ?>
                                    <option value="<?= $n ?>"><?= $n ?> tokens</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <div class="small text-muted">Total a pagar</div>
                            <div class="fs-2 fw-bold text-primary" id="totalTokens">Bs 0.00</div>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">Cada token cuesta Bs <?= number_format($precioToken, 2) ?>. Se suman a tus tokens actuales cuando confirmemos tu pago.</div>
                </div></div>
            </div>

            <h2 class="h5 fw-bold mb-3">2. Realiza el pago</h2>
            <div class="card mb-4"><div class="card-body"><div class="row g-3 align-items-center">
                <?php if ($hayQr): ?>
                    <div class="col-md-4 text-center"><img src="<?= BASE_URL ?>/img/qr-pago.png" alt="QR de pago FixGeo" class="img-fluid rounded" style="max-height: 220px;"></div>
                <?php endif; ?>
                <div class="<?= $hayQr ? 'col-md-8' : 'col-12' ?>">
                    <p class="mb-2">Paga el <strong>monto exacto</strong> por <strong>QR Simple</strong> o transferencia y guarda tu comprobante.</p>
                    <p class="small text-muted mb-0">Después completa el código del comprobante abajo. Verificamos el pago y se activa tu compra.</p>
                </div>
            </div></div></div>

            <h2 class="h5 fw-bold mb-3">3. Envía tu comprobante</h2>
            <div class="card mb-3"><div class="card-body"><div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="metodo_pago">Método de pago</label>
                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                        <option value="QR_SIMPLE_BOLIVIA">QR Simple</option>
                        <option value="TRANSFERENCIA">Transferencia bancaria</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="codigo_comprobante">Código o nº de comprobante</label>
                    <input type="text" class="form-control" id="codigo_comprobante" name="codigo_comprobante" maxlength="100" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="comprobante_foto">Foto del comprobante <span class="text-muted fw-normal">(JPG, PNG o PDF, máximo 5 MB)</span></label>
                    <input type="file" class="form-control" id="comprobante_foto" name="comprobante_foto" accept=".jpg,.jpeg,.png,.webp,.pdf">
                </div>
            </div></div></div>

            <button class="btn btn-primary btn-lg w-100"><i class="fa-solid fa-paper-plane me-2"></i>Enviar para revisión</button>
        </fieldset>
    </form>

    <?php if ($pagos): ?>
        <h2 class="h5 fw-bold mt-5 mb-3">Mis últimos pagos</h2>
        <div class="card"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Fecha</th><th>Compra</th><th>Código</th><th class="text-end">Monto</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pagos as $pg): [$txt, $cls] = $estadoPago[$pg['estado_pago']] ?? [$pg['estado_pago'], 'secondary']; ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d/m/Y', strtotime($pg['fecha_pago'])) ?></td>
                        <td><?= Security::e($concepto($pg)) ?></td>
                        <td class="small text-muted"><?= Security::e($pg['codigo_comprobante']) ?></td>
                        <td class="text-end">Bs <?= number_format((float) $pg['monto'], 2) ?></td>
                        <td>
                            <span class="badge text-bg-<?= $cls ?>"><?= $txt ?></span>
                            <?php if ($pg['estado_pago'] === 'FALLIDO' && !empty($pg['observacion'])): ?>
                                <div class="small text-danger mt-1"><?= Security::e($pg['observacion']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?php if (!empty($pg['foto_comprobante'])): ?>
                            <a class="small" target="_blank" rel="noopener" href="<?= BASE_URL ?>/profesional/miComprobante/<?= (int) $pg['id_transaccion'] ?>">Ver foto</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    <?php endif; ?>
</div>

<script>
(function () {
    const modos = document.querySelectorAll('.fx-mode');
    const campo = document.getElementById('tipoCompra');
    const grupos = { PLAN: document.getElementById('grupoPlan'), PAQUETE: document.getElementById('grupoPaquete') };
    const cantidad = document.getElementById('cantidad_tokens');
    const total = document.getElementById('totalTokens');

    function precio() {
        const unitario = parseFloat(cantidad.dataset.unitario || '0');
        const n = parseInt(cantidad.value, 10) || 0;
        total.textContent = 'Bs ' + (Math.ceil(n * unitario * 2) / 2).toFixed(2); // mismo redondeo que el servidor
    }

    function elegir(modo) {
        campo.value = modo;
        modos.forEach(m => m.classList.toggle('active', m.dataset.modo === modo));
        Object.entries(grupos).forEach(([clave, caja]) => {
            const activo = clave === modo;
            caja.classList.toggle('d-none', !activo);
            caja.querySelectorAll('input[type=radio]').forEach(r => { r.required = activo; if (!activo) r.checked = false; });
        });
    }

    modos.forEach(b => b.addEventListener('click', () => { if (!b.disabled) elegir(b.dataset.modo); }));
    cantidad.addEventListener('change', precio);
    precio();
    elegir('PLAN');
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>