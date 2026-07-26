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
            $countryInfo = self::extractCountryAndName($name);
            $country = $countryInfo['country'];
            $cleanName = $countryInfo['name'];

            $originalUrl = $p['server'] . "/live/" . $p['user'] . "/" . $p['pass'] . "/" . $item['stream_id'] . ".m3u8";
            $proxyUrl = "https://myapi-i5wf.onrender.com/proxy?url=" . urlencode($originalUrl);

            $formatted[] = [
                "id" => $p['name'] . "_" . $item['stream_id'],
                "name" => $cleanName . " (" . $p['name'] . ")",
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

    private static function extractCountryAndName($name) {
        $name = trim($name);
        $country = 'Otros';

        // Very strict matching for countries at the beginning
        if (preg_match('/^\[AR\]|^AR[:\s\-]|ARGENTINA/i', $name)) { $country = 'Argentina'; }
        elseif (preg_match('/^\[MX\]|^MX[:\s\-]|MEXICO/i', $name)) { $country = 'Mexico'; }
        elseif (preg_match('/^\[ES\]|^ES[:\s\-]|ESP/i', $name)) { $country = 'España'; }
        elseif (preg_match('/^\[US\]|^US[:\s\-]|USA/i', $name)) { $country = 'USA'; }
        elseif (preg_match('/^\[CL\]|^CL[:\s\-]|CHILE/i', $name)) { $country = 'Chile'; }
        elseif (preg_match('/^\[CO\]|^CO[:\s\-]|COLOMBIA/i', $name)) { $country = 'Colombia'; }

        // Clean name (remove the tag but keep the rest)
        $cleanName = preg_replace('/^(\[.*?\]|.*?[:\-\|])\s*/', '', $name);
        if (empty($cleanName)) $cleanName = $name;

        return ['country' => $country, 'name' => trim($cleanName)];
    }
}
