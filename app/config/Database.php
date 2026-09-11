<?php
class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 10, // Espera máximo 10 segundos para conectar
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Timeout de 10s para locks de InnoDB (evita congelamiento en INSERTs)
            $this->conn->exec("SET SESSION innodb_lock_wait_timeout = 10");
            $this->conn->exec("SET SESSION wait_timeout = 30");
        } catch (PDOException $e) {
            die("Error crítico de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}