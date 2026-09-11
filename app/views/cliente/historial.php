<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO CLIENTE — HISTORIAL UI
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-primary: #08b7a5; --geo-primary-dark: #079486; --geo-primary-soft: #e8faf7;
    --geo-blue: #2563eb; --geo-text: #172033; --geo-muted: #718096; --geo-bg: #f5f7fa; --geo-border: #e8edf2;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; overflow-x: hidden; margin: 0; }
.geo-navbar { display: none !important; }

/* Sidebar Consistente */
.cli-wrapper { display: flex; width: 100%; min-height: 100vh; }
.cli-sidebar { width: 260px; background-color: #ffffff; border-right: 1px solid var(--geo-border); position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; overflow-y: auto; padding-bottom: 50px; transition: transform 0.3s ease; }
.sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); color: var(--geo-dark); font-size: 1.5rem; font-weight: 900; }
.sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); }
.sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--geo-primary), var(--geo-blue)); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid var(--geo-primary-soft); }
.sidebar-profile h6 { color: var(--geo-dark); font-weight: 800; margin-bottom: 5px; font-size: 1.1rem; }
.role-badge { background: var(--geo-primary-soft); color: var(--geo-primary-dark); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid #c8ebe6; }

.sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
.sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #475569; text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.2s; border-left: 4px solid transparent; }
.sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
.sidebar-menu li a:hover { color: var(--geo-primary); background-color: #f8fafc; }
.sidebar-menu li a.active { color: var(--geo-primary); background-color: var(--geo-primary-soft); border-left-color: var(--geo-primary); }

.cli-main-content { flex: 1; margin-left: 260px; min-width: 0; display: flex; flex-direction: column; }
.cli-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; border-bottom: 1px solid var(--geo-border); position: sticky; top: 0; z-index: 999; }
.btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #475569; cursor: pointer; display: none; }

@media (max-width: 991px) {
    .cli-sidebar { transform: translateX(-100%); }
    .cli-sidebar.show { transform: translateX(0); }
    .cli-main-content { margin-left: 0; }
    .btn-toggle-sidebar { display: block; }
    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 998; display: none; }
    .sidebar-overlay.show { display: block; }
}

/* Tarjetas de Pedidos */
.dashboard-content { padding: 40px; max-width: 1000px; margin: 0 auto; width: 100%; }
.page-title { font-weight: 800; color: var(--geo-dark); font-size: 2rem; margin-bottom: 5px; }
.page-subtitle { color: var(--geo-muted); font-size: 1rem; margin-bottom: 30px; }

.pedido-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid var(--geo-border); margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; transition: transform 0.2s; }
.pedido-card:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }

.badge-estado { padding: 8px 15px; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; }
.estado-pendiente { background: #fef3c7; color: #92400e; }
.estado-aceptada { background: #e0f2fe; color: #0284c7; }
.estado-camino { background: #dbeafe; color: #1e40af; }
.estado-proceso { background: #d1fae5; color: #059669; }
.estado-finalizada { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
</style>

<div class="cli-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="cli-sidebar" id="cliSidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-location-crosshairs" style="color:var(--geo-primary);"></i> GEO-PRO</div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'C', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Cliente') ?></h6>
            <span class="role-badge">Modo Cliente</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/cliente/dashboard"><i class="fa-solid fa-house"></i> Panel de Inicio</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/historial" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Mis Solicitudes</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/favoritos"><i class="fa-solid fa-heart text-danger"></i> Favoritos</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/perfil"><i class="fa-solid fa-map-location-dot"></i> Direcciones</a></li>
            
            <?php if(isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 3): ?>
                <li class="menu-title mt-4 px-4 text-muted" style="font-size:0.7rem; font-weight:800;">Modo Dual</li>
                <li>
                    <a href="<?= BASE_URL ?>/profesional/dashboard" style="background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 10px; margin: 0 15px;">
                        <i class="fa-solid fa-briefcase"></i> Volver a Profesional
                    </a>
                </li>
            <?php endif; ?>

            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-3"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="cli-main-content">
        <header class="cli-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="fw-bold text-muted">Historial General</div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Mis Solicitudes</h2>
            <p class="page-subtitle">Aquí puedes ver todos tus servicios activos, en proceso y completados.</p>

            <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                    <?php 
                        // Colores según el estado
                        $estado = $p['estado_servicio'];
                        $claseEstado = 'estado-finalizada';
                        $bordeIzquierdo = '#cbd5e1';
                        
                        if($estado === 'PENDIENTE') { $claseEstado = 'estado-pendiente'; $bordeIzquierdo = '#f59e0b'; }
                        if($estado === 'ACEPTADA') { $claseEstado = 'estado-aceptada'; $bordeIzquierdo = '#38bdf8'; }
                        if($estado === 'EN_CAMINO') { $claseEstado = 'estado-camino'; $bordeIzquierdo = '#3b82f6'; }
                        if($estado === 'EN_PROCESO') { $claseEstado = 'estado-proceso'; $bordeIzquierdo = '#10b981'; }
                    ?>
                    
                    <div class="pedido-card" style="border-left: 5px solid <?= $bordeIzquierdo ?>;">
                        <div>
                            <div class="text-muted small fw-bold mb-1">
                                <i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($p['codigo_seguimiento']) ?> | <?= date('d M - H:i', strtotime($p['fecha_solicitud'])) ?>
                            </div>
                            <h5 class="fw-bold text-dark m-0"><?= htmlspecialchars($p['nombre_categoria']) ?></h5>
                            
                            <?php if ($estado !== 'PENDIENTE'): ?>
                                <div class="mt-2 text-secondary small">
                                    <i class="fa-solid fa-user-check me-1"></i> Asignado a: <strong><?= htmlspecialchars($p['prof_nombre'] . ' ' . $p['prof_apellido']) ?></strong>
                                </div>
                            <?php else: ?>
                                <div class="mt-2 text-warning small fw-bold">
                                    <i class="fa-solid fa-spinner fa-spin me-1"></i> Esperando a que un técnico acepte...
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-end">
                            <div class="badge-estado <?= $claseEstado ?> mb-2">
                                <?= str_replace('_', ' ', $estado) ?>
                            </div>
                            <br>
                            
                            <?php if($estado === 'FINALIZADA'): ?>
                                <strong class="text-dark fs-5">Bs. <?= number_format((float)$p['precio_acordado'], 2) ?></strong>
                            <?php else: ?>
                                <!-- Botón para ir al Seguimiento o Chat si está Activo -->
                                <a href="<?= BASE_URL ?>/solicitud/detalle/<?= $p['id_solicitud'] ?>" class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3">
                                    Ver Detalles / Chat
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-5 bg-white border rounded-4 shadow-sm">
                    <i class="fa-solid fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                    <h4 class="fw-bold text-dark">Aún no tienes solicitudes</h4>
                    <p class="text-muted">Cuando pidas un profesional, aparecerá en esta lista.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('cliSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>