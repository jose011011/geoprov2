<?php
require __DIR__ . '/../layouts/header.php';

$s      = $solicitud;
$id     = (int) $s['id_solicitud'];
$estado = $s['estado_servicio'];
$enViaje = ($estado === 'EN_CAMINO');
$ocultarMapa = true;

$metodoChat = null;
if (class_exists('ChatController')) {
    foreach (['conversacion', 'ver', 'abrir', 'index'] as $m) {
        if (method_exists('ChatController', $m)) { $metodoChat = $m; break; }
    }
}
$urlChat = $metodoChat ? BASE_URL . '/chat/' . $metodoChat . '/' . $id : null;
$destino = (float) $s['latitud_destino'] . ',' . (float) $s['longitud_destino'];
?>

<div class="container py-4" style="max-width: 960px;">
    <a href="<?= BASE_URL ?>/profesional/solicitudes?estado=ACTIVAS" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-3">
        <div>
            <h1 class="fx-page-title h3 mb-0"><?= Security::e($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></h1>
            <div class="small text-muted">Código <?= Security::e($s['codigo_seguimiento']) ?></div>
        </div>
        <?= Catalogo::badgeEstado($estado) ?>
    </div>

    <div class="card mb-3"><div class="card-body p-2">
        <div id="mapaViaje" style="height: 380px; border-radius: 14px;"></div>
        <div class="small text-center text-muted mt-2" id="estadoGps">
            <?= $enViaje ? '<i class="fa-solid fa-satellite-dish me-1"></i>Buscando tu ubicación...' : 'El GPS se activa cuando inicias el viaje.' ?>
        </div>
    </div></div>

    <div class="row g-3 mb-3">
        <div class="col-md-7"><div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Dirección del servicio</div>
            <div class="fw-semibold"><?= Security::e($s['direccion_servicio']) ?></div>
            <div class="small text-muted mb-3"><?= Security::e($s['zona']) ?> · <?= Security::e(Catalogo::macroEtiqueta($s['macrodistrito'])) ?></div>
            <div class="small text-muted mb-1">Problema</div>
            <div><?= nl2br(Security::e($s['descripcion_problema'])) ?></div>
        </div></div></div>

        <div class="col-md-5"><div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-2">Contacto</div>
            <div class="d-grid gap-2">
                <?php if (!empty($s['cliente_celular'])): ?>
                    <a class="btn btn-outline-primary btn-sm" href="tel:+591<?= Security::e($s['cliente_celular']) ?>"><i class="fa-solid fa-phone me-1"></i>Llamar al cliente</a>
                <?php endif; ?>
                <?php if ($urlChat): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?= $urlChat ?>"><i class="fa-regular fa-comments me-1"></i>Chat</a>
                <?php endif; ?>
                <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                   href="https://www.google.com/maps/dir/?api=1&destination=<?= $destino ?>"><i class="fa-brands fa-google me-1"></i>Abrir en Google Maps</a>
            </div>
            <?php if (!empty($s['tiempo_estimado_llegada_min'])): ?>
                <div class="small text-muted mt-3"><i class="fa-regular fa-clock me-1"></i>Prometiste llegar en <?= (int) $s['tiempo_estimado_llegada_min'] ?> min</div>
            <?php endif; ?>
        </div></div></div>
    </div>

    <div class="card"><div class="card-body"><?php require __DIR__ . '/_acciones_solicitud.php'; ?></div></div>
</div>

<script>
(function () {
    const destino = [<?= (float) $s['latitud_destino'] ?>, <?= (float) $s['longitud_destino'] ?>];
    const mapa = L.map('mapaViaje').setView(destino, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
    L.circleMarker(destino, { radius: 10, color: '#1d3fb4', fillColor: '#38bdf8', fillOpacity: .9, weight: 3 }).addTo(mapa).bindTooltip('Cliente');

    <?php if ($enViaje): ?>
    const aviso = document.getElementById('estadoGps');
    let yo = null, ultimoEnvio = 0;

    function enviar(lat, lng) {
        const ahora = Date.now();
        if (ahora - ultimoEnvio < 4000) return;
        ultimoEnvio = ahora;
        fetch(FX.base + '/profesional/actualizarGPS', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': FX.csrf },
            body: new URLSearchParams({ lat: lat, lng: lng })
        }).catch(() => {});
    }

    if (!navigator.geolocation) {
        aviso.textContent = 'Tu navegador no permite usar el GPS.';
    } else {
        navigator.geolocation.watchPosition(function (p) {
            const pos = [p.coords.latitude, p.coords.longitude];
            if (!yo) {
                yo = L.circleMarker(pos, { radius: 11, color: '#fff', fillColor: '#f59e0b', fillOpacity: 1, weight: 3 }).addTo(mapa).bindTooltip('Tú');
                mapa.fitBounds([pos, destino], { padding: [40, 40] });
            } else {
                yo.setLatLng(pos);
            }
            aviso.innerHTML = '<i class="fa-solid fa-satellite-dish me-1 text-success"></i>Compartiendo tu ubicación con el cliente';
            enviar(pos[0], pos[1]);
        }, function () {
            aviso.textContent = 'No pudimos obtener tu ubicación. Activa el GPS y da permiso al navegador.';
        }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 15000 });
    }
    <?php endif; ?>
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>