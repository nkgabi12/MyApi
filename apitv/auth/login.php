<?php
/**
 * MovieFlixTV - Login API
 */

require_once '../config/db.php';
require_once '../utils/response.php';
require_once '../utils/request.php';
require_once '../utils/jwt_helper.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = getJsonInput();
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendError("Email y contraseña son requeridos (Recibido: $email)");
    }

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password_hash'])) {
                $payload = [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "role" => $user['role'],
                    "exp" => time() + (60 * 60 * 24 * 30)
                ];

                $token = JWTHelper::generate($payload);

                $userData = [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "full_name" => $user['full_name'],
                    "role" => $user['role'],
                    "is_premium" => (bool)$user['is_premium']
                ];

                sendSuccess([
                    "token" => $token,
                    "user" => $userData
                ], "Login successful");
            } else {
                // Si falla, podrías probar temporalmente con MD5 si tu web vieja lo usaba:
                // if (md5($password) === $user['password_hash']) { ... }
                sendError("Contraseña incorrecta", 401);
            }
        } else {
            sendError("El usuario no existe", 401);
        }
    } catch (PDOException $e) {
        sendError("Error de BD: " . $e->getMessage(), 500);
    }
} else {
    sendError("Método no permitido", 405);
}
// No cerramos la etiqueta PHP para evitar espacios accidentales
