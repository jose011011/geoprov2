<?php
/**
 * FixGeo - Administración.
 * ADMIN (rol 2): técnicos, pagos, clientes, categorías, reportes.
 * SUPER_ADMIN (rol 1): además planes (precios) y auditoría.
 */
class AdminController extends Controller
{
    private AdminPanel $panel;

    public function __construct()
    {
        $this->requiereSesion([1, 2]);
        $this->panel = new AdminPanel();
    }

    private function idAdmin(): int
    {
        return (int) $_SESSION['user_id'];
    }

    private function soloSuperAdmin(): void
    {
        if ((int) $_SESSION['role_id'] !== 1) {
            Flash::set('danger', 'Esta sección es solo para el Super Administrador.');
            $this->redirigir('/admin/dashboard');
        }
    }

    private function soloPost(string $volver): void
    {
        if (!$this->esPost()) {
            $this->redirigir($volver);
        }
    }

    private function avisarUsuario(int $idUsuario, string $tipo, string $mensaje, string $ruta): void
    {
        try {
            (new Notificacion())->crear($idUsuario, $tipo, $mensaje, BASE_URL . $ruta);
        } catch (Throwable $e) {
            error_log('[FixGeo] aviso no enviado: ' . $e->getMessage());
        }
    }

    private function auditar(string $accion, string $tabla, ?int $registro, array $datos = []): void
    {
        try {
            (new Usuario())->auditar($this->idAdmin(), $accion, $tabla, $registro, $datos);
        } catch (Throwable $e) {
            error_log('[FixGeo] auditoría no registrada: ' . $e->getMessage());
        }
    }

    // ===================== PANEL =====================
    public function dashboard()
    {
        $this->view('admin/dashboard', [
            'titulo'  => 'FixGeo | Panel de administración',
            'ind'     => $this->panel->indicadores(),
            'estados' => $this->panel->solicitudesPorEstado(),
            'ultimas' => $this->panel->ultimasSolicitudes(),
        ]);
    }

