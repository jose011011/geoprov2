<?php
require __DIR__ . '/../layouts/header.php';
$old       = $old ?? [];
$esTecnico = ($tipoPrestador === 'TECNICO_PROFESIONAL');
?>

<div class="container py-4" style="max-width: 820px;">
    <a href="<?= BASE_URL ?>/cliente/dashboard" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
    <h1 class="fx-page-title h3 mt-2 mb-1">Ofrece tus servicios en FixGeo</h1>
    <p class="text-muted">Usarás <strong>tu misma cuenta</strong>: no tienes que registrarte de nuevo. Seguirás pudiendo pedir servicios como cliente.</p>

<?php if (!$tipoPrestador): ?>

    <div class="row g-3">
        <div class="col-md-6">
            <a class="fx-quick" href="<?= BASE_URL ?>/cliente/ofrecerServicios?tipo=profesional">
                <span class="ico"><i class="fa-solid fa-graduation-cap"></i></span>
                <span><span class="t d-block">Soy técnico profesional</span><small>Tengo título institucional y lo puedo verificar</small></span>
            </a>
        </div>
        <div class="col-md-6">
            <a class="fx-quick" href="<?= BASE_URL ?>/cliente/ofrecerServicios?tipo=oficio">
                <span class="ico"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                <span><span class="t d-block">Soy trabajador de oficio</span><small>Tengo experiencia práctica, sin título</small></span>
            </a>
        </div>
    </div>

<?php else: ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= nl2br(Security::e($error)) ?></div>
    <?php endif; ?>

    <div class="card"><div class="card-body">
        <div class="mb-2">
            <span class="badge text-bg-primary"><?= $esTecnico ? 'Técnico profesional' : 'Trabajador de oficio' ?></span>
            <a href="<?= BASE_URL ?>/cliente/ofrecerServicios" class="small ms-2">Cambiar</a>
        </div>

        <form method="post" action="<?= BASE_URL ?>/cliente/ofrecerServicios?tipo=<?= Security::e($tipoUrl) ?>" enctype="multipart/form-data" novalidate>
            <?= Security::campo() ?>
            <?php require __DIR__ . '/../auth/_campos_trabajo.php'; ?>
            <button type="submit" class="btn btn-primary btn-lg w-100 mt-4"><i class="fa-solid fa-paper-plane me-2"></i>Enviar mi perfil a revisión</button>
            <p class="text-center text-muted small mt-3 mb-0">Un administrador revisará tus documentos antes de mostrarte en el catálogo.</p>
        </form>
    </div></div>

    <script src="<?= BASE_URL ?>/js/auth-validation.js"></script>

<?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>