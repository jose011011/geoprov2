<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO CLIENTE — PERFIL Y UBICACIÓN UI
   ============================================================ */
:root {
    --geo-dark: #071827; 
    --geo-primary: #08b7a5; 
    --geo-primary-dark: #079486; 
    --geo-primary-soft: #e8faf7; 
    --geo-bg: #f5f7fa; 
    --geo-border: #e8edf2; 
    --geo-text: #172033; 
    --geo-muted: #718096;
}

body { background: var(--geo-bg) !important; font-family: 'Inter', sans-serif; overflow-x: hidden; margin: 0; color: var(--geo-text); }
.geo-navbar { display: none !important; }

/* Sidebar */
.cli-wrapper { display: flex; width: 100%; min-height: 100vh; }
.cli-sidebar { width: 260px; background-color: #ffffff; border-right: 1px solid var(--geo-border); position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; overflow-y: auto; }
.sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); color: var(--geo-dark); font-size: 1.5rem; font-weight: 900; }
.sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); }
.sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--geo-primary), #2563eb); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid var(--geo-primary-soft);}
.sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
.sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #475569; text-decoration: none; font-size: 0.95rem; font-weight: 600; }
.sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
.sidebar-menu li a:hover { color: var(--geo-primary); background-color: #f8fafc; }
.sidebar-menu li a.active { color: var(--geo-primary); background-color: var(--geo-primary-soft); border-left-color: var(--geo-primary); }

.cli-main-content { flex: 1; margin-left: 260px\; min-width: 0; display: flex; flex-direction: column; }
.cli-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; padding: 0 30px; border-bottom: 1px solid var(--geo-border); position: sticky; top: 0; z-index: 999; }

@media (max-width: 991px) {
    .cli-sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
    .cli-sidebar.show { transform: translateX(0); }
    .cli-main-content { margin-left: 0; }
    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 998; display: none; }
    .sidebar-overlay.show { display: block; }
}

/* Contenido Principal */
.dashboard-content { padding: 40px; max-width: 1100px; margin: 0 auto; width: 100%; }

