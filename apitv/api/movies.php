<?php
/**
 * MovieFlixTV - Movies API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/iptv_helper.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $source = $_GET['source'] ?? 'premium';

    if ($source === 'iptv') {
        $movies = IPTVHelper::getVOD('get_vod_streams');
        sendSuccess($movies);
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM movies WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $movies = $stmt->fetchAll();
            sendSuccess($movies);
        } catch (PDOException $e) {
            sendError("Database error: " . $e->getMessage(), 500);
        }
    }
} else {
    sendError("Method not allowed", 405);
}
