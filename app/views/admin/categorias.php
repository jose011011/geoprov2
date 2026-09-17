<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       HEREDAMOS LA ESTRUCTURA DEL SIDEBAR (CON SCROLL REPARADO)
       ========================================================= */
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

    /* =========================================================
       DISEÑO DE CATEGORÍAS (FORMULARIO, FILTROS Y TABLA)
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #1e293b; font-size: 1.6rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    /* Formulario Creación de Categoría */
    .form-card { background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 30px; border: 1px solid #f1f5f9; border-left: 5px solid #10b981; }
    .form-card h6 { font-weight: 800; color: #1e293b; margin-bottom: 15px; }
    .form-control-custom, .form-select-custom { width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; transition: all 0.2s; color: #475569; }
    .form-control-custom:focus, .form-select-custom:focus { outline: none; border-color: #10b981; background: #ffffff; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    
    /* Toolbar Superior */
    .toolbar-card { background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: none; display: flex; gap: 15px; flex-wrap: wrap;}
    .search-box { position: relative; flex-grow: 1; min-width: 250px;}
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-box input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; transition: all 0.2s; }
    .search-box input:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .filter-select { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; color: #475569; outline: none; cursor: pointer; }

    /* Tabla */
    .table-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; border: none; }
    .table { margin-bottom: 0; }
    .table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 15px 25px; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .table td { vertical-align: middle; padding: 15px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    .icon-box { width: 45px; height: 45px; border-radius: 12px; background: #ecfdf5; color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    
    .pagination-container { padding: 20px 25px; background: #fff; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 15px;}
    .pagination-info { color: #64748b; font-size: 0.9rem; font-weight: 600; }
    .pagination-btns button { background: #ffffff; border: 1px solid #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 8px; font-weight: 600; margin-left: 5px; transition: all 0.2s; }
    .pagination-btns button.active { background: #0d6efd; color: white; border-color: #0d6efd; }
    .pagination-btns button:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Modal SaaS */
    .custom-modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .custom-modal-overlay.active { opacity: 1; pointer-events: auto; }
    .custom-modal-card { background: #ffffff; width: 90%; max-width: 400px; border-radius: 24px; padding: 30px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .custom-modal-overlay.active .custom-modal-card { transform: scale(1); }
    .modal-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px; }
    .modal-icon.danger { background: #fef2f2; color: #dc2626; }
    .modal-icon.warning { background: #fef3c7; color: #d97706; }
    .modal-title { font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 10px; }
    .modal-text { color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
    .modal-btn.cancel { background: #f1f5f9; color: #475569; }
    .modal-btn.confirm-action { background: #1e293b; color: #ffffff; }
</style>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)); ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
            <p>Super Admin</p>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-title">Resumen</li>
            <li><a href="<?= BASE_URL ?>/admin/dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
            <li><a href="<?= BASE_URL ?>/admin/clientes"><i class="fa-solid fa-users"></i> Clientes</a></li>
            <li class="menu-title">Finanzas y Configuración</li>
            <li><a href="<?= BASE_URL ?>/admin/pagos"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
            <li><a href="<?= BASE_URL ?>/admin/categorias" class="active"><i class="fa-solid fa-layer-group"></i> Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reportes"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
        </ul>
    </aside>

    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Configuración de Plataforma</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title">Gestión de Oficios y Categorías</h3>
            <p class="page-subtitle">Administra los servicios que los clientes pueden buscar y solicitar a través del modelo de Inteligencia Artificial.</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success fw-bold"><i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <!-- FORMULARIO NUEVA CATEGORÍA -->
            <div class="form-card">
                <h6><i class="fa-solid fa-plus-circle text-success me-2"></i>Crear Nueva Categoría</h6>
                <form method="POST" action="<?= BASE_URL ?>/admin/crearCategoria" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="nombre_categoria" class="form-control-custom" placeholder="Nombre (ej. Jardinería)" required>
                    </div>
                    <div class="col-md-3">
                        <select name="tipo_clasificacion" class="form-select-custom">
                            <option value="AMBOS" selected>Ambos (Técnico / Empírico)</option>
                            <option value="TECNICO">Solo Técnico Profesional</option>
                            <option value="EMPIRICO_OFICIO">Solo Oficio Empírico</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="icono_fa" class="form-select-custom">
                            <option value="fa-solid fa-wrench">Mecánica / Mantenimiento (Llave)</option>
                            <option value="fa-solid fa-hammer">Construcción / Carpintería (Martillo)</option>
                            <option value="fa-solid fa-plug">Electricidad (Enchufe)</option>
                            <option value="fa-solid fa-droplet">Plomería / Limpieza (Gota)</option>
                            <option value="fa-solid fa-leaf">Jardinería (Hoja)</option>
                            <option value="fa-solid fa-broom">Limpieza (Escoba)</option>
                            <option value="fa-solid fa-paintbrush">Pintura (Pincel)</option>
                            <option value="fa-solid fa-truck-fast">Mudanza / Transporte (Camión)</option>
                            <option value="fa-solid fa-laptop-medical">Soporte Técnico (Laptop)</option>
                            <option value="fa-solid fa-scissors">Peluquería / Estética (Tijeras)</option>
                            <option value="fa-solid fa-stethoscope">Salud / Médico (Estetoscopio)</option>
                            <option value="fa-solid fa-graduation-cap">Educación / Tutorías (Gorro)</option>
                            <option value="fa-solid fa-camera">Fotografía (Cámara)</option>
                            <option value="fa-solid fa-utensils">Gastronomía (Cubiertos)</option>
                            <option value="fa-solid fa-briefcase">Servicios Profesionales (Maletín)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn fw-bold w-100 h-100" style="background:#10b981; color:white; border-radius:10px;">
                            <i class="fa-solid fa-save me-1"></i> Agregar
                        </button>
                    </div>
                    <div class="col-12 mt-2">
                        <input type="text" name="descripcion" class="form-control-custom" placeholder="Descripción breve del oficio (Opcional para guiar a la Inteligencia Artificial)">
                    </div>
                </form>
            </div>

            <!-- BARRA DE HERRAMIENTAS (FILTROS) -->
            <div class="toolbar-card">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Buscar categoría por nombre...">
                </div>
                <select id="filterTipo" class="filter-select">
                    <option value="">Cualquier Clasificación</option>
                    <option value="AMBOS">Ambos Tipos</option>
                    <option value="TECNICO">Técnicos</option>
                    <option value="EMPIRICO_OFICIO">Empíricos</option>
                </select>
                <select id="selectLimit" class="filter-select">
                    <option value="5">5 por pág.</option>
                    <option value="10" selected>10 por pág.</option>
                    <option value="20">20 por pág.</option>
                </select>
            </div>

            <!-- TABLA DE CATEGORÍAS -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="80px" class="text-center">Icono</th>
                                <th>Nombre de Categoría</th>
                                <th>Clasificación</th>
                                <th>Población</th>
                                <th>Estado Sistema</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        <?php if (isset($categorias) && is_array($categorias)): ?>
                            <?php foreach ($categorias as $c): ?>
                                <?php $estadoActivo = (bool)$c['estado']; ?>
                                <tr class="data-row" data-tipo="<?= htmlspecialchars($c['tipo_clasificacion']) ?>">
                                    <td class="text-center">
                                        <div class="icon-box"><i class="<?= htmlspecialchars($c['icono_fa']) ?>"></i></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['nombre_categoria']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border px-2 py-1"><?= str_replace('_', ' ', htmlspecialchars($c['tipo_clasificacion'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-muted"><i class="fa-solid fa-users me-1"></i> <?= (int) ($c['total_profesionales'] ?? 0) ?> Prof.</span>
                                    </td>
                                    <td>
                                        <?php if ($estadoActivo): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <!-- Formulario Oculto -->
                                        <form id="formToggle_<?= $c['id_categoria'] ?>" method="POST" action="<?= BASE_URL ?>/admin/toggleCategoria" style="display:none;">
                                            <input type="hidden" name="id_categoria" value="<?= $c['id_categoria'] ?>">
                                        </form>

                                        <?php if ($estadoActivo): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" onclick="abrirModal(<?= $c['id_categoria'] ?>, 'DESACTIVAR')">
                                                Apagar
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3" onclick="abrirModal(<?= $c['id_categoria'] ?>, 'ACTIVAR')">
                                                Encender
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (empty($categorias)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-folder-open fa-2x mb-2 opacity-50"></i><br>No hay categorías creadas.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="pagination-container" style="<?= empty($categorias) ? 'display:none;' : '' ?>">
                    <div class="pagination-info" id="pageInfo">Mostrando 0 a 0 de 0 registros</div>
                    <div class="pagination-btns" id="paginationBtns"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div class="custom-modal-overlay" id="customModalOverlay">
    <div class="custom-modal-card">
        <div class="modal-icon" id="modalIcon"></div>
        <h3 class="modal-title" id="modalTitle">Confirmación</h3>
        <p class="modal-text" id="modalText">¿Estás seguro?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="modal-btn confirm-action" id="modalBtnConfirmar">Sí, continuar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Sidebar
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // 2. Filtros y Paginación
    const searchInput = document.getElementById('searchInput');
    const filterTipo = document.getElementById('filterTipo');
    const selectLimit = document.getElementById('selectLimit');
    const tableBody = document.getElementById('tableBody');
    const rows = Array.from(tableBody.querySelectorAll('.data-row'));
    const pageInfo = document.getElementById('pageInfo');
    const paginationBtns = document.getElementById('paginationBtns');
    
    let currentPage = 1;
    let rowsPerPage = parseInt(selectLimit.value);
    let filteredRows = [...rows];

    function renderTable() {
        rows.forEach(row => row.style.display = 'none');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const rowsToShow = filteredRows.slice(start, end);
        rowsToShow.forEach(row => row.style.display = '');

        const total = filteredRows.length;
        const showingStart = total === 0 ? 0 : start + 1;
        const showingEnd = end > total ? total : end;
        pageInfo.textContent = `Mostrando ${showingStart} a ${showingEnd} de ${total} categorías`;
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
        const tipoValue = filterTipo.value;

        filteredRows = rows.filter(row => {
            const textContent = row.children[1].textContent.toLowerCase(); // Busca en la columna del nombre
            const matchesSearch = textContent.includes(searchText);
            const matchesTipo = tipoValue === "" || row.getAttribute('data-tipo') === tipoValue;
            return matchesSearch && matchesTipo;
        });

        currentPage = 1; renderTable();
    }

    if(searchInput) searchInput.addEventListener('keyup', applyFilters);
    if(filterTipo) filterTipo.addEventListener('change', applyFilters);
    if(selectLimit) selectLimit.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1; renderTable();
    });

    renderTable();
});

// 3. Modal de Confirmación
const modalOverlay = document.getElementById('customModalOverlay');
const modalIcon = document.getElementById('modalIcon');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');
let formularioActivo = null;

function abrirModal(idCategoria, accion) {
    formularioActivo = document.getElementById('formToggle_' + idCategoria);
    if (accion === 'DESACTIVAR') {
        modalIcon.className = 'modal-icon danger';
        modalIcon.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        modalTitle.textContent = 'Ocultar Categoría';
        modalText.textContent = 'Al desactivarla, los clientes ya no podrán pedir servicios de este oficio. ¿Continuar?';
        modalBtnConfirmar.style.backgroundColor = '#ef4444';
    } else {
        modalIcon.className = 'modal-icon success';
        modalIcon.innerHTML = '<i class="fa-solid fa-eye"></i>';
        modalTitle.textContent = 'Activar Categoría';
        modalText.textContent = 'Esta categoría volverá a estar disponible para la Inteligencia Artificial y los clientes.';
        modalBtnConfirmar.style.backgroundColor = '#10b981';
    }
    modalOverlay.classList.add('active');
}

function cerrarModal() { modalOverlay.classList.remove('active'); formularioActivo = null; }
modalBtnConfirmar.addEventListener('click', function() { if (formularioActivo) formularioActivo.submit(); });
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>