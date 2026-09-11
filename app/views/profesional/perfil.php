<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Estructura Base del Profesional */
    .geo-navbar { display: none !important; }
    body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }
    .pro-wrapper { display: flex; width: 100%; min-height: 100vh; }
    .pro-sidebar { width: 260px; background-color: #0f172a; color: #94a3b8; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; padding-bottom: 50px; }
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
    .sidebar-menu li a:hover, .sidebar-menu li a.active { color: #ffffff; background-color: #1e293b; border-left-color: #3b82f6; }

    .pro-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .pro-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 999; }
    .btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #475569; cursor: pointer; display: none; }
    
    @media (max-width: 991px) {
        .pro-sidebar { transform: translateX(-100%); }
        .pro-sidebar.show { transform: translateX(0); }
        .pro-main-content { margin-left: 0; }
        .btn-toggle-sidebar { display: block; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 998; display: none; }
        .sidebar-overlay.show { display: block; }
        .pro-topbar { padding: 0 15px; }
    }

    /* Estilos del Perfil */
    .dashboard-content { padding: 30px; }
    .page-title { font-weight: 800; color: #0f172a; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

    .profile-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%; }
    .avatar-wrapper { position: relative; width: 120px; height: 120px; margin: 0 auto 20px; }
    .avatar-circle { width: 100%; height: 100%; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800; border: 4px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden;}
    .btn-camera { position: absolute; bottom: 0; right: 0; width: 35px; height: 35px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #ffffff; cursor: pointer; transition: all 0.2s; }
    .btn-camera:hover { background: #2563eb; transform: scale(1.1); }

    .form-group-custom { margin-bottom: 20px; }
    .form-group-custom label { display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .form-control-custom, .form-select-custom { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; color: #1e293b; font-weight: 500; transition: all 0.2s; }
    .form-control-custom:focus, .form-select-custom:focus { outline: none; border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    
    .input-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
    .error-text { color: #ef4444; font-size: 0.75rem; font-weight: 700; margin-top: 5px; display: none; }
    .input-error + .error-text { display: block; }

    .btn-save { background: #10b981; color: white; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 1rem; border: none; width: 100%; cursor: pointer; transition: all 0.3s; }
    .btn-save:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); }
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
            <!-- EL ENLACE YA ESTÁ CORREGIDO SIN GUION -->
            <li><a href="<?= BASE_URL ?>/profesional/comprarTokens"><i class="fa-solid fa-coins"></i> Comprar Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/profesional/perfil" class="active"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="pro-main-content">
        <header class="pro-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Configuración de Cuenta</span></div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Perfil Público</h2>
            <p class="page-subtitle">Esta es la información que verán los clientes cuando la I.A. te asigne un trabajo.</p>

            <!-- Alertas de éxito o error -->
            <?php if(isset($_GET['success']) && $_GET['success'] == 'ok'): ?>
                <div class="alert alert-success d-flex align-items-center rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check fa-2x me-3"></i>
                    <div>
                        <strong>¡Perfil Actualizado!</strong><br>
                        <span class="small">Tus datos públicos han sido guardados correctamente.</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error']) && $_GET['error'] == 'celular_duplicado'): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                    <div>
                        <strong>Error al guardar</strong><br>
                        <span class="small">El número de celular ingresado ya pertenece a otro usuario en el sistema.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- COLUMNA IZQUIERDA: FOTO Y RESUMEN -->
                <div class="col-lg-4">
                    <div class="profile-card text-center">
                        <div class="avatar-wrapper">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($perfil['nombre'] ?? 'P', 0, 1) . substr($perfil['apellido'] ?? '', 0, 1)) ?>
                            </div>
                            <div class="btn-camera" title="Cambiar foto (Próximamente)"><i class="fa-solid fa-camera"></i></div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? '')) ?></h4>
                        <p class="text-muted mb-3"><i class="fa-solid fa-id-card me-1"></i> <?= htmlspecialchars($perfil['numero_documento'] ?? 'S/N') ?></p>
                        
                        <div class="bg-light p-3 rounded-4 border text-start">
                            <div class="mb-2"><small class="text-muted fw-bold text-uppercase">Plan Actual</small><br><span class="fw-bold text-dark"><i class="fa-solid fa-gem text-warning me-1"></i> <?= htmlspecialchars(str_replace('_', ' ', $perfil['nombre_plan'] ?? '')) ?></span></div>
                            <div class="mb-2"><small class="text-muted fw-bold text-uppercase">Especialidad</small><br><span class="fw-bold text-dark"><i class="fa-solid fa-wrench text-primary me-1"></i> <?= htmlspecialchars($perfil['nombre_categoria'] ?? '') ?></span></div>
                            <div><small class="text-muted fw-bold text-uppercase">Reputación</small><br><span class="fw-bold text-warning"><i class="fa-solid fa-star"></i> <?= number_format((float)($perfil['promedio_estrellas'] ?? 5.0), 1) ?> / 5.0</span></div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: FORMULARIO EDITABLE -->
                <div class="col-lg-8">
                    <div class="profile-card">
                        <form id="perfilForm" method="POST" action="<?= BASE_URL ?>/profesional/actualizarPerfil">
                            
                            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Datos de Contacto y Operación</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="correo">Correo Electrónico (No editable)</label>
                                        <div class="position-relative">
                                            <i class="fa-solid fa-envelope position-absolute text-muted" style="left: 15px; top: 15px;"></i>
                                            <input type="email" class="form-control-custom bg-light text-muted" value="<?= htmlspecialchars($perfil['correo'] ?? '') ?>" readonly style="padding-left: 45px;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="celular">Celular de Contacto <span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <i class="fa-solid fa-phone position-absolute text-muted" style="left: 15px; top: 15px;"></i>
                                            <input type="text" name="celular" id="celular" class="form-control-custom" value="<?= htmlspecialchars($perfil['celular'] ?? '') ?>" required style="padding-left: 45px;">
                                        </div>
                                        <div class="error-text">Ingresa un número de celular válido (Mínimo 8 dígitos).</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="macrodistrito_base">Macrodistrito Base <span class="text-danger">*</span></label>
                                        <select name="macrodistrito_base" id="macrodistrito_base" class="form-select-custom" required>
                                            <?php 
                                                $macros = ['ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES', 'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'];
                                                $macroActual = $perfil['macrodistrito_base'] ?? '';
                                                foreach($macros as $m): 
                                            ?>
                                                <option value="<?= $m ?>" <?= ($macroActual === $m) ? 'selected' : '' ?>><?= str_replace('_', ' ', $m) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="zona_especifica">Zona/Barrio Específico <span class="text-danger">*</span></label>
                                        <input type="text" name="zona_especifica" id="zona_especifica" class="form-control-custom" value="<?= htmlspecialchars($perfil['zona_especifica'] ?? '') ?>" required>
                                        <div class="error-text">Debes especificar tu zona de trabajo.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label for="tarifa_base">Tarifa Base por Visita o Revisión (Bs.) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="fa-solid fa-money-bill-wave position-absolute text-muted" style="left: 15px; top: 15px;"></i>
                                    <input type="number" step="0.50" min="0" name="tarifa_base" id="tarifa_base" class="form-control-custom" value="<?= (float)($perfil['tarifa_base'] ?? 0) ?>" required style="padding-left: 45px;">
                                </div>
                                <div class="error-text">La tarifa no puede ser negativa.</div>
                            </div>

                            <div class="form-group-custom">
                                <label for="descripcion_servicio">Descripción de tu Perfil (Marketing) <span class="text-danger">*</span></label>
                                <textarea name="descripcion_servicio" id="descripcion_servicio" class="form-control-custom" rows="4" required placeholder="Ej: Soy electricista con 10 años de experiencia, atiendo emergencias 24/7..."><?= htmlspecialchars($perfil['descripcion_servicio'] ?? '') ?></textarea>
                                <div class="error-text">Debes escribir al menos 20 caracteres para inspirar confianza a los clientes.</div>
                            </div>

                            <button type="submit" class="btn-save" id="btnGuardar">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Actualizar Perfil Público
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- VALIDACIONES JAVASCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('proSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    const form = document.getElementById('perfilForm');
    const inputCelular = document.getElementById('celular');
    const inputZona = document.getElementById('zona_especifica');
    const inputTarifa = document.getElementById('tarifa_base');
    const inputDesc = document.getElementById('descripcion_servicio');
    const btnGuardar = document.getElementById('btnGuardar');

    form.addEventListener('submit', function(e) {
        let esValido = true;

        // 1. Validar Celular (Mínimo 8 números)
        const regexCelular = /^[0-9]{8,15}$/;
        if(!regexCelular.test(inputCelular.value.trim())) {
            inputCelular.classList.add('input-error'); esValido = false;
        } else { inputCelular.classList.remove('input-error'); }

        // 2. Validar Zona (No vacía)
        if(inputZona.value.trim().length < 3) {
            inputZona.classList.add('input-error'); esValido = false;
        } else { inputZona.classList.remove('input-error'); }

        // 3. Validar Tarifa (Positiva)
        if(parseFloat(inputTarifa.value) < 0 || isNaN(parseFloat(inputTarifa.value))) {
            inputTarifa.classList.add('input-error'); esValido = false;
        } else { inputTarifa.classList.remove('input-error'); }

        // 4. Validar Descripción (Mínimo 20 caracteres)
        if(inputDesc.value.trim().length < 20) {
            inputDesc.classList.add('input-error'); esValido = false;
        } else { inputDesc.classList.remove('input-error'); }

        if(!esValido) {
            e.preventDefault();
            btnGuardar.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i> Revisa los errores en rojo';
            btnGuardar.style.backgroundColor = '#ef4444';
            setTimeout(() => {
                btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Actualizar Perfil Público';
                btnGuardar.style.backgroundColor = '';
            }, 3000);
        } else {
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';
        }
    });

    // Quitar alertas al escribir
    [inputCelular, inputZona, inputTarifa, inputDesc].forEach(input => {
        input.addEventListener('input', function() { this.classList.remove('input-error'); });
    });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>