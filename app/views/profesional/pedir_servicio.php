<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Estructura Base */
    .geo-navbar { display: none !important; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }
    .pro-wrapper { display: flex; width: 100%; min-height: 100vh; }
    .pro-sidebar { width: 260px; background-color: #0f172a; color: #94a3b8; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; padding-bottom: 50px; }
    .sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid #1e293b; }
    .sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background-color: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid #1e293b; }
    .sidebar-profile h6 { color: #f8fafc; font-weight: 700; margin-bottom: 5px; font-size: 1.1rem; }
    .sidebar-profile p { font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; }
    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; }
    .sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
    .sidebar-menu li a:hover, .sidebar-menu li a.active { color: #ffffff; background-color: #1e293b; border-left-color: #3b82f6; }
    .pro-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .pro-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 999; }
    
    .dashboard-content { padding: 30px; }
    .request-card { background: #fff; border-radius: 20px; padding: 35px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; max-width: 800px; margin: 0 auto; }
    
    .form-group-custom { margin-bottom: 25px; }
    .form-group-custom label { display: block; font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
    .form-control-custom, .form-select-custom { width: 100%; padding: 14px 15px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; color: #1e293b; font-weight: 500; }
    
    /* IA Simulation Styles */
    .ai-analyzer { display: none; text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 16px; border: 2px dashed #cbd5e1; margin-top: 20px; }
    .ai-analyzer.active { display: block; animation: fadeIn 0.5s; }
    .ai-spinner { font-size: 3rem; color: #3b82f6; margin-bottom: 15px; }
    
    /* Opciones de Asignación */
    .assignment-options { display: none; margin-top: 30px; animation: fadeIn 0.5s; }
    .assignment-options.active { display: block; }
    .btn-auto { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; width: 100%; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 15px; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); transition: transform 0.3s; margin-bottom: 15px;}
    .btn-auto:hover { transform: translateY(-3px); }
    .btn-manual-toggle { background: #f1f5f9; color: #475569; padding: 15px; border-radius: 16px; font-weight: 800; font-size: 1rem; border: 2px solid #e2e8f0; width: 100%; cursor: pointer; transition: all 0.3s; }
    .btn-manual-toggle:hover { background: #e2e8f0; }

    /* Lista de Profesionales (Manual) */
    .manual-list { display: none; margin-top: 20px; }
    .pro-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; transition: all 0.2s; }
    .pro-card:hover { border-color: #3b82f6; box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1); }
    .pro-card.plan-oro { border-left: 5px solid #f59e0b; background: #fffbeb; }
    .pro-card.plan-plata { border-left: 5px solid #94a3b8; }
    .pro-card.plan-bronce { border-left: 5px solid #cd7f32; }
    
    .pro-info { display: flex; align-items: center; gap: 15px; }
    .pro-avatar { width: 50px; height: 50px; border-radius: 50%; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; }
    .pro-card.plan-oro .pro-avatar { background: #f59e0b; }
    
    .btn-select-pro { background: #3b82f6; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .btn-select-pro:hover { background: #2563eb; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
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
            <li><a href="<?= BASE_URL ?>/profesional/pedirServicio" class="active"><i class="fa-solid fa-hand-sparkles"></i> Pedir Asistencia</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/comprarTokens"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="pro-main-content">
        <header class="pro-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Modo Cliente Inteligente</span></div>
        </header>

        <div class="dashboard-content">
            <div class="text-center mb-4">
                <h2 class="page-title text-dark">Encontrar un Especialista</h2>
                <p class="page-subtitle">Usa nuestra Inteligencia Artificial para buscar y asignar tu problema al mejor profesional disponible.</p>
            </div>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'ok'): ?>
                <div class="alert alert-success d-flex align-items-center rounded-4 shadow-sm border-0 max-w-800 mx-auto mb-4" role="alert" style="max-width: 800px;">
                    <i class="fa-solid fa-circle-check fa-2x me-3"></i>
                    <div>
                        <strong>¡Solicitud Asignada Exitosamente!</strong><br>
                        <span class="small">El profesional ha sido notificado y pronto se pondrá en contacto contigo.</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error']) && $_GET['error'] == 'sin_profesionales'): ?>
                <div class="alert alert-warning d-flex align-items-center rounded-4 shadow-sm border-0 max-w-800 mx-auto mb-4" role="alert" style="max-width: 800px;">
                    <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                    <div>
                        <strong>No hay profesionales disponibles</strong><br>
                        <span class="small">Nadie en esa categoría está disponible o tiene tokens suficientes. Intenta más tarde.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="request-card">
                <form id="requestForm" method="POST" action="<?= BASE_URL ?>/profesional/registrarPedidoServicio">
                    
                    <!-- INPUTS OCULTOS DE LÓGICA -->
                    <input type="hidden" name="tipo_asignacion" id="tipo_asignacion" value="AUTO">
                    <input type="hidden" name="id_profesional_seleccionado" id="id_profesional_seleccionado" value="0">

                    <!-- PASO 1: DATOS DEL PROBLEMA -->
                    <div id="step1">
                        <div class="form-group-custom">
                            <label for="id_categoria">1. ¿Qué servicio requieres?</label>
                            <select name="id_categoria" id="id_categoria" class="form-select-custom" required>
                                <option value="" selected disabled>Selecciona la especialidad...</option>
                                <?php foreach($categorias as $c): ?>
                                    <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre_categoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-custom">
                            <label for="descripcion_problema">2. Describe el problema detalladamente</label>
                            <textarea name="descripcion_problema" id="descripcion_problema" rows="3" class="form-control-custom" required placeholder="Ej: Fuga de agua debajo del lavamanos..."></textarea>
                        </div>

                        <div class="form-group-custom">
                            <label for="direccion_servicio">3. Dirección exacta</label>
                            <input type="text" name="direccion_servicio" id="direccion_servicio" class="form-control-custom" required value="<?= htmlspecialchars($perfil['zona_especifica'] ?? '') ?>">
                        </div>

                        <button type="button" class="btn-submit mt-2" id="btnAnalizar" onclick="simularIA()">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Analizar con Inteligencia Artificial
                        </button>
                    </div>

                    <!-- PASO 2: SIMULACIÓN IA (Oculto inicialmente) -->
                    <div class="ai-analyzer" id="aiLoading">
                        <i class="fa-solid fa-microchip fa-spin ai-spinner"></i>
                        <h4 class="fw-bold text-dark mb-1">El LLM está procesando tu solicitud...</h4>
                        <p class="text-muted">Buscando especialistas con alta calificación, verificando disponibilidad y planes de suscripción.</p>
                    </div>

                    <!-- PASO 3: OPCIONES DE ASIGNACIÓN (Auto vs Manual) -->
                    <div class="assignment-options" id="assignmentOptions">
                        <h5 class="fw-bold text-dark text-center border-bottom pb-3 mb-4">Análisis Completado. Elige cómo proceder:</h5>
                        
                        <!-- Opción Automática -->
                        <button type="button" class="btn-auto" onclick="submitAutomatico()">
                            <i class="fa-solid fa-bolt fa-2x"></i>
                            <div class="text-start">
                                <span class="d-block fw-bold fs-5">Lanzar Solicitud con I.A.</span>
                                <span class="d-block fw-normal" style="font-size: 0.85rem; opacity: 0.9;">Enviar alerta al mejor profesional clasificado (Recomendado)</span>
                            </div>
                        </button>

                        <div class="text-center my-3 text-muted fw-bold">O</div>

                        <!-- Opción Manual -->
                        <button type="button" class="btn-manual-toggle" onclick="mostrarListaManual()">
                            <i class="fa-solid fa-list-check me-2"></i> Quiero elegir manualmente de la lista
                        </button>

                        <!-- Lista de Profesionales (Se filtra por JS según la categoría elegida) -->
                        <div class="manual-list" id="manualListContainer">
                            <h6 class="fw-bold text-dark mt-4 mb-3">Especialistas disponibles para esta categoría:</h6>
                            
                            <?php foreach($listaProfesionales as $p): ?>
                                <?php 
                                    // Clases visuales según el plan
                                    $nombrePlan = strtolower($p['nombre_plan']);
                                    $clasePlan = 'plan-bronce'; $iconoPlan = 'fa-medal';
                                    if(str_contains($nombrePlan, 'premium') || str_contains($nombrePlan, 'oro')) { $clasePlan = 'plan-oro'; $iconoPlan = 'fa-star text-warning'; }
                                    elseif(str_contains($nombrePlan, 'basico') || str_contains($nombrePlan, 'plata')) { $clasePlan = 'plan-plata'; $iconoPlan = 'fa-award text-secondary'; }
                                ?>
                                <div class="pro-card <?= $clasePlan ?> pro-item-card" data-cat="<?= $p['id_categoria'] ?>">
                                    <div class="pro-info">
                                        <div class="pro-avatar"><?= strtoupper(substr($p['nombre'], 0, 1)) ?></div>
                                        <div>
                                            <div class="fw-bold text-dark mb-1">
                                                <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                                <?php if($clasePlan === 'plan-oro'): ?> <i class="fa-solid fa-circle-check text-warning ms-1" title="Profesional Premium"></i> <?php endif; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fa-solid <?= $iconoPlan ?> me-1"></i> Plan <?= htmlspecialchars(str_replace('_', ' ', $p['nombre_plan'])) ?> | 
                                                <i class="fa-solid fa-star text-warning ms-2 me-1"></i> <?= number_format((float)$p['promedio_estrellas'], 1) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-select-pro" onclick="submitManual(<?= $p['id_profesional'] ?>)">
                                        Contratar
                                    </button>
                                </div>
                            <?php endforeach; ?>
                            
                            <div id="noProsMessage" style="display:none;" class="text-center text-muted py-3">
                                No hay profesionales activos en esta categoría en este momento.
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Menú Responsive
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});

// LÓGICA DE SIMULACIÓN IA Y ENRUTAMIENTO
function simularIA() {
    const form = document.getElementById('requestForm');
    if(!form.checkValidity()) {
        form.reportValidity(); // Muestra alertas si falta llenar datos
        return;
    }

    // Ocultar paso 1, mostrar Loading
    document.getElementById('step1').style.display = 'none';
    document.getElementById('aiLoading').classList.add('active');

    // Simular tiempo de pensamiento del LLM (2 segundos)
    setTimeout(() => {
        document.getElementById('aiLoading').classList.remove('active');
        document.getElementById('aiLoading').style.display = 'none';
        document.getElementById('assignmentOptions').classList.add('active');
        
        // Al mostrar las opciones, filtramos la lista oculta por la categoría elegida
        filtrarListaCategoria();
    }, 2000);
}

function filtrarListaCategoria() {
    const catSeleccionada = document.getElementById('id_categoria').value;
    const proCards = document.querySelectorAll('.pro-item-card');
    let hayResultados = false;

    proCards.forEach(card => {
        if(card.getAttribute('data-cat') === catSeleccionada) {
            card.style.display = 'flex';
            hayResultados = true;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('noProsMessage').style.display = hayResultados ? 'none' : 'block';
}

function mostrarListaManual() {
    document.getElementById('manualListContainer').style.display = 'block';
}

// Envíos del Formulario
function submitAutomatico() {
    document.getElementById('tipo_asignacion').value = 'AUTO';
    document.getElementById('requestForm').submit();
}

function submitManual(idProfesional) {
    document.getElementById('tipo_asignacion').value = 'MANUAL';
    document.getElementById('id_profesional_seleccionado').value = idProfesional;
    document.getElementById('requestForm').submit();
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>