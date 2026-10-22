<?php
require __DIR__ . '/../layouts/header.php';
$id     = (int) $chat['id_solicitud'];
$volver = BASE_URL . '/solicitud/detalle/' . $id;
$vence  = !empty($chat['fecha_finalizacion']) ? date('d/m/Y', strtotime($chat['fecha_finalizacion'] . ' +15 days')) : null;
?>

<div class="container py-3 py-md-4" style="max-width: 780px;">
    <div class="card fx-chat overflow-hidden">
        <div class="fx-chat-head border-bottom d-flex align-items-center justify-content-between gap-3 p-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <span class="fx-avatar-sm lg"><?= Security::e(mb_strtoupper(mb_substr($chat['otro_nombre'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                <div class="min-w-0">
                    <div class="fw-bold text-truncate"><?= Security::e($chat['otro_nombre']) ?></div>
                    <div class="small text-muted text-truncate"><?= Security::e($chat['nombre_categoria'] ?? 'Servicio') ?> · <?= Security::e($chat['codigo_seguimiento']) ?> · <?= Catalogo::etiquetaEstado($chat['estado_servicio']) ?></div>
                </div>
            </div>
            <a href="<?= $volver ?>" class="btn btn-outline-primary btn-sm flex-shrink-0">Ver solicitud</a>
        </div>

        <?php if ($chat['abierto']): ?>
            <div class="fx-chat-aviso"><i class="fa-solid fa-shield-halved me-1"></i>Por tu privacidad, los mensajes se eliminan automáticamente <strong>15 días después</strong> de que termine el servicio.</div>
        <?php else: ?>
            <div class="fx-chat-aviso cerrado"><i class="fa-solid fa-lock me-1"></i>Servicio terminado: el chat es solo de lectura y se eliminará <?= $vence ? 'el <strong>' . $vence . '</strong>' : '<strong>15 días después</strong> del cierre' ?>. Guarda lo que necesites.</div>
        <?php endif; ?>

        <div class="fx-chat-msgs" id="mensajes" aria-live="polite"></div>

        <?php if ($chat['abierto']): ?>
            <div class="px-3 pt-2 small text-muted d-none" id="infoImagen"></div>
            <form class="fx-chat-form border-top d-flex align-items-center gap-2 p-3" id="formChat" enctype="multipart/form-data" autocomplete="off">
                <label class="btn btn-outline-primary mb-0" title="Adjuntar imagen"><i class="fa-regular fa-image"></i>
                    <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" hidden>
                </label>
                <input type="text" id="texto" name="mensaje" class="form-control" maxlength="1000" placeholder="Escribe un mensaje...">
                <button class="btn btn-primary" id="btnEnviar" aria-label="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Visor de imágenes -->
<div class="modal fade" id="visorImagen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content bg-transparent border-0">
        <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        <img id="visorImg" class="img-fluid rounded" alt="Imagen del chat" style="max-height: 80vh; object-fit: contain; background:#000;">
    </div></div>
</div>

<script>
(function () {
    const ID = <?= $id ?>, YO = <?= (int) $yo ?>;
    const URL_IMG = FX.base + '/uploads/chat/';
    const caja = document.getElementById('mensajes');
    let ultimo = 0, ultimoDia = '';
    const abierto = <?= $chat['abierto'] ? 'true' : 'false' ?>;

    const claveDia = d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    function etiquetaDia(d) {
        const hoy = new Date(), ayer = new Date(Date.now() - 86400000);
        if (claveDia(d) === claveDia(hoy)) return 'Hoy';
        if (claveDia(d) === claveDia(ayer)) return 'Ayer';
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long' });
    }

    function agregar(m) {
        const fecha = new Date(String(m.fecha_envio).replace(' ', 'T'));
        if (claveDia(fecha) !== ultimoDia) {
            ultimoDia = claveDia(fecha);
            const sep = document.createElement('div');
            sep.className = 'fx-fecha';
            sep.innerHTML = '<span></span>';
            sep.firstChild.textContent = etiquetaDia(fecha);
            caja.appendChild(sep);
        }

        const div = document.createElement('div');
        if (m.tipo_mensaje === 'SISTEMA') {
            div.className = 'fx-msg sistema';
            div.textContent = m.mensaje || '';
        } else {
            div.className = 'fx-msg ' + (parseInt(m.id_remitente, 10) === YO ? 'me' : 'other');
            if (m.archivo_adjunto) {
                const img = document.createElement('img');
                img.src = URL_IMG + encodeURIComponent(m.archivo_adjunto);
                img.alt = 'Imagen enviada'; img.loading = 'lazy';
                div.appendChild(img);
            }
            if (m.mensaje) {
                const p = document.createElement('div');
                p.textContent = m.mensaje;
                div.appendChild(p);
            }
            const s = document.createElement('small');
            s.textContent = fecha.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
            div.appendChild(s);
        }
        caja.appendChild(div);
        ultimo = Math.max(ultimo, parseInt(m.id_mensaje, 10));
    }

    function pintar(lista, forzarFinal) {
        if (!lista.length) return;
        const alFinal = forzarFinal || caja.scrollHeight - caja.scrollTop - caja.clientHeight < 90;
        lista.forEach(agregar);
        if (alFinal) caja.scrollTop = caja.scrollHeight;
    }

    async function consultar() {
        if (document.hidden) return;
        try {
            const r = await fetch(FX.base + '/chat/mensajes/' + ID + '?desde=' + ultimo, { headers: { 'Accept': 'application/json' } });
            const d = await r.json();
            if (d.ok) { pintar(d.mensajes, false); if (abierto && !d.abierto) location.reload(); }
        } catch (e) { /* se reintenta */ }
    }

    pintar(<?= json_encode($mensajes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, true);
    setInterval(consultar, 3000);

    // Ver imagen en grande
    caja.addEventListener('click', function (e) {
        if (e.target.tagName !== 'IMG') return;
        document.getElementById('visorImg').src = e.target.src;
        new bootstrap.Modal(document.getElementById('visorImagen')).show();
    });

    const form = document.getElementById('formChat');
    if (form) {
        const texto = document.getElementById('texto'), imagen = document.getElementById('imagen'), info = document.getElementById('infoImagen'), btn = document.getElementById('btnEnviar');

        imagen.addEventListener('change', function () {
            if (!imagen.files.length) { info.classList.add('d-none'); return; }
            info.classList.remove('d-none');
            info.innerHTML = '<i class="fa-regular fa-image me-1"></i><span></span> <a href="#" class="ms-2">Quitar</a>';
            info.querySelector('span').textContent = imagen.files[0].name;
            info.querySelector('a').addEventListener('click', function (ev) { ev.preventDefault(); imagen.value = ''; info.classList.add('d-none'); });
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!texto.value.trim() && !imagen.files.length) return;
            btn.disabled = true;
            try {
                const r = await fetch(FX.base + '/chat/enviar/' + ID, { method: 'POST', headers: { 'X-CSRF-Token': FX.csrf }, body: new FormData(form) });
                const d = await r.json();
                if (!d.ok) { alert(d.error || 'No se pudo enviar el mensaje.'); }
                else { form.reset(); info.classList.add('d-none'); await consultar(); caja.scrollTop = caja.scrollHeight; }
            } catch (err) {
                alert('No se pudo enviar el mensaje. Revisa tu conexión.');
            } finally {
                btn.disabled = false;
                texto.focus();
            }
        });
    }
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>