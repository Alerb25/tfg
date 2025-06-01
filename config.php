<?php
// config.php - Configuración de base de datos para Docker
// Coloca este archivo en la raíz de tu proyecto PHP

// Configuración de base de datos usando variables de entorno
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'db');
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'proyecto');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'proyecto');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'proyecto');

// Clase para manejar la conexión a la base de datos
class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            // Configurar charset
            $this->connection->exec("SET NAMES utf8");
            
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, inténtalo más tarde.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Función helper para obtener la conexión
function getDB() {
    return Database::getInstance()->getConnection();
}


// Configuración adicional
ini_set('display_errors', 0); // Desactivar en producción
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/logs/php_errors.log');

// Configuración de sesión segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
ini_set('session.use_strict_mode', 1);

// Configuración de timezone
date_default_timezone_set('Europe/Madrid');
?>