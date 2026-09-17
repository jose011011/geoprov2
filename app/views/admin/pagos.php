<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       HEREDAMOS LA ESTRUCTURA SAAS Y EL SIDEBAR CON SCROLL
       ========================================================= */
    .geo-navbar { display: none !important; }
    body { background-color: #f3f4f7; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
    
    /* SIDEBAR CON SCROLL REPARADO */
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
       ESTILOS FINANCIEROS (TARJETAS, FILTROS Y TABLA)
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #1e293b; font-size: 1.6rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    /* KPIs */
    .kpi-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; border: none; position: relative; overflow: hidden;}
    .kpi-card::before { content: ""; position: absolute; left: 0; top: 0; height: 100%; width: 5px; }
    .kpi-card.warning::before { background-color: #f59e0b; }
    .kpi-card.success::before { background-color: #10b981; }
    .kpi-icon-wrap { width: 55px; height: 55px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 20px; flex-shrink: 0;}
    .kpi-number { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
    .kpi-label { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Barra de Herramientas (Buscador y Filtros) */
    .toolbar-card { background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: none; }
    .toolbar-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; }
    @media (max-width: 768px) { .toolbar-grid { grid-template-columns: 1fr; } }
    
    .search-box { position: relative; width: 100%; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-box input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; transition: all 0.2s; }
    .search-box input:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .filter-select { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem; width: 100%; color: #475569; outline: none; cursor: pointer; }

    /* Tabla Corporativa */
    .table-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; border: none; }
    .table { margin-bottom: 0; }
    .table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 15px 25px; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .table td { vertical-align: middle; padding: 15px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.95rem; }
    .code-box { background: #f1f5f9; border: 1px dashed #cbd5e1; padding: 6px 10px; border-radius: 6px; font-family: monospace; font-weight: 700; color: #475569; letter-spacing: 1px;}
    
    /* Paginación */
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

    <!-- SIDEBAR PERMANENTE Y CON SCROLL CORREGIDO -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)); ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
            <p>Super Administrador</p>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-title">Resumen</li>
            <li><a href="<?= BASE_URL ?>/admin/dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
            <li><a href="<?= BASE_URL ?>/admin/clientes"><i class="fa-solid fa-users"></i> Clientes</a></li>
            <li class="menu-title">Finanzas y Configuración</li>
            <li><a href="<?= BASE_URL ?>/admin/pagos" class="active"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
            <li><a href="<?= BASE_URL ?>/admin/categorias"><i class="fa-solid fa-layer-group"></i> Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reportes"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Departamento Financiero</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <h3 class="page-title">Historial y Verificación de Pagos</h3>
            <p class="page-subtitle">Revisa los comprobantes QR pendientes y consulta el historial de transacciones procesadas.</p>

            <?php 
                $pendientes = array_filter($pagos, fn($p) => $p['estado_pago'] === 'PENDIENTE');
                if (!empty($pagos)): 
            ?>
                <!-- KPIs FINANCIEROS -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="kpi-card warning">
                            <div class="kpi-icon-wrap bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            <div>
                                <div class="kpi-number text-dark"><?= count($pendientes) ?></div>
                                <div class="kpi-label">Transacciones pendientes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="kpi-card success">
                            <div class="kpi-icon-wrap bg-success bg-opacity-10 text-success"><i class="fa-solid fa-sack-dollar"></i></div>
                            <div>
                                <div class="kpi-number text-dark">Bs. <?= number_format(array_sum(array_column($pendientes, 'monto')), 2) ?></div>
                                <div class="kpi-label">Recaudación por Aprobar</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BARRA DE HERRAMIENTAS (FILTROS) -->
            <div class="toolbar-card">
                <div class="toolbar-grid">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar profesional o comprobante...">
                    </div>
                    <div>
                        <select id="filterTipo" class="filter-select">
                            <option value="">Todos los Tipos de Pago</option>
                            <option value="MEMBRESIA_MENSUAL">Suscripciones (Membresías)</option>
                            <option value="COMPRA_TOKENS">Recarga de Tokens</option>
                        </select>
                    </div>
                    <div>
                        <select id="selectLimit" class="filter-select">
                            <option value="5">Mostrar 5</option>
                            <option value="10" selected>Mostrar 10</option>
                            <option value="20">Mostrar 20</option>
                            <option value="100">Mostrar 100</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TABLA DE TRANSACCIONES -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Profesional</th>
                                <th>Concepto</th>
                                <th>Monto Pagado</th>
                                <th>Código (QR) / Referencia</th>
                                <th class="text-end">Acción de Verificación</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        <?php if (isset($pagos) && is_array($pagos)): ?>
                            <?php foreach ($pagos as $p): ?>
                                <tr class="data-row" data-tipo="<?= htmlspecialchars($p['tipo_transaccion']) ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary fw-bold me-3 border" style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($p['nombre'] ?? 'U', 0, 1) . substr($p['apellido'] ?? '', 0, 1)) ?>
                                            </div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($p['tipo_transaccion'] === 'MEMBRESIA_MENSUAL'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-1 d-inline-block"><i class="fa-solid fa-gem me-1"></i> Plan Suscripción</span><br>
                                            <small class="text-muted fw-bold"><?= htmlspecialchars(str_replace('_', ' ', $p['nombre_plan'] ?? '')) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><i class="fa-solid fa-coins me-1"></i> Paquete de Tokens</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success" style="font-size: 1.1rem;">
                                        Bs. <?= number_format($p['monto'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="code-box"><?= htmlspecialchars($p['codigo_comprobante']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($p['estado_pago'] === 'PENDIENTE'): ?>
                                            <!-- Formularios Ocultos para envío seguro -->
                                            <form id="formAprobar_<?= $p['id_transaccion'] ?>" method="POST" action="<?= BASE_URL ?>/admin/confirmarPago" style="display:none;">
                                                <input type="hidden" name="id_transaccion" value="<?= $p['id_transaccion'] ?>">
                                            </form>
                                            <form id="formRechazar_<?= $p['id_transaccion'] ?>" method="POST" action="<?= BASE_URL ?>/admin/rechazarPago" style="display:none;">
                                                <input type="hidden" name="id_transaccion" value="<?= $p['id_transaccion'] ?>">
                                            </form>
    
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-danger fw-bold px-3" onclick="abrirModal(<?= $p['id_transaccion'] ?>, 'RECHAZAR')">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                                <button type="button" class="btn btn-success fw-bold px-3" onclick="abrirModal(<?= $p['id_transaccion'] ?>, 'APROBAR')">
                                                    <i class="fa-solid fa-check me-1"></i> Confirmar
                                                </button>
                                            </div>
                                        <?php elseif ($p['estado_pago'] === 'CONFIRMADO'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check-double me-1"></i> Aprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-ban me-1"></i> Rechazado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if (empty($pagos)): ?>
                        <div class="text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Todo está al día</h5>
                            <p class="text-muted mb-0">No hay pagos de membresías ni recargas pendientes por confirmar.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Paginación Interactiva -->
                <div class="pagination-container" style="<?= empty($pagos) ? 'display:none;' : '' ?>">
                    <div class="pagination-info" id="pageInfo">Mostrando 0 a 0 de 0 pagos</div>
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
        <h3 class="modal-title" id="modalTitle">Verificación</h3>
        <p class="modal-text" id="modalText">¿Estás seguro?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="modal-btn" id="modalBtnConfirmar">Si, procesar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Sidebar Toggle
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // 2. Paginación y Filtros de Pagos
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
        pageInfo.textContent = `Mostrando ${showingStart} a ${showingEnd} de ${total} transacciones`;
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
            const matchesSearch = row.textContent.toLowerCase().includes(searchText);
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

// 3. Lógica del Modal Personalizado de Aprobación
const modalOverlay = document.getElementById('customModalOverlay');
const modalIcon = document.getElementById('modalIcon');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');
let formularioActivo = null;

function abrirModal(idTransaccion, accion) {
    if (accion === 'APROBAR') {
        formularioActivo = document.getElementById('formAprobar_' + idTransaccion);
        modalIcon.className = 'modal-icon success';
        modalIcon.innerHTML = '<i class="fa-solid fa-check"></i>';
        modalTitle.textContent = 'Confirmar Ingreso';
        modalText.textContent = 'Al aprobar, se recargarán los tokens o se activará el plan del profesional automáticamente.';
        modalBtnConfirmar.className = 'modal-btn confirm-success';
        modalBtnConfirmar.textContent = 'Aprobar Transacción';
    } else {
        formularioActivo = document.getElementById('formRechazar_' + idTransaccion);
        modalIcon.className = 'modal-icon danger';
        modalIcon.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        modalTitle.textContent = 'Rechazar Comprobante';
        modalText.textContent = 'Rechaza este pago solo si el comprobante es falso o el dinero no ingresó a la cuenta.';
        modalBtnConfirmar.className = 'modal-btn confirm-danger';
        modalBtnConfirmar.textContent = 'Rechazar Pago';
    }
    modalOverlay.classList.add('active');
}

function cerrarModal() { modalOverlay.classList.remove('active'); formularioActivo = null; }
modalBtnConfirmar.addEventListener('click', function() { if (formularioActivo) formularioActivo.submit(); });
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>