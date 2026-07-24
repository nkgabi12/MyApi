<?php
/**
 * MovieFlixTV - Request Helper
 */

function getJsonInput() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        // Fallback for some server configurations
        return $_POST;
    }
    $decoded = json_decode($input, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function getAuthUser() {
    require_once __DIR__ . '/jwt_helper.php';
    $token = JWTHelper::getBearerToken();
    if (!$token) return null;

    return JWTHelper::verify($token);
}

function requireAuth() {
    $user = getAuthUser();
    if (!$user) {
        require_once __DIR__ . '/response.php';
        sendError("Unauthorized", 401);
    }
    return $user;
}
?>
