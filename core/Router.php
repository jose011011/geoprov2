<?php
/**
 * FixGeo - Router: /controlador/metodo/param1/param2...
 * Las rutas /api/... conservan su comportamiento original.
 */
class Router
{
    public function run(): void
    {
        $url = trim((string) ($_GET['url'] ?? ''), '/');
        $url = ($url === '') ? 'site/index' : (string) filter_var($url, FILTER_SANITIZE_URL);

        $partes = explode('/', $url);
        $esApi  = ($partes[0] === 'api');

        if ($esApi) {
            $controlador = 'Api' . ucfirst($partes[1] ?? 'Index') . 'Controller';
            $metodo      = $partes[2] ?? 'index';
            $params      = array_slice($partes, 3);
            $archivo     = __DIR__ . '/../app/controllers/api/' . $controlador . '.php';
        } else {
            $controlador = ucfirst($partes[0]) . 'Controller';
            $metodo      = $partes[1] ?? 'index';
            $params      = array_slice($partes, 2);
            $archivo     = __DIR__ . '/../app/controllers/' . $controlador . '.php';
        }

        $patron = '/^[A-Za-z][A-Za-z0-9_]*$/';
        if (!preg_match($patron, $controlador) || !preg_match($patron, $metodo)) {
            $this->enviarError(404, 'Ruta no válida.', $esApi);
        }
        if (!is_file($archivo)) {
            $this->enviarError(404, 'Controlador no encontrado.', $esApi);
        }

        require_once $archivo;
        if (!class_exists($controlador, false)) {
            $this->enviarError(500, "La clase {$controlador} no está definida.", $esApi);
        }

        try {
            $controller = new $controlador();
            if (!$this->esAccionPublica($controller, $metodo, $esApi)) {
                $this->enviarError(404, "El método {$metodo} no existe en {$controlador}.", $esApi);
            }
            call_user_func_array([$controller, $metodo], $params);
        } catch (ArgumentCountError $e) {
            error_log('[FixGeo] ' . $e->getMessage());
            $this->enviarError(404, 'Faltan datos en la dirección solicitada.', $esApi);
        } catch (Throwable $e) {
            error_log('[FixGeo] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            $detalle = APP_DEBUG
                ? $e->getMessage() . ' — ' . basename($e->getFile()) . ':' . $e->getLine()
                : 'Ocurrió un error inesperado.';
            $this->enviarError(500, $detalle, $esApi);
        }
    }

    /** En la web solo se pueden invocar métodos públicos propios del controlador (no los heredados de Controller). */
    private function esAccionPublica(object $controller, string $metodo, bool $esApi): bool
    {
        if ($esApi) {
            return is_callable([$controller, $metodo]); // comportamiento original de la API
        }
        if (!method_exists($controller, $metodo)) {
            return false;
        }
        $ref = new ReflectionMethod($controller, $metodo);
        return $ref->isPublic()
            && !$ref->isStatic()
            && !$ref->isConstructor()
            && !str_starts_with($metodo, '__')
            && $ref->getDeclaringClass()->getName() !== 'Controller';
    }

    private function enviarError(int $codigo, string $mensaje, bool $esApi): void
    {
        http_response_code($codigo);

        if ($esApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => "Error {$codigo}: {$mensaje}"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $titulo = $codigo === 404 ? 'No encontramos esa página' : 'Algo salió mal';
        $base   = defined('BASE_URL') ? BASE_URL : '';
        $detalle = htmlspecialchars((defined('APP_DEBUG') && APP_DEBUG) ? $mensaje : '', ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
           . '<title>FixGeo | ' . $titulo . '</title></head>'
           . '<body style="font-family:system-ui,sans-serif;background:#f6f8fc;color:#0f172a;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center;padding:1rem">'
           . '<div><div style="font-size:4rem;font-weight:800;color:#1d3fb4">' . $codigo . '</div>'
           . '<h1 style="margin:.25rem 0">' . $titulo . '</h1>'
           . ($detalle !== '' ? '<p style="color:#5b6b86">' . $detalle . '</p>' : '')
           . '<a href="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '/" style="display:inline-block;margin-top:1rem;background:#1d3fb4;color:#fff;padding:.7rem 1.4rem;border-radius:12px;text-decoration:none;font-weight:600">Volver al inicio</a>'
           . '</div></body></html>';
        exit;
    }
}