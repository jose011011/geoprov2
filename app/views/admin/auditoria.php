<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Estilos base y Sidebar fijo con scroll */
    .geo-navbar { display: none !important; }
    body { background-color: #f3f4f7; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }
    .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
    .admin-sidebar { width: 260px; background-color: #1e1e2d; color: #a2a3b7; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; padding-bottom: 50px; }
    .admin-sidebar::-webkit-scrollbar { width: 6px; }
    .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
    .admin-sidebar::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); border-radius: 10px; }
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
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 998; display: none; }
        .sidebar-overlay.show { display: block; }
    }

    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #1e293b; font-size: 1.6rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    .toolbar-card { background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: none; display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center; }
    .search-box { position: relative; width: 100%; max-width: 350px; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-box input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; transition: all 0.2s; }
    .search-box input:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .table-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; border: none; }
    .table { margin-bottom: 0; }
    .table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 15px 25px; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .table td { vertical-align: middle; padding: 15px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    
    .action-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
    .action-badge.success { background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0; }
    .action-badge.danger { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .action-badge.primary { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
    .ip-box { font-family: monospace; font-size: 0.85rem; color: #94a3b8; background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px dashed #cbd5e1; }

    /* Estilos Paginación */
    .pagination-container { padding: 20px 25px; background: #fff; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; }
    .pagination-info { color: #64748b; font-size: 0.9rem; font-weight: 600; }
    .pagination-btns button { background: #ffffff; border: 1px solid #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 8px; font-weight: 600; margin-left: 5px; transition: all 0.2s; }
    .pagination-btns button:hover:not(:disabled) { background: #f1f5f9; color: #0f172a; }
    .pagination-btns button.active { background: #0d6efd; color: white; border-color: #0d6efd; }
    .pagination-btns button:disabled { opacity: 0.5; cursor: not-allowed; }
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
            <li><a href="<?= BASE_URL ?>/admin/dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria" class="active"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
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
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Centro de Seguridad y Logs</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title"><i class="fa-solid fa-server text-muted me-2"></i>Auditoría del Sistema</h3>
            
            <div class="toolbar-card">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por responsable, acción o tabla...">
                </div>
                <div><span class="text-muted small fw-bold me-2"><i class="fa-solid fa-shield-check text-success"></i> Sistema Protegido</span></div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table" id="auditTable">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th><th>Responsable</th><th>Acción Realizada</th><th>Módulo / Tabla</th><th>Dirección IP</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        <?php foreach ($logs as $log): ?>
                            <?php 
                                $accionStr = strtoupper($log['accion']);
                                $badgeClase = 'secondary'; $icono = 'fa-circle-dot';
                                if (str_contains($accionStr, 'APROBAR') || str_contains($accionStr, 'CREAR') || str_contains($accionStr, 'CONFIRMAR')) { $badgeClase = 'success'; $icono = 'fa-check'; }
                                elseif (str_contains($accionStr, 'RECHAZAR') || str_contains($accionStr, 'ELIMINAR') || str_contains($accionStr, 'BLOQUEAR')) { $badgeClase = 'danger'; $icono = 'fa-xmark'; }
                                elseif (str_contains($accionStr, 'CAMBIO') || str_contains($accionStr, 'TOGGLE') || str_contains($accionStr, 'REVISION')) { $badgeClase = 'primary'; $icono = 'fa-pen-to-square'; }
                            ?>
                            <tr class="log-row">
                                <td class="text-muted fw-semibold"><i class="fa-regular fa-clock me-1 opacity-50"></i> <?= date('d/m/Y H:i', strtotime($log['fecha_evento'])) ?></td>
                                <td><div class="fw-bold text-dark"><i class="fa-solid fa-user-shield text-muted me-1"></i> <?= htmlspecialchars($log['responsable']) ?></div></td>
                                <td><span class="action-badge <?= $badgeClase ?>"><i class="fa-solid <?= $icono ?>"></i> <?= htmlspecialchars($log['accion']) ?></span></td>
                                <td><span class="fw-semibold text-secondary"><i class="fa-solid fa-database text-muted opacity-50 me-1"></i> <?= htmlspecialchars($log['tabla_afectada']) ?></span></td>
                                <td><span class="ip-box"><?= htmlspecialchars($log['ip_origen']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación Interactiva -->
                <div class="pagination-container">
                    <div class="pagination-info" id="pageInfo">Mostrando 0 a 0 de 0 registros</div>
                    <div class="pagination-btns" id="paginationBtns">
                        <!-- Botones generados por JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Menu Sidebar
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // Lógica de Paginación y Búsqueda
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const rows = Array.from(tableBody.querySelectorAll('.log-row'));
    const pageInfo = document.getElementById('pageInfo');
    const paginationBtns = document.getElementById('paginationBtns');
    
    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = [...rows];

    function renderTable() {
        // Ocultar todas
        rows.forEach(row => row.style.display = 'none');
        
        // Calcular indices
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        // Mostrar solo las correspondientes
        const rowsToShow = filteredRows.slice(start, end);
        rowsToShow.forEach(row => row.style.display = '');

        // Actualizar Info
        const total = filteredRows.length;
        const showingStart = total === 0 ? 0 : start + 1;
        const showingEnd = end > total ? total : end;
        pageInfo.textContent = `Mostrando ${showingStart} a ${showingEnd} de ${total} registros`;

        renderPagination(total);
    }

    function renderPagination(totalRows) {
        paginationBtns.innerHTML = '';
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        if (totalPages <= 1) return;

        // Btn Anterior
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { currentPage--; renderTable(); };
        paginationBtns.appendChild(prevBtn);

        // Numeros de pagina
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            if (i === currentPage) btn.classList.add('active');
            btn.onclick = () => { currentPage = i; renderTable(); };
            paginationBtns.appendChild(btn);
        }

        // Btn Siguiente
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { currentPage++; renderTable(); };
        paginationBtns.appendChild(nextBtn);
    }

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        filteredRows = rows.filter(row => row.textContent.toLowerCase().includes(filter));
        currentPage = 1; // Volver a la página 1 al buscar
        renderTable();
    });

    // Iniciar
    renderTable();
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>