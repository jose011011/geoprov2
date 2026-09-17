<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   HEREDAMOS VARIABLES DEL DISEÑO PREMIUM
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-dark-2: #0b2435; --geo-primary: #08b7a5; --geo-primary-dark: #079486; --geo-primary-soft: #e8faf7;
    --geo-blue: #2563eb; --geo-blue-soft: #eff6ff; --geo-text: #172033; --geo-muted: #718096; --geo-bg: #f5f7fa; --geo-white: #ffffff;
    --geo-border: #e8edf2; --geo-shadow: 0 12px 35px rgba(15, 23, 42, .07); --geo-radius: 22px;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; overflow-x: hidden; margin: 0; }
.geo-navbar { display: none !important; }

/* Sidebar Básico */
.cli-wrapper { display: flex; width: 100%; min-height: 100vh; }
.cli-sidebar { width: 260px; background-color: #ffffff; border-right: 1px solid var(--geo-border); position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; overflow-y: auto; padding-bottom: 50px; }
.sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); color: var(--geo-dark); font-size: 1.5rem; font-weight: 900; }
.sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); }
.sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--geo-primary), var(--geo-blue)); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; }
.sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
.sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #475569; text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.2s; border-left: 4px solid transparent; }
.sidebar-menu li a:hover { color: var(--geo-primary); background-color: #f8fafc; }

.cli-main-content { flex: 1; margin-left: 260px; min-width: 0; display: flex; flex-direction: column; }
.cli-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; padding: 0 30px; border-bottom: 1px solid var(--geo-border); position: sticky; top: 0; z-index: 999; }

@media (max-width: 991px) {
    .cli-sidebar { transform: translateX(-100%); }
    .cli-main-content { margin-left: 0; }
}

/* ============================================================
   DISEÑO LLM Y FORMULARIO
   ============================================================ */
.dashboard-content { padding: 40px; max-width: 800px; margin: 0 auto; width: 100%; }

/* Pantalla de Carga IA */
.ai-loading-screen { text-align: center; padding: 80px 20px; background: #fff; border-radius: var(--geo-radius); box-shadow: var(--geo-shadow); border: 1px solid var(--geo-border); }
.ai-icon-pulse { font-size: 4rem; color: var(--geo-primary); margin-bottom: 20px; animation: pulse 1.5s infinite; }
@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.7; } 100% { transform: scale(1); opacity: 1; } }

