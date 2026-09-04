<?php
class Controller {
    protected function model(string $model) {
        require_once "../app/models/" . $model . ".php";
        return new $model();
    }

    protected function view(string $view, array $data = []) {
        extract($data);
        if (file_exists("../app/views/" . $view . ".php")) {
            require_once "../app/views/" . $view . ".php";
        } else {
            die("La vista [" . $view . "] no existe.");
        }
    }

    protected function jsonResponse(array $data, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}