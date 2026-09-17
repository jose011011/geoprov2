<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       DISEÑO SAAS PROFESIONAL (ESCRITORIO / PC)
       ========================================================= */
    .geo-navbar { display: none !important; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }

    .pro-wrapper { display: flex; width: 100%; min-height: 100vh; }
    
    /* SIDEBAR DEL PROFESIONAL */
    .pro-sidebar { width: 260px; background-color: #0f172a; color: #94a3b8; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; border-right: 1px solid #1e293b; padding-bottom: 50px; }
    .pro-sidebar::-webkit-scrollbar { width: 6px; }
    .pro-sidebar::-webkit-scrollbar-track { background: transparent; }
    .pro-sidebar::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); border-radius: 10px; }
    
    .sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; color: #ffffff; font-size: 1.5rem; font-weight: 900; letter-spacing: 1px; }
    .sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; }
    .sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background-color: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid #1e293b; }
    .sidebar-profile h6 { color: #f8fafc; font-weight: 700; margin-bottom: 5px; font-size: 1.1rem; }
    .sidebar-profile p { font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; }
    .plan-badge { background: #334155; color: #f59e0b; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid #475569; }

    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; }
    .sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
    .sidebar-menu li a:hover { color: #ffffff; background-color: #1e293b; }
    .sidebar-menu li a.active { color: #ffffff; background-color: #1e293b; border-left-color: #3b82f6; }

    /* CONTENIDO PRINCIPAL */
    .pro-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .pro-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 999; }
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
       TARJETAS DE TRABAJO (JOB CARDS ESTILO UBER/APP)
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #0f172a; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    .status-tabs { display: flex; gap: 10px; margin-bottom: 25px; overflow-x: auto; padding-bottom: 5px;}
    .tab-btn { padding: 8px 20px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .tab-btn:hover { background: #f1f5f9; color: #0f172a; }
    .tab-btn.active { background: #3b82f6; color: #ffffff; border-color: #3b82f6; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2); }

    .job-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 20px; position: relative; overflow: hidden; transition: transform 0.2s; }
    .job-card:hover { transform: translateY(-3px); border-color: #cbd5e1; }
    .job-card::before { content: ""; position: absolute; left: 0; top: 0; width: 6px; height: 100%; background: #3b82f6; }
    .job-card.urgent::before { background: #ef4444; }
    .job-card.active-job::before { background: #10b981; }

    .job-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px; }
    .client-info { display: flex; align-items: center; gap: 15px; }
    .client-avatar { width: 50px; height: 50px; border-radius: 50%; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; border: 1px solid #cbd5e1; }
    .client-name { font-weight: 800; color: #1e293b; font-size: 1.1rem; margin-bottom: 2px; }
    .job-code { font-family: monospace; font-size: 0.8rem; color: #94a3b8; font-weight: 700; background: #f8fafc; padding: 2px 8px; border-radius: 6px; border: 1px dashed #cbd5e1; }

    .job-body { margin-bottom: 20px; }
    .job-detail { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
    .job-detail i { color: #94a3b8; font-size: 1.1rem; margin-top: 3px; width: 20px; text-align: center; }
    .job-text { color: #334155; font-size: 0.95rem; line-height: 1.5; font-weight: 500; }
    .job-highlight { color: #0f172a; font-weight: 700; }

    .job-actions { display: flex; gap: 10px; justify-content: flex-end; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    .btn-job { padding: 10px 25px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-accept { background: #10b981; color: white; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2); }
    .btn-accept:hover { background: #059669; }
    .btn-process { background: #3b82f6; color: white; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2); }
    .btn-finish { background: #f59e0b; color: white; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2); }
    .btn-chat { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .btn-chat:hover { background: #e2e8f0; }

    .token-cost { font-size: 0.8rem; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px; margin-left: 5px; }

    /* Modal SaaS */
    .custom-modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .custom-modal-overlay.active { opacity: 1; pointer-events: auto; }
    .custom-modal-card { background: #ffffff; width: 90%; max-width: 400px; border-radius: 24px; padding: 30px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .custom-modal-overlay.active .custom-modal-card { transform: scale(1); }
    .modal-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px; }
    .modal-icon.warning { background: #fef3c7; color: #d97706; }
    .modal-icon.info { background: #eff6ff; color: #3b82f6; }
    .modal-title { font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 10px; }
    .modal-text { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
    .modal-btn.cancel { background: #f1f5f9; color: #475569; }
    .modal-btn.confirm-action { background: #10b981; color: #ffffff; }
</style>

<div class="pro-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="pro-sidebar" id="proSidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-location-crosshairs text-primary"></i> GEO-PRO
        </div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($perfil['nombre'] ?? 'P', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($perfil['nombre'] ?? 'Profesional') ?></h6>
            <p><?= htmlspecialchars($perfil['nombre_categoria'] ?? 'Técnico') ?></p>
            <span class="plan-badge"><i class="fa-solid fa-gem"></i> Plan <?= htmlspecialchars(str_replace('_', ' ', $perfil['nombre_plan'] ?? 'Básico')) ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/profesional/dashboard"><i class="fa-solid fa-satellite-dish"></i> Centro de Mando</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/solicitudes" class="active"><i class="fa-solid fa-inbox"></i> Alertas de Trabajo</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/historial"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/comprarTokens"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="pro-main-content">
        <header class="pro-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
                <div class="d-none d-md-block"><span class="text-muted fw-bold">Gestión de Leads</span></div>
            </div>
            <div class="tokens-display shadow-sm">
                <i class="fa-solid fa-coins fa-beat"></i> 
                <span><?= (int)($perfil['tokens_disponibles'] ?? 0) ?> Tokens</span>
            </div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Bandeja de Solicitudes</h2>
            <p class="page-subtitle">Revisa los problemas enviados por los clientes. Aceptar un trabajo descontará 1 Token de tu saldo.</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger fw-bold shadow-sm" style="border-radius:10px; border-left: 5px solid #ef4444;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success fw-bold shadow-sm" style="border-radius:10px; border-left: 5px solid #10b981;">
                    <i class="fa-solid fa-check-circle me-2"></i> 
                    <?php 
                        if ($_GET['success'] == 'estado_actualizado') echo "El estado del trabajo ha sido actualizado correctamente.";
                        else echo htmlspecialchars($_GET['success']); 
                    ?>
                </div>
            <?php endif; ?>

            <!-- TABS DE FILTRO -->
            <div class="status-tabs">
                <a href="?estado=" class="tab-btn <?= empty($filtroActual) || $filtroActual === 'TODAS' ? 'active' : '' ?>">Pendientes de Acción</a>
                <a href="?estado=EN_PROCESO" class="tab-btn <?= $filtroActual === 'EN_PROCESO' ? 'active' : '' ?>">Trabajos Activos</a>
            </div>

            <div class="row g-4" id="jobListContainer">
                <?php 
                // Filtrado rápido en PHP para no mostrar las finalizadas (esas van al historial)
                $solicitudesActivas = array_filter($solicitudes ?? [], function($s) {
                    return !in_array($s['estado_servicio'], ['FINALIZADA', 'CANCELADA']);
                });
                ?>

                <?php if (!empty($solicitudesActivas)): ?>
                    <?php foreach ($solicitudesActivas as $s): ?>
                        <?php 
                            $estado = $s['estado_servicio'];
                            // Clases dinámicas según el estado
                            $cardClass = ($estado === 'PENDIENTE') ? 'urgent' : 'active-job';
                            $badgeColor = ($estado === 'PENDIENTE') ? 'bg-danger' : 'bg-primary';
                        ?>
                        <div class="col-xl-6">
                            <div class="job-card <?= $cardClass ?>">
                                
                               <div class="job-header">
                                    <div class="client-info">
                                        <div class="client-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <!-- Muestra el nombre real del cliente -->
                                            <div class="client-name">
                                                <?= htmlspecialchars(($s['cliente_nombre'] ?? 'Cliente') . ' ' . ($s['cliente_apellido'] ?? '')) ?>
                                            </div>
                                            <div class="text-muted small"><i class="fa-regular fa-clock me-1"></i> Recibido a las <?= date('H:i', strtotime($s['fecha_solicitud'] ?? 'now')) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge <?= $badgeColor ?> rounded-pill px-3 py-2 mb-1"><?= str_replace('_', ' ', $estado) ?></span><br>
                                        <span class="job-code"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($s['codigo_seguimiento'] ?? 'S/N') ?></span>
                                    </div>
                                </div>

                                <div class="job-body">
                                    <div class="job-detail">
                                        <i class="fa-solid fa-location-dot text-danger"></i>
                                        <div class="job-text">
                                            <span class="text-muted small d-block">Ubicación del Servicio</span>
                                            <span class="job-highlight"><?= htmlspecialchars($s['zona'] ?? 'Zona no definida') ?></span>, <?= htmlspecialchars($s['macrodistrito'] ?? 'La Paz') ?><br>
                                            <small><?= htmlspecialchars($s['direccion_servicio'] ?? '') ?></small>
                                        </div>
                                    </div>
                                    <div class="job-detail mt-3">
                                        <i class="fa-solid fa-circle-exclamation text-warning"></i>
                                        <div class="job-text">
                                            <span class="text-muted small d-block">Descripción del Problema</span>
                                            "<?= nl2br(htmlspecialchars($s['descripcion_problema'] ?? 'Sin descripción')) ?>"
                                        </div>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <!-- Formulario Oculto -->
                                    <form id="formAccion_<?= $s['id_solicitud'] ?>" method="POST" action="<?= BASE_URL ?>/profesional/cambiarEstadoSolicitud" style="display:none;">
                                        <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                                        <input type="hidden" name="estado" id="inputEstado_<?= $s['id_solicitud'] ?>" value="">
                                    </form>

                                 <!-- LÓGICA DE BOTONES SEGÚN ESTADO -->
                                    <?php if ($estado === 'PENDIENTE'): ?>
                                        <button type="button" class="btn-job btn-accept w-100" onclick="abrirModal(<?= $s['id_solicitud'] ?>, 'ACEPTAR')">
                                            Aceptar Trabajo <span class="token-cost">-1 Token</span>
                                        </button>
                                    
                                    <?php elseif ($estado === 'ACEPTADA'): ?>
                                        <a href="<?= BASE_URL ?>/chat/ver/<?= $s['id_solicitud'] ?>" class="btn-job btn-chat">
                                            <i class="fa-solid fa-comment-dots"></i> Chat
                                        </a>
                                        <button type="button" class="btn-job btn-process flex-grow-1" onclick="abrirModal(<?= $s['id_solicitud'] ?>, 'EN_CAMINO')">
                                            <i class="fa-solid fa-motorcycle"></i> Estoy en Camino
                                        </button>
                                    
                                    <?php elseif ($estado === 'EN_CAMINO'): ?>
                                        <!-- SOLO CUANDO VIAJA: Muestra el Mapa -->
                                        <a href="<?= BASE_URL ?>/chat/ver/<?= $s['id_solicitud'] ?>" class="btn-job btn-chat">
                                            <i class="fa-solid fa-comment-dots"></i> Chat
                                        </a>
                                        <a href="<?= BASE_URL ?>/profesional/mapaViaje/<?= $s['id_solicitud'] ?>" class="btn-job btn-process flex-grow-1" style="background-color: #0f172a; color: white;">
                                            <i class="fa-solid fa-map-location-dot"></i> Ver Mapa de Viaje
                                        </a>

                                    <?php elseif ($estado === 'EN_PROCESO'): ?>
                                        <!-- CUANDO YA LLEGÓ: Oculta el mapa y muestra FINALIZAR -->
                                        <a href="<?= BASE_URL ?>/chat/ver/<?= $s['id_solicitud'] ?>" class="btn-job btn-chat">
                                            <i class="fa-solid fa-comment-dots"></i> Chat
                                        </a>
                                        <button type="button" class="btn-job btn-process flex-grow-1" style="background-color: #10b981; color: white; border: none;" onclick="abrirModal(<?= $s['id_solicitud'] ?>, 'FINALIZADA')">
                                            <i class="fa-solid fa-flag-checkered"></i> Finalizar Trabajo
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width: 100px; height: 100px; font-size: 3rem;">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Bandeja Vacía</h4>
                        <p class="text-muted">No tienes solicitudes nuevas en este momento. Mantente en estado "Disponible" en el Centro de Mando.</p>
                        <a href="<?= BASE_URL ?>/profesional/dashboard" class="btn btn-outline-primary fw-bold mt-2">Ir al Radar</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div><!-- MODAL SAAS DINÁMICO (CÓDIGO CORREGIDO) -->
<div class="custom-modal-overlay" id="customModalOverlay">
    <div class="custom-modal-card">
        <form id="formDinamicoAccion" method="POST" action="<?= BASE_URL ?>/profesional/cambiarEstadoSolicitud">
            <div class="modal-icon" id="modalIcon"></div>
            <h3 class="modal-title" id="modalTitle">Confirmar Acción</h3>
            <p class="modal-text" id="modalText">¿Estás seguro?</p>
            
            <input type="hidden" name="id_solicitud" id="inputIdSolicitud" value="">
            <input type="hidden" name="estado" id="inputEstado" value="">

            <div id="extraFieldsContainer" style="display:none; margin-bottom: 20px; text-align: left;"></div>

            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="cerrarModal()">Cancelar</button>
                <!-- CAMBIO CLAVE: Botón tipo "button" que llama a la función ejecutarAccion() -->
                <button type="button" class="modal-btn confirm-action" id="modalBtnConfirmar" onclick="ejecutarAccion()">Sí, confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});

const modalOverlay = document.getElementById('customModalOverlay');
const modalIcon = document.getElementById('modalIcon');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');
const inputIdSolicitud = document.getElementById('inputIdSolicitud');
const inputEstado = document.getElementById('inputEstado');
const extraFieldsContainer = document.getElementById('extraFieldsContainer');

function abrirModal(idSolicitud, accion) {
    inputIdSolicitud.value = idSolicitud;
    inputEstado.value = accion;
    extraFieldsContainer.style.display = 'none';
    extraFieldsContainer.innerHTML = '';
    
    // Restaurar el botón en caso de que se haya quedado en "Cargando..." antes
    modalBtnConfirmar.style.pointerEvents = 'auto';
    modalBtnConfirmar.style.opacity = '1';
    
    if (accion === 'ACEPTAR') {
        inputEstado.value = 'ACEPTADA';
        modalIcon.className = 'modal-icon warning';
        modalIcon.innerHTML = '<i class="fa-solid fa-coins"></i>';
        modalTitle.textContent = 'Aceptar Trabajo';
        modalText.textContent = 'Al confirmar, se te descontará 1 Token y podrás ver la dirección exacta y chatear.';
        modalBtnConfirmar.style.backgroundColor = '#10b981';
        modalBtnConfirmar.textContent = 'Aceptar (-1 Token)';
    } 
    else if (accion === 'EN_CAMINO') {
        inputEstado.value = 'EN_CAMINO';
        modalIcon.className = 'modal-icon info';
        modalIcon.innerHTML = '<i class="fa-solid fa-motorcycle"></i>';
        modalTitle.textContent = 'Voy en Camino';
        modalText.textContent = 'Ingresa el tiempo estimado para que el cliente te espere.';
        
        extraFieldsContainer.style.display = 'block';
        extraFieldsContainer.innerHTML = `
            <label class="fw-bold text-dark small mb-2 d-block">Tiempo de llegada (en minutos):</label>
            <input type="number" name="tiempo_estimado" class="form-control" required min="5" max="180" placeholder="Ej: 15" style="padding: 12px; border-radius: 10px; width: 100%; border: 1px solid #cbd5e1;">
        `;
        
        modalBtnConfirmar.style.backgroundColor = '#3b82f6';
        modalBtnConfirmar.textContent = 'Abrir Mapa';
    }
    
    modalOverlay.classList.add('active');
}

function cerrarModal() { modalOverlay.classList.remove('active'); }

// FUNCIÓN CLAVE: Fuerza al navegador a validar los datos y enviar el formulario de verdad
function ejecutarAccion() {
    const form = document.getElementById('formDinamicoAccion');
    
    // Si los datos son válidos (ej. puso el tiempo de llegada)
    if (form.reportValidity()) {
        // Bloqueamos el botón para evitar doble clic
        modalBtnConfirmar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cargando...';
        modalBtnConfirmar.style.pointerEvents = 'none';
        modalBtnConfirmar.style.opacity = '0.8';
        
        // Enviamos el formulario al servidor
        form.submit();
    }
}

// MAGIA DE ACTUALIZACIÓN EN TIEMPO REAL:
// Si el profesional está viendo la pestaña principal, actualizamos los trabajos silenciosamente cada 10 segundos
<?php if (empty($filtroActual) || $filtroActual === 'TODAS'): ?>
setInterval(async function() {
    // Si el modal está abierto, no actualizamos para no interrumpirlo
    if (modalOverlay.classList.contains('active')) return;
    
    try {
        const response = await fetch(window.location.href);
        const html = await response.text();
        
        // Extraemos solo la lista de trabajos del HTML nuevo
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nuevoContenido = doc.getElementById('jobListContainer').innerHTML;
        
        // Reemplazamos el contenedor sin recargar la página
        document.getElementById('jobListContainer').innerHTML = nuevoContenido;
    } catch (e) {
        console.error("No se pudo actualizar la lista de trabajos automáticamente.");
    }
}, 10000);
<?php endif; ?>

</script>
<?php require_once "../app/views/layouts/footer.php"; ?>