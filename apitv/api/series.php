<?php
/**
 * MovieFlixTV - Series API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/iptv_helper.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $source = $_GET['source'] ?? 'premium';
    $seriesId = $_GET['id'] ?? null;

    if ($source === 'iptv') {
        $series = IPTVHelper::getVOD('get_series');
        sendSuccess($series);
    } else {
        if ($seriesId) {
            try {
                $stmt = $db->prepare("SELECT * FROM series WHERE id = ? AND status = 'active'");
                $stmt->execute([$seriesId]);
                $series = $stmt->fetch();
                if (!$series) sendError("Series not found", 404);

                $stmtEp = $db->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY season ASC, episode_number ASC");
                $stmtEp->execute([$seriesId]);
                $episodes = $stmtEp->fetchAll();

                $series['episodes'] = $episodes;
                sendSuccess($series);
            } catch (PDOException $e) {
                sendError("Database error: " . $e->getMessage(), 500);
            }
        } else {
            try {
                $stmt = $db->prepare("SELECT * FROM series WHERE status = 'active' ORDER BY created_at DESC");
                $stmt->execute();
                $seriesList = $stmt->fetchAll();
                sendSuccess($seriesList);
            } catch (PDOException $e) {
                sendError("Database error: " . $e->getMessage(), 500);
            }
        }
    }
} else {
    sendError("Method not allowed", 405);
}
