<?php
class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index()
    {
        $this->login();
    }

    // ================= LOGIN =================
    public function login()
    {
        $this->siYaHaySesion();
        $error = null;

        if ($this->esPost()) {
            $correo   = trim($_POST['correo'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $clave    = Security::claveIntentos($correo);
            $maximo   = APP_DEBUG ? 50 : 5;

            if ($correo === '' || $password === '') {
                $error = 'Completa tu correo y tu contraseña.';
            } elseif (Security::bloqueado($clave, $maximo)) {
                $error = 'Demasiados intentos fallidos. Espera 15 minutos e inténtalo de nuevo.';
            } else {
                $user = $this->usuarioModel->buscarPorCorreo($correo);
                // Siempre se verifica un hash, exista o no el usuario (evita adivinar correos por el tiempo de respuesta)
                $hash = $user['password'] ?? password_hash('fixgeo-relleno', PASSWORD_BCRYPT);

                if ($user && password_verify($password, $hash)) {
                    if ($user['estado'] !== 'ACTIVO') {
                        $error = 'Tu cuenta está inactiva o bloqueada. Contacta al soporte de FixGeo.';
                    } else {
                        Security::limpiar($clave);
                        Security::renovarSesion();

                        $_SESSION['user_id']     = (int) $user['id_usuario'];
                        $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
                        $_SESSION['user_correo'] = $user['correo'];
                        $_SESSION['role_id']     = (int) $user['id_rol'];

                        $this->usuarioModel->auditar((int) $user['id_usuario'], 'LOGIN_SUCCESS', 'usuarios', (int) $user['id_usuario']);
                        $this->redirigir($this->inicioPorRol((int) $user['id_rol']));
                    }
                } else {
                    Security::registrarFallo($clave);
                    $error = 'Credenciales incorrectas. Verifica tu correo y tu contraseña.';
                    $this->usuarioModel->auditar(null, 'LOGIN_FAILED', 'usuarios', null, ['correo_intentado' => $correo]);
                }
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    // ================= REGISTRO CLIENTE =================
    public function registroCliente()
    {
        $this->siYaHaySesion();
        $error = null;
        $old   = [];

        if ($this->esPost()) {
            $datos = $this->limpiar($_POST);
            $old   = $datos;
            unset($old['password'], $old['password_confirm']);

                        $error = $this->limiteRegistro() ?? $this->cuentaExistente($datos, 'cliente') ?? $this->unirErrores($this->validarCliente($datos));

            if ($error === null) {
                try {
                    $this->usuarioModel->registrarCliente($datos);
                    Flash::set('success', '¡Cuenta creada! Ya puedes ingresar.');
                    $this->redirigir('/auth/login');
                } catch (PDOException $e) {
                    error_log('[FixGeo] registroCliente: ' . $e->getMessage());
                                        $error = ($e->getCode() === '23000')
                        ? 'Ya existe una cuenta con ese correo, celular o documento. Si eres tú, ingresa con tu cuenta.'
                        : 'No pudimos completar tu registro. Inténtalo de nuevo en unos minutos.';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->view('auth/registro_cliente', ['error' => $error, 'old' => $old]);
    }

    // ================= REGISTRO PRESTADORES =================
    public function registroEmpirico()
    {
        $this->gestionarRegistroPrestador('OFICIO_EMPIRICO', 'auth/registro_empirico');
    }

    public function registroProfesional()
    {
        $this->gestionarRegistroPrestador('TECNICO_PROFESIONAL', 'auth/registro_profesional');
    }

    private function gestionarRegistroPrestador(string $tipo, string $vista): void
    {
        $this->siYaHaySesion();
        $error = null;
        $old   = [];

        $db = Database::getInstance()->getConnection();
        $tipoFiltro = ($tipo === 'TECNICO_PROFESIONAL') ? 'TECNICO' : 'EMPIRICO_OFICIO';
        $stmt = $db->prepare("SELECT id_categoria, nombre_categoria, tipo_clasificacion FROM categorias
                              WHERE estado = 1 AND (tipo_clasificacion = :tipo OR tipo_clasificacion = 'AMBOS')
                              ORDER BY nombre_categoria");
        $stmt->execute([':tipo' => $tipoFiltro]);
        $categorias = $stmt->fetchAll();

        if ($this->esPost()) {
            $datos = $this->limpiar($_POST);
            $datos['tipo_prestador'] = $tipo;
            $old = $datos;
            unset($old['password'], $old['password_confirm']);

            $error = $this->limiteRegistro() ?? $this->cuentaExistente($datos, 'prestador') ?? $this->unirErrores($this->validarPrestador($datos, $_FILES, $tipo));

            if ($error === null) {
                try {
                    $this->usuarioModel->registrarPrestador($datos, $_FILES);
                                        Flash::set('info', 'Registro enviado. Ya puedes ingresar; tu perfil aparecerá en el catálogo cuando un administrador valide tus documentos.');
                    $this->redirigir('/auth/login');
                } catch (PDOException $e) {
                    error_log('[FixGeo] registroPrestador: ' . $e->getMessage());
                                        $error = ($e->getCode() === '23000')
                        ? 'Ya existe una cuenta con ese correo, celular o documento. Si eres tú, ingresa con tu cuenta.'
                        : 'No pudimos completar tu registro. Inténtalo de nuevo en unos minutos.';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->view($vista, ['categorias' => $categorias, 'error' => $error, 'old' => $old]);
    }

    // ================= LOGOUT =================
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->usuarioModel->auditar((int) $_SESSION['user_id'], 'LOGOUT', 'usuarios', (int) $_SESSION['user_id']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    // ================= PRIVADOS =================
    private function siYaHaySesion(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirigir($this->inicioPorRol((int) ($_SESSION['role_id'] ?? 0)));
        }
    }

    /** Quita espacios de los textos (excepto contraseñas, donde un espacio es parte de la clave). */
    private function limpiar(array $entrada): array
    {
        $salida = [];
        foreach ($entrada as $clave => $valor) {
            $salida[$clave] = (is_string($valor) && !in_array($clave, ['password', 'password_confirm'], true))
                ? trim($valor)
                : $valor;
        }
        return $salida;
    }

    private function unirErrores(array $errores): ?string
    {
        return $errores ? implode("\n", array_values($errores)) : null;
    }

    private function limiteRegistro(): ?string
    {
        if (APP_DEBUG) {
            return null;
        }
        $clave = 'registro|' . ($_SERVER['REMOTE_ADDR'] ?? '');
        if (Security::bloqueado($clave, 10, 3600)) {
            return 'Demasiados registros desde tu conexión. Inténtalo más tarde.';
        }
        Security::registrarFallo($clave, 3600);
        return null;
    }

    /** Mismas reglas que public/js/auth-validation.js, ahora del lado del servidor. */
    private function validarPersona(array $d): array
    {
        return [
            'nombre'   => Validador::nombre($d['nombre'] ?? ''),
            'paterno'  => Validador::apellido($d['apellido_paterno'] ?? ''),
            'materno'  => Validador::apellido($d['apellido_materno'] ?? '', false),
            'correo'   => Validador::correo($d['correo'] ?? ''),
            'celular'  => Validador::celular($d['celular'] ?? ''),
            'password' => Validador::password((string) ($d['password'] ?? '')),
            'confirmar' => (($d['password'] ?? '') === ($d['password_confirm'] ?? ''))
                ? null : 'Las contraseñas no coinciden.',
            'macro'    => isset(Catalogo::MACRODISTRITOS[$d['macrodistrito'] ?? ''])
                ? null : 'Selecciona tu macrodistrito.',
        ];
    }

    private function validarCliente(array $d): array
    {
        $r = $this->validarPersona($d);
        $r['direccion'] = (mb_strlen($d['direccion'] ?? '') >= 5 && mb_strlen($d['direccion']) <= 255)
            ? null : 'Ingresa una dirección de referencia (mínimo 5 caracteres).';
        $r['zona'] = (mb_strlen($d['zona'] ?? '') >= 2 && mb_strlen($d['zona']) <= 100)
            ? null : 'Ingresa tu zona o barrio.';
        return Validador::errores($r);
    }

        private function validarPrestador(array $d, array $f, string $tipo): array
    {
        return Validador::errores($this->validarPersona($d)) + Validador::prestador($d, $f, $tipo);
    }
    /** Si el correo ya existe, explica cómo usar la misma cuenta en lugar de crear otra. */
    private function cuentaExistente(array $d, string $queQuiere): ?string
    {
        $u = $this->usuarioModel->buscarPorCorreo((string) ($d['correo'] ?? ''));
        if (!$u) {
            return null;
        }
        $rol = (int) $u['id_rol'];
        if ($queQuiere === 'prestador' && $rol === 4) {
            return 'Ese correo ya tiene una cuenta de cliente. No necesitas crear otra: ingresa y elige "Ofrecer mis servicios" en tu menú.';
        }
        if ($queQuiere === 'cliente' && $rol === 3) {
            return 'Ese correo ya tiene una cuenta de técnico. No necesitas crear otra: ingresa y usa el botón "Modo cliente".';
        }
        return 'Ese correo ya está registrado. Ingresa con tu cuenta.';
    }
}   