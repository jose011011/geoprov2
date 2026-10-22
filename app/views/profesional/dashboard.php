<?php
require __DIR__ . '/../layouts/header.php';

$aprobado   = $perfil['estado_validacion'] === 'APROBADO';
$disponible = $perfil['estado_disponibilidad'] === 'DISPONIBLE';
$nombresDoc = ['CI_ANVERSO' => 'CI (anverso)', 'CI_REVERSO' => 'CI (reverso)', 'TITULO_TECNICO' => 'Título técnico',
               'CERTIFICADO_ANTECEDENTES' => 'Antecedentes', 'REFERENCIA_LABORAL' => 'Referencias', 'OTRO' => 'Otro documento'];
$claseDoc   = ['APROBADO' => 'success', 'RECHAZADO' => 'danger', 'PENDIENTE' => 'warning'];
?>

<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="fx-page-title h3 mb-0">Hola, <?= Security::e($perfil['nombre']) ?></h1>
            <div class="text-muted small"><i class="<?= Security::e(Catalogo::icono($perfil['icono_fa'])) ?> me-1"></i><?= Security::e($perfil['nombre_categoria']) ?> · <?= Security::e(Catalogo::macroEtiqueta($perfil['macrodistrito_base'])) ?></div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="fx-stars"><?= Catalogo::estrellas((float) $perfil['promedio_estrellas']) ?></span>
            <span class="badge text-bg-primary"><?= Security::e(Catalogo::nombrePlan($perfil['nombre_plan'])) ?></span>
        </div>
    </div>

    <?php if (!$aprobado): ?>
        <div class="alert alert-warning">
            <div class="fw-bold mb-1"><i class="fa-solid fa-hourglass-half me-2"></i>Tu perfil está en revisión</div>
            Un administrador está validando tus documentos. Cuando te aprueben aparecerás en el catálogo y podrás recibir trabajos. Mientras tanto puedes usar FixGeo como cliente.
        </div>
    <?php endif; ?>

    <?php if ($membresiaVencida): ?>
        <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="fa-solid fa-circle-exclamation me-2"></i>Tu membresía venció. Renueva para volver a recibir solicitudes directas.</span>
            <a href="<?= BASE_URL ?>/profesional/comprarTokens" class="btn btn-danger btn-sm">Renovar</a>
        </div>
    <?php elseif ($diasParaVencer !== null && $diasParaVencer <= 5): ?>
        <div class="alert alert-warning"><i class="fa-regular fa-clock me-2"></i>Tu membresía vence en <strong><?= (int) $diasParaVencer ?></strong> día(s).</div>
    <?php endif; ?>

    <?php if (!empty($trabajoActivo)): ?>
        <div class="card fx-job activa mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="small text-muted">Trabajo en curso · <?= Security::e($trabajoActivo['codigo_seguimiento']) ?></div>
                <div class="fw-bold fs-5"><?= Security::e($trabajoActivo['cliente_nombre'] . ' ' . $trabajoActivo['cliente_apellido']) ?> <?= Catalogo::badgeEstado($trabajoActivo['estado_servicio']) ?></div>
                <div class="small text-muted"><?= Security::e($trabajoActivo['zona']) ?></div>
            </div>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/profesional/mapaViaje/<?= (int) $trabajoActivo['id_solicitud'] ?>"><i class="fa-solid fa-map-location-dot me-1"></i>Abrir trabajo</a>
        </div></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-bold fs-5" id="textoEstado"><?= $disponible ? 'Estás disponible' : 'Estás desconectado' ?></div>
                    <div class="small text-muted" id="textoAyuda">
                        <?= !$aprobado ? 'Podrás activarte cuando te aprueben.' : ($disponible ? 'Los clientes pueden verte y recibes solicitudes.' : 'Actívate para recibir solicitudes.') ?>
                    </div>
                </div>
                <div class="form-check form-switch fx-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchDisponible" <?= $disponible ? 'checked' : '' ?> <?= $aprobado ? '' : 'disabled' ?> aria-label="Disponibilidad">
                </div>
            </div></div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="small text-muted">Tokens disponibles</div>
                <div class="fx-bignum"><?= (int) $perfil['tokens_disponibles'] ?></div>
                <a href="<?= BASE_URL ?>/profesional/comprarTokens" class="small fw-semibold">Comprar o renovar</a>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <a class="card h-100 text-decoration-none text-reset" href="<?= BASE_URL ?>/profesional/solicitudes?estado=PENDIENTE"><div class="card-body">
                <div class="small text-muted">Solicitudes nuevas</div>
                <div class="fx-bignum" id="contadorNuevas"><?= (int) $nuevas ?></div>
                <span class="small fw-semibold text-primary">Ver bandeja</span>
            </div></a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ([
            ['activas',       'En curso',            false],
            ['finalizadas',   'Trabajos finalizados', false],
            ['ganancias_mes', 'Ganado este mes (Bs)', true],
            ['ganancias',     'Ganado en total (Bs)', true],
        ] as [$k, $t, $dinero]): ?>
            <div class="col-6 col-lg-3"><div class="fx-stat">
                <div class="num"><?= $dinero ? number_format((float) $stats[$k], 0) : (int) $stats[$k] ?></div><small><?= $t ?></small>
            </div></div>
        <?php endforeach; ?>
    </div>

    <h2 class="h5 fw-bold mb-2">Mis documentos</h2>
    <div class="card"><div class="card-body">
        <?php if (!$documentos): ?>
            <div class="text-muted small">Aún no hay documentos cargados.</div>
        <?php endif; ?>
        <?php foreach ($documentos as $d): $est = strtoupper((string) $d['estado_revision']); ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span><i class="fa-regular fa-file-lines me-2 text-muted"></i><?= Security::e($nombresDoc[$d['tipo_documento_archivo']] ?? $d['tipo_documento_archivo']) ?></span>
                <span class="badge text-bg-<?= $claseDoc[$est] ?? 'secondary' ?>"><?= Security::e(ucfirst(strtolower($est))) ?></span>
            </div>
        <?php endforeach; ?>
    </div></div>
</div>

<script>
(function () {
    const sw = document.getElementById('switchDisponible');
    const texto = document.getElementById('textoEstado');
    const ayuda = document.getElementById('textoAyuda');

    sw?.addEventListener('change', async function () {
        const quiere = sw.checked ? 'DISPONIBLE' : 'NO_DISPONIBLE';
        try {
            const r = await fetch(FX.base + '/profesional/toggleDisponibilidad', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': FX.csrf },
                body: new URLSearchParams({ estado: quiere })
            });
            const d = await r.json();
            if (!d.ok) { throw new Error(d.error || 'No se pudo cambiar tu estado.'); }
            texto.textContent = quiere === 'DISPONIBLE' ? 'Estás disponible' : 'Estás desconectado';
            ayuda.textContent = quiere === 'DISPONIBLE' ? 'Los clientes pueden verte y recibes solicitudes.' : 'Actívate para recibir solicitudes.';
        } catch (e) {
            sw.checked = !sw.checked;
            alert(e.message);
        }
    });

    // Latido: avisa si llegan solicitudes nuevas
    async function revisar() {
        if (document.hidden) return;
        try {
            const r = await fetch(FX.base + '/profesional/checkNuevasSolicitudes', { method: 'POST', headers: { 'X-CSRF-Token': FX.csrf } });
            const d = await r.json();
            if (d.ok) { document.getElementById('contadorNuevas').textContent = d.nuevas; }
        } catch (e) { /* se reintenta */ }
    }
    setInterval(revisar, 15000);
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>