/* Formulario de Solicitud */
.request-form-card { background: #fff; border-radius: var(--geo-radius); padding: 35px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid var(--geo-border); }
.detected-card { background: var(--geo-primary-soft); color: var(--geo-primary-dark); border-radius: 16px; padding: 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; border: 1px solid #c8ebe6; }
.detected-icon { width: 50px; height: 50px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--geo-primary); flex-shrink: 0; box-shadow: 0 4px 10px rgba(8, 183, 165, 0.1); }

.form-group-custom { margin-bottom: 20px; }
.form-group-custom label { display: block; font-size: 0.9rem; font-weight: 800; color: var(--geo-text); margin-bottom: 8px; }
.form-control-custom { width: 100%; padding: 14px 15px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; color: var(--geo-text); font-weight: 500; transition: all 0.2s; }
.form-control-custom:focus { outline: none; border-color: var(--geo-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(8, 183, 165, 0.1); }

/* Botones de Asignación Ocultos de la vista del cliente */
.assignment-section { margin-top: 35px; border-top: 2px dashed var(--geo-border); padding-top: 30px; }
.btn-auto-cascade { background: linear-gradient(135deg, var(--geo-primary) 0%, var(--geo-primary-dark) 100%); color: white; border: none; border-radius: 16px; padding: 20px; width: 100%; text-align: left; display: flex; align-items: center; gap: 20px; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; box-shadow: 0 10px 25px rgba(8, 183, 165, 0.3); margin-bottom: 15px; }
.btn-auto-cascade:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(8, 183, 165, 0.4); }
.btn-auto-icon { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }

.btn-manual-toggle { background: #fff; color: var(--geo-dark); border: 2px solid var(--geo-border); border-radius: 16px; padding: 18px; width: 100%; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s; box-shadow: var(--geo-shadow); }
.btn-manual-toggle:hover { border-color: var(--geo-primary); color: var(--geo-primary); }

/* Lista de Profesionales Manual */
.manual-list-container { display: none; margin-top: 25px; animation: fadeIn 0.5s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.pro-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid var(--geo-border); display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; transition: all 0.2s; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
.pro-card:hover { border-color: var(--geo-primary); }

.pro-avatar { width: 50px; height: 50px; border-radius: 50%; background: var(--geo-dark); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; position: relative; }
.fav-heart { position: absolute; bottom: -5px; right: -5px; background: white; border-radius: 50%; padding: 2px; font-size: 1rem; color: #ef4444; }

.pro-actions { display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
.btn-ver-perfil { background: #f1f5f9; color: #475569; border: none; padding: 6px 15px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: background 0.2s; }
.btn-ver-perfil:hover { background: #e2e8f0; }
.btn-select-pro { background: var(--geo-dark); color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; }
.btn-select-pro:hover { background: var(--geo-primary); }
</style>

<div class="cli-wrapper">
    <aside class="cli-sidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-location-crosshairs" style="color:var(--geo-primary);"></i> GEO-PRO</div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'C', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Cliente') ?></h6>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/cliente/dashboard"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a></li>
        </ul>
    </aside>

    <main class="cli-main-content">
        <header class="cli-topbar">
            <div class="fw-bold text-muted">Detalles de la Solicitud</div>
        </header>

        <div class="dashboard-content">
            
            <?php if ($categoria): ?>

                <!-- PANTALLA 1: SIMULACIÓN IA (Solo visible si usó el buscador de texto) -->
                <?php if ($esBusquedaIA): ?>
                    <div class="ai-loading-screen" id="loadingScreen">
                        <i class="fa-solid fa-microchip ai-icon-pulse"></i>
                        <h3 class="fw-bold" style="color:var(--geo-dark);">Procesando Solicitud...</h3>
                        <p class="text-muted">Nuestra Inteligencia Artificial está analizando tu problema para encontrar la especialidad correcta.</p>
                    </div>
                <?php endif; ?>

                <!-- PANTALLA 2: FORMULARIO Y ASIGNACIÓN (Oculto inicialmente si es IA, visible directo si es manual) -->
                <div id="mainFormScreen" style="display: <?= $esBusquedaIA ? 'none' : 'block' ?>;">
                    
                    <div class="request-form-card">
                        
                        <!-- Tarjeta de Categoría Detectada/Seleccionada -->
                        <div class="detected-card">
                            <div class="detected-icon"><i class="<?= htmlspecialchars($categoria['icono_fa']) ?>"></i></div>
                            <div>
                                <span style="font-weight:800; font-size:0.75rem; text-transform:uppercase; letter-spacing: 0.5px;">Especialidad Requerida</span>
                                <h4 class="m-0 fw-bold"><?= htmlspecialchars($categoria['nombre_categoria']) ?></h4>
                            </div>
                        </div>

                        <!-- FORMULARIO DE ENVÍO -->
                        <form id="assignForm" method="POST" action="<?= BASE_URL ?>/cliente/registrarPedido">
                            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                            <?php 
                                $idProf = $idProfSeleccionado ?? 0;
                                $modoAsignacion = ($idProf > 0) ? 'MANUAL' : 'AUTO_CASCADA';
                            ?>
                            <input type="hidden" name="tipo_asignacion" id="tipoAsignacion" value="<?= $modoAsignacion ?>">
                            <input type="hidden" name="id_profesional" id="idProfesional" value="<?= $idProf ?>">

                            <div class="form-group-custom">
                                <label for="descripcion">Detalla el problema a resolver <span class="text-danger">*</span></label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="form-control-custom" required placeholder="Describe lo que necesitas que reparen o realicen..."><?= htmlspecialchars($problemaOriginal ?? '') ?></textarea>
                            </div>

                            <div class="form-group-custom">
                                <label for="direccion_servicio">Dirección exacta del servicio <span class="text-danger">*</span></label>
                                <input type="text" name="direccion_servicio" id="direccion_servicio" class="form-control-custom" required value="<?= htmlspecialchars($clienteInfo['direccion_referencia'] ?? '') ?>" placeholder="Calle, número de casa, etc.">
                                <small class="text-muted fw-bold">Zona registrada: <?= htmlspecialchars($clienteInfo['zona'] ?? 'La Paz') ?></small>
                            </div>

                            <!-- OPCIONES DE ASIGNACIÓN -->
                            <div class="assignment-section">
                                <?php if ($idProf > 0): ?>
                                    <!-- MODO ASIGNACIÓN DIRECTA (MANUAL DESDE CATÁLOGO) -->
                                    <h5 class="fw-bold mb-3" style="color:var(--geo-dark);"><i class="fa-solid fa-user-check text-success"></i> Profesional Seleccionado</h5>
                                    <p class="text-muted small">Tu solicitud será enviada directamente al especialista seleccionado.</p>
                                    <button type="submit" class="btn-select-pro w-100 py-3 fs-5">
                                        Confirmar y Solicitar Servicio
                                    </button>
                                <?php else: ?>
                                    <h5 class="fw-bold mb-3" style="color:var(--geo-dark);">¿Cómo deseas buscar a tu especialista?</h5>
                                    
                                    <!-- Opción 1: Automático (Botón limpio sin detalles técnicos) -->
                                    <button type="button" class="btn-auto-cascade" onclick="submitCascada()">
                                        <div class="btn-auto-icon"><i class="fa-solid fa-satellite-dish"></i></div>
                                        <div>
                                            <h5 class="fw-bold mb-1 m-0 text-white">Enviar a profesionales (Recomendado)</h5>
                                            <p class="m-0" style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">
                                                El sistema enviará tu solicitud a la red de técnicos disponibles en tu zona para que el primero en aceptarla atienda tu caso.
                                            </p>
                                        </div>
                                    </button>

                                    <div class="text-center fw-bold text-muted my-3">O SI PREFIERES...</div>

                                    <!-- Opción 2: Manual -->
                                    <button type="button" class="btn-manual-toggle" onclick="mostrarListaManual()">
                                        <i class="fa-solid fa-list-check me-2"></i> Elegir manualmente a mi especialista
                                    </button>

                                    <!-- LISTA DE PROFESIONALES (Oculta por defecto) -->
                                    <div class="manual-list-container" id="manualList">
                                        <h6 class="fw-bold mb-3 mt-4" style="color:var(--geo-dark);">Profesionales Activos en tu Zona</h6>
                                    
                                    <?php if (!empty($profesionales)): ?>
                                        <?php foreach($profesionales as $p): ?>
                                            <div class="pro-card">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="pro-avatar">
                                                        <?= strtoupper(substr($p['nombre'], 0, 1)) ?>
                                                        <!-- Icono de Corazón si es Favorito -->
                                                        <?php if((int)$p['es_favorito'] > 0): ?>
                                                            <i class="fa-solid fa-heart fav-heart" title="Tu favorito"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold m-0 text-dark">
                                                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                                        </h6>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fa-solid fa-star text-warning"></i> <?= number_format((float)$p['promedio_estrellas'], 1) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pro-actions">
                                                    <!-- Botón Curiosear -->
                                                    <button type="button" class="btn-ver-perfil" onclick="verPerfil('<?= htmlspecialchars(addslashes($p['nombre'].' '.$p['apellido'])) ?>', '<?= htmlspecialchars(addslashes($p['descripcion_servicio'] ?? 'Especialista en su área.')) ?>', <?= (float)$p['tarifa_base'] ?>, <?= (float)$p['promedio_estrellas'] ?>, <?= $p['id_profesional'] ?>)">
                                                        Ver Perfil
                                                    </button>
                                                    <!-- Botón Asignar Real -->
                                                    <button type="button" class="btn-select-pro" onclick="submitManual(<?= $p['id_profesional'] ?>)">
                                                        Asignar
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center p-4 bg-light rounded-4 border">
                                            <i class="fa-solid fa-user-slash fa-2x text-muted opacity-50 mb-2"></i>
                                            <p class="text-muted fw-bold m-0">No hay profesionales disponibles ahora.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- Si el LLM no detectó nada (Fallo de seguridad) -->
                <div class="text-center p-5 bg-white border rounded-4 shadow-sm">
                    <i class="fa-solid fa-robot fa-3x text-warning mb-3"></i>
                    <h4 class="fw-bold text-dark">No pudimos procesar tu solicitud</h4>
                    <p class="text-muted">La categoría del servicio no pudo ser identificada.</p>
                    <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-dark fw-bold px-4 rounded-pill mt-3">Volver al inicio</a>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- MODAL "CURIOSEAR" PERFIL -->
<div class="modal fade" id="perfilModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div style="width: 70px; height: 70px; background: var(--geo-dark); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 15px;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1" id="modalProfName">Nombre</h4>
                <div class="mb-3 text-warning fw-bold"><i class="fa-solid fa-star"></i> <span id="modalProfStars">5.0</span> / 5.0</div>
                
                <div class="bg-light p-3 rounded-4 text-start mb-4 border">
                    <h6 class="fw-bold text-muted small text-uppercase mb-2">Acerca del profesional</h6>
                    <p class="text-dark small m-0" id="modalProfDesc" style="line-height: 1.5;"></p>
                </div>

                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border mb-4">
                    <span class="fw-bold text-muted small">Tarifa Base:</span>
                    <span class="fw-bold text-dark fs-5">Bs. <span id="modalProfTarifa">0.00</span></span>
                </div>

                <button type="button" class="btn-select-pro w-100 py-3 fs-6" id="btnModalContratar">
                    <i class="fa-solid fa-handshake me-2"></i> Contratar a este profesional
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Si viene de una búsqueda IA, ocultar form y mostrar loading 2 segundos
    <?php if ($esBusquedaIA): ?>
        setTimeout(() => {
            const loading = document.getElementById('loadingScreen');
            const mainForm = document.getElementById('mainFormScreen');
            if(loading && mainForm) {
                loading.style.display = 'none';
                mainForm.style.display = 'block';
                mainForm.style.animation = 'fadeIn 0.5s';
            }
        }, 2000);
    <?php endif; ?>
});

// Mostrar la lista manual (oculta por defecto)
function mostrarListaManual() {
    const list = document.getElementById('manualList');
    list.style.display = 'block';
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

// Envíos del Formulario (Garantizado que funcionan y evitan doble click)
function submitCascada() {
    const form = document.getElementById('assignForm');
    if(form.reportValidity()) {
        const btn = document.querySelector('.btn-auto-cascade');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
        }
        document.getElementById('tipoAsignacion').value = 'AUTO_CASCADA';
        form.submit();
    }
}

function submitManual(idProfesional) {
    const form = document.getElementById('assignForm');
    if(form.reportValidity()) {
        document.getElementById('tipoAsignacion').value = 'MANUAL';
        document.getElementById('idProfesional').value = idProfesional;
        form.submit();
    }
}

// Para el botón directo cuando viene del catálogo
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('assignForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const btnDirecto = document.querySelector('.btn-select-pro[type="submit"]');
            if (btnDirecto) {
                btnDirecto.disabled = true;
                btnDirecto.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Solicitando...';
            }
        });
    }
});

// Lógica del Modal "Curiosear"
function verPerfil(nombre, desc, tarifa, estrellas, idProf) {
    document.getElementById('modalProfName').textContent = nombre;
    document.getElementById('modalProfDesc').textContent = desc || 'Sin descripción detallada.';
    document.getElementById('modalProfTarifa').textContent = tarifa.toFixed(2);
    document.getElementById('modalProfStars').textContent = estrellas.toFixed(1);
    
    // Le asignamos la función de contratar al botón del modal
    const btnContratar = document.getElementById('btnModalContratar');
    btnContratar.onclick = function() { submitManual(idProf); };

    // Mostrar el modal de Bootstrap
    var myModal = new bootstrap.Modal(document.getElementById('perfilModal'));
    myModal.show();
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>