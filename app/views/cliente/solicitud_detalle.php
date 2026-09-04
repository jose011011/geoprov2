<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4" style="max-width: 600px; margin: auto;">
    
    <?php if (isset($_GET['creada'])): ?>
        <div class="alert alert-success text-center shadow-sm" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-check"></i> ¡Solicitud enviada! GEO-PRO notificó al profesional.
        </div>
    <?php endif; ?>

    <!-- ALERTA DINÁMICA SEGÚN EL ESTADO DEL SERVICIO -->
    <?php if ($solicitud['estado_servicio'] === 'EN_CAMINO' && !empty($solicitud['tiempo_estimado_llegada_min'])): ?>
        <div class="alert alert-info py-3 mb-3 shadow-sm text-center" style="border-radius: 10px; border-left: 5px solid #0dcaf0;">
            <i class="fa-solid fa-stopwatch fa-spin fa-lg text-primary"></i> 
            El profesional estima llegar a tu domicilio en <strong><?= htmlspecialchars($solicitud['tiempo_estimado_llegada_min']) ?> minutos</strong>.
        </div>
    <?php elseif ($solicitud['estado_servicio'] === 'EN_PROCESO'): ?>
        <div class="alert alert-success py-3 mb-3 shadow-sm text-center" style="border-radius: 10px; border-left: 5px solid #198754;">
            <i class="fa-solid fa-screwdriver-wrench fa-lg text-success"></i> 
            El profesional <strong>ya se encuentra en tu domicilio</strong> realizando la asistencia técnica.
        </div>
    <?php elseif ($solicitud['estado_servicio'] === 'FINALIZADA'): ?>
        <div class="alert alert-secondary py-2 mb-3 shadow-sm text-center small" style="border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation text-warning"></i> 
            Servicio concluido. Por seguridad y privacidad, este chat se eliminará automáticamente en 15 días.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4" style="border-radius: 15px; border-top: 5px solid #0A192F;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-muted">Solicitud <strong class="text-dark"><?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></strong></h6>
                <span class="badge <?= $solicitud['estado_servicio'] === 'EN_CAMINO' ? 'bg-primary' : 'bg-secondary' ?> p-2">
                    <?= htmlspecialchars($solicitud['estado_servicio']) ?>
                </span>
            </div>

            <h5 class="mb-1"><i class="fa-solid fa-user-tie text-primary"></i> <?= htmlspecialchars($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?></h5>
            <p class="text-muted small mb-3"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($solicitud['prof_celular']) ?></p>
            
            <p class="mb-1"><strong>Problema a resolver:</strong></p>
            <p class="bg-light p-2 rounded text-muted"><?= nl2br(htmlspecialchars($solicitud['descripcion_problema'])) ?></p>
            
            <p class="mb-3"><strong><i class="fa-solid fa-location-dot text-danger"></i> Destino:</strong> <?= htmlspecialchars($solicitud['direccion_servicio']) ?>, <?= htmlspecialchars($solicitud['zona']) ?></p>

            <div class="d-grid gap-2">
                <?php if (!in_array($solicitud['estado_servicio'], ['CANCELADA'], true)): ?>
                    <a href="<?= BASE_URL ?>/chat/ver/<?= $solicitud['id_solicitud'] ?>" class="btn btn-outline-primary fw-bold" style="border-radius: 10px;">
                        <i class="fa-solid fa-comments"></i> Abrir Chat Seguro
                    </a>
                <?php endif; ?>

                <!-- VALIDACIÓN DE ROL: Solo el CLIENTE (role_id = 4) ve este botón -->
                <?php if ($solicitud['estado_servicio'] === 'FINALIZADA' && isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 4): ?>
                    <?php
                        require_once "../app/models/Calificacion.php";
                        $calModel = new Calificacion();
                        $yaCalifico = $calModel->yaCalificada($solicitud['id_solicitud']);
                    ?>
                    <?php if (!$yaCalifico): ?>
                        <a href="<?= BASE_URL ?>/solicitud/calificar/<?= $solicitud['id_solicitud'] ?>" class="btn btn-warning fw-bold text-dark" style="border-radius: 10px;">
                            <i class="fa-solid fa-star"></i> Calificar servicio
                        </a>
                    <?php else: ?>
                        <button class="btn btn-light text-success fw-bold" disabled style="border-radius: 10px;">
                            <i class="fa-solid fa-check-double"></i> Servicio Calificado
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Módulo de Tracking (Solo visible si está en camino) -->
    <?php if ($solicitud['estado_servicio'] === 'EN_CAMINO'): ?>
        <div class="card shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-success text-white text-center rounded-top" style="border-radius: 15px 15px 0 0;">
                <i class="fa-solid fa-location-crosshairs fa-spin"></i> Profesional en camino a tu domicilio
            </div>
            <div class="card-body p-0">
                <div id="mapaTracking" style="height:350px; width: 100%;"></div>
            </div>
            <div class="card-footer bg-light text-center" style="border-radius: 0 0 15px 15px;">
                <p id="etaInfo" class="text-muted small mb-0 fw-bold"><i class="fa-solid fa-satellite-dish"></i> Buscando señal GPS del profesional...</p>
            </div>
        </div>
        
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const idSolicitud = <?= (int) $solicitud['id_solicitud'] ?>;
            const latDestino = <?= (float) $solicitud['latitud_destino'] ?>;
            const lngDestino = <?= (float) $solicitud['longitud_destino'] ?>;

            const mapa = L.map('mapaTracking').setView([latDestino, lngDestino], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapa);

            const iconoDestino = L.divIcon({ 
                html: '<i class="fa-solid fa-house-user fa-2x text-danger" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"></i>', 
                className: '', 
                iconSize: [30,30] 
            });
            L.marker([latDestino, lngDestino], { icon: iconoDestino }).addTo(mapa).bindPopup('<b>Tu ubicación</b>');

            let marcadorProfesional = null;

            async function consultarPosicion() {
                try {
                    const res = await fetch(`<?= BASE_URL ?>/tracking/ultimaPosicion/${idSolicitud}`);
                    const data = await res.json();

                    if (data.ok && data.posicion) {
                        const lat = parseFloat(data.posicion.latitud);
                        const lng = parseFloat(data.posicion.longitud);

                        if (!marcadorProfesional) {
                            const iconoProf = L.divIcon({ 
                                html: '<i class="fa-solid fa-motorcycle fa-2x text-primary" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"></i>', 
                                className: '', 
                                iconSize: [30,30] 
                            });
                            marcadorProfesional = L.marker([lat, lng], { icon: iconoProf }).addTo(mapa).bindPopup('<b>Técnico</b>');
                            mapa.fitBounds([[latDestino, lngDestino], [lat, lng]], { padding: [50, 50] });
                        } else {
                            marcadorProfesional.setLatLng([lat, lng]);
                        }

                        const horaString = data.posicion.timestamp_registro.replace(' ', 'T');
                        const hora = new Date(horaString).toLocaleTimeString('es-BO', {hour:'2-digit', minute:'2-digit'});
                        document.getElementById('etaInfo').innerHTML = `<i class="fa-solid fa-satellite-dish text-success"></i> Última actualización GPS: ${hora}`;
                    }
                } catch (e) {
                    console.error("Esperando datos del GPS...");
                }
            }

            consultarPosicion();
            setInterval(consultarPosicion, 5000);
        });
        </script>
    <?php endif; ?>

</div>

<?php require_once "../app/views/layouts/footer.php"; ?>