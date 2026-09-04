<?php function old($old, $campo, $default = '') { return htmlspecialchars($old[$campo] ?? $default); } ?>
<?php
if (!defined('BASE_URL')) {
    die('BASE_URL no está definida. Verifique app/config/config.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEO-PRO | Registro de Trabajador Empírico</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--geo-primary) 0%, #16213e 100%);
            min-height: 100vh;
            padding: 40px 15px;
        }
        .register-card {
            background-color: var(--geo-card);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border-top: 6px solid #ffc107; /* Amarillo Warning para Empíricos */
        }
        .form-section-title {
            color: var(--geo-primary);
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 2rem;
            margin-bottom: 1.2rem;
            border-bottom: 2px solid #eef0f2;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-control, .form-select {
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
            border-color: #ffc107;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #6b7280;
        }
        .input-group .form-control { border-left: none; }
        
        /* Ajustes visuales para validación nativa */
        .form-control.is-valid, .form-select.is-valid {
            border-color: #198754;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        }
        .input-group.has-validation > .form-control.is-invalid,
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }
        .input-group.has-validation > .input-group-text.is-invalid-addon {
            border-color: #dc3545;
            color: #dc3545;
        }
        .invalid-feedback {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Estilo elegante para los inputs de archivos (Documentos) */
        input[type="file"] { padding: 7px 15px; font-size: 0.85rem; }
        input[type="file"]::file-selector-button {
            background-color: #ffc107; color: #000; font-weight: bold; border: none;
            border-radius: 6px; padding: 5px 15px; margin-right: 10px; cursor: pointer;
            transition: background 0.2s;
        }
        input[type="file"]::file-selector-button:hover { background-color: #e0a800; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/auth/login" class="text-white text-decoration-none fw-bold">
                    <i class="fa-solid fa-arrow-left me-2"></i>Volver al Inicio de Sesión
                </a>
            </div>

            <div class="card register-card p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-person-digging fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Registro: Trabajador de Oficio</h3>
                    <p class="text-muted small px-md-4">Para albañiles, plomeros prácticos, jardineros, niñeras y trabajadores del hogar. Requiere verificación de identidad.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small py-2 fw-bold" style="border-radius: 10px;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/auth/registroEmpirico" method="POST" enctype="multipart/form-data" id="form-empirico" novalidate>
                    
                    <!-- 1. Datos Personales -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-address-card text-warning"></i> 1. Datos Personales
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Nombres</label>
                            <input type="text" name="nombre" id="nombre" class="form-control valida-nombre" value="<?= old($old, 'nombre') ?>" required>
                            <div class="invalid-feedback">Debe tener al menos 2 letras.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control valida-apellido" value="<?= old($old, 'apellido_paterno') ?>" required>
                            <div class="invalid-feedback">Debe tener al menos 2 letras.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control valida-apellido" value="<?= old($old, 'apellido_materno') ?>">
                            <div class="invalid-feedback">Debe tener al menos 2 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="correo" id="correo" class="form-control val-correo" placeholder="ejemplo@correo.com" value="<?= old($old, 'correo') ?>" required>
                                <div class="invalid-feedback w-100">Ingrese un correo válido.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Celular Bolivia (6 o 7)</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="fa-solid fa-mobile-screen"></i></span>
                                <input type="tel" name="celular" id="celular" class="form-control val-celular" placeholder="70000000" maxlength="8" value="<?= old($old, 'celular') ?>" required>
                                <div class="invalid-feedback w-100">Debe tener 8 dígitos y empezar con 6 o 7.</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Especialidad y Ubicación -->
                    <div class="form-section-title mt-4">
                        <i class="fa-solid fa-toolbox text-warning"></i> 2. Especialidad y Ubicación
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 position-relative">
                            <label class="form-label small fw-bold text-muted">Oficio Principal</label>
                            <select name="id_categoria" id="id_categoria" class="form-select val-select" required>
                                <option value="">-- Seleccione su oficio --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= (($old['id_categoria'] ?? '') == $cat['id_categoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="OTRO" <?= (($old['id_categoria'] ?? '') === 'OTRO') ? 'selected' : '' ?>>-- Otro Oficio / Especificar --</option>
                            </select>
                            <div class="invalid-feedback">Seleccione una opción.</div>
                        </div>
                        <div class="col-md-6 position-relative <?= (($old['id_categoria'] ?? '') === 'OTRO') ? '' : 'd-none' ?>" id="wrapper-otra-especialidad">
                            <label class="form-label small fw-bold text-primary">Especifique su Oficio</label>
                            <input type="text" name="otra_especialidad" id="otra_especialidad" class="form-control" placeholder="Ej. Cerrajero" value="<?= old($old, 'otra_especialidad') ?>">
                            <div class="invalid-feedback">Especifique su oficio.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Tipo Doc.</label>
                            <select name="tipo_doc" id="tipo_doc" class="form-select val-select" required>
                                <option value="CI" <?= (($old['tipo_doc'] ?? 'CI') === 'CI') ? 'selected' : '' ?>>C.I. Bolivia</option>
                                <option value="EXTRANJERO" <?= (($old['tipo_doc'] ?? '') === 'EXTRANJERO') ? 'selected' : '' ?>>Doc. Extranjero</option>
                            </select>
                            <div class="invalid-feedback">Seleccione.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">N° de Documento</label>
                            <input type="text" name="numero_documento" id="numero_documento" class="form-control" value="<?= old($old, 'numero_documento') ?>" required>
                            <div class="invalid-feedback">Documento inválido.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Años de Oficio</label>
                            <input type="number" name="experiencia" id="experiencia_anios" class="form-control" placeholder="Ej: 5" min="0" max="50" value="<?= old($old, 'experiencia') ?>" required>
                            <div class="invalid-feedback">Ingrese un valor (0-50).</div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label class="form-label small fw-bold text-muted">Macrodistrito Base</label>
                            <select name="macrodistrito" id="macrodistrito" class="form-select val-select" required>
                                <option value="">-- Seleccione --</option>
                                <option value="SOPOCACHI" <?= (($old['macrodistrito'] ?? '') === 'SOPOCACHI') ? 'selected' : '' ?>>Sopocachi</option>
                                <option value="MIRAFLORES" <?= (($old['macrodistrito'] ?? '') === 'MIRAFLORES') ? 'selected' : '' ?>>Miraflores</option>
                                <option value="ZONA_SUR" <?= (($old['macrodistrito'] ?? '') === 'ZONA_SUR') ? 'selected' : '' ?>>Zona Sur</option>
                                <option value="CENTRO" <?= (($old['macrodistrito'] ?? '') === 'CENTRO') ? 'selected' : '' ?>>Centro</option>
                                <option value="SAN_PEDRO" <?= (($old['macrodistrito'] ?? '') === 'SAN_PEDRO') ? 'selected' : '' ?>>San Pedro</option>
                                <option value="COTAHUMA" <?= (($old['macrodistrito'] ?? '') === 'COTAHUMA') ? 'selected' : '' ?>>Cotahuma</option>
                                <option value="PERIFERICA" <?= (($old['macrodistrito'] ?? '') === 'PERIFERICA') ? 'selected' : '' ?>>Periférica</option>
                                <option value="EL_ALTO" <?= (($old['macrodistrito'] ?? '') === 'EL_ALTO') ? 'selected' : '' ?>>El Alto</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un macrodistrito.</div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label class="form-label small fw-bold text-muted">Zona Específica</label>
                            <input type="text" name="zona_especifica" id="zona_especifica" class="form-control val-direccion" placeholder="Ej. Villa Fátima" value="<?= old($old, 'zona_especifica') ?>" required>
                            <div class="invalid-feedback">Este campo es obligatorio.</div>
                        </div>
                        <div class="col-12 position-relative">
                            <label class="form-label small fw-bold text-muted">Descripción del Trabajo que Realiza</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Detalle qué reparaciones hace, si tiene herramientas propias, etc." required minlength="20"><?= old($old, 'descripcion') ?></textarea>
                            <div class="invalid-feedback">Describa su trabajo (mínimo 20 caracteres).</div>
                        </div>
                    </div>

                    <!-- 3. Documentos de Seguridad -->
                    <div class="form-section-title text-success mt-5">
                        <i class="fa-solid fa-shield-halved"></i> 3. Documentos de Seguridad
                    </div>
                    <p class="small text-muted mb-3">Sube tus documentos en formato JPG, PNG o PDF (Máx 5MB). Esto es fundamental para generar confianza en los clientes.</p>
                    <div class="row g-3 bg-light p-3 rounded border">
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">C.I. Anverso (*)</label>
                            <input type="file" name="doc_ci_anverso" id="doc_ci_anverso" class="form-control file-doc" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="invalid-feedback">Obligatorio.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">C.I. Reverso (*)</label>
                            <input type="file" name="doc_ci_reverso" id="doc_ci_reverso" class="form-control file-doc" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="invalid-feedback">Obligatorio.</div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Cert. FELCC / Referencias</label>
                            <input type="file" name="doc_antecedentes" id="doc_antecedentes" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="invalid-feedback">Opcional.</div>
                        </div>
                    </div>

                    <!-- 4. Seguridad -->
                    <div class="form-section-title mt-5">
                        <i class="fa-solid fa-lock text-warning"></i> 4. Contraseña de Acceso
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Contraseña</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <div class="invalid-feedback w-100" id="feed-pass">La contraseña no cumple los requisitos.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Confirmar Contraseña</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="fa-solid fa-check-double"></i></span>
                                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirm">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <div class="invalid-feedback w-100" id="feed-confirm">Las contraseñas no coinciden.</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100 py-3 mt-3 fw-bold fs-5 shadow-sm" style="border-radius: 12px;">
                        <i class="fa-solid fa-paper-plane me-2"></i> Enviar Postulación de Oficio
                    </button>
                </form>
            </div>
            
            <div class="text-center mt-4 mb-5 text-white-50 small">
                GEO-PRO © 2026 - Plataforma con verificación documental y geolocalización.
            </div>
            
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/auth-validation.js"></script>

</body>
</html>