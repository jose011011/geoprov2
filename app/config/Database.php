<?php
class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 10,
            ]);
            $this->conn->exec('SET SESSION innodb_lock_wait_timeout = 10');
        } catch (PDOException $e) {
            error_log('[FixGeo] Conexión a BD fallida: ' . $e->getMessage());
            http_response_code(503);
            die(APP_DEBUG
                ? 'Error de conexión a la base de datos: ' . $e->getMessage()
                : 'El servicio no está disponible por el momento. Inténtalo en unos minutos.');
        }
    }

    public static function getInstance(): Database {
        return self::$instance ??= new Database();
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}