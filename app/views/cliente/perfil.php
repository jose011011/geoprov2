<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 760px;">
    <h1 class="fx-page-title h3 mb-1">Mi perfil</h1>
    <p class="text-muted">Esta dirección es adonde irá el técnico.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= nl2br(Security::e($error)) ?></div>
    <?php endif; ?>

    <div class="card mb-4"><div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><div class="small text-muted">Nombre</div><div class="fw-semibold"><?= Security::e(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')) ?></div></div>
            <div class="col-md-6"><div class="small text-muted">Celular</div><div class="fw-semibold"><?= Security::e($cliente['celular'] ?? '') ?></div></div>
            <div class="col-12"><div class="small text-muted">Correo</div><div class="fw-semibold"><?= Security::e($cliente['correo'] ?? '') ?></div></div>
        </div>
    </div></div>

    <form method="post" action="<?= BASE_URL ?>/cliente/perfil" class="card"><div class="card-body">
        <?= Security::campo() ?>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold" for="direccion_referencia">Dirección de referencia</label>
                <input type="text" class="form-control" id="direccion_referencia" name="direccion_referencia" minlength="5" maxlength="255" required
                       value="<?= Security::e($cliente['direccion_referencia'] ?? '') ?>" placeholder="Calle, número, referencia">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="zona">Zona o barrio</label>
                <input type="text" class="form-control" id="zona" name="zona" minlength="2" maxlength="100" required value="<?= Security::e($cliente['zona'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="macrodistrito">Macrodistrito</label>
                <select class="form-select" id="macrodistrito" name="macrodistrito" required>
                    <?php foreach (Catalogo::MACRODISTRITOS as $k => $t): ?>
                        <option value="<?= $k ?>" <?= (($cliente['macrodistrito'] ?? 'CENTRO') === $k) ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnUbicacion"><i class="fa-solid fa-crosshairs me-1"></i>Usar mi ubicación actual</button>
                <small id="estadoUbicacion" class="text-muted ms-2">
                    <?= !empty($cliente['latitud_predeterminada']) ? 'Ubicación guardada.' : 'Aún no guardaste tu ubicación.' ?>
                </small>
                <input type="hidden" name="latitud" id="latitud" value="<?= Security::e($cliente['latitud_predeterminada'] ?? '') ?>">
                <input type="hidden" name="longitud" id="longitud" value="<?= Security::e($cliente['longitud_predeterminada'] ?? '') ?>">
            </div>
        </div>
        <button class="btn btn-primary mt-4"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
    </div></form>
</div>

<script>
(function () {
    const btn = document.getElementById('btnUbicacion'), estado = document.getElementById('estadoUbicacion');
    btn.addEventListener('click', function () {
        if (!navigator.geolocation) { estado.textContent = 'Tu navegador no permite obtener la ubicación.'; return; }
        estado.textContent = 'Buscando tu ubicación...';
        navigator.geolocation.getCurrentPosition(function (p) {
            document.getElementById('latitud').value = p.coords.latitude.toFixed(8);
            document.getElementById('longitud').value = p.coords.longitude.toFixed(8);
            estado.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i>Ubicación lista. Presiona "Guardar cambios".';
        }, function () { estado.textContent = 'No pudimos obtener tu ubicación.'; }, { enableHighAccuracy: true, timeout: 10000 });
    });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>