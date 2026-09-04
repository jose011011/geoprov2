<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4" style="max-width: 600px; margin: auto;">
    <div class="card shadow-lg" style="border-radius: 15px; border-top: 5px solid #00BFA6;">
        <div class="card-header bg-white text-center pt-4 pb-2" style="border-radius: 15px 15px 0 0;">
            <h4 class="text-dark fw-bold">
                <i class="fa-solid <?= $solicitud['estado_servicio'] === 'EN_PROCESO' ? 'fa-screwdriver-wrench' : 'fa-motorcycle' ?> text-primary"></i> 
                <?= $solicitud['estado_servicio'] === 'EN_PROCESO' ? 'Trabajando en el Domicilio' : 'Viaje en Curso' ?>
            </h4>
            <p class="text-muted small">
                <?= $solicitud['estado_servicio'] === 'EN_PROCESO' ? 'Realizando el servicio técnico' : 'Dirígete a la ubicación del cliente' ?>
            </p>
        </div>
        
        <div class="card-body p-0">
            <!-- Contenedor del Mapa del Profesional -->
            <div id="mapaProfesional" style="height: 60vh; width: 100%;"></div>
        </div>

        <div class="card-footer bg-light p-3 text-center" style="border-radius: 0 0 15px 15px;">
            
            <!-- LÓGICA 1: TÉCNICO VIAJANDO -->
            <?php if ($solicitud['estado_servicio'] === 'EN_CAMINO'): ?>
                <p id="statusGps" class="text-warning fw-bold mb-3">
                    <i class="fa-solid fa-satellite-dish fa-spin"></i> Conectando al GPS del dispositivo...
                </p>
                
                <form action="<?= BASE_URL ?>/solicitud/actualizarEstado" method="POST">
                    <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                    <button type="submit" name="estado" value="EN_PROCESO" class="btn btn-primary w-100 fw-bold py-3" style="border-radius: 10px; font-size: 1.1rem;">
                        <i class="fa-solid fa-location-dot"></i> ¡Llegué al Domicilio!
                    </button>
                </form>

            <!-- LÓGICA 2: TÉCNICO YA LLEGÓ Y ESTÁ TRABAJANDO -->
            <?php elseif ($solicitud['estado_servicio'] === 'EN_PROCESO'): ?>
                <p class="text-success fw-bold mb-3">
                    <i class="fa-solid fa-circle-check"></i> El cliente fue notificado de tu llegada.
                </p>
                
                <!-- Botón que abre el Modal para Finalizar -->
                <button type="button" class="btn btn-success w-100 fw-bold py-3" style="border-radius: 10px; font-size: 1.1rem;" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                    <i class="fa-solid fa-flag-checkered"></i> Finalizar Trabajo y Cobrar
                </button>

                <!-- MODAL: Ingresar el Precio Final Acordado -->
                <div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 15px;">
                            <div class="modal-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-check-double"></i> Concluir Servicio</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="<?= BASE_URL ?>/solicitud/actualizarEstado" method="POST">
                                <div class="modal-body text-start p-4">
                                    <p class="text-center mb-4">Ingresa el precio final que el cliente te pagó. Esto es vital para el registro y la transparencia de GEO-PRO.</p>
                                    
                                    <label class="form-label fw-bold text-dark small"><i class="fa-solid fa-money-bill-wave text-success"></i> Precio Final Cobrado (Bs.)</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-light fw-bold">Bs.</span>
                                        <input type="number" step="0.50" name="precio_acordado" class="form-control" placeholder="Ej: 150.00" required min="5">
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                                    <button type="submit" name="estado" value="FINALIZADA" class="btn btn-success fw-bold w-100 py-2" style="border-radius: 10px;">Guardar y Finalizar Servicio</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Leaflet Core -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>




<script>
document.addEventListener("DOMContentLoaded", function() {
    const idSolicitud = <?= (int) $solicitud['id_solicitud'] ?>;
    const latDestino = <?= (float) $solicitud['latitud_destino'] ?>;
    const lngDestino = <?= (float) $solicitud['longitud_destino'] ?>;
    const estadoServicio = "<?= $solicitud['estado_servicio'] ?>";

    // 1. Inicializar Mapa centrado en el destino
    const mapa = L.map('mapaProfesional').setView([latDestino, lngDestino], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    // 2. Marcador del Cliente (Destino)
    const iconoDestino = L.divIcon({ 
        html: '<i class="fa-solid fa-house-user fa-2x text-danger"></i>', 
        className: '', iconSize: [30,30] 
    });
    L.marker([latDestino, lngDestino], { icon: iconoDestino }).addTo(mapa).bindPopup('<b>Domicilio del Cliente</b>');

    let marcadorMiUbicacion = null;

    // 3. Función para enviar la ubicación al servidor (Solo si está EN_CAMINO)
    async function enviarUbicacionAlServidor(lat, lng, velocidad) {
        if (estadoServicio !== 'EN_CAMINO') return; // Si ya llegó, no gasta internet enviando GPS

        const formData = new FormData();
        formData.append('id_solicitud', idSolicitud);
        formData.append('lat', lat);
        formData.append('lng', lng);
        formData.append('velocidad', velocidad || 0);

        try {
            await fetch(`<?= BASE_URL ?>/tracking/actualizar`, {
                method: 'POST',
                body: formData
            });
            const statusGps = document.getElementById('statusGps');
            if (statusGps) {
                statusGps.innerHTML = '<i class="fa-solid fa-satellite-dish text-success"></i> Transmitiendo ubicación en vivo...';
                statusGps.className = 'text-success fw-bold mb-3';
            }
        } catch (error) {
            console.error("Error enviando GPS");
        }
    }

    // 4. Leer el GPS del celular del Profesional
    if ("geolocation" in navigator) {
        const opcionesGps = { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 };

        navigator.geolocation.watchPosition(
            (posicion) => {
                const lat = posicion.coords.latitude;
                const lng = posicion.coords.longitude;
                const velocidad = posicion.coords.speed;

                if (!marcadorMiUbicacion) {
                    const iconoMiUbicacion = L.divIcon({ 
                        html: '<i class="fa-solid fa-motorcycle fa-2x text-primary"></i>', 
                        className: '', iconSize: [30,30] 
                    });
                    marcadorMiUbicacion = L.marker([lat, lng], { icon: iconoMiUbicacion }).addTo(mapa).bindPopup('<b>Mi Ubicación</b>');
                    mapa.fitBounds([[latDestino, lngDestino], [lat, lng]], { padding: [50, 50] });
                } else {
                    marcadorMiUbicacion.setLatLng([lat, lng]);
                }

                enviarUbicacionAlServidor(lat, lng, velocidad);
            },
            (error) => {
                const statusGps = document.getElementById('statusGps');
                if (statusGps) {
                    statusGps.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i> Por favor, activa el GPS.';
                    statusGps.className = 'text-danger fw-bold mb-3';
                }
            },
            opcionesGps
        );
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>