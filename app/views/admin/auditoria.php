<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-lg-4 py-4">
    <h1 class="fx-page-title h3 mb-3">Auditoría</h1>

    <form method="get" action="<?= BASE_URL ?>/admin/auditoria" class="row g-2 mb-3" style="max-width: 720px;">
        <div class="col-md-8"><input type="search" name="q" class="form-control" placeholder="Buscar por acción, tabla, correo o IP" value="<?= Security::e($buscar) ?>"></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</button></div>
    </form>

    <div class="card"><div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Reg.</th><th>IP</th><th>Detalle</th></tr></thead>
            <tbody>
            <?php foreach ($eventos as $e): ?>
                <tr>
                    <td class="text-nowrap small"><?= date('d/m/Y H:i', strtotime($e['fecha_evento'])) ?></td>
                    <td class="small"><?= $e['correo'] ? Security::e($e['nombre'] . ' ' . $e['apellido']) . '<div class="text-muted">' . Security::e($e['correo']) . '</div>' : '<span class="text-muted">Sistema / visitante</span>' ?></td>
                    <td><span class="badge text-bg-light border"><?= Security::e($e['accion']) ?></span></td>
                    <td class="small"><?= Security::e($e['tabla_afectada']) ?></td>
                    <td class="small"><?= $e['registro_id'] !== null ? (int) $e['registro_id'] : '—' ?></td>
                    <td class="small text-muted"><?= Security::e($e['ip_origen']) ?></td>
                    <td class="small text-muted" style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= Security::e((string) $e['valores_nuevos']) ?>"><?= Security::e((string) $e['valores_nuevos']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$eventos): ?><tr><td colspan="7" class="text-center text-muted py-4">No hay eventos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div></div>
    <p class="small text-muted mt-2">Se muestran los últimos 200 eventos.</p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>