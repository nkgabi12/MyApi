<?php
/**
 * MovieFlixTV - Categories API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/iptv_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $type = $_GET['type'] ?? 'live';
        $action = ($type === 'movie') ? 'get_vod_categories' : (($type === 'series') ? 'get_series_categories' : 'get_live_categories');

        $categories = IPTVHelper::getCategories($action);
        sendSuccess($categories);
    } catch (Exception $e) {
        sendError($e->getMessage(), 500);
    }
} else {
    sendError("Method not allowed", 405);
}
