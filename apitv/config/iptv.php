<?php
/**
 * MovieFlixTV - IPTV Provider Configuration
 */

define('IPTV_SERVER', 'http://your-iptv-provider.com:8080');
define('IPTV_USER', 'your_username');
define('IPTV_PASS', 'your_password');

// Cache settings (to avoid hitting the provider on every request)
define('IPTV_CACHE_TIME', 3600); // 1 hour
define('IPTV_CACHE_FILE', __DIR__ . '/../cache/iptv_channels.json');
?>