    // ===================== TÉCNICOS =====================
    public function revisionTecnicos()
    {
        $filtro = strtoupper((string) ($_GET['estado'] ?? 'PENDIENTE'));
        if (!in_array($filtro, ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'TODOS'], true)) {
            $filtro = 'PENDIENTE';
        }
        $buscar = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60);

        $this->view('admin/revision_tecnicos', [
            'titulo'   => 'FixGeo | Validar técnicos',
            'tecnicos' => $this->panel->tecnicos($filtro, $buscar),
            'resumen'  => $this->panel->resumen(),
            'filtro'   => $filtro,
            'buscar'   => $buscar,
        ]);
    }

    public function revisionTecnico($idProfesional = 0)
    {
        $tecnico = $this->panel->tecnico((int) $idProfesional);
        if (!$tecnico) {
            Flash::set('danger', 'No encontramos a ese técnico.');
            $this->redirigir('/admin/revisionTecnicos');
        }
        $this->view('admin/revision_tecnico', [
            'titulo'     => 'FixGeo | Revisar técnico',
            'tecnico'    => $tecnico,
            'documentos' => $this->panel->documentos((int) $idProfesional),
        ]);
    }

    public function revisarDocumentoTecnico()
    {
        $this->soloPost('/admin/revisionTecnicos');
        $idDoc  = (int) ($_POST['id_documento'] ?? 0);
        $accion = (string) ($_POST['decision'] ?? '');
        $obs    = mb_substr(trim((string) ($_POST['observacion'] ?? '')), 0, 250);
        $volver = '/admin/revisionTecnico/' . (int) ($_POST['id_profesional'] ?? 0);

        try {
            $doc = $this->panel->revisarDocumento($idDoc, $accion, $obs, $this->idAdmin());
            $this->auditar('ADMIN_DOC_' . $accion, 'documentos_profesional', $idDoc, ['observacion' => $obs]);
            if ($accion === 'RECHAZADO') {
                $this->avisarUsuario((int) $doc['id_usuario'], 'DOCUMENTO_RECHAZADO', 'Un documento fue rechazado: ' . $obs, '/profesional/perfil');
            }
            Flash::set('success', $accion === 'APROBADO' ? 'Documento aprobado.' : 'Documento rechazado.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] revisarDocumentoTecnico: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo guardar la revisión.');
        }
        $this->redirigir($volver);
    }

    public function decidirValidacionTecnico()
    {
        $this->soloPost('/admin/revisionTecnicos');
        $idProf = (int) ($_POST['id_profesional'] ?? 0);
        $accion = (string) ($_POST['decision'] ?? '');
        $obs    = mb_substr(trim((string) ($_POST['observacion'] ?? '')), 0, 250);

        try {
            $t = $this->panel->decidirTecnico($idProf, $accion, $obs);
            $this->auditar('ADMIN_TECNICO_' . $accion, 'profesionales', $idProf, ['observacion' => $obs]);
            if ($accion === 'APROBAR') {
                $this->avisarUsuario((int) $t['id_usuario'], 'PERFIL_APROBADO', '¡Tu perfil fue aprobado! Ya puedes activarte y recibir trabajos.', '/profesional/dashboard');
                Flash::set('success', 'Técnico aprobado.');
            } else {
                $this->avisarUsuario((int) $t['id_usuario'], 'PERFIL_RECHAZADO', 'Tu perfil fue rechazado: ' . $obs, '/profesional/perfil');
                Flash::set('info', 'Técnico rechazado. Se le avisó el motivo.');
            }
            $this->redirigir('/admin/revisionTecnicos');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] decidirValidacionTecnico: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo guardar la decisión.');
        }
        $this->redirigir('/admin/revisionTecnico/' . $idProf);
    }

    // ===================== PAGOS =====================
    public function revisionPagos()
    {
        $filtro = strtoupper((string) ($_GET['estado'] ?? 'PENDIENTE'));
        if (!in_array($filtro, ['PENDIENTE', 'CONFIRMADO', 'FALLIDO', 'TODOS'], true)) {
            $filtro = 'PENDIENTE';
        }
        $this->view('admin/revision_pagos', [
            'titulo'  => 'FixGeo | Revisar pagos',
            'pagos'   => $this->panel->pagos($filtro),
            'resumen' => $this->panel->resumen(),
            'filtro'  => $filtro,
        ]);
    }

    public function decidirPagoSuscripcion()
    {
        $this->soloPost('/admin/revisionPagos');
        $idPago = (int) ($_POST['id_transaccion'] ?? 0);
        $accion = (string) ($_POST['decision'] ?? '');
        $obs    = mb_substr(trim((string) ($_POST['observacion'] ?? '')), 0, 250);

        try {
            $t = $this->panel->decidirPago($idPago, $accion, $obs, $this->idAdmin());
            $this->auditar('ADMIN_PAGO_' . $accion, 'transacciones_suscripcion', $idPago, ['monto' => $t['monto'], 'observacion' => $obs]);

            if ($accion === 'CONFIRMAR') {
                $texto = $t['tipo_transaccion'] === 'PAQUETE_TOKENS'
                    ? 'Tu pago fue confirmado: se sumaron ' . (int) $t['tokens_comprados'] . ' tokens a tu cuenta.'
                    : 'Tu pago fue confirmado: tu plan está activo hasta el ' . date('d/m/Y', strtotime($t['nuevo_fin'])) . '.';
                $this->avisarUsuario((int) $t['id_usuario'], 'PAGO_CONFIRMADO', $texto, '/profesional/comprarTokens');
                Flash::set('success', 'Pago confirmado y aplicado a la cuenta del técnico.');
            } else {
                $this->avisarUsuario((int) $t['id_usuario'], 'PAGO_RECHAZADO', 'Tu pago fue rechazado: ' . $obs, '/profesional/comprarTokens');
                Flash::set('info', 'Pago rechazado. Se le avisó el motivo al técnico.');
            }
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] decidirPagoSuscripcion: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo procesar el pago. No se aplicó ningún cambio.');
        }
        $this->redirigir('/admin/revisionPagos');
    }

    // ===================== ARCHIVOS PRIVADOS (solo administración) =====================
    public function verDocumento($idDocumento = 0)
    {
        $doc = $this->panel->documentoPorId((int) $idDocumento);
        if (!$doc) {
            http_response_code(404);
            exit('Documento no encontrado.');
        }
        $this->servirArchivoPrivado('documentos', (string) $doc['archivo_url']);
    }

    public function verComprobante($idTransaccion = 0)
    {
        $archivo = $this->panel->comprobante((int) $idTransaccion);
        if (!$archivo) {
            http_response_code(404);
            exit('Comprobante no encontrado.');
        }
        $this->servirArchivoPrivado('comprobantes', $archivo);
    }

    // ===================== CLIENTES =====================
    public function clientes()
    {
        $buscar = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60);
        $this->view('admin/clientes', [
            'titulo'   => 'FixGeo | Clientes',
            'clientes' => $this->panel->clientes($buscar),
            'buscar'   => $buscar,
        ]);
    }

    public function cambiarEstadoCliente()
    {
        $this->soloPost('/admin/clientes');
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $estado    = (string) ($_POST['estado'] ?? '');
        try {
            $this->panel->cambiarEstadoUsuario($idUsuario, $estado, $this->idAdmin());
            $this->auditar('ADMIN_USUARIO_' . $estado, 'usuarios', $idUsuario);
            Flash::set('success', $estado === 'BLOQUEADO' ? 'Cuenta bloqueada: ya no puede ingresar.' : 'Cuenta activada.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] cambiarEstadoCliente: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo cambiar el estado.');
        }
        $this->redirigir('/admin/clientes');
    }

    // ===================== CATEGORÍAS =====================
    public function categorias()
    {
        $this->view('admin/categorias', [
            'titulo'     => 'FixGeo | Categorías',
            'categorias' => $this->panel->categorias(),
        ]);
    }

    public function guardarCategoria()
    {
        $this->soloPost('/admin/categorias');
        $id = (int) ($_POST['id_categoria'] ?? 0);
        try {
            $this->panel->guardarCategoria($id, [
                'nombre'      => (string) ($_POST['nombre'] ?? ''),
                'descripcion' => (string) ($_POST['descripcion'] ?? ''),
                'tipo'        => (string) ($_POST['tipo'] ?? ''),
                'icono'       => (string) ($_POST['icono'] ?? ''),
            ]);
            $this->auditar($id > 0 ? 'ADMIN_CATEGORIA_EDITADA' : 'ADMIN_CATEGORIA_CREADA', 'categorias', $id > 0 ? $id : null, ['nombre' => $_POST['nombre'] ?? '']);
            Flash::set('success', $id > 0 ? 'Categoría actualizada.' : 'Categoría creada.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] guardarCategoria: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo guardar la categoría.');
        }
        $this->redirigir('/admin/categorias');
    }

    public function cambiarEstadoCategoria()
    {
        $this->soloPost('/admin/categorias');
        $id     = (int) ($_POST['id_categoria'] ?? 0);
        $estado = (int) ($_POST['estado'] ?? 0);
        try {
            $this->panel->cambiarEstadoCategoria($id, $estado);
            $this->auditar('ADMIN_CATEGORIA_ESTADO', 'categorias', $id, ['estado' => $estado]);
            Flash::set('success', $estado === 1 ? 'Categoría activada.' : 'Categoría desactivada: ya no aparece para los clientes.');
        } catch (Throwable $e) {
            error_log('[FixGeo] cambiarEstadoCategoria: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo cambiar el estado.');
        }
        $this->redirigir('/admin/categorias');
    }

    // ===================== PLANES (solo SUPER_ADMIN) =====================
    public function planes()
    {
        $this->soloSuperAdmin();
        $this->view('admin/planes', [
            'titulo' => 'FixGeo | Planes',
            'planes' => $this->panel->planes(),
        ]);
    }

    public function guardarPlan()
    {
        $this->soloSuperAdmin();
        $this->soloPost('/admin/planes');
        $id = (int) ($_POST['id_plan'] ?? 0);
        try {
           
            $this->panel->guardarPlan($id, [
                'precio'      => $_POST['precio'] ?? 0,
                'tokens'      => $_POST['tokens'] ?? 0,
                'descripcion' => (string) ($_POST['descripcion'] ?? ''),
                'estado'      => $_POST['estado'] ?? 1,
                'retraso'     => $_POST['retraso'] ?? 0,
            ]);

            $this->auditar('ADMIN_PLAN_EDITADO', 'planes_suscripcion', $id, ['precio' => $_POST['precio'] ?? null, 'tokens' => $_POST['tokens'] ?? null]);
            Flash::set('success', 'Plan actualizado.');
        } catch (DomainException $e) {
            Flash::set('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[FixGeo] guardarPlan: ' . $e->getMessage());
            Flash::set('danger', 'No se pudo guardar el plan.');
        }
        $this->redirigir('/admin/planes');
    }

    // ===================== REPORTES =====================
    public function reportes()
    {
        $this->view('admin/reportes', [
            'titulo'      => 'FixGeo | Reportes',
            'meses'       => $this->panel->reporteMensual(6),
            'top'         => $this->panel->topTecnicos(5),
            'porCategoria' => $this->panel->solicitudesPorCategoria(),
        ]);
    }

    // ===================== AUDITORÍA (solo SUPER_ADMIN) =====================
    public function auditoria()
    {
        $this->soloSuperAdmin();
        $buscar = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60);
        $this->view('admin/auditoria', [
            'titulo'  => 'FixGeo | Auditoría',
            'eventos' => $this->panel->auditoria($buscar),
            'buscar'  => $buscar,
        ]);
    }


    // Enlaces antiguos (las notificaciones de la base de datos todavía apuntan a estas rutas)
    public function profesionales()
    {
        $this->redirigir('/admin/revisionTecnicos');
    }

    public function pagos()
    {
        $this->redirigir('/admin/revisionPagos');
    }

    public function verProfesional($id = 0)
    {
        $this->redirigir('/admin/revisionTecnico/' . (int) $id);
    }






    // ===================== INFORME DEL SISTEMA (PDF por impresión + CSV) =====================
    private function rangoInforme(): array
    {
        $valido = fn($f) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $f) && strtotime((string) $f) !== false;
        $desde  = $valido($_GET['desde'] ?? '') ? (string) $_GET['desde'] : date('Y-m-01');
        $hasta  = $valido($_GET['hasta'] ?? '') ? (string) $_GET['hasta'] : date('Y-m-d');
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        if (strtotime($hasta) - strtotime($desde) > 366 * 86400) {          // máximo 1 año por informe
            $desde = date('Y-m-d', strtotime($hasta . ' -366 days'));
        }
        return [$desde, $hasta];
    }

    public function informe()
    {
        [$desde, $hasta] = $this->rangoInforme();
        $idCategoria = max(0, (int) ($_GET['categoria'] ?? 0));
        $this->auditar('ADMIN_INFORME_GENERADO', 'informes', null, ['desde' => $desde, 'hasta' => $hasta, 'categoria' => $idCategoria]);

        $this->view('admin/informe', [
            'titulo'      => 'FixGeo | Informe del sistema',
            'desde'       => $desde,
            'hasta'       => $hasta,
            'idCategoria' => $idCategoria,
            'categorias'  => $this->panel->categorias(),
            'datos'       => $this->panel->informe($desde, $hasta, $idCategoria),
            'generadoPor' => (string) ($_SESSION['user_nombre'] ?? ''),
        ]);
    }

    public function informeCsv()
    {
        [$desde, $hasta] = $this->rangoInforme();
        $idCategoria = max(0, (int) ($_GET['categoria'] ?? 0));
        $tipo  = (($_GET['tipo'] ?? '') === 'pagos') ? 'pagos' : 'solicitudes';
        $filas = $tipo === 'pagos'
            ? $this->panel->exportarPagos($desde, $hasta, $idCategoria)
            : $this->panel->exportarSolicitudes($desde, $hasta, $idCategoria);
        $this->auditar('ADMIN_EXPORTAR_' . strtoupper($tipo), 'informes', null, ['desde' => $desde, 'hasta' => $hasta, 'categoria' => $idCategoria, 'filas' => count($filas)]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="fixgeo_' . $tipo . '_' . $desde . '_' . $hasta . '.csv"');
        header('Cache-Control: private, no-store');
        $salida = fopen('php://output', 'w');
        fwrite($salida, "\xEF\xBB\xBF");
        if ($filas) {
            fputcsv($salida, array_keys($filas[0]), ';');
        }
        foreach ($filas as $fila) {
            fputcsv($salida, array_map([$this, 'celdaCsv'], $fila), ';');
        }
        fclose($salida);
        exit;
    }
    /** Evita que Excel ejecute fórmulas escondidas en textos que empiezan con = + - @ */
    private function celdaCsv($valor): string
    {
        $valor = (string) $valor;
        return preg_match('/^[=+\-@\t\r]/', $valor) ? "'" . $valor : $valor;
    }








    
}