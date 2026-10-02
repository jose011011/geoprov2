<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       HEREDAMOS LA ESTRUCTURA DEL SIDEBAR DEL DASHBOARD
       ========================================================= */
    .geo-navbar { display: none !important; }
    body { background-color: #f3f4f7; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
    .admin-sidebar { width: 260px; background-color: #1e1e2d; color: #a2a3b7; position: fixed; top: 0; left: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; }
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
       ESTILOS DEL EXPEDIENTE DEL PROFESIONAL
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .dossier-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: none; overflow: hidden; margin-bottom: 25px; position: relative;}
    .profile-header { padding: 30px; display: flex; gap: 20px; align-items: center; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; }
    .profile-avatar { width: 80px; height: 80px; background: #e2e8f0; color: #475569; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; flex-shrink: 0; }
    .profile-name { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin-bottom: 5px; }
    .profile-meta { color: #64748b; font-size: 0.9rem; }
    
    .data-section { padding: 25px 30px; }
    .section-title { font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: #94a3b8; margin-bottom: 20px; letter-spacing: 0.5px; }
    .info-group { margin-bottom: 15px; }
    .info-label { display: block; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
    .info-value { display: block; font-size: 1rem; color: #334155; font-weight: 600; }
    
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
    .doc-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.2s; background: #f8fafc; }
    .doc-card:hover { border-color: #3b82f6; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1); transform: translateY(-3px); }
    .doc-img { width: 100%; height: 160px; object-fit: cover; border-bottom: 1px solid #e2e8f0; }
    .doc-title { padding: 12px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; }

    .status-banner { padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .banner-pending { background-color: #fffbeb; border-top: 1px solid #fde68a; }
    .banner-approved { background-color: #ecfdf5; border-top: 1px solid #a7f3d0; }
    .banner-rejected { background-color: #fef2f2; border-top: 1px solid #fecaca; }

    .stamp { font-size: 1.5rem; font-weight: 900; padding: 10px 25px; border-radius: 12px; text-transform: uppercase; letter-spacing: 2px; border: 4px solid; display: inline-block; transform: rotate(-3deg); }
    .stamp.approved { color: #10b981; border-color: #10b981; }
    .stamp.rejected { color: #ef4444; border-color: #ef4444; }

    /* =========================================================
       MODAL DE CONFIRMACIÓN PERSONALIZADO (SaaS Style)
       ========================================================= */
    .custom-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
    }
    .custom-modal-overlay.active { opacity: 1; pointer-events: auto; }
    .custom-modal-card {
        background: #ffffff; width: 90%; max-width: 400px; border-radius: 24px;
        padding: 30px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .custom-modal-overlay.active .custom-modal-card { transform: scale(1); }
    .modal-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px; }
    .modal-icon.warning { background: #fef3c7; color: #d97706; }
    .modal-icon.danger { background: #fef2f2; color: #dc2626; }
    .modal-icon.success { background: #ecfdf5; color: #10b981; }
    .modal-title { font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 10px; }
    .modal-text { color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
    .modal-btn.cancel { background: #f1f5f9; color: #475569; }
    .modal-btn.cancel:hover { background: #e2e8f0; }
    .modal-btn.confirm-danger { background: #ef4444; color: #ffffff; }
    .modal-btn.confirm-danger:hover { background: #dc2626; }
    .modal-btn.confirm-success { background: #10b981; color: #ffffff; }
    .modal-btn.confirm-success:hover { background: #059669; }
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
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales" class="active"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
            <li><a href="<?= BASE_URL ?>/admin/clientes"><i class="fa-solid fa-users"></i> Clientes</a></li>
            <li class="menu-title">Finanzas y Configuración</li>
            <li><a href="<?= BASE_URL ?>/admin/pagos"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
        </ul>
    </aside>

    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Verificación de Expediente</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-white border fw-bold mb-4 shadow-sm text-dark bg-white">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver al Listado
            </a>

            <?php if (!isset($perfil) || empty($perfil)): ?>
                <div class="alert alert-warning text-center p-5 rounded-4 shadow-sm border-0 bg-white">
                    <i class="fa-solid fa-user-slash fa-3x mb-3 text-warning"></i>
                    <h4>Profesional no encontrado</h4>
                    <p class="text-muted mb-0">No se encontraron los datos del profesional. Es posible que el ID sea incorrecto.</p>
                </div>
            <?php else: 
                $nombreCompleto = trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? ''));
                $iniciales = strtoupper(substr($perfil['nombre'] ?? 'X', 0, 1) . substr($perfil['apellido'] ?? '', 0, 1));
                $estadoVal = $perfil['estado_validacion'] ?? 'PENDIENTE';
            ?>
            <div class="dossier-card">
                <div class="profile-header">
                    <div class="profile-avatar"><?= $iniciales ?></div>
                    <div class="flex-grow-1">
                        <h2 class="profile-name"><?= htmlspecialchars($nombreCompleto) ?></h2>
                        <div class="profile-meta d-flex flex-wrap gap-3 mt-2">
                            <span><i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($perfil['correo'] ?? 'N/A') ?></span>
                            <span><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($perfil['celular'] ?? 'N/A') ?></span>
                            <span><i class="fa-solid fa-id-card me-1"></i> <?= htmlspecialchars($perfil['tipo_documento_identidad'] ?? 'CI') ?>: <?= htmlspecialchars($perfil['numero_documento'] ?? 'S/N') ?></span>
                        </div>
                    </div>
                    <div>
                        <?php 
                            $codigoPlan = strtolower($perfil['nombre_plan'] ?? '');
                            $nombrePlanDisplay = 'Bronce (Gratuito)';
                            if (strpos($codigoPlan, 'basico') !== false || strpos($codigoPlan, 'plata') !== false) {
                                $nombrePlanDisplay = 'Plata (Básico)';
                            } elseif (strpos($codigoPlan, 'premium') !== false || strpos($codigoPlan, 'oro') !== false) {
                                $nombrePlanDisplay = 'Oro (Premium)';
                            }
                        ?>
                        <div class="bg-light border rounded px-4 py-2 text-center">
                            <span class="info-label mb-1">Membresía Actual</span>
                            <span class="text-dark fw-bold"><i class="fa-solid fa-gem text-warning me-1"></i> Plan <?= $nombrePlanDisplay ?></span>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-lg-6 border-end">
                        <div class="data-section">
                            <div class="section-title"><i class="fa-solid fa-clipboard-list me-2"></i> Datos Operativos</div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-group"><span class="info-label">Clasificación</span><span class="info-value"><?= ($perfil['tipo_prestador'] ?? '') === 'TECNICO_PROFESIONAL' ? '<i class="fa-solid fa-user-graduate text-primary"></i> Técnico' : '<i class="fa-solid fa-hammer text-warning"></i> Empírico' ?></span></div>
                                    <div class="info-group"><span class="info-label">Especialidad</span><span class="info-value text-success fw-bold"><?= htmlspecialchars($perfil['nombre_categoria'] ?? 'Sin Categoría') ?></span></div>
                                    <div class="info-group"><span class="info-label">Experiencia Declarada</span><span class="info-value"><?= (int)($perfil['experiencia_anios'] ?? 0) ?> años</span></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-group"><span class="info-label">Macrodistrito Base</span><span class="info-value"><?= htmlspecialchars($perfil['macrodistrito_base'] ?? 'N/A') ?></span></div>
                                    <div class="info-group"><span class="info-label">Zona Específica</span><span class="info-value"><?= htmlspecialchars($perfil['zona_especifica'] ?? 'N/A') ?></span></div>
                                    <div class="info-group"><span class="info-label">Tarifa Base (Referencial)</span><span class="info-value">Bs. <?= number_format((float)($perfil['tarifa_base'] ?? 0), 2) ?></span></div>
                                </div>
                            </div>
                            <div class="info-group mt-3"><span class="info-label">Descripción (Perfil Público)</span><div class="bg-light p-3 rounded text-secondary small lh-sm border mt-2"><?= nl2br(htmlspecialchars($perfil['descripcion_servicio'] ?? 'No proporcionada.')) ?></div></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="data-section h-100 bg-light">
                            <div class="section-title"><i class="fa-solid fa-folder-open me-2"></i> Documentos Subidos</div>
                            <?php if (!isset($documentos) || empty($documentos)): ?>
                                <div class="text-center p-5 bg-white border border-dashed rounded-4"><i class="fa-solid fa-file-circle-xmark fa-3x text-muted opacity-25 mb-3"></i><p class="mb-0 text-muted fw-bold">No adjuntó documentos.</p></div>
                            <?php else: ?>
                                <div class="doc-grid">
                                    <?php foreach ($documentos as $doc): ?>
                                        <div class="doc-card">
                                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($doc['archivo_url'] ?? '') ?>" target="_blank"><img src="<?= BASE_URL ?>/<?= htmlspecialchars($doc['archivo_url'] ?? '') ?>" class="doc-img"></a>
                                            <div class="doc-title"><?= str_replace('_', ' ', htmlspecialchars($doc['tipo_documento_archivo'] ?? 'DOC')) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($estadoVal === 'PENDIENTE'): ?>
                    <div class="status-banner banner-pending">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-circle-exclamation text-warning me-2"></i> Pendiente de Auditoría</h5>
                            <p class="text-muted small mb-0">Revisa los documentos. Si apruebas, el profesional aparecerá en el mapa.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Formularios ocultos -->
                            <form id="formRechazar" action="<?= BASE_URL ?>/admin/cambiarEstadoProfesional/<?= $perfil['id_profesional'] ?? 0 ?>" method="POST" style="display:none;">
                                <input type="hidden" name="nuevo_estado" value="RECHAZADO">
                            </form>
                            <form id="formAprobar" action="<?= BASE_URL ?>/admin/cambiarEstadoProfesional/<?= $perfil['id_profesional'] ?? 0 ?>" method="POST" style="display:none;">
                                <input type="hidden" name="nuevo_estado" value="APROBADO">
                            </form>
                            
                            <!-- Botones que abren el modal personalizado -->
                            <button type="button" class="btn btn-danger fw-bold" onclick="abrirModal('RECHAZAR')">
                                <i class="fa-solid fa-xmark me-1"></i> Rechazar
                            </button>
                            <button type="button" class="btn btn-success fw-bold" onclick="abrirModal('APROBAR')">
                                <i class="fa-solid fa-check me-1"></i> Aprobar Perfil
                            </button>
                        </div>
                    </div>
                <?php elseif ($estadoVal === 'APROBADO'): ?>
                    <div class="status-banner banner-approved justify-content-center py-4"><div class="stamp approved"><i class="fa-solid fa-shield-check"></i> Perfil Aprobado</div></div>
                <?php else: ?>
                    <div class="status-banner banner-rejected justify-content-center py-4"><div class="stamp rejected"><i class="fa-solid fa-ban"></i> Perfil Rechazado</div></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- =========================================================
   MODAL PERSONALIZADO (REEMPLAZA AL window.confirm)
   ========================================================= -->
<div class="custom-modal-overlay" id="customModalOverlay">
    <div class="custom-modal-card">
        <div class="modal-icon" id="modalIcon"></div>
        <h3 class="modal-title" id="modalTitle">Confirmación</h3>
        <p class="modal-text" id="modalText">¿Estás seguro de realizar esta acción?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="modal-btn" id="modalBtnConfirmar">Si, confirmar</button>
        </div>
    </div>
</div>

<script>
// Lógica del menú lateral responsive
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    function toggleMenu() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
    if(btnToggle) btnToggle.addEventListener('click', toggleMenu);
    if(overlay) overlay.addEventListener('click', toggleMenu);
});

// Lógica del Modal Personalizado
const modalOverlay = document.getElementById('customModalOverlay');
const modalIcon = document.getElementById('modalIcon');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');

let formularioActivo = null;

function abrirModal(accion) {
    if (accion === 'APROBAR') {
        modalIcon.className = 'modal-icon success';
        modalIcon.innerHTML = '<i class="fa-solid fa-check"></i>';
        modalTitle.textContent = 'Aprobar Profesional';
        modalText.textContent = 'Al aprobar, este perfil será visible en el mapa y podrá recibir solicitudes de trabajo en La Paz.';
        modalBtnConfirmar.className = 'modal-btn confirm-success';
        modalBtnConfirmar.textContent = 'Aprobar Perfil';
        formularioActivo = document.getElementById('formAprobar');
    } else if (accion === 'RECHAZAR') {
        modalIcon.className = 'modal-icon danger';
        modalIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
        modalTitle.textContent = 'Rechazar Profesional';
        modalText.textContent = 'Esta acción bloqueará al profesional permanentemente en el sistema. ¿Estás seguro?';
        modalBtnConfirmar.className = 'modal-btn confirm-danger';
        modalBtnConfirmar.textContent = 'Rechazar Definitivamente';
        formularioActivo = document.getElementById('formRechazar');
    }
    modalOverlay.classList.add('active');
}

function cerrarModal() {
    modalOverlay.classList.remove('active');
    formularioActivo = null;
}

modalBtnConfirmar.addEventListener('click', function() {
    if (formularioActivo) {
        formularioActivo.submit();
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>