<?php
// Global CORS handling for all API endpoints that include this file.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Vary: Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Max-Age: 86400");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database Configuration
$db_host = getenv('DB_HOST') ?: "localhost";
$db_name = getenv('DB_NAME') ?: "driver_license_school";
$db_user = getenv('DB_USER') ?: "root";
$db_pass = getenv('DB_PASS') ?: "";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "details" => $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
