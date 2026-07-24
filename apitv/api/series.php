<?php
/**
 * MovieFlixTV - Series API
 */

require_once '../config/db.php';
require_once '../utils/response.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $seriesId = isset($_GET['id']) ? $_GET['id'] : null;

    if ($seriesId) {
        try {
            $stmt = $db->prepare("SELECT * FROM series WHERE id = ? AND status = 'active'");
            $stmt->execute([$seriesId]);
            $series = $stmt->fetch();

            if (!$series) {
                sendError("Series not found", 404);
            }

            $stmtEp = $db->prepare("SELECT id, series_id, season, episode_number, title, description, duration_minutes, video_url, thumbnail_url FROM episodes WHERE series_id = ? ORDER BY season ASC, episode_number ASC");
            $stmtEp->execute([$seriesId]);
            $episodes = $stmtEp->fetchAll();

            // Format series
            $result = [
                "id" => $series['id'],
                "title" => $series['title'],
                "description" => $series['description'],
                "genre" => $series['genre'],
                "year" => (int)$series['year'],
                "poster_url" => $series['poster_url'],
                "banner_url" => $series['banner_url'],
                "rating" => $series['rating'],
                "featured" => (bool)$series['featured'],
                "episodes" => $episodes
            ];

            sendSuccess($result);
        } catch (PDOException $e) {
            sendError("Database error: " . $e->getMessage(), 500);
        }
    } else {
        try {
            $stmt = $db->prepare("SELECT id, title, description, genre, year, poster_url, banner_url, rating, featured FROM series WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $seriesList = $stmt->fetchAll();

            $formattedSeries = array_map(function($s) {
                return [
                    "id" => $s['id'],
                    "title" => $s['title'],
                    "description" => $s['description'],
                    "genre" => $s['genre'],
                    "year" => (int)$s['year'],
                    "poster_url" => $s['poster_url'],
                    "banner_url" => $s['banner_url'],
                    "rating" => $s['rating'],
                    "featured" => (bool)$s['featured'],
                    "video_url" => "" // Series don't have a direct video_url, but MainFragment expects it for Movie object
                ];
            }, $seriesList);

            sendSuccess($formattedSeries);
        } catch (PDOException $e) {
            sendError("Database error: " . $e->getMessage(), 500);
        }
    }
} else {
    sendError("Method not allowed", 405);
}
?>
