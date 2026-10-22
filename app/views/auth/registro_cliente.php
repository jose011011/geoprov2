<?php
$titulo    = 'FixGeo | Crear cuenta';
$authAncho = 'lg';
$old       = $old ?? [];
require __DIR__ . '/../layouts/auth_header.php';
?>

<div class="text-center mb-3">
    <h1 class="h3 fw-bold mb-1">Crea tu cuenta</h1>
    <p class="text-muted mb-0">Pide técnicos verificados y síguelos en el mapa.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= nl2br(Security::e($error)) ?></div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/auth/registroCliente" novalidate>
    <?= Security::campo() ?>

    <?php require __DIR__ . '/_campos_persona.php'; ?>

    <div class="fx-section-title">¿Dónde necesitas el servicio?</div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold" for="direccion">Dirección de referencia</label>
            <input type="text" class="form-control val-direccion" id="direccion" name="direccion"
                   value="<?= Security::e($old['direccion'] ?? '') ?>" minlength="5" maxlength="255"
                   placeholder="Calle, número, referencia" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="zona">Zona o barrio</label>
            <input type="text" class="form-control val-direccion" id="zona" name="zona"
                   value="<?= Security::e($old['zona'] ?? '') ?>" minlength="2" maxlength="100" required>
            <div class="invalid-feedback"></div>
        </div>
        <?php require __DIR__ . '/_ubicacion.php'; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Crear cuenta</button>
    <p class="text-center text-muted small mt-3 mb-0">
        ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/auth/login" class="fw-semibold">Ingresa</a>
    </p>
</form>

<?php require __DIR__ . '/../layouts/auth_footer.php'; ?>