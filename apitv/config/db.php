<?php
/**
 * MovieFlixTV - Database Configuration
 */

define('DB_HOST', '151.80.4.227');
define('DB_NAME', 'moviefli_tvapi');
define('DB_USER', 'moviefli_user1');
define('DB_PASS', 'Fo&*@0C{d!j4*Ut0');
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
