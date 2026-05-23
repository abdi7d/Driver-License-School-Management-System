<?php
include "../../config/db.php";
include "../../includes/jwt.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if ($email == "" || $password == "") {
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode(["error" => "Wrong password"]);
    exit;
}

// CREATE JWT PAYLOAD
$payload = [
    "id" => $user["id"],
    "user_id" => $user["id"],
    "role" => $user["role"],
    "email" => $user["email"],
    "exp" => time() + (60 * 60 * 24) // 1 day
];

$token = JWT::generate($payload);

echo json_encode([
    "message" => "Login success",
    "token" => $token,
    "role" => $user["role"],
    "status" => $user["status"],
    "id" => $user["id"],
    "name" => $user["full_name"]
]);
?>