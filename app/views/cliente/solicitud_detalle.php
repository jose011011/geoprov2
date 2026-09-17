<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO SEGUIMIENTO — PREMIUM UI
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-dark-2: #0b2435; --geo-primary: #08b7a5; --geo-primary-dark: #079486; --geo-primary-soft: #e8faf7;
    --geo-blue: #2563eb; --geo-blue-soft: #eff6ff; --geo-text: #172033; --geo-muted: #718096; --geo-bg: #f5f7fa; --geo-white: #ffffff;
    --geo-border: #e8edf2; --geo-shadow: 0 12px 35px rgba(15, 23, 42, .07); --geo-radius: 24px;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; overflow-x: hidden; }
.geo-navbar { display: none !important; }

/* Contenedor Focus */
.tracking-wrapper { max-width: 650px; margin: 40px auto; padding: 0 20px; }

/* Alertas Modernas */
.geo-alert { border: none; border-radius: 16px; padding: 18px 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.03); margin-bottom: 25px; animation: slideDown 0.4s ease; }
.geo-alert-icon { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }

.alert-created { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-created .geo-alert-icon { background: #d1fae5; color: #10b981; }

.alert-way { background: var(--geo-blue-soft); color: #1e3a8a; border: 1px solid #bfdbfe; }
.alert-way .geo-alert-icon { background: #dbeafe; color: var(--geo-blue); }

.alert-process { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.alert-process .geo-alert-icon { background: #fef08a; color: #d97706; }

.alert-finished { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.alert-finished .geo-alert-icon { background: #e2e8f0; color: #64748b; }

/* Tarjeta Principal */
.detail-card { background: #fff; border-radius: var(--geo-radius); box-shadow: var(--geo-shadow); border: 1px solid var(--geo-border); overflow: hidden; margin-bottom: 25px; }
.detail-header { padding: 25px 30px; border-bottom: 1px solid var(--geo-border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }
.detail-body { padding: 30px; }

.solicitud-code { font-family: monospace; font-size: 0.9rem; color: var(--geo-muted); font-weight: 700; background: #f1f5f9; padding: 4px 10px; border-radius: 8px; border: 1px dashed #cbd5e1; }
.badge-status { padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }

/* Perfil Profesional */
.prof-block { display: flex; align-items: center; gap: 18px; margin-bottom: 25px; }
.prof-avatar { width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, var(--geo-dark), var(--geo-dark-2)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: bold; box-shadow: 0 4px 15px rgba(7, 24, 39, 0.2); border: 3px solid #f8fafc; }
.prof-name { font-weight: 800; color: var(--geo-text); font-size: 1.2rem; margin: 0 0 2px 0; }
.prof-phone { color: var(--geo-muted); font-size: 0.9rem; margin: 0; font-weight: 500; }

/* Cajas de Info */
.info-box { background: #f8fafc; border-radius: 16px; padding: 20px; border: 1px solid var(--geo-border); margin-bottom: 20px; }
.info-label { font-size: 0.75rem; font-weight: 800; color: var(--geo-muted); text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px; }
.info-text { color: var(--geo-text); font-size: 0.95rem; line-height: 1.6; margin: 0; font-weight: 500;}

/* Botones de Acción */
.action-grid { display: flex; flex-direction: column; gap: 12px; margin-top: 30px; }
.btn-geo { padding: 16px 20px; border-radius: 16px; font-weight: 800; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
.btn-chat { background: linear-gradient(135deg, var(--geo-blue) 0%, #1d4ed8 100%); color: white; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25); }
.btn-chat:hover { transform: translateY(-2px); color: white; box-shadow: 0 12px 25px rgba(37, 99, 235, 0.35); }
.btn-rate { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25); }
.btn-rate:hover { transform: translateY(-2px); color: white; box-shadow: 0 12px 25px rgba(245, 158, 11, 0.35); }

/* Módulo de Mapa */
.map-card { background: #fff; border-radius: var(--geo-radius); box-shadow: var(--geo-shadow); border: 1px solid var(--geo-border); overflow: hidden; }
.map-header { padding: 15px 25px; background: linear-gradient(135deg, var(--geo-primary) 0%, var(--geo-primary-dark) 100%); color: white; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.95rem; }
.map-footer { padding: 15px; background: #f8fafc; text-align: center; border-top: 1px solid var(--geo-border); font-size: 0.85rem; font-weight: 600; color: var(--geo-muted); }

@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="tracking-wrapper">
    
    <!-- Botón Volver -->
    <a href="javascript:history.back()" class="btn btn-light rounded-pill border shadow-sm mb-4 fw-bold text-muted px-4 py-2" style="background:#fff;">
        <i class="fa-solid fa-arrow-left me-2"></i> Volver atrás
    </a>

    <!-- ALERTA DE CREACIÓN -->
    <?php if (isset($_GET['creada'])): ?>
        <div class="geo-alert alert-created">
            <div class="geo-alert-icon"><i class="fa-solid fa-check"></i></div>
            <div>
                <h6 class="fw-bold mb-1 m-0">¡Solicitud enviada con éxito!</h6>
                <span class="small">GEO-PRO ya ha notificado al profesional sobre tu problema.</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ALERTAS DINÁMICAS SEGÚN EL ESTADO DEL SERVICIO -->
    <?php if ($solicitud['estado_servicio'] === 'EN_CAMINO'): ?>
        <div class="geo-alert alert-way">
            <div class="geo-alert-icon"><i class="fa-solid fa-motorcycle fa-fade"></i></div>
            <div>
                <h6 class="fw-bold mb-1 m-0">Profesional en Camino</h6>
                <span class="small">
                    <?= !empty($solicitud['tiempo_estimado_llegada_min']) ? "Tiempo estimado de llegada: <strong>{$solicitud['tiempo_estimado_llegada_min']} minutos</strong>." : "El profesional se dirige a tu ubicación." ?>
                </span>
            </div>
        </div>
    <?php elseif ($solicitud['estado_servicio'] === 'EN_PROCESO'): ?>
        <div class="geo-alert alert-process">
            <div class="geo-alert-icon"><i class="fa-solid fa-screwdriver-wrench fa-bounce"></i></div>
            <div>
                <h6 class="fw-bold mb-1 m-0">Trabajo en Proceso</h6>
                <span class="small">El profesional ya se encuentra en el domicilio realizando la asistencia técnica.</span>
            </div>
        </div>
    <?php elseif ($solicitud['estado_servicio'] === 'FINALIZADA'): ?>
        <div class="geo-alert alert-finished">
            <div class="geo-alert-icon"><i class="fa-solid fa-flag-checkered"></i></div>
            <div>
                <h6 class="fw-bold mb-1 m-0">Servicio Concluido</h6>
                <span class="small">Por seguridad, el chat se eliminará de los servidores en 15 días. ¡Gracias por usar GEO-PRO!</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- TARJETA PRINCIPAL DEL DETALLE -->
    <div class="detail-card">
        <div class="detail-header">
            <div>
                <span class="text-muted small fw-bold">ID DE SEGUIMIENTO</span><br>
                <span class="solicitud-code"><?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></span>
            </div>
            <div>
                <?php 
                    $estado = $solicitud['estado_servicio'];
                    $color = 'bg-secondary';
                    if($estado === 'PENDIENTE') $color = 'bg-warning text-dark';
                    if($estado === 'ACEPTADA') $color = 'bg-info text-white';
                    if($estado === 'EN_CAMINO') $color = 'bg-primary text-white';
                    if($estado === 'EN_PROCESO') $color = 'bg-success text-white';
                    if($estado === 'FINALIZADA') $color = 'bg-dark text-white';
                ?>
                <span class="badge-status <?= $color ?> shadow-sm"><?= str_replace('_', ' ', $estado) ?></span>
            </div>
        </div>

        <div class="detail-body">
            <!-- Info del Profesional -->
            <div class="prof-block">
                <div class="prof-avatar"><?= strtoupper(substr($solicitud['prof_nombre'] ?? 'P', 0, 1)) ?></div>
                <div>
                    <h5 class="prof-name"><?= htmlspecialchars($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?></h5>
                    <p class="prof-phone"><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($solicitud['prof_celular']) ?></p>
                </div>
            </div>

            <!-- Detalles del Servicio -->
            <div class="info-box">
                <span class="info-label"><i class="fa-solid fa-circle-exclamation me-1"></i> Problema a resolver</span>
                <p class="info-text">"<?= nl2br(htmlspecialchars($solicitud['descripcion_problema'])) ?>"</p>
            </div>

            <div class="info-box" style="margin-bottom: 0;">
                <span class="info-label"><i class="fa-solid fa-location-dot me-1"></i> Destino del Servicio</span>
                <p class="info-text"><?= htmlspecialchars($solicitud['direccion_servicio']) ?>, <?= htmlspecialchars($solicitud['zona']) ?></p>
            </div>

           <!-- BOTONES DE ACCIÓN -->
            <div class="action-grid">
                <?php if (!in_array($solicitud['estado_servicio'], ['CANCELADA'], true)): ?>
                    <a href="<?= BASE_URL ?>/chat/ver/<?= $solicitud['id_solicitud'] ?>" class="btn-geo btn-chat">
                        <i class="fa-solid fa-comments"></i> Abrir Chat Seguro
                    </a>
                <?php endif; ?>

                <!-- ACCIONES DE FINALIZACIÓN (Solo visibles para clientes) -->
                <?php if ($solicitud['estado_servicio'] === 'FINALIZADA' && in_array((int)$_SESSION['role_id'], [3, 4])): ?>
                    
                    <?php
                        require_once "../app/models/Calificacion.php";
                        $calModel = new Calificacion();
                        $yaCalifico = $calModel->yaCalificada($solicitud['id_solicitud']);
                    ?>
                    
                    <?php if (!$yaCalifico): ?>
                        <!-- Obliga visualmente a Calificar -->
                        <a href="<?= BASE_URL ?>/solicitud/calificar/<?= $solicitud['id_solicitud'] ?>" class="btn-geo btn-rate" style="animation: pulse 2s infinite; background: #f59e0b; color: white;">
                            <i class="fa-solid fa-star"></i> Calificar Servicio (Requerido)
                        </a>
                    <?php else: ?>
                        <button class="btn-geo" disabled style="background:#f8fafc; color:#10b981; border: 2px solid #10b981; cursor:not-allowed;">
                            <i class="fa-solid fa-check-double"></i> Servicio Calificado
                        </button>
                    <?php endif; ?>

                    <!-- NUEVO BOTÓN: Añadir a Favoritos -->
                    <a href="<?= BASE_URL ?>/cliente/agregarFavorito/<?= $solicitud['id_profesional'] ?>" class="btn-geo" style="background:#fef2f2; color:#ef4444; border: 1px solid #fca5a5; margin-top: 10px;">
                        <i class="fa-solid fa-heart"></i> Guardar en mis Favoritos
                    </a>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MÓDULO DE TRACKING GPS (SOLO EN CAMINO) -->
    <?php if ($solicitud['estado_servicio'] === 'EN_CAMINO'): ?>
        <div class="map-card">
            <div class="map-header">
                <i class="fa-solid fa-location-crosshairs fa-spin"></i> Radar en tiempo real activado
            </div>
            
            <div id="mapaTracking" style="height:380px; width: 100%;"></div>
            
            <div class="map-footer" id="etaInfo">
                <i class="fa-solid fa-satellite-dish fa-fade text-primary me-1"></i> Buscando señal GPS del profesional...
            </div>
        </div>
        
        <!-- LIBRERÍAS LEAFLET -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <!-- TU LÓGICA DE JAVASCRIPT INTACTA -->
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
                        document.getElementById('etaInfo').innerHTML = `<i class="fa-solid fa-satellite-dish text-success me-1"></i> Última actualización GPS: <strong>${hora}</strong>`;
                    }
                } catch (e) {
                    console.error("Esperando datos del GPS...");
                }
            }

            consultarPosicion();
            setInterval(consultarPosicion, 5000);
        });

        // =========================================================
// MOTOR GPS: RECEPTOR DEL CLIENTE
// =========================================================
document.addEventListener("DOMContentLoaded", function() {
    // Solo rastreamos si el estado es EN_CAMINO
    const estadoServicio = '<?= $solicitud["estado_servicio"] ?>';
    const idSolicitud = <?= $solicitud["id_solicitud"] ?>;
    
    // Si tienes un marcador de Leaflet guardado en una variable global, úsala aquí. 
    // Supondremos que se llama 'marcadorProfesional'
    
    if (estadoServicio === 'EN_CAMINO') {
        setInterval(() => {
            fetch('<?= BASE_URL ?>/cliente/rastrearProfesional/' + idSolicitud)
            .then(response => response.json())
            .then(data => {
                if (data.latitud_actual && data.longitud_actual) {
                    const nuevaLat = parseFloat(data.latitud_actual);
                    const nuevaLng = parseFloat(data.longitud_actual);
                    
                    // Movemos suavemente el icono de la moto en el mapa del cliente
                    if (typeof marcadorProfesional !== 'undefined') {
                        marcadorProfesional.setLatLng([nuevaLat, nuevaLng]);
                        // mapa.panTo([nuevaLat, nuevaLng]); // Opcional: Centrar el mapa automáticamente
                    }
                    console.log("Técnico detectado en:", nuevaLat, nuevaLng);
                }
            })
            .catch(err => console.error("Error rastreando:", err));
        }, 5000); // Consulta cada 5 segundos
    }
});









        </script>
    <?php endif; ?>

</div>

<?php require_once "../app/views/layouts/footer.php"; ?>