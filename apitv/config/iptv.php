<?php
/**
 * MovieFlixTV - Multi-Provider IPTV Configuration
 */

$IPTV_PROVIDERS = [
    [
        "name" => "SV1",
        "server" => "http://livetv-plus.com:2082/",
        "user" => "kfS8c",
        "pass" => "VNMAE"
    ];

define('IPTV_CACHE_TIME', 3600);
define('IPTV_CACHE_FILE', __DIR__ . '/../cache/iptv_channels_multi.json');
?>
