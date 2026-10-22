<div class="col-md-6">
    <label class="form-label fw-semibold" for="macrodistrito">Macrodistrito</label>
    <select class="form-select val-select" id="macrodistrito" name="macrodistrito" required>
        <option value="">Selecciona...</option>
        <?php foreach (Catalogo::MACRODISTRITOS as $clave => $etiqueta): ?>
            <option value="<?= $clave ?>" <?= (($old['macrodistrito'] ?? '') === $clave) ? 'selected' : '' ?>><?= $etiqueta ?></option>
        <?php endforeach; ?>
    </select>
    <div class="invalid-feedback"></div>
</div>

<div class="col-12">
    <button type="button" class="btn btn-outline-primary btn-sm" id="btnUbicacion">
        <i class="fa-solid fa-crosshairs me-1"></i>Usar mi ubicación actual
    </button>
    <small id="estadoUbicacion" class="text-muted ms-2">Opcional: ayuda a ubicarte con más precisión.</small>
    <input type="hidden" name="latitud" id="latitud" value="<?= Security::e($old['latitud'] ?? '') ?>">
    <input type="hidden" name="longitud" id="longitud" value="<?= Security::e($old['longitud'] ?? '') ?>">
</div>

<script>
(function () {
    const btn = document.getElementById('btnUbicacion');
    const estado = document.getElementById('estadoUbicacion');
    btn.addEventListener('click', function () {
        if (!navigator.geolocation) { estado.textContent = 'Tu navegador no permite obtener la ubicación.'; return; }
        estado.textContent = 'Buscando tu ubicación...';
        navigator.geolocation.getCurrentPosition(function (pos) {
            document.getElementById('latitud').value = pos.coords.latitude.toFixed(8);
            document.getElementById('longitud').value = pos.coords.longitude.toFixed(8);
            estado.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i>Ubicación guardada.';
        }, function () {
            estado.textContent = 'No pudimos obtener tu ubicación. Puedes continuar sin ella.';
        }, { enableHighAccuracy: true, timeout: 10000 });
    });
})();
</script>