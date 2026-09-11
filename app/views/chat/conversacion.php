<?php require_once "../app/views/layouts/header.php"; ?>

<style>
/* ============================================================
   GEO-PRO CHAT — PREMIUM UI
   ============================================================ */
:root {
    --geo-dark: #071827;
    --geo-primary: #08b7a5;
    --geo-primary-dark: #079486;
    --geo-bg: #f5f7fa;
    --geo-text: #172033;
    --geo-border: #e8edf2;
}

body { background: var(--geo-bg) !important; font-family: 'Inter', sans-serif; overflow-x: hidden; }
.geo-navbar { display: none !important; } /* Ocultamos el navbar por defecto para mayor inmersión */

/* Contenedor Principal del Chat */
.chat-container {
    max-width: 950px;
    margin: 30px auto;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(7, 24, 39, 0.08);
    display: flex;
    flex-direction: column;
    height: 85vh; /* Ocupa casi toda la pantalla */
    min-height: 500px;
    border: 1px solid var(--geo-border);
    overflow: hidden;
}

/* Cabecera del Chat */
.chat-header {
    padding: 20px 25px;
    background: #ffffff;
    border-bottom: 1px solid var(--geo-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--geo-primary), #2563eb);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    box-shadow: 0 4px 10px rgba(8, 183, 165, 0.2);
}

