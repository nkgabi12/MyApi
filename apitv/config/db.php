<?php
/**
 * MovieFlixTV - Database Configuration
 */

define('DB_HOST', 'sql302.infinityfree.com');
define('DB_NAME', 'if0_42012775_1movieflix_2');
define('DB_USER', 'if0_42012775');
define('DB_PASS', 'sQa5vi3rzrR33a');
define('DB_CHARSET', 'utf8mb4');
define('JWT_SECRET', 'https://1movie-flix.xo.je/');

// Seguridad para evitar ensuciar el JSON
error_reporting(0);
ini_set('display_errors', 0);
// Iniciar buffer de salida para poder limpiarlo si hay errores
ob_start();

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Si falla la conexión, enviamos JSON limpio inmediatamente
                header('Content-Type: application/json');
                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexión: " . $e->getMessage()
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}
