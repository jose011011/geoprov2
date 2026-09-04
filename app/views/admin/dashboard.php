<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* =========================================
       ESTILOS PREMIUM - INSPIRADO EN ADMIN TEMPLATES
       ========================================= */
    body {
        background-color: #f4f6f9; /* Fondo gris/azulado muy suave */
    }
    
    /* Títulos y textos */
    .page-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2b3445;
        letter-spacing: -0.3px;
    }
    .text-subtitle {
        color: #7a8b9a;
        font-size: 0.85rem;
    }

    /* Tarjetas KPI (Estilo imagen de referencia) */
    .dashboard-card {
        background: #ffffff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    /* Bloques de íconos de colores sólidos */
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #ffffff;
        margin-right: 18px;
        flex-shrink: 0;
    }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #84d9d2 0%, #07cdae 100%); }
    .bg-gradient-info    { background: linear-gradient(135deg, #90CAF9 0%, #007bff 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }

    .kpi-text {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #7a8b9a;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }
    .kpi-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2b3445;
        line-height: 1;
    }

    /* Botones de Acción Superiores */
    .action-btn {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .action-btn:hover {
        background: #f8fafc;
        color: #1a202c;
        border-color: #cbd5e0;
    }
    .action-btn-primary {
        background: #2b3445;
        color: #ffffff;
        border: none;
    }
    .action-btn-primary:hover {
        background: #1a202c;
        color: #ffffff;
    }

    /* Contenedor de la Tabla */
    .table-card {
        background: #ffffff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Pestañas de Filtro Modernas (Tabs) */
    .nav-filters {
        display: flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
    }
    .nav-filters a {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .nav-filters a:hover { color: #1e293b; }
    .nav-filters a.active {
        background: #ffffff;
        color: #0ea5e9;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Estilos de la Tabla Corporativa */
    .custom-table { margin-bottom: 0; }
    .custom-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 24px;
        border-bottom: 1px solid #edf2f7;
    }
    .custom-table td {
        padding: 16px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .custom-table tbody tr:hover { background-color: #f8fafc; }
    .custom-table tbody tr:last-child td { border-bottom: none; }

    /* Avatares e Insignias */
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: #ffffff;
        margin-right: 12px;
    }
    .badge-soft {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .badge-soft-warning { background: #fef3c7; color: #b45309; }
    .badge-soft-success { background: #d1fae5; color: #047857; }
    .badge-soft-danger  { background: #fee2e2; color: #b91c1c; }
</style>

<div class="container-fluid py-4 px-lg-5">
    
    <!-- Encabezado y Acciones (Top Bar) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="page-title mb-0">Panel de Control</h2>
            <p class="text-subtitle mb-0">Resumen y validación de profesionales - GEO-PRO</p>
        </div>
        
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= BASE_URL ?>/admin/pagos" class="btn action-btn d-flex align-items-center">
                <i class="fa-solid fa-qrcode text-success me-2"></i> Pagos QR
            </a>
            <a href="<?= BASE_URL ?>/admin/categorias" class="btn action-btn d-flex align-items-center">
                <i class="fa-solid fa-tags text-info me-2"></i> Categorías
            </a>
            <a href="<?= BASE_URL ?>/admin/auditoria" class="btn action-btn action-btn-primary d-flex align-items-center">
                <i class="fa-solid fa-shield-halved me-2"></i> Logs Auditoría
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI (Estilo Bloque de Color y Texto) -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card">
                <div class="icon-box bg-gradient-warning">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <div class="kpi-text">Por Validar</div>
                    <div class="kpi-number"><?= (int) $stats['pendientes'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card">
                <div class="icon-box bg-gradient-success">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="kpi-text">Aprobados</div>
                    <div class="kpi-number"><?= (int) $stats['aprobados'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card">
                <div class="icon-box bg-gradient-info">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="kpi-text">Clientes Activos</div>
                    <div class="kpi-number"><?= (int) $stats['total_clientes'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="dashboard-card">
                <div class="icon-box bg-gradient-primary">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div>
                    <div class="kpi-text">Servicios Globales</div>
                    <div class="kpi-number"><?= (int) $stats['total_solicitudes'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Principal de la Tabla -->
    <div class="table-card">
        
        <!-- Cabecera de la tabla con Filtros Tabs -->
        <div class="table-header">
            <div>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Gestión de Profesionales</h5>
                <span class="text-subtitle">Directorio de postulantes y activos</span>
            </div>
            
            <div class="nav-filters">
                <a href="?estado=PENDIENTE" class="<?= $filtroActual === 'PENDIENTE' ? 'active' : '' ?>">
                    Pendientes
                </a>
                <a href="?estado=APROBADO" class="<?= $filtroActual === 'APROBADO' ? 'active' : '' ?>">
                    Aprobados
                </a>
                <a href="?estado=RECHAZADO" class="<?= $filtroActual === 'RECHAZADO' ? 'active' : '' ?>">
                    Rechazados
                </a>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th>Profesional</th>
                        <th>Especialidad</th>
                        <th>Clasificación</th>
                        <th>Zona Base</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profesionales as $p): ?>
                        <?php 
                            // Generar colores aleatorios para los avatares según la inicial
                            $colores = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];
                            $inicial = strtoupper(substr($p['nombre'], 0, 1));
                            $colorIndex = ord($inicial) % count($colores);
                            $bgColor = $colores[$colorIndex];
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle" style="background-color: <?= $bgColor ?>;">
                                        <?= strtoupper(substr($p['nombre'], 0, 1) . substr($p['apellido'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-dark fw-bold"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Doc: <?= htmlspecialchars($p['numero_documento'] ?? 'S/N') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary"><?= htmlspecialchars($p['nombre_categoria']) ?></span>
                            </td>
                            <td>
                                <?php if ($p['tipo_prestador'] === 'TECNICO_PROFESIONAL'): ?>
                                    <span class="text-primary" style="font-size: 0.85rem;"><i class="fa-solid fa-circle-check me-1"></i> Profesional</span>
                                <?php else: ?>
                                    <span class="text-warning text-dark" style="font-size: 0.85rem;"><i class="fa-solid fa-hammer me-1"></i> Empírico</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-muted"><i class="fa-solid fa-map-pin me-1 opacity-50"></i> <?= htmlspecialchars($p['macrodistrito_base']) ?></span>
                            </td>
                            <td>
                                <?php
                                    $badgeClass = 'badge-soft-warning';
                                    if ($p['estado_validacion'] === 'APROBADO') $badgeClass = 'badge-soft-success';
                                    if ($p['estado_validacion'] === 'RECHAZADO') $badgeClass = 'badge-soft-danger';
                                ?>
                                <span class="badge-soft <?= $badgeClass ?>">
                                    <?= htmlspecialchars($p['estado_validacion']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/verProfesional/<?= $p['id_profesional'] ?>" class="btn action-btn px-3">
                                    <?php if ($p['estado_validacion'] === 'PENDIENTE'): ?>
                                        <i class="fa-solid fa-file-signature text-primary"></i> Revisar
                                    <?php else: ?>
                                        <i class="fa-solid fa-arrow-right"></i> Ver Perfil
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($profesionales)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-50" alt="No data">
                                <h6 class="text-muted fw-bold">No hay registros</h6>
                                <p class="text-muted small mb-0">No se encontraron profesionales en la pestaña seleccionada.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "../app/views/layouts/footer.php"; ?>