<?php
$lista      = $pedidos ?? [];
$tabInicial = 'todas';
require __DIR__ . '/../layouts/header.php';
?>
<div class="container py-4" style="max-width: 920px;">
    <h1 class="fx-page-title h3 mb-3">Historial de solicitudes</h1>
    <?php require __DIR__ . '/_lista_solicitudes.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>