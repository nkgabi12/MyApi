<?php
/**
 * MovieFlixTV - Channels API (Pure IPTV Integration)
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/request.php';
require_once '../utils/iptv_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $requestedCountry = $_GET['country'] ?? null;

        // Fetch all channels from IPTV Provider (Xtream Codes)
        // The helper handles caching to avoid hitting the provider too often
        $allChannels = IPTVHelper::getChannels();

        if (empty($allChannels)) {
            sendSuccess([], "No channels found from provider");
        }

        // Filter by country if requested
        if ($requestedCountry) {
            $filtered = array_filter($allChannels, function($c) use ($requestedCountry) {
                // Case insensitive match
                return strcasecmp($c['country'], $requestedCountry) === 0;
            });
            $allChannels = array_values($filtered);
        }

        // If no country is requested, we might want to limit the total number
        // to avoid huge JSON responses if there are thousands of channels.
        // However, for TV apps, usually they want the full list to filter locally.

        sendSuccess($allChannels);
    } catch (Exception $e) {
        sendError("IPTV Error: " . $e->getMessage(), 500);
    }
} else {
    sendError("Method not allowed", 405);
}
?>
