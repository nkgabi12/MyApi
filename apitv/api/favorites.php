<?php
/**
 * MovieFlixTV - Favorites API
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
        $sql = "SELECT * FROM favorite_lists WHERE user_id = ?";
        $params = [$authUser['id']];

        if ($profileId) {
            $sql .= " AND profile_id = ?";
            $params[] = $profileId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $favorites = $stmt->fetchAll();

        sendSuccess($favorites);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $input = getJsonInput();
    $contentType = $input['content_type'] ?? '';
    $contentId = $input['content_id'] ?? '';
    $profileId = $input['profile_id'] ?? null;

    if (empty($contentType) || empty($contentId)) {
        sendError("Content type and ID are required");
    }

    try {
        // First check if already exists
        $stmt = $db->prepare("SELECT id FROM favorite_lists WHERE user_id = ? AND content_type = ? AND content_id = ?");
        $stmt->execute([$authUser['id'], $contentType, $contentId]);

        if ($stmt->fetch()) {
            // Already in favorites, so remove it (toggle)
            $stmt = $db->prepare("DELETE FROM favorite_lists WHERE user_id = ? AND content_type = ? AND content_id = ?");
            $stmt->execute([$authUser['id'], $contentType, $contentId]);
            sendSuccess(null, "Removed from favorites");
        } else {
            // Add to favorites
            // We might need to fetch title and poster from movies/series table
            $title = "";
            $poster = "";

            if ($contentType === 'movie') {
                $stmt = $db->prepare("SELECT title, poster_url FROM movies WHERE id = ?");
                $stmt->execute([$contentId]);
                $content = $stmt->fetch();
                if ($content) {
                    $title = $content['title'];
                    $poster = $content['poster_url'];
                }
            } else {
                $stmt = $db->prepare("SELECT title, poster_url FROM series WHERE id = ?");
                $stmt->execute([$contentId]);
                $content = $stmt->fetch();
                if ($content) {
                    $title = $content['title'];
                    $poster = $content['poster_url'];
                }
            }

            $id = bin2hex(random_bytes(18)); // Simple UUID-like ID
            $stmt = $db->prepare("INSERT INTO favorite_lists (id, user_id, profile_id, content_type, content_id, content_title, poster_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $authUser['id'], $profileId, $contentType, $contentId, $title, $poster]);

            sendSuccess(null, "Added to favorites");
        }
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} else {
    sendError("Method not allowed", 405);
}
?>
