<?php
/**
 * MovieFlixTV - Sistema de Historial (Continuar Viendo)
 */

header('Content-Type: application/json');

// Aquí deberías incluir tu conexión a BD y validación de sesión
// require_once 'db_connection.php';
// $user_id = $_SESSION['user_id']; 

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // RECIBIR PROGRESO DESDE LA APP
    $input = json_decode(file_get_contents('php://input'), true);
    
    $itemId   = $input['item_id'] ?? null;
    $position = $input['position'] ?? 0; // En milisegundos
    $duration = $input['duration'] ?? 0;

    if ($itemId) {
        // LÓGICA: Guardar o actualizar en tu tabla "history"
        // SQL EJEMPLO: 
        // INSERT INTO history (user_id, item_id, position, duration, last_watch) 
        // VALUES ($user_id, '$itemId', $position, $duration, NOW())
        // ON DUPLICATE KEY UPDATE position=$position, last_watch=NOW();
        
        echo json_encode(["status" => "success", "message" => "Progreso guardado"]);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    }

} else if ($method === 'GET') {
    // ENVIAR LISTA DE "CONTINUAR VIENDO" A LA APP
    // SQL EJEMPLO:
    // SELECT h.*, m.title, m.poster_url, m.video_url 
    // FROM history h 
    // JOIN movies m ON h.item_id = m.id 
    // WHERE h.user_id = $user_id ORDER BY h.last_watch DESC LIMIT 10;

    // Por ahora, devolvemos un array vacío o de prueba para que la App no de error
    $historyList = []; 
    
    echo json_encode(["status" => "success", "data" => $historyList]);
}
?>
