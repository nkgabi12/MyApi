<?php
/**
 * MovieFlixTV - Direct IPTV Helper
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
            $name = $item['name'] ?? 'Unknown';
            $info = self::extractCountry($name);

            // VOLVEMOS A LA URL DIRECTA
            $streamUrl = $p['server'] . "/live/" . $p['user'] . "/" . $p['pass'] . "/" . $item['stream_id'] . ".m3u8";

            $formatted[] = [
                "id" => $p['name'] . "_" . $item['stream_id'],
                "name" => $info['name'] . " (" . $p['name'] . ")",
                "logo" => $item['stream_icon'] ?? "",
                "url" => $streamUrl,
                "cat" => $item['category_name'] ?? "General"
            ];
        }
        return $formatted;
    }

    private static function extractCountry($name) {
        $name = trim($name);
        $country = 'Otros';
        if (preg_match('/^(AR|ARGENTINA)[:\-\s\[\]]/i', $name)) { $country = 'ARGENTINA'; }
        elseif (preg_match('/^(MX|MEXICO)[:\-\s\[\]]/i', $name)) { $country = 'MEXICO'; }
        elseif (preg_match('/^(ES|ESP)[:\-\s\[\]]/i', $name)) { $country = 'ESPAÑA'; }
        elseif (preg_match('/^(US|USA)[:\-\s\[\]]/i', $name)) { $country = 'USA'; }

        $cleanName = preg_replace('/^.*?[:\-\|]\*/s', '', $name);
        if (empty($cleanName) || $cleanName == $name) {
            $cleanName = preg_replace('/^\[.*?\]\*/s', '', $name);
        }

        return ['country' => $country, 'name' => trim($cleanName)];
    }
}
?>
