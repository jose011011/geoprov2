<?php require_once "../app/views/layouts/header.php"; ?>

<style>
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    .catalogo-header { background: linear-gradient(135deg, #0f172a, #1e293b); padding: 60px 20px; color: white; text-align: center; border-radius: 0 0 30px 30px; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .catalogo-title { font-weight: 900; font-size: 2.5rem; margin-bottom: 15px; }
    .catalogo-subtitle { font-size: 1.1rem; color: #94a3b8; max-width: 600px; margin: 0 auto; }
    
    /* Filtros */
    .filter-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 30px; display: flex; gap: 15px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .filter-select { padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f1f5f9; font-weight: 600; color: #334155; min-width: 250px; outline: none; }
    
    /* Tarjetas de Profesionales */
    .pro-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: all 0.3s ease; position: relative; overflow: hidden; border: 2px solid transparent; height: 100%; display: flex; flex-direction: column; }
    .pro-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    
    /* Diseño Premium */
    .pro-card.premium { border-color: #f59e0b; background: linear-gradient(to bottom, #fffbf0, #ffffff); }
    .badge-premium { position: absolute; top: 15px; right: 15px; background: #f59e0b; color: white; padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); z-index: 10; }
    
    .pro-avatar { width: 70px; height: 70px; border-radius: 50%; background: #1e293b; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; margin-bottom: 15px; border: 3px solid #f1f5f9; }
    .pro-card.premium .pro-avatar { background: linear-gradient(135deg, #f59e0b, #d97706); border-color: #fef3c7; }
    
    .pro-name { font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 5px; }
    .pro-category { color: #64748b; font-size: 0.9rem; font-weight: 600; margin-bottom: 15px; display: inline-block; background: #f1f5f9; padding: 4px 12px; border-radius: 50px; }
    
    .pro-stats { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
    .stat-item { display: flex; align-items: center; gap: 5px; font-size: 0.95rem; font-weight: 700; color: #475569; }
    .stat-icon { color: #f59e0b; }
    .stat-icon.location { color: #ef4444; }
    
    .btn-solicitar { margin-top: auto; padding: 12px; border-radius: 12px; font-weight: 700; transition: all 0.2s; border: none; width: 100%; }
    .btn-solicitar.regular { background: #f1f5f9; color: #334155; }
    .btn-solicitar.regular:hover { background: #e2e8f0; }
    .btn-solicitar.premium-btn { background: #1e293b; color: white; }
    .btn-solicitar.premium-btn:hover { background: #0f172a; }
</style>

<div class="catalogo-header">
    <div class="container">
        <h1 class="catalogo-title">Encuentra al Especialista Ideal</h1>
        <p class="catalogo-subtitle">Explora nuestro directorio de profesionales verificados. Selecciona directamente al técnico que mejor se adapte a tus necesidades.</p>
    </div>
</div>

<div class="container mb-5">
    <!-- Barra de Filtros -->
    <div class="filter-card">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-filter text-muted"></i>
            <h5 class="mb-0 fw-bold text-dark">Filtrar Especialistas</h5>
        </div>
        <form method="GET" action="<?= BASE_URL ?>/cliente/especialistas" class="m-0 d-flex gap-2">
            <select name="categoria" class="filter-select" onchange="this.form.submit()">
                <option value="0">Todas las Categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id_categoria'] ?>" <?= ($categoriaActual == $cat['id_categoria']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Cuadrícula de Profesionales -->
    <div class="row g-4">
        <?php if (!empty($especialistas)): ?>
            <?php foreach ($especialistas as $pro): ?>
                <?php $esPremium = ($pro['posicionamiento_destacado'] == 1 || $pro['id_plan'] == 3); ?>
                
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="pro-card <?= $esPremium ? 'premium' : '' ?>">
                        
                        <?php if ($esPremium): ?>
                            <div class="badge-premium"><i class="fa-solid fa-crown me-1"></i> Destacado</div>
                        <?php endif; ?>

                        <div class="pro-avatar">
                            <?= strtoupper(substr($pro['nombre'] ?? 'P', 0, 1)) ?>
                        </div>
                        
                        <h3 class="pro-name"><?= htmlspecialchars($pro['nombre'] . ' ' . $pro['apellido']) ?></h3>
                        <div>
                            <span class="pro-category"><?= htmlspecialchars($pro['nombre_categoria']) ?></span>
                        </div>

                        <div class="pro-stats">
                            <div class="stat-item">
                                <i class="fa-solid fa-star stat-icon"></i> 
                                <?= number_format($pro['promedio_estrellas'], 1) ?> 
                                <span class="text-muted fw-normal small">(<?= $pro['total_resenas'] ?>)</span>
                            </div>
                        </div>

                        <div class="pro-stats mb-4">
                            <div class="stat-item">
                                <i class="fa-solid fa-location-dot stat-icon location"></i> 
                                <?= htmlspecialchars($pro['zona_especifica']) ?>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/cliente/solicitarManual" class="mt-auto">
                            <input type="hidden" name="id_profesional" value="<?= $pro['id_profesional'] ?>">
                            <button type="submit" class="btn-solicitar <?= $esPremium ? 'premium-btn' : 'regular' ?>">
                                Solicitar Servicio
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted mb-3" style="font-size: 3rem;"><i class="fa-solid fa-users-slash"></i></div>
                <h4 class="fw-bold text-dark">No hay especialistas disponibles</h4>
                <p class="text-muted">No encontramos profesionales activos en esta categoría en este momento.</p>
                <a href="<?= BASE_URL ?>/cliente/especialistas" class="btn btn-dark px-4 py-2 fw-bold rounded-pill mt-2">Ver todos</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../app/views/layouts/footer.php"; ?>