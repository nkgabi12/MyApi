<?php
/**
 * MovieFlixTV - Watch History API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/request.php';

$authUser = requireAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $profileId = $_GET['profile_id'] ?? null;

    try {
        // Obtenemos el historial ordenado por el más reciente
        // Se intenta unir con movies, pero si no existe (es IPTV), usamos los campos guardados en watch_history
        $sql = "SELECT h.position, h.duration, h.content_id as id,
                       IFNULL(m.title, h.title) as title,
                       IFNULL(m.description, '') as description,
                       IFNULL(m.poster_url, h.poster_url) as poster_url,
                       IFNULL(m.banner_url, h.poster_url) as banner_url,
                       IFNULL(m.video_url, '') as video_url
                FROM watch_history h
                LEFT JOIN movies m ON h.content_id = m.id AND h.content_type = 'movie'
                WHERE h.user_id = ?";
        $params = [$authUser['id']];

        if ($profileId) {
            $sql .= " AND h.profile_id = ?";
            $params[] = $profileId;
        }

        $sql .= " ORDER BY h.updated_at DESC LIMIT 20";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll();

        sendSuccess($history);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $input = getJsonInput();
    $contentId = $input['item_id'] ?? '';
    $position = $input['position'] ?? 0;
    $duration = $input['duration'] ?? 0;
    $title = $input['title'] ?? null;
    $poster = $input['poster_url'] ?? null;
    $profileId = $input['profile_id'] ?? null;
    $contentType = 'movie';

    if (empty($contentId)) {
        sendError("Content ID is required");
    }

    try {
        $stmt = $db->prepare("INSERT INTO watch_history (user_id, profile_id, content_type, content_id, position, duration, title, poster_url)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE
                               position = ?,
                               duration = ?,
                               title = IFNULL(?, title),
                               poster_url = IFNULL(?, poster_url),
                               updated_at = CURRENT_TIMESTAMP");

        $stmt->execute([
            $authUser['id'],
            $profileId,
            $contentType,
            $contentId,
            $position,
            $duration,
            $title,
            $poster,
            $position,
            $duration,
            $title,
            $poster
        ]);

        sendSuccess(null, "Progress saved");
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}
else {
    sendError("Method not allowed", 405);
}
?>
