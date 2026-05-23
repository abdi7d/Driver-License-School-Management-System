<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/db.php';
include_once '../includes/jwt.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data provided"]);
    exit;
}

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, first_name, last_name, role, status, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode(["success" => false, "message" => "Incorrect password"]);
    exit;
}

if ($user["status"] === "pending") {
    echo json_encode(["success" => false, "message" => "Your account is pending approval. Please contact administration."]);
    exit;
}

if ($user["status"] === "inactive") {
    echo json_encode(["success" => false, "message" => "Your account is inactive. Please contact administration."]);
    exit;
}

// Generate JWT
$payload = [
    "user_id" => $user["id"],
    "role" => $user["role"],
    "email" => $email,
    "exp" => time() + (60 * 60 * 24) // 24 hours
];

$token = JWT::generate($payload);

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "token" => $token,
    "user" => [
        "id" => $user["id"],
        "name" => $user["first_name"] . " " . $user["last_name"],
        "role" => $user["role"],
        "status" => $user["status"]
    ]
]);
?>
