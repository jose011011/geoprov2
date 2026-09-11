<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO CLIENTE — FAVORITOS UI
   ============================================================ */
:root {
    --geo-dark: #071827; --geo-primary: #08b7a5; --geo-primary-dark: #079486; --geo-primary-soft: #e8faf7;
    --geo-blue: #2563eb; --geo-text: #172033; --geo-muted: #718096; --geo-bg: #f5f7fa; --geo-border: #e8edf2;
}

body { background: var(--geo-bg) !important; color: var(--geo-text); font-family: 'Inter', sans-serif; overflow-x: hidden; margin: 0; }
.geo-navbar { display: none !important; }

/* SIDEBAR CLIENTE (Heredado para consistencia) */
.cli-wrapper { display: flex; width: 100%; min-height: 100vh; }
.cli-sidebar { width: 260px; background-color: #ffffff; border-right: 1px solid var(--geo-border); position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 1000; overflow-y: auto; padding-bottom: 50px; }
.sidebar-brand { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); color: var(--geo-dark); font-size: 1.5rem; font-weight: 900; }
.sidebar-profile { padding: 25px 20px; text-align: center; border-bottom: 1px solid var(--geo-border); }
.sidebar-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--geo-primary), var(--geo-blue)); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 15px; border: 4px solid var(--geo-primary-soft); }
.sidebar-profile h6 { color: var(--geo-dark); font-weight: 800; margin-bottom: 5px; font-size: 1.1rem; }
.role-badge { background: var(--geo-primary-soft); color: var(--geo-primary-dark); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid #c8ebe6; }

.sidebar-menu { padding: 20px 0; list-style: none; margin: 0; }
.sidebar-menu li a { display: flex; align-items: center; padding: 14px 25px; color: #475569; text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.2s; border-left: 4px solid transparent; }
.sidebar-menu li a i { width: 30px; font-size: 1.2rem; }
.sidebar-menu li a:hover { color: var(--geo-primary); background-color: #f8fafc; }
.sidebar-menu li a.active { color: var(--geo-primary); background-color: var(--geo-primary-soft); border-left-color: var(--geo-primary); }

.cli-main-content { flex: 1; margin-left: 260px; min-width: 0; display: flex; flex-direction: column; }
.cli-topbar { background-color: #ffffff; height: 75px; display: flex; align-items: center; padding: 0 30px; border-bottom: 1px solid var(--geo-border); position: sticky; top: 0; z-index: 999; }

@media (max-width: 991px) {
    .cli-sidebar { transform: translateX(-100%); }
    .cli-sidebar.show { transform: translateX(0); }
    .cli-main-content { margin-left: 0; }
    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 998; display: none; }
    .sidebar-overlay.show { display: block; }
}

/* ============================================================
   DISEÑO DE TARJETAS DE FAVORITOS
   ============================================================ */
.dashboard-content { padding: 40px; max-width: 1000px; margin: 0 auto; width: 100%; }
.page-title { font-weight: 800; color: var(--geo-dark); font-size: 2rem; margin-bottom: 5px; }
.page-subtitle { color: var(--geo-muted); font-size: 1rem; margin-bottom: 30px; }

.fav-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

.fav-card { background: #fff; border-radius: 20px; padding: 25px; border: 1px solid var(--geo-border); box-shadow: 0 10px 30px rgba(0,0,0,0.03); position: relative; transition: transform 0.3s, box-shadow 0.3s; overflow: hidden; }
.fav-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(8, 183, 165, 0.1); border-color: #c8ebe6; }

/* Cinta decorativa superior */
.fav-card::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, var(--geo-primary), var(--geo-blue)); }

.fav-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.fav-avatar { width: 60px; height: 60px; border-radius: 50%; background: var(--geo-dark); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; border: 3px solid #f8fafc; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
.btn-delete-fav { background: #fef2f2; color: #ef4444; border: none; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration: none; }
.btn-delete-fav:hover { background: #ef4444; color: white; transform: scale(1.1); }

.fav-name { font-weight: 800; color: var(--geo-text); font-size: 1.15rem; margin-bottom: 2px; }
.fav-category { color: var(--geo-primary-dark); font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; display: inline-flex; align-items: center; gap: 5px; background: var(--geo-primary-soft); padding: 4px 10px; border-radius: 8px; }

.fav-stats { display: flex; gap: 15px; margin-bottom: 20px; padding: 15px 0; border-top: 1px dashed var(--geo-border); border-bottom: 1px dashed var(--geo-border); }
.stat-mini { display: flex; flex-direction: column; }
.stat-mini-val { font-weight: 800; color: var(--geo-dark); font-size: 1rem; display: flex; align-items: center; gap: 5px; }
.stat-mini-lbl { font-size: 0.7rem; color: var(--geo-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }

.btn-hire { background: var(--geo-dark); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 700; width: 100%; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
.btn-hire:hover { background: var(--geo-primary); color: white; }
</style>

<div class="cli-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="cli-sidebar" id="cliSidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-location-crosshairs" style="color:var(--geo-primary);"></i> GEO-PRO</div>
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'C', 0, 1)) ?></div>
            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Cliente') ?></h6>
            <span class="role-badge">Modo Cliente</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/cliente/dashboard"><i class="fa-solid fa-house"></i> Panel de Inicio</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/historial"><i class="fa-solid fa-clock-rotate-left"></i> Mis Solicitudes</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/favoritos" class="active"><i class="fa-solid fa-heart"></i> Favoritos</a></li>
            <li><a href="<?= BASE_URL ?>/cliente/perfil"><i class="fa-solid fa-map-location-dot"></i> Direcciones</a></li>
            
            <?php if(isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 3): ?>
                <li class="menu-title mt-4 px-4 text-muted" style="font-size:0.7rem; font-weight:800;">Modo Dual</li>
                <li>
                    <a href="<?= BASE_URL ?>/profesional/dashboard" style="background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 10px; margin: 0 15px;">
                        <i class="fa-solid fa-briefcase"></i> Volver a Profesional
                    </a>
                </li>
            <?php endif; ?>

            <li><a href="<?= BASE_URL ?>/auth/logout" class="text-danger mt-3"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="cli-main-content">
        <header class="cli-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="fw-bold text-muted">Gestión de Profesionales de Confianza</div>
        </header>

        <div class="dashboard-content">
            <h2 class="page-title">Mis Favoritos</h2>
            <p class="page-subtitle">Guarda aquí a los especialistas que hicieron un gran trabajo para contratarlos directamente la próxima vez.</p>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'eliminado'): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" style="background:#ecfdf5; color:#065f46;">
                    <i class="fa-solid fa-circle-check me-2"></i> Profesional eliminado de tu lista de favoritos.
                </div>
            <?php endif; ?>

            <div class="fav-grid">
                <?php if(!empty($favoritos)): ?>
                    <?php foreach($favoritos as $fav): ?>
                        <div class="fav-card">
                            <div class="fav-header">
                                <div class="fav-avatar">
                                    <?= strtoupper(substr($fav['nombre'], 0, 1)) ?>
                                </div>
                                <!-- Botón Eliminar -->
                                <a href="<?= BASE_URL ?>/cliente/eliminarFavorito/<?= $fav['id_favorito'] ?>" class="btn-delete-fav" title="Quitar de Favoritos" onclick="return confirm('¿Estás seguro de quitar a este profesional de tus favoritos?');">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>

                            <div class="fav-name"><?= htmlspecialchars($fav['nombre'] . ' ' . $fav['apellido']) ?></div>
                            <div class="fav-category">
                                <i class="<?= htmlspecialchars($fav['icono_fa']) ?>"></i> <?= htmlspecialchars($fav['nombre_categoria']) ?>
                            </div>

                            <div class="fav-stats">
                                <div class="stat-mini">
                                    <div class="stat-mini-val"><i class="fa-solid fa-star text-warning"></i> <?= number_format((float)$fav['promedio_estrellas'], 1) ?></div>
                                    <div class="stat-mini-lbl">Reputación</div>
                                </div>
                                <div class="stat-mini" style="border-left: 1px solid var(--geo-border); padding-left: 15px;">
                                    <div class="stat-mini-val"><i class="fa-solid fa-medal text-muted"></i> <?= htmlspecialchars(str_replace('_', ' ', $fav['nombre_plan'])) ?></div>
                                    <div class="stat-mini-lbl">Suscripción</div>
                                </div>
                            </div>

                            <!-- Ojo aquí: Lo mandamos al buscador de IA, pero forzamos la categoría para que la UI de asignación manual se abra rápido -->
                            <a href="<?= BASE_URL ?>/cliente/buscarEspecialista?cat=<?= $fav['id_categoria'] ?>" class="btn-hire">
                                <i class="fa-solid fa-bolt"></i> Contratar de Nuevo
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1;" class="text-center p-5 bg-white border rounded-4 shadow-sm">
                        <i class="fa-solid fa-heart-crack fa-3x text-muted opacity-25 mb-3"></i>
                        <h4 class="fw-bold text-dark">Aún no tienes favoritos</h4>
                        <p class="text-muted">Cuando finalices un servicio y te guste el trabajo del profesional, búscalo en tu historial para agregarlo a esta lista.</p>
                        <a href="<?= BASE_URL ?>/cliente/dashboard" class="btn btn-dark fw-bold px-4 rounded-pill mt-2">Buscar un servicio ahora</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('cliSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnToggle) btnToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>