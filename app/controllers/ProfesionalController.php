<?php
class ProfesionalController extends Controller
{
    private ProfesionalPanel $panel;

    public function __construct()
    {
        $this->requiereSesion([3]); // solo cuentas con perfil de técnico
        $this->panel = new ProfesionalPanel();
    }

    private function perfilActual(): array
    {
        $perfil = $this->panel->perfilPorUsuario((int) $_SESSION['user_id']);
        if (!$perfil) {
            Flash::set('danger', 'No encontramos tu perfil de técnico.');
            $this->redirigir('/cliente/dashboard');
        }
        return $perfil;
    }

    /** Solicitudes nuevas = las que te piden directamente + las abiertas que la cascada te muestra según tu plan. */
    private function nuevasSolicitudes(array $perfil, Solicitud $modelo): array
    {
        $idProf = (int) $perfil['id_profesional'];
        if ($perfil['estado_validacion'] !== 'APROBADO' || $modelo->tieneTrabajoActivo($idProf)) {
            return [];
        }

        $directas = $modelo->listarPorProfesional($idProf, 'PENDIENTE');
        foreach ($directas as &$d) { $d['origen'] = 'DIRECTA'; }
        unset($d);

        $cascada = $modelo->obtenerOportunidadesCascada(
            (int) $perfil['id_plan'], $perfil['macrodistrito_base'] ?? 'CENTRO', (int) $perfil['id_categoria'], $idProf
        );
        foreach ($cascada as &$c) { $c['origen'] = 'CASCADA'; }
        unset($c);

        return array_merge($directas, $cascada);
    }

    private function soloActivas(array $lista): array
    {
        return array_values(array_filter($lista, fn($s) => in_array($s['estado_servicio'], ['ACEPTADA', 'EN_CAMINO', 'EN_PROCESO'], true)));
    }

    // ================= PANEL =================
    public function dashboard()
    {
        $perfil  = $this->perfilActual();
        $idProf  = (int) $perfil['id_profesional'];
        $modelo  = new Solicitud();
        $activas = $this->soloActivas($modelo->listarPorProfesional($idProf));

        $this->view('profesional/dashboard', [
            'titulo'           => 'FixGeo | Mi panel',
            'perfil'           => $perfil,
            'documentos'       => $this->panel->documentos($idProf),
            'stats'            => $this->panel->estadisticas($idProf),
            'membresiaVencida' => $this->panel->membresiaVencida($perfil),
            'diasParaVencer'   => $this->panel->diasParaVencer($perfil),
            'trabajoActivo'    => $activas[0] ?? null,
            'nuevas'           => count($this->nuevasSolicitudes($perfil, $modelo)),
        ]);
    }

    public function toggleDisponibilidad()
    {
        if (!$this->esPost()) {
            $this->jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
        }
        $perfil = $this->perfilActual();
        try {
            $resultado = $this->panel->actualizarDisponibilidad((int) $perfil['id_profesional'], $perfil['estado_validacion'], (string) ($_POST['estado'] ?? ''));
            $this->jsonResponse(['ok' => true] + $resultado);
        } catch (DomainException $e) {
            $this->jsonResponse(['ok' => false, 'error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('[FixGeo] toggleDisponibilidad: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'No se pudo cambiar tu estado.'], 500);
        }
    }

    // ================= SOLICITUDES =================
    public function solicitudes()
    {
        $perfil = $this->perfilActual();
        $modelo = new Solicitud();
        $idProf = (int) $perfil['id_profesional'];

        $filtro = strtoupper((string) ($_GET['estado'] ?? 'PENDIENTE'));
        if (!in_array($filtro, ['PENDIENTE', 'ACTIVAS', 'FINALIZADA', 'TODAS'], true)) {
            $filtro = 'PENDIENTE';
        }

        $todas       = $modelo->listarPorProfesional($idProf);
        $nuevas      = $this->nuevasSolicitudes($perfil, $modelo);
        $activas     = $this->soloActivas($todas);
        $finalizadas = array_values(array_filter($todas, fn($s) => $s['estado_servicio'] === 'FINALIZADA'));

        $lista = match ($filtro) {
            'PENDIENTE'  => $nuevas,
            'ACTIVAS'    => $activas,
            'FINALIZADA' => $finalizadas,
            default      => $todas,
        };

        $this->view('profesional/solicitudes', [
            'titulo'       => 'FixGeo | Solicitudes de trabajo',
            'perfil'       => $perfil,
            'solicitudes'  => $lista,
            'filtroActual' => $filtro,
            'conteos'      => ['PENDIENTE' => count($nuevas), 'ACTIVAS' => count($activas), 'FINALIZADA' => count($finalizadas), 'TODAS' => count($todas)],
            'trabajoActivo' => (bool) $activas,
        ]);
    }

