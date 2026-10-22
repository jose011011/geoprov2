<?php
$lista      = $solicitudes ?? [];
$tabInicial = 'curso';
require __DIR__ . '/../layouts/header.php';
?>
<div class="container py-4" style="max-width: 920px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="fx-page-title h3 mb-0">Mis pedidos</h1>
        <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nuevo pedido</a>
    </div>
    <?php require __DIR__ . '/_lista_solicitudes.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>