.chat-title { margin: 0; font-weight: 800; color: var(--geo-text); font-size: 1.1rem; }
.chat-subtitle { margin: 0; font-size: 0.8rem; color: #64748b; font-weight: 500; }
.chat-code { background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-family: monospace; font-size: 0.75rem; color: #475569; border: 1px solid #e2e8f0;}

/* Cuerpo del Chat (Mensajes) */
.chat-body {
    flex: 1;
    background: #f8fafc;
    padding: 25px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.chat-body::-webkit-scrollbar { width: 6px; }
.chat-body::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

/* Burbujas de Mensaje */
.msg-row { display: flex; width: 100%; }
.msg-mine { justify-content: flex-end; }
.msg-theirs { justify-content: flex-start; }

.bubble {
    max-width: 70%;
    padding: 12px 18px;
    position: relative;
    word-wrap: break-word;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Mi burbuja (Verde/Azul) */
.bubble-mine {
    background: linear-gradient(135deg, var(--geo-primary), var(--geo-primary-dark));
    color: white;
    border-radius: 20px 20px 4px 20px; /* Esquina inferior derecha en punta */
    box-shadow: 0 5px 15px rgba(8, 183, 165, 0.2);
}

/* Su burbuja (Blanca) */
.bubble-theirs {
    background: #ffffff;
    color: var(--geo-text);
    border-radius: 20px 20px 20px 4px; /* Esquina inferior izquierda en punta */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--geo-border);
}

.msg-time { font-size: 0.7rem; margin-top: 5px; text-align: right; font-weight: 600; }
.bubble-mine .msg-time { color: rgba(255,255,255,0.7); }
.bubble-theirs .msg-time { color: #94a3b8; }

.msg-image { max-width: 100%; border-radius: 12px; margin-bottom: 8px; border: 2px solid rgba(255,255,255,0.2); }

/* Pie del Chat (Input) */
.chat-footer {
    padding: 20px 25px;
    background: #ffffff;
    border-top: 1px solid var(--geo-border);
}

.chat-input-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f1f5f9;
    padding: 8px 12px;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.chat-input-wrapper:focus-within { background: #ffffff; border-color: var(--geo-primary); box-shadow: 0 0 0 4px rgba(8, 183, 165, 0.1); }

.btn-attach {
    width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; color: #64748b; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; flex-shrink: 0;
}
.btn-attach:hover { background: #cbd5e1; color: var(--geo-dark); }

.chat-input { border: none; background: transparent; flex: 1; outline: none; padding: 0 10px; font-size: 0.95rem; color: var(--geo-text); }

.btn-send {
    width: 42px; height: 42px; border-radius: 50%; background: var(--geo-primary); color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; flex-shrink: 0; box-shadow: 0 4px 10px rgba(8, 183, 165, 0.3);
}
.btn-send:hover { background: var(--geo-primary-dark); transform: scale(1.05); }

/* Responsive */
@media(max-width: 768px) {
    .chat-container { margin: 0; height: 100vh; border-radius: 0; border: none; }
    .bubble { max-width: 85%; }
}
</style>

<div class="container-fluid p-0">
    <div class="chat-container">
        
        <!-- CABECERA -->
        <div class="chat-header">
            <div class="chat-header-info">
                <a href="javascript:history.back()" class="btn btn-light rounded-circle border me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-arrow-left text-muted"></i>
                </a>
                
                <div class="chat-avatar">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h5 class="chat-title"><?= htmlspecialchars($solicitud['prof_nombre'] . ' ' . $solicitud['prof_apellido']) ?></h5>
                    <p class="chat-subtitle">Asistencia Técnica con <?= htmlspecialchars($solicitud['cliente_nombre']) ?></p>
                </div>
            </div>
            <div>
                <span class="chat-code"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($solicitud['codigo_seguimiento']) ?></span>
            </div>
        </div>

        <!-- CUERPO DE MENSAJES -->
        <div id="chatBox" class="chat-body">
            <?php foreach ($mensajes as $m): ?>
                <?php $esMio = (int) $m['id_remitente'] === (int) $_SESSION['user_id']; ?>
                
                <div class="msg-row <?= $esMio ? 'msg-mine' : 'msg-theirs' ?>">
                    <div class="bubble <?= $esMio ? 'bubble-mine' : 'bubble-theirs' ?>">
                        
                        <?php if ($m['tipo_mensaje'] === 'IMAGEN' && $m['archivo_adjunto']): ?>
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($m['archivo_adjunto']) ?>" target="_blank">
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($m['archivo_adjunto']) ?>" class="msg-image" alt="Imagen adjunta">
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($m['mensaje']): ?>
                            <div><?= nl2br(htmlspecialchars($m['mensaje'])) ?></div>
                        <?php endif; ?>
                        
                        <div class="msg-time"><?= date('H:i', strtotime($m['fecha_envio'])) ?></div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <!-- PIE / FORMULARIO -->
        <div class="chat-footer">
            <form id="formChat" enctype="multipart/form-data">
                <input type="hidden" name="id_solicitud" value="<?= (int) $idSolicitud ?>">
                
                <div class="chat-input-wrapper">
                    <!-- Botón de Adjuntar Archivo Oculto -->
                    <label class="btn-attach mb-0" title="Adjuntar Imagen">
                        <i class="fa-solid fa-paperclip"></i>
                        <input type="file" name="adjunto" id="adjuntoInput" accept="image/*" class="d-none">
                    </label>
                    
                    <input type="text" name="mensaje" id="inputMensaje" class="chat-input" placeholder="Escribe tu mensaje aquí..." autocomplete="off">
                    
                    <button type="submit" class="btn-send" title="Enviar Mensaje">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
const idSolicitud = <?= (int) $idSolicitud ?>;
let ultimoId = <?= !empty($mensajes) ? (int) end($mensajes)['id_mensaje'] : 0 ?>;
const chatBox = document.getElementById('chatBox');
const miId = <?= (int) $_SESSION['user_id'] ?>;

// Mantener el scroll siempre abajo
function scrollAbajo() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
scrollAbajo();

// Lógica para pintar mensajes nuevos sin recargar
function pintarMensaje(m) {
    const esMio = parseInt(m.id_remitente) === miId;
    const div = document.createElement('div');
    div.className = 'msg-row ' + (esMio ? 'msg-mine' : 'msg-theirs');

    let contenido = '';
    if (m.tipo_mensaje === 'IMAGEN' && m.archivo_adjunto) {
        contenido += `<a href="<?= BASE_URL ?>/${m.archivo_adjunto}" target="_blank"><img src="<?= BASE_URL ?>/${m.archivo_adjunto}" class="msg-image" alt="Adjunto"></a>`;
    }
    if (m.mensaje) {
        contenido += `<div>${m.mensaje.replace(/</g,'&lt;')}</div>`;
    }
    const hora = new Date(m.fecha_envio.replace(' ', 'T')).toLocaleTimeString('es-BO', {hour:'2-digit', minute:'2-digit'});

    div.innerHTML = `
        <div class="bubble ${esMio ? 'bubble-mine' : 'bubble-theirs'}">
            ${contenido}
            <div class="msg-time">${hora}</div>
        </div>
    `;
    chatBox.appendChild(div);
}

// POLLING: Consultar nuevos mensajes cada 4 segundos
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
    } catch (e) { /* Fallo silencioso de red, reintentará luego */ }
}

setInterval(consultarNuevos, 4000);

// Enviar mensaje vía AJAX
document.getElementById('formChat').addEventListener('submit', async function (e) {
    e.preventDefault();
    
    const inputMsg = document.getElementById('inputMensaje');
    const inputAdj = document.getElementById('adjuntoInput');
    
    // Evitar envío vacío
    if(inputMsg.value.trim() === '' && inputAdj.files.length === 0) return;

    const formData = new FormData(this);
    
    // UI: Limpiar input instantáneamente para dar sensación de rapidez
    inputMsg.value = '';
    
    try {
        const res = await fetch('<?= BASE_URL ?>/chat/enviar', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.ok) {
            inputAdj.value = ''; // Limpiar el archivo si había uno
            await consultarNuevos(); // Forzar la consulta para verlo inmediatamente
        } else {
            alert(data.error || 'No se pudo enviar el mensaje.');
        }
    } catch (e) {
        alert('Error de conexión al enviar el mensaje.');
    }
});

// Auto-enviar formulario al seleccionar una imagen
document.getElementById('adjuntoInput').addEventListener('change', function () {
    if (this.files.length > 0) {
        document.getElementById('formChat').dispatchEvent(new Event('submit'));
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>