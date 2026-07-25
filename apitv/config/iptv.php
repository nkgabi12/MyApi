<?php
/**
 * MovieFlixTV - Multi-Provider IPTV Configuration
 */

$IPTV_PROVIDERS = [
    [
        "name" => "SV1",
        "server" => "http://31.43.191.125:8080",
        "user" => "VIP011651756141415591",
        "pass" => "fdc0fd87f83a"
    ],
    [
        "name" => "SV2",
        "server" => "http://tv.m3uts.xyz",
        "user" => "m",
        "pass" => "m"
    ]
];

define('IPTV_CACHE_TIME', 3600);
define('IPTV_CACHE_FILE', __DIR__ . '/../cache/iptv_channels_multi.json');
?>
