<?php
/**
 * MovieFlixTV - API Response Helper
 */

function sendResponse($success, $data = null, $message = "", $code = 200) {
    // Clear any previous output (warnings, spaces, etc)
    if (ob_get_length()) ob_clean();

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);

    $response = [
        "success" => (bool)$success
    ];

    if ($data !== null) {
        $response["data"] = $data;
    }

    if (!empty($message)) {
        $response["message"] = $message;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function sendError($message, $code = 400) {
    sendResponse(false, null, $message, $code);
}

function sendSuccess($data = null, $message = "") {
    sendResponse(true, $data, $message, 200);
}
?>
