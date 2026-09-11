<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       HEREDAMOS LA ESTRUCTURA DEL SIDEBAR DEL PROFESIONAL
       ========================================================= */
    .geo-navbar { display: none !important; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .pro-wrapper { display: flex; width: 100%; min-height: 100vh; }
    
    .pro-sidebar { width: 260px; background-color: #0f172a; color: #94a3b8; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; border-right: 1px solid #1e293b; padding-bottom: 50px; }
    .pro-sidebar::-webkit-scrollbar { width: 6px; }
    .pro-sidebar::-webkit-scrollbar-track { background: transparent; }
    .pro-sidebar::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); border-radius: 10px; }
    
    .sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; color: #ffffff; font-size: 1.5rem; font-weight: 900; letter-spacing: 1px; }
    .sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; }
    .sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background-color: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid #1e293b; }
    .sidebar-profile h6 { color: #f8fafc; font-weight: 700; margin-bottom: 5px; font-size: 1.1rem; }
    .sidebar-profile p { font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; }

    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; }
    .sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
    .sidebar-menu li a:hover { color: #ffffff; background-color: #1e293b; }
    .sidebar-menu li a.active { color: #ffffff; background-color: #1e293b; border-left-color: #3b82f6; }

    .pro-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .pro-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 999; }
    .btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #475569; cursor: pointer; display: none; }
    
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
       DISEÑO DEL HISTORIAL DE TRABAJOS
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #0f172a; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    .history-card { background: #fff; border-radius: 20px; padding: 20px 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; transition: transform 0.2s; }
    .history-card:hover { transform: translateX(5px); border-color: #cbd5e1; }
    .history-card.success { border-left: 5px solid #10b981; }
    .history-card.canceled { border-left: 5px solid #ef4444; }

    .history-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .history-icon.success { background: #ecfdf5; color: #10b981; }
    .history-icon.canceled { background: #fef2f2; color: #ef4444; }

    .history-details { flex-grow: 1; min-width: 250px; }
    .history-title { font-weight: 800; color: #1e293b; font-size: 1.1rem; margin-bottom: 2px; }
    .history-meta { color: #64748b; font-size: 0.85rem; font-weight: 500; }
    .history-code { font-family: monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; }

    .history-price { text-align: right; }
    .price-value { font-size: 1.3rem; font-weight: 900; color: #0f172a; }
    .price-label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; }
</style>

<div class="pro-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="pro-sidebar" id="proSidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-location-crosshairs text-primary"></i> GEO-PRO</div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($perfil['nombre'] ?? 'P', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($perfil['nombre'] ?? 'Profesional') ?></h6>
            <p><?= htmlspecialchars($perfil['nombre_categoria'] ?? 'Técnico') ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/profesional/dashboard"><i class="fa-solid fa-satellite-dish"></i> Centro de Mando</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/solicitudes"><i class="fa-solid fa-inbox"></i> Alertas de Trabajo</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/historial" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/comprarTokens"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="pro-main-content">
        <header class="pro-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Registro de Actividad</span></div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Historial de Trabajos</h2>
            <p class="page-subtitle">Aquí puedes ver todos los servicios que has finalizado o que fueron cancelados.</p>

            <div class="mt-4">
                <?php if (!empty($solicitudes)): ?>
                    <?php foreach ($solicitudes as $s): ?>
                        <?php 
                            $esFinalizada = ($s['estado_servicio'] ?? '') === 'FINALIZADA';
                            $cardClass = $esFinalizada ? 'success' : 'canceled';
                            $iconClass = $esFinalizada ? 'fa-check' : 'fa-xmark';
                            $fecha = date('d M Y - H:i', strtotime($s['fecha_finalizacion'] ?? $s['fecha_solicitud']));
                        ?>
                        <div class="history-card <?= $cardClass ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="history-icon <?= $cardClass ?>"><i class="fa-solid <?= $iconClass ?>"></i></div>
                                <div class="history-details">
                                    <div class="history-title">
                                        <?= htmlspecialchars(($s['cliente_nombre'] ?? 'Cliente') . ' ' . ($s['cliente_apellido'] ?? '')) ?>
                                    </div>
                                    <div class="history-meta">
                                        <i class="fa-regular fa-calendar me-1"></i> <?= $fecha ?> | 
                                        <span class="history-code">#<?= htmlspecialchars($s['codigo_seguimiento'] ?? '0000') ?></span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($s['zona'] ?? 'Zona no definida') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="history-price">
                                <div class="price-value">
                                    <?= $esFinalizada ? 'Bs. ' . number_format((float)($s['precio_acordado'] ?? 0), 2) : '<span class="text-danger fs-6">Cancelado</span>' ?>
                                </div>
                                <div class="price-label"><?= $esFinalizada ? 'Monto Cobrado' : 'Estado' ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Historial en blanco</h5>
                        <p class="text-muted">Aún no has completado ningún trabajo en la plataforma.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>