/* Tarjetas */
.geo-card { background: #fff; border-radius: 24px; padding: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--geo-border); height: 100%; }
.card-title { font-weight: 800; color: var(--geo-dark); font-size: 1.3rem; margin-bottom: 25px; border-bottom: 2px dashed var(--geo-border); padding-bottom: 15px; }

/* Perfil (Columna Izquierda) */
.profile-info-box { background: #f8fafc; border-radius: 16px; padding: 20px; margin-bottom: 15px; border: 1px solid var(--geo-border); }
.profile-info-label { font-size: 0.75rem; font-weight: 800; color: var(--geo-muted); text-transform: uppercase; margin-bottom: 5px; display: block; }
.profile-info-text { font-size: 1rem; font-weight: 700; color: var(--geo-text); margin: 0; display: flex; align-items: center; gap: 10px; }

/* Formulario y Mapa (Columna Derecha) */
.form-group-custom { margin-bottom: 20px; }
.form-group-custom label { display: block; font-size: 0.9rem; font-weight: 800; color: var(--geo-text); margin-bottom: 8px; }
.form-control-custom { width: 100%; padding: 14px 15px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; color: var(--geo-text); font-weight: 500; transition: all 0.2s; }
.form-control-custom:focus { outline: none; border-color: var(--geo-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(8, 183, 165, 0.1); }

/* MAPA INTERACTIVO */
.map-container-wrapper { position: relative; width: 100%; height: 350px; border-radius: 16px; overflow: hidden; border: 2px solid var(--geo-border); margin-bottom: 20px; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); z-index: 1;}
#mapaUbicacion { width: 100%; height: 100%; z-index: 1; }

.btn-get-location { position: absolute; bottom: 20px; right: 20px; z-index: 1000; background: white; border: none; padding: 12px 20px; border-radius: 50px; font-weight: 800; font-size: 0.9rem; color: var(--geo-primary); box-shadow: 0 4px 15px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s; display: flex; align-items: center; gap: 8px; }
.btn-get-location:hover { transform: translateY(-2px); background: var(--geo-primary-soft); }

.map-instruction { background: #fef3c7; color: #92400e; padding: 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; margin-bottom: 15px; border: 1px solid #fde68a; display: flex; align-items: center; gap: 10px; }

.btn-save { background: linear-gradient(135deg, var(--geo-primary), var(--geo-primary-dark)); color: white; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.05rem; border: none; width: 100%; cursor: pointer; box-shadow: 0 8px 20px rgba(8, 183, 165, 0.25); transition: transform 0.3s; }
.btn-save:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(8, 183, 165, 0.35); }
</style>

<div class="cli-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="cli-sidebar" id="cliSidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-location-crosshairs" style="color:var(--geo-primary);"></i> GEO-PRO</div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($cliente['nombre'] ?? 'Cliente') ?></h6>
            <span style="font-size: 0.75rem; background: var(--geo-primary-soft); color: var(--geo-primary-dark); padding: 4px 10px; border-radius: 20px; font-weight: 800;">PERFIL ACTIVO</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/cliente/dashboard"><i class="fa-solid fa-house"></i> Panel de Inicio</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/historial"><i class="fa-solid fa-clock-rotate-left"></i> Mis Solicitudes</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/favoritos"><i class="fa-solid fa-heart text-danger"></i> Favoritos</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/perfil" class="active"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            
            <?php if(isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 3): ?>
                <li><a href="<?= BASE_URL ?>/profesional/dashboard" style="color: #3b82f6; margin: 20px 15px; background: rgba(59,130,246,0.1); border-radius: 12px;"><i class="fa-solid fa-briefcase"></i> Volver a Profesional</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-3"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="cli-main-content">
        <header class="cli-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="fw-bold text-muted">Configuración de Cuenta y Ubicación</div>
        </header>

        <div class="dashboard-content">
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 p-3 d-flex align-items-center gap-3" style="background:#ecfdf5; color:#065f46;">
                    <i class="fa-solid fa-circle-check fa-2x"></i>
                    <div>
                        <h6 class="fw-bold m-0">¡Ubicación Actualizada!</h6>
                        <span class="small">Tus coordenadas exactas han sido guardadas. Los profesionales llegarán más rápido.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- COLUMNA IZQUIERDA: DATOS DEL PERFIL -->
                <div class="col-lg-4">
                    <div class="geo-card">
                        <h4 class="card-title"><i class="fa-solid fa-id-card text-primary me-2"></i> Mis Datos</h4>
                        
                        <div class="text-center mb-4">
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--geo-dark); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; margin: 0 auto 10px; border: 4px solid var(--geo-border);">
                                <?= strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)) ?>
                            </div>
                            <h5 class="fw-bold text-dark m-0"><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></h5>
                        </div>

                        <div class="profile-info-box">
                            <span class="profile-info-label">Teléfono / WhatsApp</span>
                            <p class="profile-info-text"><i class="fa-solid fa-phone text-muted"></i> <?= htmlspecialchars($cliente['celular'] ?? 'No registrado') ?></p>
                        </div>

                        <div class="profile-info-box">
                            <span class="profile-info-label">Correo Electrónico</span>
                            <p class="profile-info-text"><i class="fa-solid fa-envelope text-muted"></i> <?= htmlspecialchars($cliente['correo'] ?? 'No registrado') ?></p>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: DIRECCIÓN Y MAPA -->
                <div class="col-lg-8">
                    <div class="geo-card">
                        <h4 class="card-title"><i class="fa-solid fa-map-location-dot text-primary me-2"></i> Precisión de Domicilio</h4>
                        
                        <form method="POST" id="locationForm" action="<?= BASE_URL ?>/cliente/perfil">
                            
                            <!-- INPUTS OCULTOS PARA GUARDAR COORDENADAS EXACTAS -->
                            <input type="hidden" name="latitud" id="inputLat" value="<?= $cliente['latitud_predeterminada'] ?? -16.500000 ?>">
                            <input type="hidden" name="longitud" id="inputLng" value="<?= $cliente['longitud_predeterminada'] ?? -68.150000 ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label>Zona Principal <span class="text-danger">*</span></label>
                                        <select name="zona" class="form-control-custom" required>
                                            <option value="La Paz" <?= ($cliente['zona']??'') == 'La Paz' ? 'selected' : '' ?>>Ciudad de La Paz</option>
                                            <option value="El Alto" <?= ($cliente['zona']??'') == 'El Alto' ? 'selected' : '' ?>>El Alto</option>
                                            <option value="Zona Sur" <?= ($cliente['zona']??'') == 'Zona Sur' ? 'selected' : '' ?>>Zona Sur (LPZ)</option>
                                            <option value="Viacha" <?= ($cliente['zona']??'') == 'Viacha' ? 'selected' : '' ?>>Viacha</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label>Dirección de Referencia Escrita <span class="text-danger">*</span></label>
                                        <input type="text" name="direccion_referencia" class="form-control-custom" required placeholder="Ej: Av. Santa Fe, Casa #123, Puerta verde" value="<?= htmlspecialchars($cliente['direccion_referencia'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- MAPA INTERACTIVO -->
                            <label class="fw-bold mb-2 text-dark" style="font-size: 0.9rem;">Ubica el Pin Rojo exactamente sobre tu casa:</label>
                            
                            <div class="map-instruction">
                                <i class="fa-solid fa-hand-pointer fa-fade"></i> Arrastra el marcador rojo por el mapa para fijar tu ubicación exacta.
                            </div>

                            <div class="map-container-wrapper">
                                <div id="mapaUbicacion"></div>
                                <button type="button" class="btn-get-location" id="btnGetGps">
                                    <i class="fa-solid fa-crosshairs"></i> Usar mi ubicación actual
                                </button>
                            </div>

                            <button type="submit" class="btn-save" id="btnSubmit">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Coordenadas y Perfil
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- LIBRERÍAS DE MAPA -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Sidebar Toggle
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('cliSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // 2. Inicializar Mapa Interactivo
    const inputLat = document.getElementById('inputLat');
    const inputLng = document.getElementById('inputLng');
    
    // Valores iniciales (Si no tiene, cae en La Paz Centro por defecto)
    let lat = parseFloat(inputLat.value);
    let lng = parseFloat(inputLng.value);

    const map = L.map('mapaUbicacion', { zoomControl: true }).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    // 3. Crear el Marcador "Arrastrable" (Draggable)
    const pinIcon = L.divIcon({
        html: '<i class="fa-solid fa-location-dot fa-3x text-danger" style="text-shadow: 0 5px 15px rgba(220,38,38,0.5); transform: translateY(-15px);"></i>',
        className: 'bg-transparent',
        iconSize: [40, 40]
    });

    const marker = L.marker([lat, lng], {
        draggable: true, // ¡ESTO ES LO IMPORTANTE! Permite arrastrar el pin
        icon: pinIcon
    }).addTo(map);

    marker.bindPopup('<b class="text-dark">Arrastrame a tu casa</b>').openPopup();

    // 4. Actualizar inputs ocultos cuando el usuario suelta el Pin
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        inputLat.value = position.lat.toFixed(6);
        inputLng.value = position.lng.toFixed(6);
        map.panTo(position); // Centrar mapa en el nuevo pin
    });

    // 5. Botón GPS (Geolocalización HTML5)
    document.getElementById('btnGetGps').addEventListener('click', function() {
        const btn = this;
        if ("geolocation" in navigator) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Obteniendo...';
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const newLat = position.coords.latitude;
                    const newLng = position.coords.longitude;
                    
                    // Mover mapa y marcador
                    map.setView([newLat, newLng], 17);
                    marker.setLatLng([newLat, newLng]);
                    
                    // Actualizar inputs ocultos
                    inputLat.value = newLat.toFixed(6);
                    inputLng.value = newLng.toFixed(6);
                    
                    btn.innerHTML = '<i class="fa-solid fa-check text-success"></i> ¡Ubicación exacta encontrada!';
                    setTimeout(() => { btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Usar mi ubicación actual'; }, 3000);
                },
                (error) => {
                    alert("No pudimos acceder a tu GPS. Por favor, arrastra el Pin rojo manualmente.");
                    btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Usar mi ubicación actual';
                },
                { enableHighAccuracy: true }
            );
        } else {
            alert("Tu navegador no soporta geolocalización.");
        }
    });

    // 6. Efecto de carga al guardar (CÓDIGO CORREGIDO)
    const btnSubmit = document.getElementById('btnSubmit');
    const formLocation = document.getElementById('locationForm');
    
    btnSubmit.addEventListener('click', function(e) {
        e.preventDefault(); // Detenemos el envío automático del navegador
        
        // Verificamos si llenó todos los datos requeridos (Zona y Referencia)
        if(formLocation.reportValidity()) {
            // Cambiamos el diseño del botón
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando Perfil...';
            btnSubmit.style.opacity = '0.8';
            btnSubmit.style.pointerEvents = 'none'; // Evita que haga doble clic
            
            // Forzamos el envío real de los datos
            formLocation.submit();
        }
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>