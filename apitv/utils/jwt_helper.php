<?php
/**
 * MovieFlixTV - Simple JWT Helper
 */

require_once __DIR__ . '/../config/db.php';

class JWTHelper {
    private static $secret = JWT_SECRET;

    public static function generate($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $validSignature = self::base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, self::$secret, true));

        if ($signature !== $validSignature) return false;

        $payloadData = json_decode(self::base64UrlDecode($payload), true);

        // Check expiration if present
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData;
    }

    public static function getBearerToken() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
?>
