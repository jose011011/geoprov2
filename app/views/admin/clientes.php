<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Estructura Base y Sidebar Fijo (Mismos estilos que Profesionales para consistencia) */
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
    .modal-icon.success { background: #ecfdf5; color: #10b981; }
    .modal-title { font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 10px; }
    .modal-text { color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
    .modal-btn.cancel { background: #f1f5f9; color: #475569; }
    .modal-btn.confirm-danger { background: #ef4444; color: #ffffff; }
    .modal-btn.confirm-success { background: #10b981; color: #ffffff; }
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
            <li><a href="<?= BASE_URL ?>/admin/clientes" class="active"><i class="fa-solid fa-users"></i> Clientes</a></li>
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
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Gestión de Usuarios Activos</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title">Control de Clientes Demandantes</h3>
            <p class="text-muted mb-4">Administra a los usuarios que contratan servicios en GEO-PRO La Paz.</p>
            
            <!-- Filtros Inteligentes -->
            <div class="toolbar-card">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Buscar cliente por nombre, zona o correo...">
                </div>
                <select id="selectLimit" class="filter-select">
                    <option value="5">5 por página</option>
                    <option value="10" selected>10 por página</option>
                    <option value="20">20 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cliente / Contacto</th>
                                <th>Zona Principal</th>
                                <th>Pedidos Históricos</th>
                                <th>Estado de Cuenta</th>
                                <th class="text-end">Acción de Seguridad</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        <?php if (isset($clientes) && is_array($clientes)): ?>
                            <?php foreach ($clientes as $c): ?>
                                <?php $estado = $c['estado'] ?? 'ACTIVO'; ?>
                                <tr class="data-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-success fw-bold me-3 border" style="width: 42px; height: 42px;">
                                                <?= strtoupper(substr($c['nombre'] ?? 'C', 0, 1) . substr($c['apellido'] ?? '', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($c['correo'] ?? '') ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($c['celular'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-bold"><?= htmlspecialchars($c['zona'] ?? 'Sin zona') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($c['direccion_referencia'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-briefcase"></i> <?= (int)($c['total_pedidos'] ?? 0) ?> servicios
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($estado === 'ACTIVO'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-user-check me-1"></i> Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-user-lock me-1"></i> Bloqueado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <!-- Formulario Oculto -->
                                        <form id="formToggle_<?= $c['id_usuario'] ?>" action="<?= BASE_URL ?>/admin/toggleEstadoUsuario" method="POST" style="display:none;">
                                            <input type="hidden" name="id_usuario" value="<?= $c['id_usuario'] ?>">
                                            <input type="hidden" name="redirect_to" value="/admin/clientes">
                                        </form>
                                        
                                        <?php if ($estado === 'ACTIVO'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" onclick="abrirModal(<?= $c['id_usuario'] ?>, 'BLOQUEAR')">
                                                <i class="fa-solid fa-ban me-1"></i> Suspender
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-success fw-bold px-3" onclick="abrirModal(<?= $c['id_usuario'] ?>, 'ACTIVAR')">
                                                <i class="fa-solid fa-unlock me-1"></i> Reactivar
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (empty($clientes)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-users-slash fa-2x mb-2 opacity-50"></i><br>No hay clientes registrados.</td></tr>
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

<!-- MODAL SAAS PERSONALIZADO -->
<div class="custom-modal-overlay" id="customModalOverlay">
    <div class="custom-modal-card">
        <div class="modal-icon" id="modalIcon"></div>
        <h3 class="modal-title" id="modalTitle">Confirmación</h3>
        <p class="modal-text" id="modalText">¿Estás seguro?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="modal-btn" id="modalBtnConfirmar">Si, confirmar</button>
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

    // 2. Paginación y Búsqueda
    const searchInput = document.getElementById('searchInput');
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
        pageInfo.textContent = `Mostrando ${showingStart} a ${showingEnd} de ${total} clientes`;
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

    searchInput.addEventListener('keyup', function() {
        const text = this.value.toLowerCase();
        filteredRows = rows.filter(row => row.textContent.toLowerCase().includes(text));
        currentPage = 1; renderTable();
    });

    selectLimit.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1; renderTable();
    });

    renderTable();
});

// 3. Lógica del Modal Personalizado
const modalOverlay = document.getElementById('customModalOverlay');
const modalIcon = document.getElementById('modalIcon');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');
let formularioActivo = null;

function abrirModal(idUsuario, accion) {
    formularioActivo = document.getElementById('formToggle_' + idUsuario);
    if (accion === 'BLOQUEAR') {
        modalIcon.className = 'modal-icon danger';
        modalIcon.innerHTML = '<i class="fa-solid fa-user-lock"></i>';
        modalTitle.textContent = 'Suspender Cuenta';
        modalText.textContent = 'El cliente no podrá iniciar sesión ni pedir servicios en la plataforma. ¿Estás seguro?';
        modalBtnConfirmar.className = 'modal-btn confirm-danger';
        modalBtnConfirmar.textContent = 'Bloquear Cliente';
    } else {
        modalIcon.className = 'modal-icon success';
        modalIcon.innerHTML = '<i class="fa-solid fa-user-check"></i>';
        modalTitle.textContent = 'Reactivar Cuenta';
        modalText.textContent = 'El cliente recuperará el acceso total a GEO-PRO La Paz.';
        modalBtnConfirmar.className = 'modal-btn confirm-success';
        modalBtnConfirmar.textContent = 'Reactivar Cliente';
    }
    modalOverlay.classList.add('active');
}

function cerrarModal() { modalOverlay.classList.remove('active'); formularioActivo = null; }
modalBtnConfirmar.addEventListener('click', function() { if (formularioActivo) formularioActivo.submit(); });
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>