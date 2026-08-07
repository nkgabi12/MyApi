<?php
/**
 * MovieFlixTV - Professional IPTV Helper
 */

require_once __DIR__ . '/../config/iptv.php';

class IPTVHelper {

    private static function getProvider() {
        global $IPTV_PROVIDERS;
        // Forzamos el uso del primer proveedor (SV1) como principal
        return $IPTV_PROVIDERS[0];
    }

    public static function getCategories($action = 'get_live_categories') {
        $p = self::getProvider();
        $url = $p['server'] . "/player_api.php?username=" . $p['user'] . "&password=" . $p['pass'] . "&action=" . $action;

        return self::fetchData($url);
    }

    public static function getChannels() {
        $p = self::getProvider();
        $url = $p['server'] . "/player_api.php?username=" . $p['user'] . "&password=" . $p['pass'] . "&action=get_live_streams";

        $data = self::fetchData($url);
        if (!$data) return [];

        $formatted = [];
        foreach ($data as $item) {
            $streamUrl = $p['server'] . "/live/" . $p['user'] . "/" . $p['pass'] . "/" . $item['stream_id'] . ".m3u8";
            $formatted[] = [
                "id"            => $item['stream_id'],
                "name"          => $item['name'],
                "stream_icon"   => $item['stream_icon'] ?? "",
                "url"           => $streamUrl,
                "category_id"   => $item['category_id'] ?? "0",
                "category_name" => $item['category_name'] ?? "General"
            ];
        }
        return $formatted;
    }

    public static function getVOD($action = 'get_vod_streams') {
        $p = self::getProvider();
        $url = $p['server'] . "/player_api.php?username=" . $p['user'] . "&password=" . $p['pass'] . "&action=" . $action;

        $data = self::fetchData($url);
        if (!$data) return [];

        $formatted = [];
        $type = ($action === 'get_series') ? 'series' : 'movie';

        foreach ($data as $item) {
            $id = $item['stream_id'] ?? $item['series_id'] ?? 0;

            if ($type === 'movie') {
                $ext = $item['container_extension'] ?? 'mp4';
                $videoUrl = $p['server'] . "/movie/" . $p['user'] . "/" . $p['pass'] . "/" . $id . "." . $ext;
            } else {
                $videoUrl = "";
            }

            $formatted[] = [
                "id"            => $id,
                "title"         => $item['name'] ?? 'Sin título',
                "description"   => "Calificación: " . ($item['rating'] ?? 'N/A'),
                "poster_url"    => $item['stream_icon'] ?? $item['cover'] ?? "",
                "banner_url"    => $item['stream_icon'] ?? $item['cover'] ?? "",
                "video_url"     => $videoUrl,
                "category_id"   => $item['category_id'] ?? "0"
            ];
        }
        return $formatted;
    }

    private static function fetchData($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Aumentamos tiempo para listas grandes
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return null;
        return json_decode($response, true);
    }
}
