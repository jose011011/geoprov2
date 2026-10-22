<?php
$mensajes = Flash::pull();
$envolver = $alertasContenedor ?? true;
if ($mensajes):
    if ($envolver): ?><div class="container pt-3"><?php endif;
    foreach ($mensajes as $m):
        $tipo = in_array($m['t'], ['success', 'danger', 'warning', 'info'], true) ? $m['t'] : 'info'; ?>
        <div class="alert alert-<?= $tipo ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Security::e($m['m']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach;
    if ($envolver): ?></div><?php endif;
endif;