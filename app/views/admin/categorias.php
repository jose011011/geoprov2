<?php require_once "../app/views/layouts/header.php"; ?>
<div class="container py-4">
    <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver</a>
    <h3 class="mb-4">Gestión de Categorías</h3>

    <div class="card p-3 mb-4">
        <h6>Nueva categoría</h6>
        <form method="POST" action="<?= BASE_URL ?>/admin/crearCategoria" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="nombre_categoria" class="form-control" placeholder="Nombre (ej. Jardinería)" required>
            </div>
            <div class="col-md-3">
                <select name="tipo_clasificacion" class="form-select">
                    <option value="TECNICO">Técnico</option>
                    <option value="EMPIRICO_OFICIO">Oficio Empírico</option>
                    <option value="AMBOS" selected>Ambos</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="icono_fa" class="form-control" placeholder="fa-solid fa-leaf">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Agregar</button>
            </div>
            <div class="col-12">
                <input type="text" name="descripcion" class="form-control" placeholder="Descripción breve (opcional)">
            </div>
        </form>
        <small class="text-muted mt-1">Los íconos usan clases de Font Awesome. <a href="https://fontawesome.com/search?o=r&m=free" target="_blank">Buscar íconos</a></small>
    </div>

    <table class="table bg-white">
        <thead><tr><th></th><th>Categoría</th><th>Tipo</th><th>Profesionales</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categorias as $c): ?>
            <tr>
                <td><i class="<?= htmlspecialchars($c['icono_fa']) ?> text-success"></i></td>
                <td><?= htmlspecialchars($c['nombre_categoria']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($c['tipo_clasificacion']) ?></span></td>
                <td><?= (int) $c['total_profesionales'] ?></td>
                <td>
                    <span class="badge-estado <?= $c['estado'] ? 'badge-aprobado' : 'badge-rechazado' ?>">
                        <?= $c['estado'] ? 'ACTIVA' : 'INACTIVA' ?>
                    </span>
                </td>
                <td>
                    <form method="POST" action="<?= BASE_URL ?>/admin/toggleCategoria" class="d-inline">
                        <input type="hidden" name="id_categoria" value="<?= $c['id_categoria'] ?>">
                        <button class="btn btn-sm btn-outline-dark"><?= $c['estado'] ? 'Desactivar' : 'Activar' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once "../app/views/layouts/footer.php"; ?>