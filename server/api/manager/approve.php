<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();

// 🔐 Only manager
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"] ?? 0;

if ($user_id == 0) {
    echo json_encode(["error" => "User ID required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status='active' WHERE id=?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Student approved"]);
} else {
    echo json_encode(["error" => "Failed"]);
}
?>