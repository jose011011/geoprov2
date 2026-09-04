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
    <title>GEO-PRO | Registro de Cliente</title>
    
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
            border-top: 6px solid #198754;
        }
        .form-section-title {
            color: var(--geo-primary);
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 2rem;
            margin-bottom: 1.2rem;
            border-bottom: 2px solid #eef0f2;
            padding-bottom: 8px;
        }
        .form-control, .form-select {
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
            border-color: #198754;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #6b7280;
        }
        .input-group .form-control { border-left: none; }
        
        /* Ajustes visuales para la validación nativa de Bootstrap */
        .form-control.is-valid, .form-select.is-valid {
            border-color: #198754;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        }
        .invalid-feedback {
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/auth/login" class="text-white text-decoration-none fw-bold">
                    <i class="fa-solid fa-arrow-left me-2"></i>Volver al Inicio de Sesión
                </a>
            </div>

            <div class="card register-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-user-plus fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Registro de Cliente</h3>
                    <p class="text-muted small px-md-4">Crea tu cuenta gratuita para solicitar servicios técnicos, mantenimientos y oficios a domicilio en toda la ciudad de La Paz.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small py-2 fw-bold" style="border-radius: 10px;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/auth/registroCliente" method="POST" id="form-cliente" novalidate>
                    
                    <!-- 1. Datos Personales -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-id-badge text-success me-2"></i> 1. Datos Personales
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Nombres</label>
                            <input type="text" name="nombre" id="nombre" class="form-control valida-nombre" placeholder="Ej. Juan Carlos" value="<?= old($old, 'nombre') ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control valida-apellido" placeholder="Ej. Pérez" value="<?= old($old, 'apellido_paterno') ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 position-relative">
                            <label class="form-label small fw-bold text-muted">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control valida-apellido" placeholder="Ej. Mamani" value="<?= old($old, 'apellido_materno') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="correo" id="correo" class="form-control" placeholder="cliente@correo.com" value="<?= old($old, 'correo') ?>" required>
                            </div>
                            <div class="invalid-feedback mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Celular Bolivia (6 o 7)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-mobile-screen"></i></span>
                                <input type="tel" name="celular" id="celular" class="form-control" placeholder="70123456" maxlength="8" value="<?= old($old, 'celular') ?>" required>
                            </div>
                            <div class="invalid-feedback mt-1"></div>
                        </div>
                    </div>

                    <!-- 2. Ubicación -->
                    <div class="form-section-title mt-4">
                        <i class="fa-solid fa-location-dot text-success me-2"></i> 2. Ubicación de Residencia
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 position-relative">
                            <label class="form-label small fw-bold text-muted">Macrodistrito / Zona Principal</label>
                            <select name="zona" id="zona" class="form-select" required>
                                <option value="">-- Seleccione su zona --</option>
                                <option value="Sopocachi" <?= (($old['zona'] ?? '') === 'Sopocachi') ? 'selected' : '' ?>>Sopocachi</option>
                                <option value="Miraflores" <?= (($old['zona'] ?? '') === 'Miraflores') ? 'selected' : '' ?>>Miraflores</option>
                                <option value="Zona Sur" <?= (($old['zona'] ?? '') === 'Zona Sur') ? 'selected' : '' ?>>Zona Sur (Calacoto, San Miguel, Achumani)</option>
                                <option value="Centro" <?= (($old['zona'] ?? '') === 'Centro') ? 'selected' : '' ?>>Centro</option>
                                <option value="San Pedro" <?= (($old['zona'] ?? '') === 'San Pedro') ? 'selected' : '' ?>>San Pedro</option>
                                <option value="Cotahuma" <?= (($old['zona'] ?? '') === 'Cotahuma') ? 'selected' : '' ?>>Cotahuma</option>
                                <option value="Periférica" <?= (($old['zona'] ?? '') === 'Periférica') ? 'selected' : '' ?>>Periférica</option>
                                <option value="El Alto" <?= (($old['zona'] ?? '') === 'El Alto') ? 'selected' : '' ?>>El Alto</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Dirección / Referencia</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-house-chimney"></i></span>
                                <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Av. 6 de Agosto Nro 123" value="<?= old($old, 'direccion') ?>" required minlength="10">
                            </div>
                            <div class="invalid-feedback mt-1"></div>
                        </div>
                    </div>

                    <!-- 3. Seguridad -->
                    <div class="form-section-title mt-4">
                        <i class="fa-solid fa-lock text-success me-2"></i> 3. Seguridad de la Cuenta
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-check-double"></i></span>
                                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirm">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback mt-1"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 mt-3 fw-bold fs-5 shadow-sm" style="border-radius: 12px;">
                        <i class="fa-solid fa-check-circle me-2"></i> Completar Registro
                    </button>
                </form>
            </div>
            
            <div class="text-center mt-4 mb-5 text-white-50 small">
                GEO-PRO © 2026 - Soluciones técnicas inmediatas para tu hogar.
            </div>
            
        </div>
    </div>
</div>

<!-- Lógica Externa Intacta -->
<script src="<?= BASE_URL ?>/js/auth-validation.js"></script>
</body>
</html>