<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="fx-page-title h3 mb-3">Clientes</h1>

    <form method="get" action="<?= BASE_URL ?>/admin/clientes" class="row g-2 mb-3">
        <div class="col-md-6"><input type="search" name="q" class="form-control" placeholder="Buscar por nombre, correo o celular" value="<?= Security::e($buscar) ?>"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</button></div>
    </form>

    <div class="card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Cliente</th><th>Zona</th><th class="text-center">Solicitudes</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($clientes as $c):
                $bloqueado = $c['estado'] === 'BLOQUEADO';
                $esAdmin   = in_array((int) $c['id_rol'], [1, 2], true); ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= Security::e($c['nombre'] . ' ' . $c['apellido']) ?>
                            <?php if ((int) $c['id_rol'] === 3): ?><span class="badge text-bg-primary ms-1">También técnico</span><?php endif; ?>
                        </div>
                        <div class="small text-muted"><?= Security::e($c['correo']) ?> · <?= Security::e($c['celular']) ?></div>
                    </td>
                    <td class="small"><?= Security::e($c['zona']) ?> · <?= Security::e(Catalogo::macroEtiqueta($c['macrodistrito'])) ?></td>
                    <td class="text-center"><?= (int) $c['solicitudes'] ?></td>
                    <td><span class="badge text-bg-<?= $bloqueado ? 'danger' : ($c['estado'] === 'ACTIVO' ? 'success' : 'secondary') ?>"><?= Security::e(ucfirst(strtolower($c['estado']))) ?></span></td>
                    <td class="text-end">
                        <?php if (!$esAdmin): ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/cambiarEstadoCliente" class="m-0"
                              onsubmit="return confirm('<?= $bloqueado ? '¿Activar esta cuenta?' : '¿Bloquear esta cuenta? No podrá ingresar.' ?>');">
                            <?= Security::campo() ?>
                            <input type="hidden" name="id_usuario" value="<?= (int) $c['id_usuario'] ?>">
                            <input type="hidden" name="estado" value="<?= $bloqueado ? 'ACTIVO' : 'BLOQUEADO' ?>">
                            <button class="btn btn-sm <?= $bloqueado ? 'btn-primary' : 'btn-outline-danger' ?>"><?= $bloqueado ? 'Activar' : 'Bloquear' ?></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$clientes): ?><tr><td colspan="5" class="text-center text-muted py-4">No hay clientes que coincidan.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>