<?php require_once "../app/views/layouts/header.php"; ?>

<div class="container py-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver</a>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <strong><?= htmlspecialchars($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?></strong>
            <span class="text-white-50 small"> con <?= htmlspecialchars($solicitud['cliente_nombre'] . ' ' . $solicitud['cliente_apellido']) ?></span>
            · <span class="badge bg-secondary"><?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></span>
        </div>

        <div id="chatBox" class="card-body" style="height: 400px; overflow-y: auto; background: #f4f6f8;">
            <?php foreach ($mensajes as $m): ?>
                <?php $esMio = (int) $m['id_remitente'] === (int) $_SESSION['user_id']; ?>
                <div class="d-flex mb-2 <?= $esMio ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="p-2 rounded <?= $esMio ? 'bg-success text-white' : 'bg-white border' ?>" style="max-width:70%;">
                        <?php if ($m['tipo_mensaje'] === 'IMAGEN' && $m['archivo_adjunto']): ?>
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($m['archivo_adjunto']) ?>" target="_blank">
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($m['archivo_adjunto']) ?>" style="max-width:200px; border-radius:8px;">
                            </a>
                        <?php endif; ?>
                        <?php if ($m['mensaje']): ?>
                            <div><?= nl2br(htmlspecialchars($m['mensaje'])) ?></div>
                        <?php endif; ?>
                        <div class="small <?= $esMio ? 'text-white-50' : 'text-muted' ?>"><?= date('H:i', strtotime($m['fecha_envio'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer">
            <form id="formChat" class="d-flex gap-2" enctype="multipart/form-data">
                <input type="hidden" name="id_solicitud" value="<?= (int) $idSolicitud ?>">
                <label class="btn btn-outline-secondary mb-0">
                    <i class="fa-solid fa-image"></i>
                    <input type="file" name="adjunto" id="adjuntoInput" accept="image/*" class="d-none">
                </label>
                <input type="text" name="mensaje" id="inputMensaje" class="form-control" placeholder="Escribe un mensaje...">
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
const idSolicitud = <?= (int) $idSolicitud ?>;
let ultimoId = <?= !empty($mensajes) ? (int) end($mensajes)['id_mensaje'] : 0 ?>;
const chatBox = document.getElementById('chatBox');
const miId = <?= (int) $_SESSION['user_id'] ?>;

function scrollAbajo() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
scrollAbajo();

function pintarMensaje(m) {
    const esMio = parseInt(m.id_remitente) === miId;
    const div = document.createElement('div');
    div.className = 'd-flex mb-2 ' + (esMio ? 'justify-content-end' : 'justify-content-start');

    let contenido = '';
    if (m.tipo_mensaje === 'IMAGEN' && m.archivo_adjunto) {
        contenido += `<a href="<?= BASE_URL ?>/${m.archivo_adjunto}" target="_blank"><img src="<?= BASE_URL ?>/${m.archivo_adjunto}" style="max-width:200px;border-radius:8px;"></a>`;
    }
    if (m.mensaje) {
        contenido += `<div>${m.mensaje.replace(/</g,'&lt;')}</div>`;
    }
    const hora = new Date(m.fecha_envio.replace(' ', 'T')).toLocaleTimeString('es-BO', {hour:'2-digit', minute:'2-digit'});

    div.innerHTML = `<div class="p-2 rounded ${esMio ? 'bg-success text-white' : 'bg-white border'}" style="max-width:70%;">
        ${contenido}
        <div class="small ${esMio ? 'text-white-50' : 'text-muted'}">${hora}</div>
    </div>`;
    chatBox.appendChild(div);
}

async function consultarNuevos() {
    try {
        const res = await fetch(`<?= BASE_URL ?>/chat/nuevos/${idSolicitud}?ultimo_id=${ultimoId}`);
        const data = await res.json();
        if (data.ok && data.mensajes.length > 0) {
            data.mensajes.forEach(m => {
                pintarMensaje(m);
                ultimoId = m.id_mensaje;
            });
            scrollAbajo();
        }
    } catch (e) { /* silencioso, reintenta en el próximo ciclo */ }
}

setInterval(consultarNuevos, 4000); // Polling cada 4 segundos

document.getElementById('formChat').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    const res = await fetch('<?= BASE_URL ?>/chat/enviar', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.ok) {
        document.getElementById('inputMensaje').value = '';
        document.getElementById('adjuntoInput').value = '';
        await consultarNuevos();
    } else {
        alert(data.error || 'No se pudo enviar el mensaje.');
    }
});

document.getElementById('adjuntoInput').addEventListener('change', function () {
    if (this.files.length > 0) {
        document.getElementById('formChat').dispatchEvent(new Event('submit'));
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>