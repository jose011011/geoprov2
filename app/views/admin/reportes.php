<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    /* Estructura SaaS del Sidebar */
    .geo-navbar { display: none !important; }
    body { background-color: #f3f4f7; margin: 0; font-family: 'Inter', sans-serif; overflow-x: hidden; }
    .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
    .admin-sidebar { width: 260px; background-color: #1e1e2d; color: #a2a3b7; position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-y: auto; padding-bottom: 50px; }
    .admin-sidebar::-webkit-scrollbar { width: 6px; }
    .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
    .admin-sidebar::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); border-radius: 10px; }
    .sidebar-profile { padding: 30px 20px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .sidebar-avatar { width: 70px; height: 70px; border-radius: 50%; background-color: #e83e8c; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 10px; border: 3px solid #2b2b40; }
    .sidebar-profile h6 { color: #ffffff; font-weight: 700; margin-bottom: 2px; }
    .sidebar-profile p { font-size: 0.75rem; color: #a2a3b7; margin-bottom: 0; }
    .sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
    .menu-title { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #6c7293; padding: 10px 25px; letter-spacing: 1px; }
    .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #a2a3b7; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.3s; border-left: 3px solid transparent; }
    .sidebar-menu li a i { width: 25px; font-size: 1.1rem; }
    .sidebar-menu li a:hover { color: #ffffff; background-color: #1b1b29; }
    .sidebar-menu li a.active { color: #ffffff; background-color: #1b1b29; border-left-color: #e83e8c; }
    .admin-main-content { flex: 1; margin-left: 260px; min-width: 0; transition: all 0.3s ease; }
    .admin-topbar { background-color: #ffffff; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); position: sticky; top: 0; z-index: 999; }
    .btn-toggle-sidebar { background: none; border: none; font-size: 1.5rem; color: #495057; cursor: pointer; display: none; }
    .topbar-logout { background: #fef2f2; color: #dc2626; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; }

    /* Reportes CSS */
    .dashboard-content { padding: 30px; }
    .page-header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;}
    .page-title { font-weight: 800; color: #1e293b; font-size: 1.8rem; margin-bottom: 5px; }
    .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 0; }
    
    .report-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: none; margin-bottom: 25px; height: 100%; }
    .report-card-title { font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .chart-container { position: relative; height: 300px; width: 100%; }

    /* Tabla Top Profesionales */
    .table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }
    .table td { vertical-align: middle; padding: 12px 15px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    .star-rating { color: #f59e0b; font-size: 0.85rem; }

    /* CSS para Impresión (El PDF) */
    @media print {
        body { background: white !important; }
        .admin-sidebar, .admin-topbar, .btn-print { display: none !important; }
        .admin-main-content { margin-left: 0 !important; width: 100% !important; }
        .report-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; break-inside: avoid; }
        .chart-container { height: 250px !important; }
    }
</style>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['user_nombre'] ?? 'A', 0, 1)); ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
            <p>Super Admin</p>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-title">Resumen</li>
            <li><a href="<?= BASE_URL ?>/admin/dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a></li>
            <li class="menu-title">Gestión de Usuarios</li>
            <li><a href="<?= BASE_URL ?>/admin/profesionales"><i class="fa-solid fa-users-gear"></i> Profesionales</a></li>
            <li><a href="<?= BASE_URL ?>/admin/clientes"><i class="fa-solid fa-users"></i> Clientes</a></li>
            <li class="menu-title">Finanzas y Configuración</li>
            <li><a href="<?= BASE_URL ?>/admin/pagos"><i class="fa-solid fa-money-check-dollar"></i> Pagos y Tokens</a></li>
            <li><a href="<?= BASE_URL ?>/admin/planes"><i class="fa-solid fa-gem"></i> Planes (Oro, Plata)</a></li>
            <li><a href="<?= BASE_URL ?>/admin/categorias"><i class="fa-solid fa-layer-group"></i> Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reportes" class="active"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
        </ul>
    </aside>

    <main class="admin-main-content">
        <header class="admin-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="d-none d-md-block"><span class="text-muted fw-bold">Inteligencia de Negocio</span></div>
            <a href="<?= BASE_URL ?>/auth/logout" class="topbar-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión</a>
        </header>

        <div class="dashboard-content" id="reportArea">
            <div class="page-header-flex">
                <div>
                    <h3 class="page-title">Reportes Analíticos</h3>
                    <p class="page-subtitle">Rendimiento financiero y operativo de GEO-PRO.</p>
                </div>
                <!-- BOTÓN MÁGICO PARA PDF/IMPRIMIR -->
                <button class="btn btn-dark fw-bold px-4 py-2 shadow-sm btn-print" onclick="window.print()">
                    <i class="fa-solid fa-file-pdf me-2"></i>Exportar Reporte
                </button>
            </div>

            <!-- Gráficos -->
            <div class="row g-4">
                
                <!-- Gráfico de Barras: Demanda por Categorías -->
                <div class="col-lg-6">
                    <div class="report-card">
                        <div class="report-card-title"><i class="fa-solid fa-chart-bar text-primary"></i> Oficios Más Demandados</div>
                        <div class="chart-container">
                            <canvas id="demandaChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Líneas: Ingresos Financieros -->
                <div class="col-lg-6">
                    <div class="report-card">
                        <div class="report-card-title"><i class="fa-solid fa-chart-line text-success"></i> Ingresos Mensuales (Bs.)</div>
                        <div class="chart-container">
                            <canvas id="ingresosChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla: Top 10 Profesionales -->
                <div class="col-12">
                    <div class="report-card">
                        <div class="report-card-title"><i class="fa-solid fa-medal text-warning"></i> Top 10 Profesionales de la Plataforma</div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Profesional</th>
                                        <th>Especialidad</th>
                                        <th class="text-center">Trabajos Finalizados</th>
                                        <th class="text-center">Calificación Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($topProfesionales)): ?>
                                        <?php foreach($topProfesionales as $tp): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?= htmlspecialchars($tp['nombre_completo']) ?></td>
                                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($tp['nombre_categoria'] ?? 'S/N') ?></span></td>
                                                <td class="text-center fw-bold text-primary"><?= (int)$tp['trabajos_completados'] ?></td>
                                                <td class="text-center fw-bold">
                                                    <?= number_format((float)$tp['promedio_estrellas'], 1) ?> 
                                                    <i class="fa-solid fa-star star-rating"></i>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted py-4">No hay datos suficientes para calcular el Top 10.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- LIBRERÍA CHART.JS PARA LOS GRÁFICOS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });

    // Preparar datos desde PHP a JS
    const demandaData = <?= json_encode($demanda) ?>;
    const financieroData = <?= json_encode($financiero) ?>;

    // 1. DIBUJAR GRÁFICO DE DEMANDA (Oficios)
    const ctxDemanda = document.getElementById('demandaChart');
    if (ctxDemanda && demandaData.length > 0) {
        new Chart(ctxDemanda, {
            type: 'bar',
            data: {
                labels: demandaData.map(d => d.nombre_categoria),
                datasets: [{
                    label: 'Solicitudes Completadas',
                    data: demandaData.map(d => d.total_solicitudes),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 2. DIBUJAR GRÁFICO FINANCIERO (Ingresos por Mes)
    const ctxIngresos = document.getElementById('ingresosChart');
    if (ctxIngresos && financieroData.length > 0) {
        // Agrupar por mes en caso de tener repetidos por "Membresía vs Tokens"
        let meses = []; let montos = [];
        financieroData.forEach(d => {
            if(!meses.includes(d.mes)) meses.push(d.mes);
        });
        
        meses.forEach(mes => {
            let total = financieroData.filter(d => d.mes === mes).reduce((acc, curr) => acc + parseFloat(curr.total_recaudado), 0);
            montos.push(total);
        });

        new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: meses.reverse(), // Orden cronológico (antiguo a nuevo)
                datasets: [{
                    label: 'Ingresos (Bs.)',
                    data: montos.reverse(),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>