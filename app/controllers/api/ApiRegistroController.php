<?php
/**
 *   GET  /api/registro/disponibilidad?correo=&celular=&documento=
 *   POST /api/registro/cliente
 *   POST /api/registro/profesional   (tipo_prestador: TECNICO_PROFESIONAL | OFICIO_EMPIRICO)
 */
class ApiRegistroController {
    private PDO $db;

    private const MACROS = [
        'ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES',
        'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO'
    ];
    private const MAX_IMG = 5242880; // 5 MB por foto
    private const ROL_PROFESIONAL = 3;
    private const ROL_CLIENTE = 4;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* ==========================================================
       VERIFICAR DUPLICADOS (se usa en el paso 1 y 2 de la app)
       ========================================================== */
    public function disponibilidad() {
        try {
            $correo    = strtolower(trim((string) ($_GET['correo'] ?? '')));
            $celular   = trim((string) ($_GET['celular'] ?? ''));
            $documento = strtoupper(trim((string) ($_GET['documento'] ?? '')));

            $this->responder([
                'ok' => true,
                'conflictos' => $this->conflictosCuenta($correo, $celular, $documento),
            ]);
        } catch (Throwable $e) {
            $this->fallo($e);
        }
    }

    /* ==========================================================
       REGISTRO DE CLIENTE
       ========================================================== */
    public function cliente() {
        $this->soloPost();
        $d = $this->leerJson();
        $errores = [];

        $p = $this->validarPersona($d, $errores);

        $direccion = $this->limpiar($d['direccion_referencia'] ?? '');
        $zona      = $this->limpiar($d['zona'] ?? '');
        if (mb_strlen($direccion) < 8 || mb_strlen($direccion) > 255) {
            $errores[] = 'Escribe una dirección más exacta (mínimo 8 letras).';
        }
        if (mb_strlen($zona) < 3 || mb_strlen($zona) > 100) {
            $errores[] = 'Escribe tu zona o barrio (mínimo 3 letras).';
        }

        $lat = $d['latitud'] ?? null;
        $lng = $d['longitud'] ?? null;
        if ($lat !== null || $lng !== null) {
            if (!is_numeric($lat) || !is_numeric($lng)
                || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                $errores[] = 'La ubicación del mapa no es válida.';
            }
        }

        if ($errores) {
            $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
        }

        try {
            $conf = $this->conflictosCuenta($p['correo'], $p['celular'], null);
            if ($conf) {
                $this->responder(['ok' => false, 'error' => implode(' ', $conf)], 409);
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO usuarios (id_rol, nombre, apellido, correo, celular, password, estado)
                VALUES (:rol, :nom, :ape, :correo, :cel, :pass, 'ACTIVO')
            ");
            $stmt->execute([
                ':rol'    => self::ROL_CLIENTE,
                ':nom'    => $p['nombre'],
                ':ape'    => $p['apellido'],
                ':correo' => $p['correo'],
                ':cel'    => $p['celular'],
                ':pass'   => password_hash($p['password'], PASSWORD_BCRYPT),
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            $stmtC = $this->db->prepare("
                INSERT INTO clientes (id_usuario, direccion_referencia, zona,
                                      latitud_predeterminada, longitud_predeterminada)
                VALUES (:u, :dir, :zona, :lat, :lng)
            ");
            $stmtC->bindValue(':u', $idUsuario, PDO::PARAM_INT);
            $stmtC->bindValue(':dir', $direccion);
            $stmtC->bindValue(':zona', $zona);
            if ($lat === null) {
                $stmtC->bindValue(':lat', null, PDO::PARAM_NULL);
                $stmtC->bindValue(':lng', null, PDO::PARAM_NULL);
            } else {
                $stmtC->bindValue(':lat', (string) $lat);
                $stmtC->bindValue(':lng', (string) $lng);
            }
            $stmtC->execute();

            // El cliente entra directo: se le entrega su token
            $token = bin2hex(random_bytes(32));
            $stmtT = $this->db->prepare('UPDATE usuarios SET api_token = :t WHERE id_usuario = :id');
            $stmtT->execute([':t' => $token, ':id' => $idUsuario]);

            $this->auditar($idUsuario, 'REGISTRO_CLIENTE_APP', 'usuarios', $idUsuario, [
                'rol' => self::ROL_CLIENTE, 'origen' => 'app_movil'
            ]);

            $this->db->commit();

            $this->responder([
                'ok' => true,
                'token' => $token,
                'usuario' => [
                    'id_usuario' => $idUsuario,
                    'id_rol'     => self::ROL_CLIENTE,
                    'nombre'     => $p['nombre'],
                    'apellido'   => $p['apellido'],
                    'correo'     => $p['correo'],
                    'celular'    => $p['celular'],
                ],
            ], 201);
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->responder(['ok' => false, 'error' => 'Ese correo o celular ya está registrado.'], 409);
            }
            $this->fallo($e);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->fallo($e);
        }
    }

    /* ==========================================================
       REGISTRO DE PROFESIONAL (técnico titulado o trabajador de oficio)
       ========================================================== */
    public function profesional() {
        $this->soloPost();
        $d = $this->leerJson();
        $errores = [];

        $p = $this->validarPersona($d, $errores);

        $tipo = strtoupper(trim((string) ($d['tipo_prestador'] ?? '')));
        if (!in_array($tipo, ['TECNICO_PROFESIONAL', 'OFICIO_EMPIRICO'], true)) {
            $errores[] = 'Tipo de cuenta no válido.';
        }

        // Documento de identidad
        $tipoDoc = strtoupper(trim((string) ($d['tipo_documento'] ?? 'CI')));
        $numDoc  = strtoupper(trim((string) ($d['numero_documento'] ?? '')));
        if (!in_array($tipoDoc, ['CI', 'NIT', 'EXTRANJERO'], true)) {
            $errores[] = 'Tipo de documento no válido.';
                } elseif ($tipoDoc === 'CI' && !preg_match('/^[0-9A-Z\-]{5,12}$/', $numDoc)) {
            $errores[] = 'El carnet debe tener entre 5 y 12 caracteres (números, letras o guion).';
        } elseif ($tipoDoc === 'NIT' && !preg_match('/^\d{7,15}$/', $numDoc)) {
            $errores[] = 'El NIT debe tener entre 7 y 15 números.';
        } elseif ($tipoDoc === 'EXTRANJERO' && !preg_match('/^[A-Z0-9]{5,15}$/', $numDoc)) {
            $errores[] = 'El documento de extranjero debe tener entre 5 y 15 letras o números.';
        }

        // Perfil de trabajo
        $idCategoria = (int) ($d['id_categoria'] ?? 0);
        $experiencia = $d['experiencia_anios'] ?? null;
        $descripcion = $this->limpiar($d['descripcion_servicio'] ?? '');
        $macro       = strtoupper(trim((string) ($d['macrodistrito_base'] ?? '')));
        $zona        = $this->limpiar($d['zona_especifica'] ?? '');
        $tarifa      = $d['tarifa_base'] ?? null;

               if (!is_numeric($experiencia) || $experiencia < 0 || $experiencia > 50) {
            $errores[] = 'Los años de experiencia deben estar entre 0 y 50.';
        }
        $lenDesc = mb_strlen($descripcion);
        if ($lenDesc < 20 || $lenDesc > 600) {
            $errores[] = 'Describe tu servicio (entre 20 y 600 letras).';
        }
        if (!in_array($macro, self::MACROS, true)) {
            $errores[] = 'Elige un macrodistrito válido.';
        }
        if (mb_strlen($zona) < 3 || mb_strlen($zona) > 150) {
            $errores[] = 'Escribe tu zona de trabajo (mínimo 3 letras).';
        }
        if ($tarifa !== null && $tarifa !== '' && (!is_numeric($tarifa) || $tarifa < 0 || $tarifa > 10000)) {
            $errores[] = 'La tarifa base no es válida.';
        }
        $tarifa = ($tarifa === null || $tarifa === '') ? 0 : (float) $tarifa;

        // Fotos (se validan como imágenes reales)
        $imgA = $this->decodificarImagen($d['ci_anverso_base64'] ?? null, 'carnet (parte de adelante)', $errores);
        $imgR = $this->decodificarImagen($d['ci_reverso_base64'] ?? null, 'carnet (parte de atrás)', $errores);
        $imgT = null;
        if ($tipo === 'TECNICO_PROFESIONAL') {
            $imgT = $this->decodificarImagen($d['titulo_base64'] ?? null, 'título o certificado técnico', $errores);
        }

        if ($errores) {
            $this->responder(['ok' => false, 'error' => implode(' ', $errores)], 422);
        }

        $archivos = []; // para borrarlos si algo falla

        try {
            // Categoría compatible con el tipo de cuenta
            $stmtCat = $this->db->prepare('SELECT tipo_clasificacion FROM categorias WHERE id_categoria = :c AND estado = 1');
            $stmtCat->execute([':c' => $idCategoria]);
            $clasif = $stmtCat->fetchColumn();
            if (!$clasif) {
                $this->responder(['ok' => false, 'error' => 'La especialidad elegida no existe.'], 422);
            }
            $compatible = $clasif === 'AMBOS'
                || ($tipo === 'TECNICO_PROFESIONAL' && $clasif === 'TECNICO')
                || ($tipo === 'OFICIO_EMPIRICO' && $clasif === 'EMPIRICO_OFICIO');
            if (!$compatible) {
                $this->responder(['ok' => false, 'error' => 'Esa especialidad no corresponde a este tipo de cuenta.'], 422);
            }

            $conf = $this->conflictosCuenta($p['correo'], $p['celular'], $numDoc);
            if ($conf) {
                $this->responder(['ok' => false, 'error' => implode(' ', $conf)], 409);
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO usuarios (id_rol, nombre, apellido, correo, celular, password, estado)
                VALUES (:rol, :nom, :ape, :correo, :cel, :pass, 'ACTIVO')
            ");
            $stmt->execute([
                ':rol'    => self::ROL_PROFESIONAL,
                ':nom'    => $p['nombre'],
                ':ape'    => $p['apellido'],
                ':correo' => $p['correo'],
                ':cel'    => $p['celular'],
                ':pass'   => password_hash($p['password'], PASSWORD_BCRYPT),
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            // id_plan (gratuito) y tokens (5) toman su valor por defecto
            $stmtP = $this->db->prepare("
                INSERT INTO profesionales
                    (id_usuario, id_categoria, tipo_prestador, tipo_documento_identidad,
                     numero_documento, experiencia_anios, descripcion_servicio,
                     macrodistrito_base, zona_especifica, tarifa_base, estado_validacion)
                VALUES
                    (:u, :cat, :tipo, :tdoc, :ndoc, :exp, :desc, :macro, :zona, :tarifa, 'PENDIENTE')
            ");
            $stmtP->execute([
                ':u'      => $idUsuario,
                ':cat'    => $idCategoria,
                ':tipo'   => $tipo,
                ':tdoc'   => $tipoDoc,
                ':ndoc'   => $numDoc,
                ':exp'    => (int) $experiencia,
                ':desc'   => $descripcion,
                ':macro'  => $macro,
                ':zona'   => $zona,
                ':tarifa' => $tarifa,
            ]);
            $idProfesional = (int) $this->db->lastInsertId();

            // Archivos + filas en documentos_profesional (lo que revisa el admin)
            $docs = [
                ['tipo' => 'CI_ANVERSO', 'img' => $imgA],
                ['tipo' => 'CI_REVERSO', 'img' => $imgR],
            ];
            if ($imgT !== null) {
                $docs[] = ['tipo' => 'TITULO_TECNICO', 'img' => $imgT];
            }

            $dir = dirname(__DIR__, 3) . '/public/uploads/documentos/';
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new RuntimeException('No se pudo crear la carpeta de documentos.');
            }

            $stmtDoc = $this->db->prepare("
                INSERT INTO documentos_profesional (id_profesional, tipo_documento_archivo, archivo_url)
                VALUES (:p, :t, :u)
            ");
            foreach ($docs as $doc) {
                $nombre = 'DOC_' . $idProfesional . '_' . bin2hex(random_bytes(8)) . '.' . $doc['img']['ext'];
                $ruta = $dir . $nombre;
                if (file_put_contents($ruta, $doc['img']['bin']) === false) {
                    throw new RuntimeException('No se pudo guardar un documento.');
                }
                $archivos[] = $ruta;
                $stmtDoc->execute([
                    ':p' => $idProfesional,
                    ':t' => $doc['tipo'],
                    ':u' => 'uploads/documentos/' . $nombre,
                ]);
            }

            $this->auditar($idUsuario, 'REGISTRO_PROFESIONAL_APP', 'profesionales', $idProfesional, [
                'tipo_prestador' => $tipo, 'estado_validacion' => 'PENDIENTE', 'origen' => 'app_movil'
            ]);

            // Aviso a los administradores
            $stmtA = $this->db->prepare("
                INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                SELECT id_usuario, 'NUEVO_PROFESIONAL', :m, 'admin/profesionales'
                  FROM usuarios WHERE id_rol IN (1, 2) AND estado = 'ACTIVO'
            ");
            $stmtA->execute([':m' => 'Nuevo profesional pendiente de validar: ' . $p['nombre'] . ' ' . $p['apellido'] . '.']);

            $this->db->commit();

            $this->responder([
                'ok' => true,
                'mensaje' => 'Tu registro fue enviado. Nuestro equipo revisará tus documentos y te avisaremos cuando tu cuenta sea aprobada.',
            ], 201);
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            foreach ($archivos as $f) { @unlink($f); }
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->responder(['ok' => false, 'error' => 'Ese correo, celular o documento ya está registrado.'], 409);
            }
            $this->fallo($e);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            foreach ($archivos as $f) { @unlink($f); }
            $this->fallo($e);
        }
    }

    /* ==========================================================
       VALIDACIONES Y AUXILIARES
       ========================================================== */

    /** Valida nombre, apellidos, celular, correo y contraseña. Devuelve datos limpios. */
    private function validarPersona(array $d, array &$errores): array {
        $nombres = $this->limpiar($d['nombres'] ?? '');
        $apPat   = $this->limpiar($d['apellido_paterno'] ?? '');
        $apMat   = $this->limpiar($d['apellido_materno'] ?? '');
        $celular = trim((string) ($d['celular'] ?? ''));
        $correo  = strtolower(trim((string) ($d['correo'] ?? '')));
        $pass    = (string) ($d['password'] ?? '');

        if ($nombres === '') {
            $errores[] = 'Escribe tu nombre.';
        } else {
            $partes = preg_split('/\s+/u', $nombres);
            if (count($partes) > 2) {
                $errores[] = 'Escribe máximo 2 nombres.';
            }
            foreach ($partes as $parte) {
                if (!preg_match('/^\p{L}{2,}$/u', $parte)) {
                    $errores[] = 'El nombre solo puede tener letras (mínimo 2 por nombre).';
                    break;
                }
            }
        }

                if ($apPat === '') {
            $errores[] = 'Escribe tu apellido paterno.';
        } elseif (!$this->soloLetras($apPat, 1)) {
            $errores[] = 'El apellido paterno debe ser una sola palabra, solo letras.';
        }
        if ($apMat !== '' && !$this->soloLetras($apMat, 1)) {
            $errores[] = 'El apellido materno debe ser una sola palabra, solo letras.';
        }

        if (!preg_match('/^[67]\d{7}$/', $celular)) {
            $errores[] = 'El celular debe tener 8 números y empezar con 6 o 7.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
            $errores[] = 'El correo no es válido.';
        }
               if (!Utilidades::passwordValida($pass)) {
            $errores[] = Utilidades::mensajePassword();
        }

        $apellido = trim($apPat . ' ' . $apMat);
        if (mb_strlen($nombres) > 100 || mb_strlen($apellido) > 100) {
            $errores[] = 'El nombre o los apellidos son demasiado largos.';
        }

        return [
            'nombre'   => $this->titulo($nombres),
            'apellido' => $this->titulo($apellido),
            'celular'  => $celular,
            'correo'   => $correo,
            'password' => $pass,
        ];
    }

    private function soloLetras(string $texto, int $maxPalabras): bool {
        $partes = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        if (!$partes || count($partes) > $maxPalabras) return false;
        foreach ($partes as $p) {
            if (!preg_match('/^\p{L}+$/u', $p)) return false;
        }
        return true;
    }

    private function limpiar($valor): string {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $valor));
    }

    private function titulo(string $t): string {
        return mb_convert_case(mb_strtolower($t, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /** Devuelve la imagen validada ['bin' => ..., 'ext' => ...] o null (y agrega el error). */
    private function decodificarImagen($b64, string $etiqueta, array &$errores): ?array {
        if (!is_string($b64) || $b64 === '') {
            $errores[] = "Falta la foto: $etiqueta.";
            return null;
        }
        if (stripos($b64, 'base64,') !== false) {
            $b64 = substr($b64, stripos($b64, 'base64,') + 7);
        }
        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') {
            $errores[] = "La foto de $etiqueta no se pudo leer.";
            return null;
        }
        if (strlen($bin) > self::MAX_IMG) {
            $errores[] = "La foto de $etiqueta pesa más de 5 MB.";
            return null;
        }
        $info = @getimagesizefromstring($bin);
        if (!$info) {
            $errores[] = "El archivo de $etiqueta no es una imagen válida.";
            return null;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG: $ext = 'jpg';  break;
            case IMAGETYPE_PNG:  $ext = 'png';  break;
            case IMAGETYPE_WEBP: $ext = 'webp'; break;
            default:
                $errores[] = "La foto de $etiqueta debe ser JPG, PNG o WEBP.";
                return null;
        }
        return ['bin' => $bin, 'ext' => $ext];
    }

    private function conflictosCuenta(string $correo, string $celular, ?string $documento): array {
        $c = [];
        if ($correo !== '' && $this->existe('SELECT 1 FROM usuarios WHERE correo = :v LIMIT 1', $correo)) {
            $c[] = 'Ese correo ya tiene una cuenta. Usa otro correo o inicia sesión.';
        }
        if ($celular !== '' && $this->existe('SELECT 1 FROM usuarios WHERE celular = :v LIMIT 1', $celular)) {
            $c[] = 'Ese número de celular ya está registrado.';
        }
        if ($documento !== null && $documento !== ''
            && $this->existe('SELECT 1 FROM profesionales WHERE numero_documento = :v LIMIT 1', $documento)) {
            $c[] = 'Ese número de documento ya está registrado.';
        }
        return $c;
    }

    private function existe(string $sql, string $valor): bool {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':v' => $valor]);
        return (bool) $stmt->fetchColumn();
    }

    private function auditar(?int $idUsuario, string $accion, string $tabla, int $registroId, array $nuevos): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auditoria_logs
                    (id_usuario, accion, tabla_afectada, registro_id, valores_nuevos, ip_origen, user_agent)
                VALUES (:u, :a, :t, :r, :n, :ip, :ua)
            ");
            $stmt->execute([
                ':u'  => $idUsuario,
                ':a'  => $accion,
                ':t'  => $tabla,
                ':r'  => $registroId,
                ':n'  => json_encode($nuevos, JSON_UNESCAPED_UNICODE),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ':ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (Throwable $e) {
            error_log('[ApiRegistro] auditoria: ' . $e->getMessage());
        }
    }

    private function soloPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
        }
    }

    private function leerJson(): array {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }

    private function responder(array $data, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fallo(Throwable $e): void {
        error_log('[ApiRegistro] ' . $e->getMessage());
        $this->responder([
            'ok' => false,
            'error' => 'No pudimos completar tu registro. Intenta de nuevo en unos minutos.'
        ], 500);
    }
}