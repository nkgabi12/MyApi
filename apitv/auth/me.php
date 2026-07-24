<?php
/**
 * MovieFlixTV - Get Current User API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/request.php';

$authUser = requireAuth();
$db = Database::getConnection();

try {
    $stmt = $db->prepare("SELECT id, email, full_name, role, is_premium FROM users WHERE id = ?");
    $stmt->execute([$authUser['id']]);
    $user = $stmt->fetch();

    if ($user) {
        $user['is_premium'] = (bool)$user['is_premium'];
        sendSuccess($user);
    } else {
        sendError("User not found", 404);
    }
} catch (PDOException $e) {
    sendError("Database error: " . $e->getMessage(), 500);
}
?>
