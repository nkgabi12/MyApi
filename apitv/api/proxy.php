<?php
/**
 * MovieFlixTV - High Performance Streaming Proxy with HLS Rewriting
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

$url = $_GET['url'] ?? '';
if (empty($url)) die("No URL provided");

$parsedUrl = parse_url($url);
$baseUrl = $parsedUrl['scheme'] . "://" . $parsedUrl['host'] . (isset($parsedUrl['port']) ? ":" . $parsedUrl['port'] : "");
$path = dirname($parsedUrl['path']);
if ($path == "/") $path = "";
$fullBaseDir = $baseUrl . $path . "/";

$extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept: */*",
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // For HLS we need to process the body

$response = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($extension == 'm3u8' || strpos($contentType, 'mpegurl') !== false) {
    header("Content-Type: application/vnd.apple.mpegurl");

    // Rewrite relative URLs to absolute via proxy
    $lines = explode("\n", $response);
    $output = "";
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if ($line[0] !== '#' && !filter_var($line, FILTER_VALIDATE_URL)) {
            // Relative URL, make it absolute via proxy
            $absoluteUrl = $fullBaseDir . $line;
            $line = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/proxy.php?url=" . urlencode($absoluteUrl);
        } elseif (filter_var($line, FILTER_VALIDATE_URL) && strpos($line, 'http') === 0) {
            // Absolute URL, wrap it with proxy
            $line = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/proxy.php?url=" . urlencode($line);
        }
        $output .= $line . "\n";
    }
    echo $output;
} else {
    // For .ts or other binary files, just stream them
    header("Content-Type: " . ($contentType ?: "video/mp2t"));

    // Re-fetch binary if we already captured it or stream it if big
    // For simplicity, we re-run curl but streaming to output
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 512);
    curl_exec($ch);
    curl_close($ch);
}
