<?php
class Router {
    public function run() {
        // Obtenemos la URL solicitada; si viene vacía, enviamos al Login
        #$url = $_GET['url'] ?? 'auth/login';
        $url = $_GET['url'] ?? 'site/index';
        
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $urlParts = explode('/', $url);

        // Controlador: primera parte (ej: 'auth' -> 'AuthController')
        $controllerName = ucfirst($urlParts[0]) . 'Controller';
        
        // Método / Acción: segunda parte (ej: 'login' o 'index' por defecto)
        $methodName = $urlParts[1] ?? 'index';
        
        // Parámetros adicionales
        $params = array_slice($urlParts, 2);

        $controllerFile = "../app/controllers/" . $controllerName . ".php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();

                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $params);
                } else {
                    http_response_code(404);
                    echo "<div style='font-family: sans-serif; padding: 20px;'><h3>Error 404</h3><p>El método <strong>{$methodName}</strong> no existe en el controlador <strong>{$controllerName}</strong>.</p></div>";
                }
            } else {
                http_response_code(500);
                echo "<div style='font-family: sans-serif; padding: 20px;'><h3>Error 500</h3><p>La clase <strong>{$controllerName}</strong> no está definida.</p></div>";
            }
        } else {
            http_response_code(404);
            echo "<div style='font-family: sans-serif; padding: 20px;'><h3>Error 404</h3><p>Controlador no encontrado: <code>{$controllerFile}</code></p></div>";
        }
    }
}