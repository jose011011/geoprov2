<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO TRACKING PROFESIONAL — PREMIUM UI
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-primary: #08b7a5; --geo-primary-dark: #079486;
    --geo-bg: #f5f7fa; --geo-text: #172033; --geo-muted: #718096;
    --geo-border: #e8edf2; --geo-blue: #2563eb;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; margin: 0; overflow: hidden; height: 100vh; }
.geo-navbar { display: none !important; }

/* Contenedor Flex para Pantalla Completa */
.tracking-layout { display: flex; flex-direction: column; height: 100vh; width: 100%; position: relative; }

/* Botón Flotante Volver */
.btn-back-floating { position: absolute; top: 20px; left: 20px; z-index: 1000; background: white; border: none; width: 45px; height: 45px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; color: var(--geo-dark); font-size: 1.2rem; cursor: pointer; text-decoration: none; transition: transform 0.2s; }
.btn-back-floating:hover { transform: scale(1.05); color: var(--geo-primary); }

/* Mapa Ocupa el Espacio Sobrante */
.map-section { flex: 1; position: relative; z-index: 1; width: 100%; }
#mapaProfesional { width: 100%; height: 100%; }

/* Tarjeta de Control Inferior (Action Sheet) */
.action-sheet { background: #ffffff; border-radius: 30px 30px 0 0; padding: 30px 25px; box-shadow: 0 -10px 40px rgba(7, 24, 39, 0.1); position: relative; z-index: 1000; display: flex; flex-direction: column; gap: 20px; animation: slideUpSheet 0.5s ease; }
@keyframes slideUpSheet { from { transform: translateY(100%); } to { transform: translateY(0); } }

/* Indicador de Arrastre (Visual) */
.drag-indicator { width: 50px; height: 5px; background: #e2e8f0; border-radius: 10px; margin: 0 auto 15px; }

/* Cabecera del Estado */
.status-header { display: flex; align-items: center; justify-content: space-between; }
.status-title { font-weight: 900; font-size: 1.3rem; color: var(--geo-dark); margin: 0; }
.status-subtitle { color: var(--geo-muted); font-size: 0.9rem; font-weight: 500; margin: 0; }
.icon-box { width: 50px; height: 50px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.icon-travel { background: #eff6ff; color: var(--geo-blue); }
.icon-work { background: #ecfdf5; color: #10b981; }

/* Estado del GPS */
.gps-status { display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px 18px; border-radius: 14px; border: 1px solid var(--geo-border); font-size: 0.9rem; font-weight: 700; }
.gps-warning { color: #d97706; }
.gps-success { color: #10b981; }
.gps-error { color: #dc2626; }

/* Botones de Acción */
.btn-action-main { padding: 18px; border-radius: 18px; font-weight: 800; font-size: 1.1rem; border: none; width: 100%; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: transform 0.2s, box-shadow 0.2s; color: white; }
.btn-arrive { background: linear-gradient(135deg, var(--geo-blue) 0%, #1d4ed8 100%); box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); }
.btn-finish { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); }
.btn-action-main:hover { transform: translateY(-2px); }

/* Diseño del Modal de Cobro */
.modal-content-premium { border-radius: 24px; border: none; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
.modal-header-premium { background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 25px; border: none; }
.modal-body-premium { padding: 30px; }
.input-money-wrapper { position: relative; margin-top: 10px; }
.input-money-wrapper span { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-weight: 900; color: var(--geo-dark); font-size: 1.2rem; }
.input-money { width: 100%; padding: 20px 20px 20px 55px; border-radius: 16px; border: 2px solid var(--geo-border); font-size: 1.5rem; font-weight: 800; color: var(--geo-dark); background: #f8fafc; transition: all 0.3s; }
.input-money:focus { outline: none; border-color: #10b981; background: #ffffff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }
</style>

<div class="tracking-layout">

    <!-- Botón Flotante para salir del mapa -->
    <a href="javascript:history.back()" class="btn-back-floating" title="Volver al inicio">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <!-- SECCIÓN DEL MAPA (Pantalla Completa) -->
    <div class="map-section">
        <div id="mapaProfesional"></div>
    </div>

    <!-- TARJETA INFERIOR DE CONTROL (Action Sheet) -->
    <div class="action-sheet">
        <div class="drag-indicator"></div>

        <div class="status-header">
            <div>
                <h3 class="status-title">Viaje en Curso</h3>
                <p class="status-subtitle">Dirígete a la ubicación del cliente</p>
            </div>
            <div class="icon-box icon-travel">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
        </div>

        <!-- Barra de estado del GPS -->
        <div class="gps-status" id="gpsContainer">
            <i id="gpsIcon" class="fa-solid fa-satellite-dish fa-fade gps-warning"></i>
            <span id="gpsText" class="gps-warning">Buscando señal GPS...</span>
        </div>
        
        <!-- BOTÓN LLEGUÉ: Actualiza estado y cierra el mapa -->
        <form action="<?= BASE_URL ?>/solicitud/actualizarEstado" method="POST">
            <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
            <button type="submit" name="estado" value="EN_PROCESO" class="btn-action-main btn-arrive">
                <i class="fa-solid fa-location-dot"></i> ¡Llegué al Domicilio!
            </button>
        </form>
    </div>
</div>

<!-- LIBRERÍAS DE MAPA -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const idSolicitud = <?= (int) $solicitud['id_solicitud'] ?>;
    const latDestino = <?= (float) $solicitud['latitud_destino'] ?>;
    const lngDestino = <?= (float) $solicitud['longitud_destino'] ?>;
    const estadoServicio = "<?= $solicitud['estado_servicio'] ?>";

    // 1. Inicializar Mapa
    // Ocultamos controles por defecto para que se vea como app nativa
    const mapa = L.map('mapaProfesional', { zoomControl: false }).setView([latDestino, lngDestino], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    // 2. Marcador del Cliente
    const iconoDestino = L.divIcon({ 
        html: `<div style="width:45px; height:45px; border-radius:50%; background:#dc2626; color:white; display:flex; align-items:center; justify-content:center; border:3px solid white; box-shadow: 0 6px 15px rgba(220,38,38,0.4);"><i class="fa-solid fa-house-user fs-5"></i></div>`, 
        className: 'bg-transparent', iconSize: [45,45], iconAnchor: [22,22]
    });
    L.marker([latDestino, lngDestino], { icon: iconoDestino }).addTo(mapa).bindPopup('<b class="text-dark">Destino del Cliente</b>');

    let marcadorMiUbicacion = null;

    // 3. Enviar ubicación
    async function enviarUbicacionAlServidor(lat, lng, velocidad) {
        if (estadoServicio !== 'EN_CAMINO') return;

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
            
            // Actualización Visual Exitosa
            const gpsIcon = document.getElementById('gpsIcon');
            const gpsText = document.getElementById('gpsText');
            if (gpsIcon && gpsText) {
                gpsIcon.className = 'fa-solid fa-satellite-dish gps-success';
                gpsText.className = 'gps-success';
                gpsText.innerText = 'Transmitiendo ubicación en vivo...';
            }
        } catch (error) {
            console.error("Error enviando GPS");
        }
    }

    // 4. Leer GPS del navegador
    if ("geolocation" in navigator) {
        const opcionesGps = { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 };

        navigator.geolocation.watchPosition(
            (posicion) => {
                const lat = posicion.coords.latitude;
                const lng = posicion.coords.longitude;
                const velocidad = posicion.coords.speed;

                if (!marcadorMiUbicacion) {
                    const iconoMiUbicacion = L.divIcon({ 
                        html: `<div style="width:45px; height:45px; border-radius:50%; background:#2563eb; color:white; display:flex; align-items:center; justify-content:center; border:3px solid white; box-shadow: 0 6px 15px rgba(37,99,235,0.4);"><i class="fa-solid fa-motorcycle fs-5"></i></div>`, 
                        className: 'bg-transparent', iconSize: [45,45], iconAnchor: [22,22]
                    });
                    marcadorMiUbicacion = L.marker([lat, lng], { icon: iconoMiUbicacion }).addTo(mapa).bindPopup('<b>Tú</b>');
                    
                    // Ajustar mapa para que se vean ambos puntos
                    mapa.fitBounds([[latDestino, lngDestino], [lat, lng]], { padding: [60, 60] });
                } else {
                    marcadorMiUbicacion.setLatLng([lat, lng]);
                }

                enviarUbicacionAlServidor(lat, lng, velocidad);
            },
            (error) => {
                const gpsIcon = document.getElementById('gpsIcon');
                const gpsText = document.getElementById('gpsText');
                if (gpsIcon && gpsText) {
                    gpsIcon.className = 'fa-solid fa-triangle-exclamation gps-error';
                    gpsText.className = 'gps-error';
                    gpsText.innerText = 'Por favor, activa tu GPS para continuar.';
                }
            },
            opcionesGps
        );
    }
});




// =========================================================
// MOTOR GPS: TRANSMISOR DEL PROFESIONAL
// =========================================================
document.addEventListener("DOMContentLoaded", function() {
    if ("geolocation" in navigator) {
        // watchPosition lee el GPS constantemente mientras el profesional se mueve
        navigator.geolocation.watchPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Enviamos las coordenadas al servidor silenciosamente
            const formData = new FormData();
            formData.append('lat', lat);
            formData.append('lng', lng);

            fetch('<?= BASE_URL ?>/profesional/actualizarGPS', {
                method: 'POST',
                body: formData
            }).catch(err => console.error("Error transmitiendo GPS:", err));
            
            // Aquí puedes agregar la lógica de Leaflet/Google Maps para mover el propio marcador del técnico
            // miMarcador.setLatLng([lat, lng]);

        }, function(error) {
            console.warn("GPS no disponible o denegado por el usuario.");
        }, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 5000
        });
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>