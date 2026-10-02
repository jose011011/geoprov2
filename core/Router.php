<?php
class Router {
    public function run() {
        // Obtenemos la URL solicitada; si viene vacía, enviamos a site/index
        $url = $_GET['url'] ?? 'site/index';
        
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $urlParts = explode('/', $url);

        $esApi = false;

        // ==========================================
        // 1. DETECTAR SI ES UNA PETICIÓN API
        // ==========================================
        if ($urlParts[0] === 'api') {
            $esApi = true;
            // Si es API, el controlador está en la posición 1 (ej: /api/auth/login -> 'auth')
            $baseName = isset($urlParts[1]) ? ucfirst($urlParts[1]) : 'Index';
            // Le agregamos el prefijo 'Api' para que busque ApiAuthController.php
            $controllerName = 'Api' . $baseName . 'Controller';
            
            $methodName = $urlParts[2] ?? 'index';
            $params = array_slice($urlParts, 3);
            
            // La ruta física de los controladores API
            $controllerFile = "../app/controllers/api/" . $controllerName . ".php";
            
        } else {
            // ==========================================
            // 2. PETICIÓN WEB NORMAL (HTML)
            // ==========================================
            $controllerName = ucfirst($urlParts[0]) . 'Controller';
            $methodName = $urlParts[1] ?? 'index';
            $params = array_slice($urlParts, 2);
            
            $controllerFile = "../app/controllers/" . $controllerName . ".php";
        }

        if ($urlParts[0] === 'admin' && $methodName === 'crearCategoria') {
            file_put_contents(__DIR__ . '/../public/debug_router.log', date('Y-m-d H:i:s') . " - HIT ROUTER - METHOD: {$_SERVER['REQUEST_METHOD']}\n", FILE_APPEND);
        }

        // ==========================================
        // 3. EJECUCIÓN DEL CONTROLADOR
        // ==========================================
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();

                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $params);
                } else {
                    $this->enviarError(404, "El método {$methodName} no existe en {$controllerName}.", $esApi);
                }
            } else {
                $this->enviarError(500, "La clase {$controllerName} no está definida.", $esApi);
            }
        } else {
            $this->enviarError(404, "Controlador no encontrado.", $esApi);
        }
    }

    /* ========================================================
       FUNCIÓN AUXILIAR: RESPUESTA DE ERRORES (JSON O HTML)
       ======================================================== */
    private function enviarError($codigo, $mensaje, $esApi) {
        http_response_code($codigo);
        
        if ($esApi) {
            // Si Flutter llamó a una ruta que no existe, le devolvemos JSON
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'error' => "Error {$codigo}: {$mensaje}"]);
        } else {
            // Si es la web, mostramos el HTML bonito
            echo "<div style='font-family: sans-serif; padding: 20px;'>
                    <h3>Error {$codigo}</h3>
                    <p>{$mensaje}</p>
                  </div>";
        }
        exit;
    }
}