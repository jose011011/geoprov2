<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       1. DISEÑO WEB SAAS PARA EL PROFESIONAL (ESCRITORIO / PC)
       ========================================================= */
    .geo-navbar { display: none !important; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .pro-wrapper { display: flex; width: 100%; min-height: 100vh; }
    
    /* SIDEBAR DEL PROFESIONAL */
    .pro-sidebar {
        width: 260px; background-color: #0f172a; color: #94a3b8;
        position: fixed; top: 0; left: 0; height: 100vh; z-index: 1000;
        transition: all 0.3s ease; overflow-y: auto; border-right: 1px solid #1e293b;
    }
    .sidebar-brand {
        padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b;
        color: #ffffff; font-size: 1.5rem; font-weight: 900; letter-spacing: 1px;
    }
    .sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; }
    .sidebar-avatar {
        width: 80px; height: 80px; border-radius: 50%; background-color: #3b82f6; color: white;
        display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold;
        margin: 0 auto 15px; border: 4px solid #1e293b;
    }
    .sidebar-profile h6 { color: #f8fafc; font-weight: 700; margin-bottom: 5px; font-size: 1.1rem; }
    .sidebar-profile p { font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; }
    
    .plan-badge { background: #334155; color: #f59e0b; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid #475569; }

    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .sidebar-menu li a {
        display: flex; align-items: center; padding: 14px 25px; color: #cbd5e1; text-decoration: none;
        font-size: 0.95rem; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent;
    }
    .sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
    .sidebar-menu li a:hover { color: #ffffff; background-color: #1e293b; }
    .sidebar-menu li a.active { color: #ffffff; background-color: #1e293b; border-left-color: #3b82f6; }

    /* CONTENIDO PRINCIPAL */
    .pro-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .pro-topbar {
        background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between;
        padding: 0 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 999;
    }
    
    .btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #475569; cursor: pointer; display: none; }
    .tokens-display { background: #fef3c7; border: 1px solid #fde68a; color: #d97706; padding: 8px 20px; border-radius: 12px; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 10px; }
    
    @media (max-width: 991px) {
        .pro-sidebar { transform: translateX(-100%); }
        .pro-sidebar.show { transform: translateX(0); }
        .pro-main-content { margin-left: 0; }
        .btn-toggle-sidebar { display: block; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 998; display: none; }
        .sidebar-overlay.show { display: block; }
        .pro-topbar { padding: 0 15px; }
    }

    /* =========================================================
       TARJETAS Y PANEL DE CONTROL
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #0f172a; font-size: 1.8rem; margin-bottom: 25px; }

    /* Stats Web */
    .stat-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; display: flex; align-items: center; }
    .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-right: 20px; }
    .stat-value { font-size: 2.2rem; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 5px; }
    .stat-label { font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; }

    /* Panel Central: Mapa y Disponibilidad */
    .radar-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden; display: flex; flex-direction: column; }
    .radar-header { padding: 25px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    
    /* Toggle Switch Robusto para Web */
    .status-toggle { display: flex; align-items: center; gap: 15px; background: #f8fafc; padding: 10px 20px; border-radius: 16px; border: 1px solid #e2e8f0; }
    .status-text { font-size: 1.1rem; font-weight: 800; color: #94a3b8; transition: color 0.3s; }
    .status-text.active { color: #10b981; }
    .switch { position: relative; display: inline-block; width: 70px; height: 36px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 36px; }
    .slider:before { position: absolute; content: ""; height: 28px; width: 28px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    input:checked + .slider { background-color: #10b981; }
    input:checked + .slider:before { transform: translateX(34px); }

    /* Mapa Contenedor */
    .map-container { width: 100%; height: 500px; position: relative; background: #e2e8f0; }
    .map-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity 0.3s; backdrop-filter: blur(3px); }
    .map-overlay.hidden { opacity: 0; pointer-events: none; }
</style>

<div class="pro-wrapper">
    <!-- Overlay para móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- 1. SIDEBAR DEL PROFESIONAL -->
    <aside class="pro-sidebar" id="proSidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-location-crosshairs text-primary"></i> GEO-PRO
        </div>
        
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($profesional['nombre'] ?? 'P', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($profesional['nombre'] ?? 'Profesional') ?></h6>
            <p><?= htmlspecialchars($profesional['nombre_categoria'] ?? 'Técnico') ?></p>
            <span class="plan-badge"><i class="fa-solid fa-gem"></i> Plan <?= htmlspecialchars(str_replace('_', ' ', $profesional['nombre_plan'] ?? 'Básico')) ?></span>
        </div>

        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/profesional/dashboard" class="active"><i class="fa-solid fa-satellite-dish"></i> Centro de Mando</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/solicitudes"><i class="fa-solid fa-inbox"></i> Solicitudes Recibidas</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/historial"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Trabajos</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/comprar-tokens"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil Público</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- 2. CONTENIDO PRINCIPAL WEB -->
    <main class="pro-main-content">
        
        <!-- TOPBAR -->
        <header class="pro-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
                <div class="d-none d-md-block"><span class="text-muted fw-bold">Panel de Recepción de Trabajos</span></div>
            </div>
            
            <div class="tokens-display shadow-sm" title="Tokens Disponibles para aceptar trabajos">
                <i class="fa-solid fa-coins fa-beat"></i> 
                <span><?= (int)($profesional['tokens_disponibles'] ?? 0) ?> Tokens</span>
            </div>
        </header>

        <!-- DASHBOARD WEB -->
        <div class="dashboard-content">
            <h2 class="page-title">Centro de Mando GEO-PRO</h2>

            <!-- Tarjetas de Estadísticas -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-star"></i></div>
                        <div>
                            <div class="stat-value"><?= number_format((float)($profesional['promedio_estrellas'] ?? 5.0), 1) ?></div>
                            <div class="stat-label">Reputación</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-briefcase"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)($stats['trabajos_completados'] ?? 0) ?></div>
                            <div class="stat-label">Trabajos Completados</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-bolt"></i></div>
                        <div>
                            <div class="stat-value">15<span style="font-size: 1rem;">min</span></div>
                            <div class="stat-label">Tiempo de Respuesta</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel del Mapa y Control de Disponibilidad -->
            <div class="radar-card">
                <div class="radar-header">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Radar de Solicitudes</h4>
                        <p class="text-muted small mb-0">Actívate para que la IA te envíe trabajos cercanos (Costo por propuesta: 1 Token).</p>
                    </div>
                    
                    <div class="status-toggle shadow-sm">
                        <span class="status-text" id="statusOff">Desconectado</span>
                        <label class="switch">
                            <input type="checkbox" id="toggleAvailability" <?= ($profesional['estado_disponibilidad'] ?? '') === 'DISPONIBLE' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="status-text <?= ($profesional['estado_disponibilidad'] ?? '') === 'DISPONIBLE' ? 'active' : '' ?>" id="statusOn">¡Disponible!</span>
                    </div>
                </div>

                <div class="map-container">
                    <!-- Overlay de Protección (Bloquea el mapa si está desconectado) -->
                    <div class="map-overlay <?= ($profesional['estado_disponibilidad'] ?? '') === 'DISPONIBLE' ? 'hidden' : '' ?>" id="mapOverlay">
                        <i class="fa-solid fa-power-off fa-4x text-muted mb-3 opacity-50"></i>
                        <h3 class="fw-bold text-dark">Estás Desconectado</h3>
                        <p class="text-muted">Enciende tu disponibilidad en el interruptor de arriba para empezar a recibir clientes en La Paz.</p>
                    </div>
                    
                    <!-- Integración del Mapa de Leaflet -->
                    <div id="radarMap" style="width: 100%; height: 100%; z-index: 1;"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Estilos y Scripts de Leaflet (Mapa) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Lógica del Menú Responsive
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    function toggleMenu() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
    if(btnToggle) btnToggle.addEventListener('click', toggleMenu);
    if(overlay) overlay.addEventListener('click', toggleMenu);

    // 2. Inicialización del Mapa de Trabajo (Centrado en La Paz por defecto)
    const latBase = <?= $profesional['latitud_actual'] ?? -16.5000 ?>;
    const lngBase = <?= $profesional['longitud_actual'] ?? -68.1500 ?>;
    
    const mapa = L.map('radarMap', { zoomControl: true }).setView([latBase, lngBase], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    // Marcador del Profesional
    const proIcon = L.divIcon({ 
        html: '<div style="background:#0d6efd; color:white; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3);"><i class="fa-solid fa-motorcycle"></i></div>', 
        className: 'bg-transparent', iconSize: [40, 40], iconAnchor: [20, 20]
    });
    L.marker([latBase, lngBase], { icon: proIcon }).addTo(mapa).bindPopup('<b>Tu ubicación actual</b>');

    // Círculo de Radar (Área de influencia)
    const radioInfluencia = L.circle([latBase, lngBase], {
        color: '#10b981', fillColor: '#10b981', fillOpacity: 0.1, radius: 3000 // 3 km a la redonda
    }).addTo(mapa);

    // 3. Lógica del Botón de Disponibilidad (Toggle)
    document.getElementById('toggleAvailability').addEventListener('change', function() {
        const statusOn = document.getElementById('statusOn');
        const statusOff = document.getElementById('statusOff');
        const mapOverlay = document.getElementById('mapOverlay');

        if(this.checked) {
            statusOn.classList.add('active');
            statusOff.classList.remove('active');
            mapOverlay.classList.add('hidden');
            // Aquí iría el AJAX para actualizar a DISPONIBLE en BD
            console.log("Sistema Activado. Buscando clientes...");
        } else {
            statusOn.classList.remove('active');
            statusOff.classList.add('active');
            mapOverlay.classList.remove('hidden');
            // Aquí iría el AJAX para actualizar a NO_DISPONIBLE en BD
            console.log("Sistema Desactivado.");
        }
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>