    public function historial()
    {
        $perfil = $this->perfilActual();
        $this->view('profesional/historial', [
            'titulo'      => 'FixGeo | Historial de trabajos',
            'perfil'      => $perfil,
            'solicitudes' => (new Solicitud())->listarPorProfesional((int) $perfil['id_profesional'], 'FINALIZADA'),
        ]);
    }

    // ================= CAMBIOS DE ESTADO (aceptar, viajar, llegar, finalizar, rechazar) =================
    public function cambiarEstadoSolicitud()
    {
        if (!$this->esPost()) {
            $this->redirigir('/profesional/solicitudes');
        }

        $idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
        $nuevoEstado = trim((string) ($_POST['estado'] ?? ''));
        $tiempo      = (int) ($_POST['tiempo_estimado'] ?? 0);
        $precio      = (float) ($_POST['precio_acordado'] ?? 0);

        $perfil = $this->perfilActual();
        $idProf = (int) $perfil['id_profesional'];
        $modelo = new Solicitud();

        try {
            if ($nuevoEstado === 'ACEPTADA') {
                if (!$modelo->reclamarTrabajo($idSolicitud, $idProf)) {
                    Flash::set('warning', 'Otro profesional aceptó este trabajo antes que tú, o ya no está disponible.');
                    $this->redirigir('/profesional/solicitudes');
                }
                // El tiempo estimado se guarda al aceptar
                if ($tiempo > 0) {
                    $modelo->guardarTiempoEstimado($idSolicitud, $idProf, $tiempo);
                }
                Flash::set('success', '¡Trabajo aceptado! Cuando salgas, pulsa "Iniciar viaje" para que el cliente te siga en el mapa.');
                $this->redirigir('/profesional/solicitudes?estado=ACTIVAS');
            }

            $modelo->cambiarEstado($idSolicitud, $nuevoEstado, $idProf, $tiempo > 0 ? $tiempo : null, $precio > 0 ? $precio : null);

            switch ($nuevoEstado) {
                case 'EN_CAMINO':
                    $this->redirigir('/profesional/mapaViaje/' . $idSolicitud);
                case 'EN_PROCESO':
                    Flash::set('success', 'Marcaste tu llegada. Cuando termines, finaliza el trabajo e ingresa el cobro.');
                    $this->redirigir('/profesional/mapaViaje/' . $idSolicitud);
                case 'FINALIZADA':
                    Flash::set('success', 'Trabajo finalizado. ¡Buen trabajo!');
                    $this->redirigir('/profesional/historial');
                default:
                    Flash::set('info', 'La solicitud fue cancelada.');
                    $this->redirigir('/profesional/solicitudes');
            }
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] cambiarEstadoSolicitud: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo actualizar la solicitud. Inténtalo de nuevo.');
        }
        $this->redirigir('/profesional/solicitudes?estado=ACTIVAS');
    }

    // ================= MAPA DE VIAJE =================
    public function mapaViaje($idSolicitud = 0)
    {
        $perfil    = $this->perfilActual();
        $solicitud = (new Solicitud())->obtenerPorId((int) $idSolicitud);

        if (!$solicitud || (int) $solicitud['id_profesional'] !== (int) $perfil['id_profesional']) {
            Flash::set('danger', 'No encontramos ese trabajo.');
            $this->redirigir('/profesional/solicitudes');
        }

        $this->view('profesional/mapa_viaje', [
            'titulo'    => 'FixGeo | Viaje al cliente',
            'perfil'    => $perfil,
            'solicitud' => $solicitud,
        ]);
    }

    // ================= AJAX =================
    public function checkNuevasSolicitudes()
    {
        if (!$this->esPost()) {
            $this->jsonResponse(['ok' => false], 405);
        }
        $perfil = $this->perfilActual();
        if ($perfil['estado_disponibilidad'] !== 'DISPONIBLE') {
            $this->jsonResponse(['ok' => true, 'nuevas' => 0]);
        }
        $this->jsonResponse(['ok' => true, 'nuevas' => count($this->nuevasSolicitudes($perfil, new Solicitud()))]);
    }

    public function actualizarGPS()
    {
        if (!$this->esPost()) {
            $this->jsonResponse(['ok' => false], 405);
        }
        $lat = filter_var($_POST['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($_POST['lng'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
            $this->jsonResponse(['ok' => false, 'error' => 'Coordenadas inválidas'], 422);
        }
        try {
            $this->panel->actualizarGps((int) $_SESSION['user_id'], $lat, $lng);
            $this->jsonResponse(['ok' => true]);
        } catch (Throwable $e) {
            error_log('[FixGeo] actualizarGPS: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false], 500);
        }
    }

    


    

    // ================= PERFIL =================
    public function perfil()
    {
        $perfil = $this->perfilActual();
        $idProf = (int) $perfil['id_profesional'];

        $this->view('profesional/perfil', [
            'titulo'     => 'FixGeo | Mi perfil',
            'perfil'     => $perfil,
            'documentos' => $this->panel->documentos($idProf),
            'stats'      => $this->panel->estadisticas($idProf),
        ]);
    }

    public function actualizarPerfil()
    {
        if (!$this->esPost()) {
            $this->redirigir('/profesional/perfil');
        }
        $perfil = $this->perfilActual();

        $datos = [
            'celular'       => trim((string) ($_POST['celular'] ?? '')),
            'macrodistrito' => (string) ($_POST['macrodistrito_base'] ?? ''),
            'zona'          => trim((string) ($_POST['zona_especifica'] ?? '')),
            'tarifa'        => (float) ($_POST['tarifa_base'] ?? 0),
            'descripcion'   => trim((string) ($_POST['descripcion_servicio'] ?? '')),
        ];

        $errores = Validador::errores([
            'celular' => Validador::celular($datos['celular']),
            'macro'   => isset(Catalogo::MACRODISTRITOS[$datos['macrodistrito']]) ? null : 'Selecciona tu macrodistrito.',
            'zona'    => (mb_strlen($datos['zona']) >= 3 && mb_strlen($datos['zona']) <= 150) ? null : 'Indica la zona donde trabajas.',
            'tarifa'  => ($datos['tarifa'] >= 0 && $datos['tarifa'] <= 9999) ? null : 'La tarifa debe estar entre 0 y 9999 Bs.',
            'desc'    => (mb_strlen($datos['descripcion']) >= 20 && mb_strlen($datos['descripcion']) <= 500) ? null : 'La descripción debe tener entre 20 y 500 caracteres.',
        ]);
        if ($errores) {
            Flash::set('danger', implode(' ', $errores));
            $this->redirigir('/profesional/perfil');
        }

        try {
            $this->panel->actualizarPerfil((int) $_SESSION['user_id'], (int) $perfil['id_profesional'], $datos);
            Flash::set('success', 'Tu perfil se actualizó correctamente.');
        } catch (PDOException $e) {
            Flash::set('danger', 'Ese número de celular ya está registrado en otra cuenta.');
        }
        $this->redirigir('/profesional/perfil');
    }

    // ================= PEDIR UN SERVICIO: ahora se hace desde el "Modo cliente" =================
    public function pedirServicio()
    {
        Flash::set('info', 'Para pedir un servicio usa el "Modo cliente": es la misma cuenta, con el mismo buscador.');
        $this->redirigir('/cliente/dashboard');
    }

    public function registrarPedidoServicio()
    {
        $this->pedirServicio();
    }










    // ================= TOKENS Y PLANES =================
        public function comprarTokens()
    {
        $perfil    = $this->perfilActual();
        $idProf    = (int) $perfil['id_profesional'];
        $pendiente = $this->panel->pagoPendiente($idProf);

        $this->view('profesional/comprar_tokens', [
            'titulo'        => 'FixGeo | Tokens y planes',
            'perfil'        => $perfil,
            'planes'        => $this->panel->planes(),
            'precioToken'   => $this->panel->precioPorToken(),
            'pendiente'     => $pendiente,
            'motivoBloqueo' => $this->panel->motivoBloqueoCompra($perfil, $pendiente),
            'ilimitado'     => $this->panel->tieneTokensIlimitados($perfil),
            'vencida'       => $this->panel->membresiaVencida($perfil),
            'pagos'         => $this->panel->ultimosPagos($idProf),
        ]);
    }

    public function registrarPago()
    {
        if (!$this->esPost()) {
            $this->redirigir('/profesional/comprarTokens');
        }

        $perfil = $this->perfilActual();
        $idProf = (int) $perfil['id_profesional'];

        $motivo = $this->panel->motivoBloqueoCompra($perfil, $this->panel->pagoPendiente($idProf));
        if ($motivo) {
            Flash::set('warning', $motivo);
            $this->redirigir('/profesional/comprarTokens');
        }

        $tipo   = (($_POST['tipo_compra'] ?? '') === 'PAQUETE') ? 'PAQUETE' : 'PLAN';
        $idItem = (int) ($tipo === 'PAQUETE' ? ($_POST['cantidad_tokens'] ?? 0) : ($_POST['id_plan'] ?? 0));
        $metodo = substr(trim((string) ($_POST['metodo_pago'] ?? 'QR_SIMPLE_BOLIVIA')), 0, 50);
        $codigo = substr(trim((string) ($_POST['codigo_comprobante'] ?? '')), 0, 100);

        if ($idItem <= 0) {
            Flash::set('danger', 'Elige un plan o la cantidad de tokens que quieres comprar.');
            $this->redirigir('/profesional/comprarTokens');
        }
        if ($codigo === '') {
            Flash::set('danger', 'Ingresa el código de tu comprobante de pago.');
            $this->redirigir('/profesional/comprarTokens');
        }

        $foto = null;
        if (isset($_FILES['comprobante_foto']) && $_FILES['comprobante_foto']['size'] > 0) {
            $resultado = $this->subirArchivoSeguro($_FILES['comprobante_foto'], __DIR__ . '/../../public/uploads/comprobantes');
            if (!$resultado['ok']) {
                Flash::set('danger', $resultado['error']);
                $this->redirigir('/profesional/comprarTokens');
            }
            $foto = $resultado['nombre_archivo'];
        }

        try {
            $idTransaccion = $this->panel->registrarPago($perfil, $tipo, $idItem, $metodo, $codigo, $foto);
            (new Usuario())->auditar((int) $_SESSION['user_id'], 'REPORTE_PAGO_ENVIADO', 'transacciones_suscripcion', $idTransaccion);
            Flash::set('success', 'Recibimos tu comprobante. Un administrador lo revisará y te avisaremos cuando esté confirmado.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (PDOException $e) {
            error_log('[FixGeo] registrarPago: ' . $e->getMessage());
            Flash::set('danger', 'Ese código de comprobante ya fue registrado o no se pudo guardar.');
        }
        $this->redirigir('/profesional/comprarTokens');
    }

    /** Ver la foto de MI comprobante (la carpeta es privada). */
    public function miComprobante($idTransaccion = 0)
    {
        $archivo = $this->panel->comprobanteDe((int) $idTransaccion, (int) $_SESSION['user_id']);
        if (!$archivo) {
            Flash::set('danger', 'No encontramos ese comprobante.');
            $this->redirigir('/profesional/comprarTokens');
        }
        $this->servirArchivoPrivado('comprobantes', $archivo);
    }


        /** El técnico corrige un documento que el administrador rechazó. */
    public function reenviarDocumento()
    {
        if (!$this->esPost()) {
            $this->redirigir('/profesional/perfil');
        }
        $perfil = $this->perfilActual();
        $doc    = $this->panel->documentoPropio((int) ($_POST['id_documento'] ?? 0), (int) $_SESSION['user_id']);

        if (!$doc || $doc['estado_revision'] !== 'RECHAZADO') {
            Flash::set('danger', 'Solo puedes volver a enviar documentos que fueron rechazados.');
            $this->redirigir('/profesional/perfil');
        }
        if (empty($_FILES['archivo']['size'])) {
            Flash::set('danger', 'Selecciona el archivo corregido.');
            $this->redirigir('/profesional/perfil');
        }

        $carpeta   = __DIR__ . '/../../public/uploads/documentos';
        $resultado = $this->subirArchivoSeguro($_FILES['archivo'], $carpeta);
        if (!$resultado['ok']) {
            Flash::set('danger', $resultado['error']);
            $this->redirigir('/profesional/perfil');
        }

        try {
            $this->panel->reemplazarDocumento((int) $doc['id_documento'], (int) $perfil['id_profesional'], 'uploads/documentos/' . $resultado['nombre_archivo']);

            $viejo = basename(str_replace('\\', '/', (string) $doc['archivo_url']));
            if ($viejo !== '' && is_file($carpeta . '/' . $viejo)) {
                @unlink($carpeta . '/' . $viejo);               // el documento rechazado ya no se conserva
            }
            foreach ($this->panel->idsAdministradores() as $idAdmin) {
                (new Notificacion())->crear((int) $idAdmin, 'DOCUMENTO_REENVIADO', 'Un técnico corrigió un documento y espera revisión.',
                    BASE_URL . '/admin/revisionTecnico/' . (int) $perfil['id_profesional']);
            }
            (new Usuario())->auditar((int) $_SESSION['user_id'], 'DOCUMENTO_REENVIADO', 'documentos_profesional', (int) $doc['id_documento']);
            Flash::set('success', 'Enviamos tu documento corregido. Un administrador lo revisará pronto.');
        } catch (Throwable $e) {
            error_log('[FixGeo] reenviarDocumento: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo guardar el documento. Inténtalo de nuevo.');
        }
        $this->redirigir('/profesional/perfil');
    }
}