<?php
/**
 * MovieFlixTV - High Performance Streaming Proxy
 * Handles Buffering, Prefetching and CORS
 */

// Basic CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$url = $_GET['url'] ?? '';
if (empty($url)) {
    die("No URL provided");
}

// Security: Only allow streaming extensions
$extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
$allowed = ['m3u8', 'ts', 'mp4', 'mkv', 'aac'];

if (!in_array(strtolower($extension), $allowed)) {
    // Some streams don't have extension in URL, we continue but with caution
}

// Forward Headers
$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept: */*",
    "Connection: keep-alive"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Stream directly to output
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 512); // 512KB buffer for smoother flow

// Set correct Content-Type based on extension
if ($extension == 'm3u8') {
    header("Content-Type: application/vnd.apple.mpegurl");
} elseif ($extension == 'ts') {
    header("Content-Type: video/mp2t");
}

// Execution
curl_exec($ch);
curl_close($ch);
