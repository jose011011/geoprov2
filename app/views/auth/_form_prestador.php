<?php
$old       = $old ?? [];
$esTecnico = ($tipoPrestador === 'TECNICO_PROFESIONAL');
$authAncho = 'lg';
$accion    = $esTecnico ? 'registroProfesional' : 'registroEmpirico';
require __DIR__ . '/../layouts/auth_header.php';
?>

<div class="text-center mb-3">
    <h1 class="h3 fw-bold mb-1"><?= $esTecnico ? 'Registro de técnico profesional' : 'Registro de trabajador de oficio' ?></h1>
    <p class="text-muted mb-0">
        <?= $esTecnico
            ? 'Verifica tu título y consigue la insignia de profesional verificado.'
            : 'Tu experiencia vale. Regístrate gratis y recibe trabajos cerca de ti.' ?>
    </p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= nl2br(Security::e($error)) ?></div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/auth/<?= $accion ?>" enctype="multipart/form-data" novalidate>
    <?= Security::campo() ?>

    <?php require __DIR__ . '/_campos_persona.php'; ?>
    <?php require __DIR__ . '/_campos_trabajo.php'; ?>

    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Enviar mi registro</button>
    <p class="text-center text-muted small mt-3 mb-0">
        Tu perfil aparecerá en el catálogo cuando un administrador valide tus documentos.<br>
        ¿Ya tienes cuenta de cliente? <a href="<?= BASE_URL ?>/auth/login" class="fw-semibold">Ingresa y usa "Ofrecer mis servicios"</a>
    </p>
</form>

<?php require __DIR__ . '/../layouts/auth_footer.php'; ?>