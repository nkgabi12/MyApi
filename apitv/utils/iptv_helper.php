<?php
/**
 * MovieFlixTV - Multi-Provider IPTV Helper
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return null;
        $data = json_decode($response, true);
        if (!is_array($data)) return null;

        $formatted = [];
        foreach ($data as $item) {
            $name = $item['name'] ?? 'Unknown';
            $country = self::guessCountry($name);

            // Build the stream URL and wrap it with our proxy
            $originalUrl = $p['server'] . "/live/" . $p['user'] . "/" . $p['pass'] . "/" . $item['stream_id'] . ".m3u8";
            $proxyUrl = "https://myapi-i5wf.onrender.com/api/proxy.php?url=" . urlencode($originalUrl);

            $formatted[] = [
                "id" => $p['name'] . "_" . $item['stream_id'],
                "name" => $name . " (" . $p['name'] . ")",
                "description" => $item['category_name'] ?? "",
                "logo_url" => $item['stream_icon'] ?? "",
                "stream_url" => $proxyUrl,
                "country" => $country,
                "category" => $item['category_name'] ?? "General",
                "provider" => $p['name']
            ];
        }
        return $formatted;
    }

    private static function guessCountry($name) {
        $name = strtoupper($name);
        if (strpos($name, 'AR:') !== false || strpos($name, 'ARGENTINA') !== false) return 'Argentina';
        if (strpos($name, 'MX:') !== false || strpos($name, 'MEXICO') !== false) return 'Mexico';
        if (strpos($name, 'ES:') !== false || strpos($name, 'ESPAÑA') !== false) return 'España';
        if (strpos($name, 'US:') !== false || strpos($name, 'USA') !== false) return 'USA';
        return 'Otros';
    }
}
?>
