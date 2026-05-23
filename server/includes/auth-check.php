<?php
include_once __DIR__ . "/auth.php";

function authCheck($roles = []) {
    $user = auth();
    if (!empty($roles)) {
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($user["role"] ?? "", $allowed, true)) {
            http_response_code(403);
            echo json_encode(["error" => "Access denied"]);
            exit;
        }
    }
    return $user;
}
?>
