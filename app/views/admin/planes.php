<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================================
       HEREDAMOS EL SIDEBAR CON SCROLL ARREGLADO
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
    
    @media (max-width: 991px) {
        .admin-sidebar { transform: translateX(-100%); }
        .admin-sidebar.show { transform: translateX(0); }
        .admin-main-content { margin-left: 0; }
        .btn-toggle-sidebar { display: block; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 998; display: none; }
        .sidebar-overlay.show { display: block; }
    }

    /* =========================================================
       TARJETAS DE PRECIOS Y PLANES
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #1e293b; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 40px; }

    .pricing-card { background: #ffffff; border-radius: 20px; padding: 40px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 2px solid transparent; position: relative; transition: transform 0.3s; display: flex; flex-direction: column; height: 100%; }
    .pricing-card:hover { transform: translateY(-10px); }
    .pricing-card.premium { border-color: #f59e0b; box-shadow: 0 15px 35px rgba(245, 158, 11, 0.15); }
    .premium-badge { position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); background: #f59e0b; color: white; padding: 5px 20px; border-radius: 20px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; }
    
    .plan-icon { font-size: 3rem; margin-bottom: 20px; }
    .plan-title { font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 10px; }
    .plan-price { font-size: 3rem; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 5px; }
    .plan-currency { font-size: 1.2rem; vertical-align: super; color: #64748b; }
    .plan-period { font-size: 0.9rem; color: #64748b; font-weight: 600; }
    
    .plan-features { list-style: none; padding: 0; margin: 30px 0; text-align: left; flex-grow: 1;}
    .plan-features li { padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; }
    .plan-features li i { color: #10b981; margin-right: 10px; font-size: 1.1rem; }
    .plan-features li.disabled i { color: #cbd5e1; }
    .plan-features li.disabled { color: #94a3b8; text-decoration: line-through; }

    /* =========================================================
       MODAL DE EDICIÓN DE PLANES
       ========================================================= */
    .custom-modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .custom-modal-overlay.active { opacity: 1; pointer-events: auto; }
    .custom-modal-card { background: #ffffff; width: 90%; max-width: 450px; border-radius: 24px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .custom-modal-overlay.active .custom-modal-card { transform: scale(1); }
    
    .modal-header-form { text-align: center; margin-bottom: 25px; }
    .modal-icon-form { width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }
    
    .form-group-custom { margin-bottom: 20px; text-align: left; }
    .form-group-custom label { display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .form-group-custom input { width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 1rem; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.2s; }
    .form-group-custom input:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .modal-actions { display: flex; gap: 10px; margin-top: 30px; }
    .modal-btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
    .modal-btn.cancel { background: #f1f5f9; color: #475569; }
    .modal-btn.cancel:hover { background: #e2e8f0; }
    .modal-btn.save { background: #3b82f6; color: #ffffff; }
    .modal-btn.save:hover { background: #2563eb; }
</style>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)) ?></div>
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
            <li><a href="<?= BASE_URL ?>/admin/pagos"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes" class="active"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
            <li><a href="<?= BASE_URL ?>/admin/categorias"><i class="fa-solid fa-layer-group"></i> Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reportes"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
        </ul>
    </aside>

    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Gestión de Planes y Monetización</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content">
            <div class="text-center mb-5">
                <h2 class="page-title text-dark">Planes de Suscripción GEO-PRO</h2>
                <p class="page-subtitle">Configuración del modelo de monetización y distribución de Tokens para Profesionales.</p>
            </div>

            <!-- ESCUDO DE PROTECCIÓN PHP -->
            <?php 
                // Valores por defecto seguros
                $idPlata = 2; $precioPlata = 29.00; $tokensPlata = 40;
                $idOro = 3;   $precioOro = 69.00;   $tokensOro = 999;
                
                if (isset($planes) && is_array($planes)) {
                    foreach ($planes as $plan) {
                        $nombrePlan = strtolower($plan['nombre_plan'] ?? '');
                        $precioBD = $plan['precio'] ?? 0;
                        $tokensBD = $plan['tokens_otorgados'] ?? 0;

                        if (str_contains($nombrePlan, 'basico') || str_contains($nombrePlan, 'plata')) {
                            $idPlata = $plan['id_plan'];
                            $precioPlata = $precioBD;
                            $tokensPlata = $tokensBD;
                        }
                        if (str_contains($nombrePlan, 'premium') || str_contains($nombrePlan, 'oro')) {
                            $idOro = $plan['id_plan'];
                            $precioOro = $precioBD;
                            $tokensOro = $tokensBD;
                        }
                    }
                }
            ?>

            <div class="row g-4 justify-content-center align-items-stretch">
                
                <!-- PLAN BRONCE (GRATUITO) -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <i class="fa-solid fa-medal plan-icon" style="color: #cd7f32;"></i>
                        <h3 class="plan-title">Plan Bronce</h3>
                        <div class="plan-price"><span class="plan-currency">Bs.</span>0</div>
                        <div class="plan-period">Gratis / Para Siempre</div>
                        
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check-circle"></i> 5 Tokens Gratuitos de Bienvenida</li>
                            <li><i class="fa-solid fa-check-circle"></i> Perfil visible en búsquedas manuales</li>
                            <li class="disabled"><i class="fa-solid fa-xmark-circle"></i> Recepción de Leads con retraso (10 min)</li>
                            <li class="disabled"><i class="fa-solid fa-xmark-circle"></i> Sin Insignia de Destacado</li>
                        </ul>
                        <button class="btn btn-light w-100 fw-bold border py-2" disabled>Plan Base / No Editable</button>
                    </div>
                </div>

                <!-- PLAN PLATA (BÁSICO) -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <i class="fa-solid fa-medal plan-icon" style="color: #94a3b8;"></i>
                        <h3 class="plan-title">Plan Plata</h3>
                        <div class="plan-price"><span class="plan-currency">Bs.</span><?= (int)$precioPlata ?></div>
                        <div class="plan-period">por mes</div>
                        
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check-circle"></i> <?= (int)$tokensPlata ?> Tokens Mensuales</li>
                            <li><i class="fa-solid fa-check-circle"></i> Prioridad Nivel 2 en Leads de IA</li>
                            <li><i class="fa-solid fa-check-circle"></i> Retraso menor en notificaciones (10 min)</li>
                            <li class="disabled"><i class="fa-solid fa-xmark-circle"></i> Sin Insignia de Destacado</li>
                        </ul>
                        <!-- AQUÍ SE ENVÍA EL ID_PLAN CORRECTO -->
                        <button class="btn btn-outline-primary w-100 fw-bold py-2" onclick="abrirModalEdicion(<?= $idPlata ?>, 'Plata', <?= (float)$precioPlata ?>, <?= (int)$tokensPlata ?>)">Editar Configuración</button>
                    </div>
                </div>

                <!-- PLAN ORO (PREMIUM) -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card premium">
                        <div class="premium-badge"><i class="fa-solid fa-star me-1"></i> Recomendado</div>
                        <i class="fa-solid fa-trophy plan-icon text-warning"></i>
                        <h3 class="plan-title">Plan Oro</h3>
                        <div class="plan-price"><span class="plan-currency">Bs.</span><?= (int)$precioOro ?></div>
                        <div class="plan-period">por mes</div>
                        
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check-circle text-warning"></i> Tokens Ilimitados (<?= (int)$tokensOro ?>)</li>
                            <li><i class="fa-solid fa-check-circle text-warning"></i> Prioridad Absoluta (Lead Inmediato)</li>
                            <li><i class="fa-solid fa-check-circle text-warning"></i> 15 Minutos de ventaja exclusiva</li>
                            <li><i class="fa-solid fa-check-circle text-warning"></i> Insignia Dorada en Búsquedas</li>
                        </ul>
                        <!-- AQUÍ SE ENVÍA EL ID_PLAN CORRECTO -->
                        <button class="btn btn-warning w-100 fw-bold py-2 text-dark" onclick="abrirModalEdicion(<?= $idOro ?>, 'Oro', <?= (float)$precioOro ?>, <?= (int)$tokensOro ?>)">Editar Configuración</button>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
<!-- MODAL DE EDICIÓN DE PLANES CORREGIDO -->
<div class="custom-modal-overlay" id="modalEdicionPlan">
    <div class="custom-modal-card">
        <form action="<?= BASE_URL ?>/admin/actualizarPlan" method="POST">
            <div class="modal-header-form">
                <div class="modal-icon-form"><i class="fa-solid fa-pen-to-square"></i></div>
                <h3 class="modal-title m-0">Editar Plan <span id="nombrePlanDisplay"></span></h3>
            </div>
            
            <!-- EL ID_PLAN VERDADERO OCULTO -->
            <input type="hidden" name="id_plan" id="inputIdPlan">

            <div class="form-group-custom">
                <label for="inputPrecio">Precio Mensual (Bs.)</label>
                <div class="position-relative">
                    <i class="fa-solid fa-money-bill-wave position-absolute text-muted" style="left: 15px; top: 15px;"></i>
                    <!-- AQUÍ ESTABA EL ERROR. Ahora dice name="precio" -->
                    <input type="number" step="0.01" min="0" name="precio" id="inputPrecio" required style="padding-left: 40px;">
                </div>
            </div>

            <div class="form-group-custom mb-0">
                <label for="inputTokens">Cantidad de Tokens a Otorgar</label>
                <div class="position-relative">
                    <i class="fa-solid fa-coins position-absolute text-muted" style="left: 15px; top: 15px;"></i>
                    <input type="number" min="0" name="tokens" id="inputTokens" required style="padding-left: 40px;">
                </div>
                <small class="text-muted mt-2 d-block text-start"><i class="fa-solid fa-circle-info"></i> Usa 999 para simbolizar tokens ilimitados en el Plan Oro.</small>
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="cerrarModalEdicion()">Cancelar</button>
                <button type="submit" class="modal-btn save"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});

// Lógica del Modal (Ahora recibe el ID correctamente)
const modalEdicion = document.getElementById('modalEdicionPlan');

function abrirModalEdicion(idPlan, nombrePlan, precioActual, tokensActuales) {
    document.getElementById('inputIdPlan').value = idPlan;
    document.getElementById('nombrePlanDisplay').textContent = nombrePlan;
    document.getElementById('inputPrecio').value = precioActual;
    document.getElementById('inputTokens').value = tokensActuales;
    modalEdicion.classList.add('active');
}

function cerrarModalEdicion() {
    modalEdicion.classList.remove('active');
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>