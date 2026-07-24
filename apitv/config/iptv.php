<?php
/**
 * MovieFlixTV - IPTV Provider Configuration
 */

define('IPTV_SERVER', 'http://31.43.191.125:8080');
define('IPTV_USER', 'VIP011651756141415591');
define('IPTV_PASS', 'fdc0fd87f83a');

// Cache settings (to avoid hitting the provider on every request)
define('IPTV_CACHE_TIME', 3600); // 1 hour
define('IPTV_CACHE_FILE', __DIR__ . '/../cache/iptv_channels.json');
?>
