<?php
/**
 * MovieFlixTV - Movies API
 */

require_once '../config/db.php';
require_once '../utils/response.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, title, description, genre, year, duration_minutes, poster_url, banner_url, video_url, trailer_url, rating, featured FROM movies WHERE status = 'active' ORDER BY created_at DESC");
        $stmt->execute();
        $movies = $stmt->fetchAll();

        // Map database fields to API format (using snake_case as expected by the Android app)
        $formattedMovies = array_map(function($movie) {
            return [
                "id" => $movie['id'],
                "title" => $movie['title'],
                "description" => $movie['description'],
                "genre" => $movie['genre'],
                "year" => (int)$movie['year'],
                "duration_minutes" => (int)$movie['duration_minutes'],
                "poster_url" => $movie['poster_url'],
                "banner_url" => $movie['banner_url'],
                "video_url" => $movie['video_url'],
                "trailer_url" => $movie['trailer_url'],
                "rating" => $movie['rating'],
                "featured" => (bool)$movie['featured']
            ];
        }, $movies);

        sendSuccess($formattedMovies);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} else {
    sendError("Method not allowed", 405);
}
?>
