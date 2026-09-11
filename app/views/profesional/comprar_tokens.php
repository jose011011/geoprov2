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
       DISEÑO DE LA TIENDA Y CHECKOUT
       ========================================================= */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #0f172a; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    /* Tarjetas de Precios */
    .pricing-card { background: #fff; border-radius: 20px; border: 2px solid #e2e8f0; padding: 30px; text-align: center; transition: all 0.3s; height: 100%; position: relative; overflow: hidden; }
    .pricing-card:hover { transform: translateY(-5px); border-color: #3b82f6; box-shadow: 0 15px 30px rgba(59, 130, 246, 0.1); }
    .pricing-card.destacado { border-color: #f59e0b; background: #fffbeb; }
    .badge-popular { position: absolute; top: 15px; right: -35px; background: #f59e0b; color: white; padding: 5px 40px; transform: rotate(45deg); font-weight: bold; font-size: 0.8rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .plan-icon { font-size: 3rem; color: #3b82f6; margin-bottom: 15px; }
    .destacado .plan-icon { color: #f59e0b; }
    .plan-name { font-weight: 800; color: #0f172a; font-size: 1.3rem; margin-bottom: 10px; }
    .plan-price { font-size: 2.5rem; font-weight: 900; color: #1e293b; margin-bottom: 5px; }
    .plan-price span { font-size: 1rem; color: #64748b; font-weight: 500; }
    .plan-tokens { display: inline-block; background: #f1f5f9; color: #334155; padding: 5px 15px; border-radius: 20px; font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; }
    .destacado .plan-tokens { background: #fde68a; color: #92400e; }
    .plan-desc { color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; height: 60px; overflow: hidden;}
    .btn-select-plan { width: 100%; padding: 12px; border-radius: 10px; font-weight: 700; border: 2px solid #3b82f6; background: transparent; color: #3b82f6; transition: all 0.2s; cursor: pointer;}
    .btn-select-plan:hover, .pricing-card.selected .btn-select-plan { background: #3b82f6; color: white; }
    .destacado .btn-select-plan { border-color: #f59e0b; color: #f59e0b; }
    .destacado .btn-select-plan:hover, .pricing-card.selected.destacado .btn-select-plan { background: #f59e0b; color: white; }
    .pricing-card.selected { border-width: 3px; border-color: #10b981; }
    
    #checkoutSection { display: none; margin-top: 40px; animation: slideUp 0.5s ease; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Tarjetas de Instrucciones y QR */
    .bank-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; text-align: center; height: 100%; }
    .qr-box { width: 200px; height: 200px; border: 2px dashed #cbd5e1; border-radius: 16px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden;}
    .qr-box img { max-width: 100%; height: auto; opacity: 0.8;}
    .bank-info-box { background: #eff6ff; padding: 15px; border-radius: 12px; border: 1px solid #bfdbfe; margin-top: 20px; text-align: left; }
    .bank-info-box h6 { color: #1d4ed8; font-weight: 800; margin-bottom: 5px; }
    .bank-info-box p { color: #3b82f6; font-size: 0.85rem; margin: 0; }

    /* Formulario de Validación */
    .checkout-card { background: #fff; border-radius: 20px; padding: 35px 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%; }
    .checkout-title { font-weight: 800; color: #1e293b; font-size: 1.2rem; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;}
    
    .form-group-custom { margin-bottom: 20px; }
    .form-group-custom label { display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .form-control-custom, .form-select-custom { width: 100%; padding: 14px 15px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; color: #1e293b; font-weight: 600; transition: all 0.2s; }
    .form-control-custom:focus, .form-select-custom:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    
    /* Validaciones CSS Visuales */
    .input-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
    .error-text { color: #ef4444; font-size: 0.75rem; font-weight: 700; margin-top: 5px; display: none; }
    .input-error + .error-text { display: block; }

    .btn-submit { background: #10b981; color: white; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 1rem; border: none; width: 100%; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); margin-top: 10px;}
    .btn-submit:hover { background: #059669; transform: translateY(-2px); }
    .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; box-shadow: none; }
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
            <li><a href="<?= BASE_URL ?>/profesional/historial"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/comprarTokens" class="active"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="pro-main-content">
        <header class="pro-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Billetera Virtual</span></div>
            <div class="tokens-display shadow-sm">
                <i class="fa-solid fa-coins fa-beat"></i> 
                <span><?= (int)($perfil['tokens_disponibles'] ?? 0) ?> Tokens</span>
            </div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Recargar Saldo y Membresía</h2>
            <?php if(isset($_GET['success']) && $_GET['success'] == 'ok'): ?>
                <div class="alert alert-success d-flex align-items-center rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check fa-2x me-3"></i>
                    <div>
                        <strong>¡Comprobante enviado exitosamente!</strong><br>
                        <span class="small">El departamento financiero verificará el pago y tus tokens se activarán pronto.</span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'codigo_duplicado'): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                    <div>
                        <strong>Error al reportar</strong><br>
                        <span class="small">Ese código de comprobante ya fue registrado anteriormente en el sistema.</span>
                    </div>
                </div>
            <?php endif; ?>
            <p class="page-subtitle">Adquiere tokens para poder enviar propuestas a los clientes. 1 Token = 1 Cliente Contactado.</p>

            <div class="row g-4" id="plansSection">
                <?php if(isset($planes)): ?>
                    <?php foreach($planes as $plan): ?>
                        <?php 
                            // Ignoramos el plan gratuito si el precio es 0
                            if (!isset($plan['precio']) || $plan['precio'] <= 0) continue; 
                            $esDestacado = $plan['posicionamiento_destacado'] == 1;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="pricing-card <?= $esDestacado ? 'destacado' : '' ?>" id="card_plan_<?= $plan['id_plan'] ?>">
                                <?php if($esDestacado): ?>
                                    <div class="badge-popular">ORO</div>
                                    <i class="fa-solid fa-crown plan-icon"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-medal plan-icon"></i>
                                <?php endif; ?>
                                
                                <h3 class="plan-name"><?= htmlspecialchars(str_replace('_', ' ', $plan['nombre_plan'])) ?></h3>
                                <div class="plan-price">Bs. <?= number_format($plan['precio'], 0) ?><span>/mes</span></div>
                                <div class="plan-tokens"><i class="fa-solid fa-coins me-1"></i> <?= $plan['tokens_otorgados'] ?> Tokens</div>
                                <p class="plan-desc"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                
                                <button type="button" class="btn-select-plan" onclick="seleccionarPlan(<?= $plan['id_plan'] ?>, <?= $plan['precio'] ?>)">
                                    Seleccionar Plan
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- SECCIÓN DE CHECKOUT (Oculta por defecto) -->
            <div id="checkoutSection">
                <div class="row g-4">
                    <!-- COLUMNA IZQUIERDA: INSTRUCCIONES Y QR -->
                    <div class="col-lg-5">
                        <div class="bank-card">
                            <h5 class="fw-bold text-dark mb-4">1. Escanea y Paga</h5>
                            <div class="qr-box">
                                <i class="fa-solid fa-qrcode fa-5x text-muted opacity-25"></i>
                            </div>
                            <p class="text-muted small fw-bold mb-0">Escanea este código desde la App de tu banco (QR Simple Bolivia).</p>
                            
                            <div class="bank-info-box">
                                <h6><i class="fa-solid fa-building-columns me-1"></i> Transferencia Bancaria</h6>
                                <p>Banco: <strong>Banco Unión</strong><br>
                                   Cuenta: <strong>10000012345678</strong><br>
                                   Titular: <strong>GEO-PRO LA PAZ SRL</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: FORMULARIO DE REPORTE -->
                    <div class="col-lg-7">
                        <div class="checkout-card">
                            <h4 class="checkout-title">2. Reportar el Pago</h4>
                            
                            <form id="paymentForm" action="<?= BASE_URL ?>/profesional/registrarPago" method="POST">
                                <input type="hidden" name="id_plan" id="input_id_plan" value="">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label for="monto">Monto a Pagar (Bs.)</label>
                                            <div class="position-relative">
                                                <i class="fa-solid fa-money-bill-wave position-absolute text-muted" style="left: 15px; top: 16px;"></i>
                                                <input type="number" step="0.10" id="monto" name="monto" class="form-control-custom" placeholder="Ej: 29.00" style="padding-left: 45px;" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label for="codigo_comprobante">Nro. de Comprobante / Transacción <span class="text-danger">*</span></label>
                                            <input type="text" id="codigo_comprobante" name="codigo_comprobante" class="form-control-custom" placeholder="Ej: 000123456789" autocomplete="off">
                                            <div class="error-text">El código debe tener al menos 6 números/letras.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-custom">
                                    <label for="metodo_pago">Método utilizado <span class="text-danger">*</span></label>
                                    <select name="metodo_pago" id="metodo_pago" class="form-select-custom">
                                        <option value="QR_SIMPLE_BOLIVIA">Código QR Simple</option>
                                        <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                        <option value="DEPOSITO_CAJA">Depósito en Caja</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn-submit" id="btnSubmit"><i class="fa-solid fa-paper-plane me-2"></i> Enviar Comprobante a Verificación</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- LÓGICA DE VALIDACIONES (JAVASCRIPT A PRUEBA DE BALAS) -->
<script>
// Función global para seleccionar un plan y mostrar el checkout
function seleccionarPlan(idPlan, precio) {
    // Marcar la tarjeta seleccionada
    document.querySelectorAll('.pricing-card').forEach(card => card.classList.remove('selected'));
    document.getElementById('card_plan_' + idPlan).classList.add('selected');
    
    // Setear valores en el formulario
    document.getElementById('input_id_plan').value = idPlan;
    document.getElementById('monto').value = parseFloat(precio).toFixed(2);
    
    // Mostrar el formulario
    const checkout = document.getElementById('checkoutSection');
    checkout.style.display = 'block';
    
    // Scroll suave hasta el formulario
    checkout.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.addEventListener("DOMContentLoaded", function() {
    // Menú Responsive
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // Referencias del Formulario
    const form = document.getElementById('paymentForm');
    const inputIdPlan = document.getElementById('input_id_plan');
    const inputCodigo = document.getElementById('codigo_comprobante');
    const btnSubmit = document.getElementById('btnSubmit');

    // Validación al enviar (Previene que se vaya data sucia al servidor)
    form.addEventListener('submit', function(e) {
        let esValido = true;

        // 1. Validar que seleccionó un plan
        if (inputIdPlan.value === "") {
            alert("Por favor, selecciona un plan primero.");
            esValido = false;
        }

        // 2. Validar Código de Comprobante (Mínimo 6 caracteres alfanuméricos)
        const codigoLimpio = inputCodigo.value.trim();
        const regexCodigo = /^[a-zA-Z0-9]{6,20}$/; 
        
        if (!regexCodigo.test(codigoLimpio)) {
            inputCodigo.classList.add('input-error');
            esValido = false;
        } else {
            inputCodigo.classList.remove('input-error');
        }

        // Si algo falló, detenemos el envío
        if (!esValido) {
            e.preventDefault(); 
            btnSubmit.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i> Revisa los errores';
            btnSubmit.style.backgroundColor = '#ef4444';
            
            setTimeout(() => {
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> Enviar Comprobante a Verificación';
                btnSubmit.style.backgroundColor = '';
            }, 3000);
        } else {
            // Si todo está bien, mostramos estado de carga
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Procesando...';
            btnSubmit.style.opacity = '0.7';
            btnSubmit.style.pointerEvents = 'none';
        }
    });

    // Limpiar alertas al escribir en el código
    inputCodigo.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>