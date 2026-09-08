<?php require_once "../app/views/layouts/header.php"; ?>

<style>
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
    
    .toolbar-card { background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: none; }
    .toolbar-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; } /* Añadida 4ta columna */
    @media (max-width: 991px) { .toolbar-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 576px) { .toolbar-grid { grid-template-columns: 1fr; } }
    
    .search-box { position: relative; width: 100%; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-box input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; transition: all 0.2s; }
    .search-box input:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .filter-select { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; width: 100%; color: #475569; outline: none; cursor: pointer; }
    
    .status-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .tab-btn { padding: 8px 20px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s; }
    .tab-btn.active { background: #1e293b; color: #ffffff; border-color: #1e293b; }

    .table-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; border: none; }
    .table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 15px 25px; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .table td { vertical-align: middle; padding: 15px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    
    .pagination-container { padding: 20px 25px; background: #fff; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 15px;}
    .pagination-info { color: #64748b; font-size: 0.9rem; font-weight: 600; }
    .pagination-btns button { background: #ffffff; border: 1px solid #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 8px; font-weight: 600; margin-left: 5px; transition: all 0.2s; }
    .pagination-btns button.active { background: #0d6efd; color: white; border-color: #0d6efd; }
    .pagination-btns button:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
            <p>Super Admin</p>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-title">Resumen</li>
            <li><a href="<?= BASE_URL ?>/admin/dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales" class="active"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
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
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Directorio de Profesionales</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title">Gestión de Profesionales y Empíricos</h3>
            
            <div class="status-tabs mt-4">
                <a href="?estado=TODOS" class="tab-btn <?= ($filtroActual === 'TODOS' || empty($filtroActual)) ? 'active' : '' ?>">Todos</a>
                <a href="?estado=PENDIENTE" class="tab-btn <?= $filtroActual === 'PENDIENTE' ? 'active' : '' ?>">Pendientes</a>
                <a href="?estado=APROBADO" class="tab-btn <?= $filtroActual === 'APROBADO' ? 'active' : '' ?>">Aprobados</a>
                <a href="?estado=RECHAZADO" class="tab-btn <?= $filtroActual === 'RECHAZADO' ? 'active' : '' ?>">Rechazados</a>
            </div>

            <!-- Filtros Inteligentes Avanzados -->
            <div class="toolbar-card">
                <div class="toolbar-grid">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar nombre o CI...">
                    </div>
                    <div>
                        <select id="filterCategoria" class="filter-select">
                            <option value="">Todas las Categorías</option>
                            <!-- Se llena por JS -->
                        </select>
                    </div>
                    <div>
                        <!-- PLANES FIJOS (Solución a tu observación) -->
                        <select id="filterPlan" class="filter-select">
                            <option value="">Todos los Planes</option>
                            <option value="gratuito_tokens">Plan Bronce (Gratuito)</option>
                            <option value="basico_mensual">Plan Plata (Básico)</option>
                            <option value="premium_destacado">Plan Oro (Premium)</option>
                        </select>
                    </div>
                    <div>
                        <!-- SELECTOR DE CANTIDAD (5, 10, 20, 100) -->
                        <select id="selectLimit" class="filter-select">
                            <option value="5">Mostrar 5 por página</option>
                            <option value="10" selected>Mostrar 10 por página</option>
                            <option value="20">Mostrar 20 por página</option>
                            <option value="50">Mostrar 50 por página</option>
                            <option value="100">Mostrar 100 por página</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Profesional</th>
                                <th>Oficio / Categoría</th>
                                <th>Nivel de Plan</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        <?php if (isset($profesionales) && is_array($profesionales)): ?>
                            <?php foreach ($profesionales as $p): ?>
                                <?php 
                                    $nombreCat = htmlspecialchars($p['nombre_categoria'] ?? 'Sin Categoría');
                                    // Guardamos el código original del plan (ej: premium_destacado) para el JS
                                    $codigoPlan = strtolower($p['nombre_plan'] ?? '');
                                    $nombrePlanDisplay = str_replace('_', ' ', htmlspecialchars($p['nombre_plan'] ?? 'Básico')); 
                                    $estado = htmlspecialchars($p['estado_validacion'] ?? 'PENDIENTE');
                                ?>
                                <tr class="data-row" data-categoria="<?= $nombreCat ?>" data-plan="<?= $codigoPlan ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-primary fw-bold me-3 border" style="width: 42px; height: 42px;">
                                                <?= strtoupper(substr($p['nombre'] ?? 'U', 0, 1) . substr($p['apellido'] ?? '', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')) ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> <?= htmlspecialchars($p['numero_documento'] ?? 'S/N') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border px-2 py-1"><?= $nombreCat ?></span><br>
                                        <small class="text-muted fw-bold"><?= ($p['tipo_prestador'] ?? '') === 'TECNICO_PROFESIONAL' ? 'Profesional' : 'Empírico' ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white"><i class="fa-solid fa-gem text-warning me-1"></i> Plan <?= ucwords(strtolower($nombrePlanDisplay)) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= claseBadgeEstado($estado) ?>"><?= $estado ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/verProfesional/<?= $p['id_profesional'] ?? 0 ?>" class="btn btn-sm <?= $estado === 'PENDIENTE' ? 'btn-primary' : 'btn-light border text-dark' ?> fw-bold px-3">
                                            <?= $estado === 'PENDIENTE' ? 'Auditar' : 'Ver Perfil' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <div class="pagination-info" id="pageInfo">Mostrando 0 a 0 de 0 registros</div>
                    <div class="pagination-btns" id="paginationBtns"></div>
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
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    const searchInput = document.getElementById('searchInput');
    const filterCategoria = document.getElementById('filterCategoria');
    const filterPlan = document.getElementById('filterPlan');
    const selectLimit = document.getElementById('selectLimit'); // Nuevo Selector
    
    const tableBody = document.getElementById('tableBody');
    const rows = Array.from(tableBody.querySelectorAll('.data-row'));
    const pageInfo = document.getElementById('pageInfo');
    const paginationBtns = document.getElementById('paginationBtns');
    
    let currentPage = 1;
    let rowsPerPage = parseInt(selectLimit.value); // Lee el valor (10 por defecto)
    let filteredRows = [...rows];

    // Llenar solo el select de Categorías dinámicamente
    const categoriasSet = new Set();
    rows.forEach(row => categoriasSet.add(row.getAttribute('data-categoria')));
    categoriasSet.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat; option.textContent = cat;
        filterCategoria.appendChild(option);
    });

    function renderTable() {
        rows.forEach(row => row.style.display = 'none');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const rowsToShow = filteredRows.slice(start, end);
        rowsToShow.forEach(row => row.style.display = '');

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

        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { currentPage--; renderTable(); };
        paginationBtns.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            if (i === currentPage) btn.classList.add('active');
            btn.onclick = () => { currentPage = i; renderTable(); };
            paginationBtns.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { currentPage++; renderTable(); };
        paginationBtns.appendChild(nextBtn);
    }

    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const catValue = filterCategoria.value;
        const planValue = filterPlan.value;

        filteredRows = rows.filter(row => {
            const matchesSearch = row.textContent.toLowerCase().includes(searchText);
            const matchesCat = catValue === "" || row.getAttribute('data-categoria') === catValue;
            const matchesPlan = planValue === "" || row.getAttribute('data-plan') === planValue;
            return matchesSearch && matchesCat && matchesPlan;
        });

        currentPage = 1; 
        renderTable();
    }

    // Eventos
    searchInput.addEventListener('keyup', applyFilters);
    filterCategoria.addEventListener('change', applyFilters);
    filterPlan.addEventListener('change', applyFilters);
    
    // Evento para cambiar el número de registros por página
    selectLimit.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderTable();
    });

    renderTable();
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>