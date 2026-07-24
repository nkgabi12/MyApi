<?php
/**
 * MovieFlixTV - IPTV Provider Helper (Xtream Codes)
 */

require_once __DIR__ . '/../config/iptv.php';

class IPTVHelper {

    public static function getChannels() {
        if (file_exists(IPTV_CACHE_FILE) && (time() - filemtime(IPTV_CACHE_FILE) < IPTV_CACHE_TIME)) {
            return json_decode(file_get_contents(IPTV_CACHE_FILE), true);
        }

        $channels = self::fetchFromProvider();
        if ($channels) {
            if (!is_dir(dirname(IPTV_CACHE_FILE))) {
                mkdir(dirname(IPTV_CACHE_FILE), 0777, true);
            }
            file_put_contents(IPTV_CACHE_FILE, json_encode($channels));
            return $channels;
        }

        return [];
    }

    private static function fetchFromProvider() {
        $loginUrl = IPTV_SERVER . "/player_api.php?username=" . IPTV_USER . "&password=" . IPTV_PASS;
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

        $formattedChannels = [];
        foreach ($data as $item) {
            // Xtream codes usually provides category_id, we might need to fetch categories too
            // or parse country from the name (common in IPTV lists)

            $name = $item['name'] ?? 'Unknown';
            $country = self::guessCountry($name);

            $formattedChannels[] = [
                "id" => $item['stream_id'],
                "name" => $name,
                "description" => $item['category_name'] ?? "",
                "logo_url" => $item['stream_icon'] ?? "",
                "stream_url" => IPTV_SERVER . "/live/" . IPTV_USER . "/" . IPTV_PASS . "/" . $item['stream_id'] . ".m3u8",
                "country" => $country,
                "category" => $item['category_name'] ?? "General"
            ];
        }

        return $formattedChannels;
    }

    private static function guessCountry($name) {
        $name = strtoupper($name);
        if (strpos($name, 'AR:') !== false || strpos($name, 'ARGENTINA') !== false) return 'Argentina';
        if (strpos($name, 'MX:') !== false || strpos($name, 'MEXICO') !== false) return 'Mexico';
        if (strpos($name, 'ES:') !== false || strpos($name, 'ESPAÑA') !== false || strpos($name, 'SPAIN') !== false) return 'España';
        if (strpos($name, 'US:') !== false || strpos($name, 'USA') !== false) return 'USA';
        if (strpos($name, 'CL:') !== false || strpos($name, 'CHILE') !== false) return 'Chile';
        if (strpos($name, 'CO:') !== false || strpos($name, 'COLOMBIA') !== false) return 'Colombia';

        // Default extraction if format is [COUNTRY] Name
        if (preg_match('/^\[(.*?)\]/', $name, $matches)) {
            return $matches[1];
        }

        return 'Otros';
    }
}
?>
