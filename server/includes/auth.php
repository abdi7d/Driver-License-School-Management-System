<?php
include_once __DIR__ . "/jwt.php";

function auth() {
    $headers = function_exists("getallheaders") ? getallheaders() : [];
    $authorization = $headers["Authorization"]
        ?? $headers["authorization"]
        ?? ($_SERVER["HTTP_AUTHORIZATION"] ?? null);

    if (!$authorization) {
        echo json_encode(["error" => "No token"]);
        exit;
    }

    $token = trim(str_replace("Bearer ", "", $authorization));

    $decoded = JWT::verify($token);

    if (!$decoded) {
        echo json_encode(["error" => "Invalid token"]);
        exit;
    }

    return $decoded;
}
?>