<?php
class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorCorreo(string $correo): ?array {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function registrarCliente(array $data): int {
        $this->validarSeguridadDatos($data);
           $this->validarDuplicados($data);  
        $this->db->beginTransaction();

        try {
            $passHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $apellidos = trim($data['apellido_paterno'] . ' ' . ($data['apellido_materno'] ?? ''));

            $stmtUser = $this->db->prepare("
                INSERT INTO usuarios (id_rol, nombre, apellido, correo, celular, password, estado)
                VALUES (4, :nombre, :apellido, :correo, :celular, :password, 'ACTIVO')
            ");
            $stmtUser->execute([
                ':nombre'   => trim($data['nombre']),
                ':apellido' => $apellidos,
                ':correo'   => trim($data['correo']),
                ':celular'  => trim($data['celular']),
                ':password' => $passHash
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            $stmtCli = $this->db->prepare("
                INSERT INTO clientes (id_usuario, direccion_referencia, zona, latitud_predeterminada, longitud_predeterminada)
                VALUES (:id_usuario, :direccion, :zona, :lat, :lng)
            ");
            $stmtCli->execute([
                ':id_usuario' => $idUsuario,
                ':direccion'  => trim($data['direccion']),
                ':zona'       => $data['zona'],
                ':lat'        => !empty($data['latitud']) ? (float)$data['latitud'] : -16.50000000,
                ':lng'        => !empty($data['longitud']) ? (float)$data['longitud'] : -68.15000000
            ]);

            $this->auditar($idUsuario, 'REGISTRO_CLIENTE', 'usuarios', $idUsuario, ['zona' => $data['zona']]);
            $this->db->commit();
            return $idUsuario;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registrarPrestador(array $data, array $archivos): int {
        $this->validarSeguridadDatos($data);
           $this->validarDuplicados($data);  
        $this->db->beginTransaction();
        try {
            $passHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $apellidos = trim($data['apellido_paterno'] . ' ' . ($data['apellido_materno'] ?? ''));

            // 1. Insertar usuario base (Rol 3 = PROFESIONAL / PRESTADOR)
            $stmtUser = $this->db->prepare("
                INSERT INTO usuarios (id_rol, nombre, apellido, correo, celular, password, estado)
                VALUES (3, :nombre, :apellido, :correo, :celular, :password, 'ACTIVO')
            ");
            $stmtUser->execute([
                ':nombre'   => trim($data['nombre']),
                ':apellido' => $apellidos,
                ':correo'   => trim($data['correo']),
                ':celular'  => trim($data['celular']),
                ':password' => $passHash
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            // 2. Gestionar categoría (si eligió "OTRO", se crea o asigna)
            $idCategoria = $this->obtenerOCrearCategoria($data);

            // 3. Registrar tabla profesionales
            $stmtProf = $this->db->prepare("
                INSERT INTO profesionales (
                    id_usuario, id_categoria, id_plan, tipo_prestador, tipo_documento_identidad,
                    numero_documento, experiencia_anios, descripcion_servicio, macrodistrito_base,
                    zona_especifica, tarifa_base, estado_validacion, estado_disponibilidad,
                    latitud_actual, longitud_actual
                ) VALUES (
                    :id_usuario, :id_categoria, 1, :tipo_prestador, :tipo_doc,
                    :num_doc, :experiencia, :descripcion, :macrodistrito,
                    :zona_especifica, :tarifa, 'PENDIENTE', 'NO_DISPONIBLE',
                    :lat, :lng
                )
            ");
            $stmtProf->execute([
                ':id_usuario'       => $idUsuario,
                ':id_categoria'     => $idCategoria,
                ':tipo_prestador'   => $data['tipo_prestador'],
                ':tipo_doc'         => $data['tipo_doc'],
                ':num_doc'          => trim($data['numero_documento']),
                ':experiencia'      => (int) $data['experiencia'],
                ':descripcion'      => trim($data['descripcion']),
                ':macrodistrito'    => $data['macrodistrito'],
                ':zona_especifica'  => trim($data['zona_especifica']),
                ':tarifa'           => !empty($data['tarifa']) ? (float)$data['tarifa'] : 0.00,
                ':lat'              => !empty($data['latitud']) ? (float)$data['latitud'] : -16.50000000,
                ':lng'              => !empty($data['longitud']) ? (float)$data['longitud'] : -68.15000000
            ]);
            $idProfesional = (int) $this->db->lastInsertId();

            // 4. Guardar archivos documentales validados
            $this->procesarDocumentos($idProfesional, $archivos, $data['tipo_prestador']);

            $this->auditar($idUsuario, 'REGISTRO_PRESTADOR', 'profesionales', $idProfesional, [
                'tipo_prestador' => $data['tipo_prestador'],
                'macrodistrito'  => $data['macrodistrito']
            ]);

            $this->db->commit();
            return $idUsuario;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

private function validarSeguridadDatos(array $data): void {
        // Validar celular boliviano
        if (!preg_match('/^[67]\d{7}$/', $data['celular'])) {
            throw new Exception("El número celular en Bolivia debe tener 8 dígitos y comenzar con 6 o 7.");
        }
        
        // Validar contraseña (AQUÍ SE CORRIGIÓ LA REGLA PARA ACEPTAR EL PUNTO)
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_.,])[A-Za-z\d@$!%*?&\-_.,]{8,}$/', $data['password'])) {
            throw new Exception("La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.");
        }
        
        // Validar coincidencia si existe el campo de confirmación
        if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
            throw new Exception("Las contraseñas no coinciden.");
        }
    }

    private function obtenerOCrearCategoria(array $data): int {
        if ($data['id_categoria'] === 'OTRO') {
            $nuevaCat = trim($data['otra_especialidad'] ?? 'General');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nuevaCat)));
            
            $stmtCheck = $this->db->prepare("SELECT id_categoria FROM categorias WHERE slug = :slug LIMIT 1");
            $stmtCheck->execute([':slug' => $slug]);
            $catExistente = $stmtCheck->fetch();

            if ($catExistente) {
                return (int) $catExistente['id_categoria'];
            }

            $stmtIns = $this->db->prepare("
                INSERT INTO categorias (nombre_categoria, slug, tipo_clasificacion, descripcion, estado)
                VALUES (:nombre, :slug, :tipo, :desc, 1)
            ");
            $tipoCat = ($data['tipo_prestador'] === 'TECNICO_PROFESIONAL') ? 'TECNICO' : 'EMPIRICO_OFICIO';
            $stmtIns->execute([
                ':nombre' => $nuevaCat,
                ':slug'   => $slug,
                ':tipo'   => $tipoCat,
                ':desc'   => 'Especialidad ingresada por el prestador'
            ]);
            return (int) $this->db->lastInsertId();
        }
        return (int) $data['id_categoria'];
    }

    private function procesarDocumentos(int $idProfesional, array $archivos, string $tipoPrestador): void {
        $carpetaDestino = '../public/uploads/documentos/';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        $mimesPermitidos = ['image/jpeg', 'image/png', 'application/pdf'];

        foreach ($archivos as $nombreCampo => $archivo) {
            if (empty($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($archivo['size'] > 5 * 1024 * 1024) {
                throw new Exception("El archivo supera el tamaño límite permitido de 5 MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);

            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $extensionesPermitidas) || !in_array($mimeReal, $mimesPermitidos)) {
                throw new Exception("Formato inválido en {$nombreCampo}. Solo se permiten imágenes JPG, PNG o documentos PDF.");
            }

            $nombreArchivoSeguro = 'DOC_' . $idProfesional . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $rutaCompleta = $carpetaDestino . $nombreArchivoSeguro;

            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                $tipoDocDb = 'OTRO';
                if ($nombreCampo === 'doc_ci_anverso') $tipoDocDb = 'CI_ANVERSO';
                if ($nombreCampo === 'doc_ci_reverso') $tipoDocDb = 'CI_REVERSO';
                if ($nombreCampo === 'doc_titulo')     $tipoDocDb = 'TITULO_TECNICO';
                if ($nombreCampo === 'doc_antecedentes')$tipoDocDb = 'CERTIFICADO_ANTECEDENTES';
                if ($nombreCampo === 'doc_referencias') $tipoDocDb = 'REFERENCIA_LABORAL';

                $stmtDoc = $this->db->prepare("
                    INSERT INTO documentos_profesional (id_profesional, tipo_documento_archivo, archivo_url, estado_revision)
                    VALUES (:id_prof, :tipo, :url, 'PENDIENTE')
                ");
                $stmtDoc->execute([
                    ':id_prof' => $idProfesional,
                    ':tipo'    => $tipoDocDb,
                    ':url'     => 'uploads/documentos/' . $nombreArchivoSeguro
                ]);
            }
        }
    }

    public function auditar(?int $idUsuario, string $accion, string $tabla, ?int $registroId, array $detalles = []): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auditoria_logs (id_usuario, accion, tabla_afectada, registro_id, valores_nuevos, ip_origen, user_agent)
                VALUES (:id_user, :accion, :tabla, :reg_id, :valores, :ip, :ua)
            ");
            $stmt->execute([
                ':id_user'  => $idUsuario,
                ':accion'   => $accion,
                ':tabla'    => $tabla,
                ':reg_id'   => $registroId,
                ':valores'  => json_encode($detalles, JSON_UNESCAPED_UNICODE),
                ':ip'       => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':ua'       => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255)
            ]);
        } catch (Exception $ignored) {}
    }
    private function validarDuplicados(array $data, ?string $numeroDocumento = null): void {
        // Correo
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => trim($data['correo'])]);
        if ($stmt->fetch()) {
            throw new Exception("Este correo electrónico ya está registrado. ¿Ya tienes una cuenta? Intenta iniciar sesión.");
        }

        // Celular
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE celular = :celular LIMIT 1");
        $stmt->execute([':celular' => trim($data['celular'])]);
        if ($stmt->fetch()) {
            throw new Exception("Este número de celular ya está registrado en GEO-PRO.");
        }

        // Número de documento (solo aplica a prestadores)
        if ($numeroDocumento !== null) {
            $stmt = $this->db->prepare("SELECT id_profesional FROM profesionales WHERE numero_documento = :doc LIMIT 1");
            $stmt->execute([':doc' => trim($numeroDocumento)]);
            if ($stmt->fetch()) {
                throw new Exception("Este número de documento (CI/NIT) ya está registrado por otro profesional.");
            }
        }
    }
}