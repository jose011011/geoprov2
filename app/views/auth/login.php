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
    <title>GEO-PRO | Acceso</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
    <style>
        /* Estilos inmersivos exclusivos para Login */
        body {
            background: linear-gradient(135deg, var(--geo-primary) 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background-color: var(--geo-card);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: var(--geo-accent);
        }
        .login-brand {
            font-size: 2rem;
            font-weight: 800;
            color: var(--geo-primary);
            text-align: center;
            margin-bottom: 5px;
        }
        .input-group-text { background-color: transparent; border-right: none; color: #6b7280; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #dee2e6; }
        .input-group:focus-within { box-shadow: 0 0 0 0.25rem rgba(0, 191, 166, 0.25); border-radius: 0.375rem; }
        .input-group:focus-within .input-group-text, .input-group:focus-within .form-control { border-color: var(--geo-accent); }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-brand">
                <i class="fa-solid fa-location-crosshairs text-success mb-2"></i><br>
                GEO<span style="color: var(--geo-accent);">PRO</span>
            </div>
            <p class="text-muted small">Ingresa con tus credenciales seguras</p>
        </div>

        <!-- TUS ALERTAS LÓGICAS PHP ORIGINALES -->
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success small py-2" style="border-radius: 10px;">
                <i class="fa-solid fa-circle-check me-1"></i> Registro completado con éxito. Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['pending_validation'])): ?>
            <div class="alert alert-warning small py-2" style="border-radius: 10px;">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Postulación recibida. La administración revisará tus documentos antes de habilitar tu perfil en el mapa.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small py-2" style="border-radius: 10px;">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- TU FORMULARIO ORIGINAL -->
        <form action="<?= BASE_URL ?>/auth/login" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted mb-0">Contraseña</label>
                <div class="input-group mt-2">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="login_pass" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-secondary border border-start-0" id="togglePassword">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 mt-2 fw-bold shadow-sm" style="border-radius: 12px;">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
            </button>
        </form>

        <hr class="my-4">
        
        <!-- TUS 3 BOTONES DE REGISTRO ORIGINALES (Estilizados) -->
        <div class="text-center small">
            <p class="fw-bold text-muted mb-3">¿Deseas registrarte en la plataforma?</p>
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/auth/registroCliente" class="btn btn-outline-success btn-sm rounded-pill fw-bold py-2">
                    <i class="fa-solid fa-user me-1"></i> Soy Cliente (Solicitar Servicios)
                </a>
                <a href="<?= BASE_URL ?>/auth/registroEmpirico" class="btn btn-outline-warning text-dark btn-sm rounded-pill fw-bold py-2">
                    <i class="fa-solid fa-person-digging me-1"></i> Soy Trabajador de Oficio / Empírico
                </a>
                <a href="<?= BASE_URL ?>/auth/registroProfesional" class="btn btn-outline-primary btn-sm rounded-pill fw-bold py-2">
                    <i class="fa-solid fa-user-graduate me-1"></i> Soy Técnico Profesional Calificado
                </a>
            </div>
        </div>
    </div>

    <!-- Script del ojito de contraseña -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('login_pass');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>