<?php
/**
 * MovieFlixTV - Ultra Fast IPTV Helper
 */

require_once __DIR__ . '/../config/iptv.php';

class IPTVHelper {

    public static function getChannels() {
        if (file_exists(IPTV_CACHE_FILE) && (time() - filemtime(IPTV_CACHE_FILE) < IPTV_CACHE_TIME)) {
            return json_decode(file_get_contents(IPTV_CACHE_FILE), true);
        }

        global $IPTV_PROVIDERS;
        $allChannels = [];

        foreach ($IPTV_PROVIDERS as $provider) {
            $channels = self::fetchFromProvider($provider);
            if ($channels) {
                $allChannels = array_merge($allChannels, $channels);
            }
        }

        if (!empty($allChannels)) {
            if (!is_dir(dirname(IPTV_CACHE_FILE))) {
                mkdir(dirname(IPTV_CACHE_FILE), 0777, true);
            }
            file_put_contents(IPTV_CACHE_FILE, json_encode($allChannels));
        }

        return $allChannels;
    }

    private static function fetchFromProvider($p) {
        $loginUrl = $p['server'] . "/player_api.php?username=" . $p['user'] . "&password=" . $p['pass'];
        $streamsUrl = $loginUrl . "&action=get_live_streams";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $streamsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return null;
        $data = json_decode($response, true);
        if (!is_array($data)) return null;

        $formatted = [];
        foreach ($data as $item) {
            // Raw mapping for maximum speed
            $formatted[] = [
                "id" => $p['name'] . "_" . $item['stream_id'],
                "name" => $item['name'] ?? 'Unknown',
                "logo" => $item['stream_icon'] ?? "",
                "url" => $p['server'] . "/live/" . $p['user'] . "/" . $p['pass'] . "/" . $item['stream_id'] . ".m3u8",
                "cat" => $item['category_name'] ?? "General"
            ];
        }
        return $formatted;
    }
}
