<?php
// =========================
// CONFIGURACIÓN
// =========================
$host   = "151.80.4.227";
$dbname = "moviefli_tvapi";
$user   = "moviefli_user1";
$pass   = "Fo&*@0C{d!j4*Ut0";
$port   = 3306;

header('Content-Type: application/json; charset=utf-8');

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Obtener versión de MySQL
    $version = $pdo->query("SELECT VERSION() AS version")->fetch();

    // Leer la tabla de prueba
    $stmt = $pdo->query("SELECT id, mensaje FROM prueba LIMIT 1");
    $fila = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Conexión exitosa.",
        "mysql_version" => $version['version'],
        "tabla_prueba" => $fila
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

}
?>
