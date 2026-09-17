<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Ocultamos el header normal */
    .geo-navbar { display: none !important; }
    body { background-color: #f3f4f7; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    /* Layout Principal */
    .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
    
    /* Sidebar Fijo */
    .admin-sidebar {
        width: 260px; background-color: #1e1e2d; color: #a2a3b7;
        position: fixed; top: 0; left: 0; height: 100vh; z-index: 1000;
        transition: all 0.3s ease; overflow-y: auto;
    }
    .sidebar-profile { padding: 30px 20px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .sidebar-avatar { width: 70px; height: 70px; border-radius: 50%; background-color: #e83e8c; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 10px; border: 3px solid #2b2b40; }
    .sidebar-profile h6 { color: #ffffff; font-weight: 700; margin-bottom: 2px; }
    .sidebar-profile p { font-size: 0.75rem; color: #a2a3b7; margin-bottom: 0; }

    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .menu-title { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #6c7293; padding: 10px 25px; letter-spacing: 1px; }
    .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #a2a3b7; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.3s; border-left: 3px solid transparent; }
    .sidebar-menu li a i { width: 25px; font-size: 1.1rem; }
    .sidebar-menu li a:hover { color: #ffffff; background-color: #1b1b29; }
    .sidebar-menu li a.active { color: #ffffff; background-color: #1b1b29; border-left-color: #e83e8c; }

    /* Contenido Principal */
    .admin-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .admin-topbar { background-color: #ffffff; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); position: sticky; top: 0; z-index: 999; }
    .btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #495057; cursor: pointer; display: none; }
    .topbar-logout { background: #fef2f2; color: #dc2626; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; }
    .topbar-logout:hover { background: #dc2626; color: #ffffff; }

    @media (max-width: 991px) {
        .admin-sidebar { transform: translateX(-100%); }
        .admin-sidebar.show { transform: translateX(0); }
        .admin-main-content { margin-left: 0; }
        .btn-toggle-sidebar { display: block; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .sidebar-overlay.show { display: block; }
    }

    /* Tarjetas y Tablas */
    .dashboard-content { padding: 25px; }
    .page-title { font-weight: 700; color: #3f4254; font-size: 1.5rem; margin-bottom: 20px; }
    
    .kpi-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 0 20px rgba(0,0,0,0.03); display: flex; flex-direction: column; border: none; }
    .kpi-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .kpi-number { font-size: 2rem; font-weight: 800; color: #181c32; line-height: 1; }
    .kpi-label { font-size: 0.8rem; font-weight: 700; color: #b5b5c3; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-icon { padding: 10px; border-radius: 10px; font-size: 1.2rem; color: #fff; }
    
    .table-card { background: #fff; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.03); padding: 20px; border: none; overflow: hidden;}
    .table th { font-size: 0.75rem; text-transform: uppercase; color: #b5b5c3; border-bottom: 1px dashed #ebedf3; padding: 15px 10px; }
    .table td { vertical-align: middle; border-bottom: 1px dashed #ebedf3; padding: 15px 10px; color: #3f4254; font-weight: 500; font-size: 0.9rem; }
</style>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)); ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
            <p>Super Administrador</p>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-title">Resumen</li>
            <li><a href="<?= BASE_URL ?>/admin/dashboard" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
            <li><a href="<?= BASE_URL ?>/admin/clientes"><i class="fa-solid fa-users"></i> Clientes</a></li>
            
            <li class="menu-title">Finanzas y Configuración</li>
            <li><a href="<?= BASE_URL ?>/admin/pagos"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
            <li><a href="<?= BASE_URL ?>/admin/categorias"><i class="fa-solid fa-layer-group"></i> Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reportes"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
        </ul>
    </aside>

    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Plataforma GEO-PRO</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title">Dashboard Operativo</h3>

            <div class="row g-4 mb-4">
                <div class="col-md-3 col-6">
                    <div class="kpi-card" style="border-bottom: 4px solid #f6c23e;">
                        <div class="kpi-top"><span class="kpi-label">Por Validar</span><div class="kpi-icon" style="background: #f6c23e;"><i class="fa-solid fa-user-clock"></i></div></div>
                        <div class="kpi-number"><?= (int) ($stats['pendientes'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="kpi-card" style="border-bottom: 4px solid #1cc88a;">
                        <div class="kpi-top"><span class="kpi-label">Aprobados</span><div class="kpi-icon" style="background: #1cc88a;"><i class="fa-solid fa-user-shield"></i></div></div>
                        <div class="kpi-number"><?= (int) ($stats['aprobados'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="kpi-card" style="border-bottom: 4px solid #36b9cc;">
                        <div class="kpi-top"><span class="kpi-label">Clientes</span><div class="kpi-icon" style="background: #36b9cc;"><i class="fa-solid fa-users"></i></div></div>
                        <div class="kpi-number"><?= (int) ($stats['total_clientes'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="kpi-card" style="border-bottom: 4px solid #858796;">
                        <div class="kpi-top"><span class="kpi-label">Servicios</span><div class="kpi-icon" style="background: #858796;"><i class="fa-solid fa-briefcase"></i></div></div>
                        <div class="kpi-number"><?= (int) ($stats['total_solicitudes'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <h5 class="fw-bold mb-0">Control de Profesionales</h5>
                    <div class="btn-group mt-3 mt-md-0 shadow-sm">
                        <a href="?estado=PENDIENTE" class="btn btn-sm btn-outline-warning <?= ($filtroActual ?? '') === 'PENDIENTE' ? 'active' : '' ?>">Pendientes</a>
                        <a href="?estado=APROBADO" class="btn btn-sm btn-outline-success <?= ($filtroActual ?? '') === 'APROBADO' ? 'active' : '' ?>">Aprobados</a>
                        <a href="?estado=RECHAZADO" class="btn btn-sm btn-outline-danger <?= ($filtroActual ?? '') === 'RECHAZADO' ? 'active' : '' ?>">Rechazados</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Profesional</th>
                                <th>Especialidad</th>
                                <th>Clasificación</th>
                                <th>Plan / Nivel</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($profesionales) && is_array($profesionales)): ?>
                                <?php foreach ($profesionales as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')) ?></div>
                                            <small class="text-muted">CI/NIT: <?= htmlspecialchars($p['numero_documento'] ?? 'S/N') ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin Categoría') ?></span></td>
                                        <td>
                                            <?php if (($p['tipo_prestador'] ?? '') === 'TECNICO_PROFESIONAL'): ?>
                                                <span class="text-primary fw-bold small"><i class="fa-solid fa-user-graduate"></i> Profesional</span>
                                            <?php else: ?>
                                                <span class="text-warning text-dark fw-bold small"><i class="fa-solid fa-hammer"></i> Empírico</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                // Convertir el nombre del plan a un formato amigable (ej: GRATUITO_TOKENS -> Gratuito Tokens)
                                                $nombrePlan = str_replace('_', ' ', htmlspecialchars($p['nombre_plan'] ?? 'Básico')); 
                                            ?>
                                            <span class="badge bg-dark text-white fw-bold"><i class="fa-solid fa-gem text-warning me-1"></i> <?= ucwords(strtolower($nombrePlan)) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill <?= claseBadgeEstado($p['estado_validacion'] ?? '') ?>"><?= htmlspecialchars($p['estado_validacion'] ?? 'PENDIENTE') ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/verProfesional/<?= $p['id_profesional'] ?? 0 ?>" class="btn btn-sm <?= ($p['estado_validacion'] ?? '') === 'PENDIENTE' ? 'btn-primary' : 'btn-light border text-dark' ?> fw-bold px-3">
                                                <?= ($p['estado_validacion'] ?? '') === 'PENDIENTE' ? 'Revisar' : 'Ver Perfil' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (empty($profesionales)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa-solid fa-folder-open fa-2x mb-2 opacity-50"></i><br>No hay registros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleMenu() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
    if(btnToggle) btnToggle.addEventListener('click', toggleMenu);
    if(overlay) overlay.addEventListener('click', toggleMenu);
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>