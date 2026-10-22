<?php
$titulo    = 'FixGeo | Ingresar';
$authAncho = 'sm';
require __DIR__ . '/../layouts/auth_header.php';
?>

<div class="text-center mb-4">
    <h1 class="h3 fw-bold mb-1">Bienvenido de nuevo</h1>
    <p class="text-muted mb-0">Ingresa para pedir o gestionar servicios.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= Security::e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/auth/login" novalidate>
    <?= Security::campo() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold" for="login_correo">Correo electrónico</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
            <input type="email" class="form-control" id="login_correo" name="correo"
                   value="<?= Security::e($_POST['correo'] ?? '') ?>" autocomplete="username" required autofocus>
        </div>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold" for="login_password">Contraseña</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="login_password" name="password"
                   autocomplete="current-password" required>
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="login_password"
                    aria-label="Mostrar u ocultar contraseña"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="invalid-feedback"></div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
</form>

<hr class="my-4">
<p class="text-center mb-2">¿Necesitas un servicio? <a href="<?= BASE_URL ?>/auth/registroCliente" class="fw-semibold">Crea tu cuenta</a></p>
<p class="text-center text-muted small mb-0">
    ¿Eres técnico?
    <a href="<?= BASE_URL ?>/auth/registroProfesional">Registro profesional</a> ·
    <a href="<?= BASE_URL ?>/auth/registroEmpirico">Trabajador de oficio</a>
</p>

<?php require __DIR__ . '/../layouts/auth_footer.php'; ?>