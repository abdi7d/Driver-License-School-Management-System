<?php
function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($payload);
    exit;
}

function requireRole($user, $allowedRoles) {
    $allowed = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    if (!in_array($user["role"] ?? "", $allowed, true)) {
        jsonResponse(["success" => false, "message" => "Access denied"], 403);
    }
}
?>
