<?php
require __DIR__ . '/../layouts/header.php';
$tabs = ['PENDIENTE' => 'Pendientes', 'APROBADO' => 'Aprobados', 'RECHAZADO' => 'Rechazados', 'TODOS' => 'Todos'];
$claseEstado = ['PENDIENTE' => 'warning', 'APROBADO' => 'success', 'RECHAZADO' => 'danger'];
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="fx-page-title h3 mb-0">Validar técnicos</h1>
        <span class="badge text-bg-warning fs-6"><?= (int) $resumen['tecnicos'] ?> pendientes</span>
    </div>

    <form method="get" action="<?= BASE_URL ?>/admin/revisionTecnicos" class="row g-2 mb-3">
        <div class="col-md-5"><input type="search" name="q" class="form-control" placeholder="Buscar por nombre, correo o documento" value="<?= Security::e($buscar) ?>"></div>
        <div class="col-md-3">
            <select name="estado" class="form-select" onchange="this.form.submit()">
                <?php foreach ($tabs as $k => $t): ?><option value="<?= $k ?>" <?= $filtro === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</button></div>
    </form>

    <?php if (!$tecnicos): ?>
        <div class="text-center text-muted py-5"><i class="fa-regular fa-circle-check fs-1 d-block mb-2"></i>No hay técnicos en esta lista.</div>
    <?php else: ?>
    <div class="card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Técnico</th><th>Especialidad</th><th>Zona</th><th>Documentos</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($tecnicos as $t): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= Security::e($t['nombre'] . ' ' . $t['apellido']) ?></div>
                        <div class="small text-muted"><?= Security::e($t['correo']) ?> · <?= Security::e($t['celular']) ?></div>
                    </td>
                    <td>
                        <?= Security::e($t['nombre_categoria']) ?>
                        <div class="small text-muted"><?= $t['tipo_prestador'] === 'TECNICO_PROFESIONAL' ? 'Técnico profesional' : 'Trabajador de oficio' ?></div>
                    </td>
                    <td class="small"><?= Security::e(Catalogo::macroEtiqueta($t['macrodistrito_base'])) ?></td>
                    <td><?= (int) $t['docs_pendientes'] > 0 ? '<span class="badge text-bg-warning">' . (int) $t['docs_pendientes'] . ' por revisar</span>' : '<span class="text-muted small">Al día</span>' ?></td>
                    <td><span class="badge text-bg-<?= $claseEstado[$t['estado_validacion']] ?? 'secondary' ?>"><?= Security::e(ucfirst(strtolower($t['estado_validacion']))) ?></span></td>
                    <td class="text-end"><a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/admin/revisionTecnico/<?= (int) $t['id_profesional'] ?>">Revisar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>