<?php
/**
 * MovieFlixTV - Profiles API (Robust Version)
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/request.php';

$authUser = requireAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT * FROM profiles WHERE main_user_id = ?");
        $stmt->execute([$authUser['id']]);
        $profiles = $stmt->fetchAll();

        $formattedProfiles = array_map(function($p) {
            return [
                "id" => $p['id'],
                "mainUserId" => $p['main_user_id'],
                "name" => $p['name'],
                "avatarUrl" => $p['avatar_url'],
                "color" => $p['avatar_color'],
                "isKid" => (bool)$p['is_kid'],
                "hasPin" => !empty($p['pin']),
                "country" => $p['country'] ?? ""
            ];
        }, $profiles);

        sendSuccess($formattedProfiles);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} elseif ($method === 'PUT' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update')) {
    // Capturar ID tanto de URL como de JSON
    $input = getJsonInput();
    $profileId = $_GET['id'] ?? ($input['id'] ?? null);

    if (!$profileId) {
        sendError("Profile ID is required");
    }

    $name = $input['name'] ?? null;
    $color = $input['color'] ?? null;
    $country = $input['country'] ?? null;

    try {
        // Verificar propiedad
        $stmt = $db->prepare("SELECT id FROM profiles WHERE id = ? AND main_user_id = ?");
        $stmt->execute([$profileId, $authUser['id']]);
        if (!$stmt->fetch()) {
            sendError("Profile not found or unauthorized", 404);
        }

        $fields = [];
        $params = [];
        if ($name) { $fields[] = "name = ?"; $params[] = $name; }
        if ($color) { $fields[] = "avatar_color = ?"; $params[] = $color; }
        if ($country) { $fields[] = "country = ?"; $params[] = $country; }

        if (empty($fields)) {
            sendError("Nothing to update");
        }

        $params[] = $profileId;
        $sql = "UPDATE profiles SET " . implode(", ", $fields) . " WHERE id = ?";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            sendSuccess(null, "Profile updated successfully");
        } else {
            sendError("Failed to update profile");
        }
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
} else {
    sendError("Method not allowed", 405);
}
