<?php
/**
 * MovieFlixTV - High Performance Transparent Proxy
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

$url = $_GET['url'] ?? '';
if (empty($url)) die("No URL");

// User-Agent profesional para evitar bloqueos del proveedor
$userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Stream directo a la salida
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 256); // 256KB buffer

// Detectar el tipo de contenido y pasarlo a la app
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
    if (stripos($header, 'Content-Type:') !== false) {
        header($header);
    }
    return strlen($header);
});

curl_exec($ch);
curl_close($ch);
