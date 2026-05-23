<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data["user_id"] ?? null;

if (!$user_id) {
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Student approved successfully"]);
} else {
    echo json_encode(["error" => "Failed to approve student"]);
}
?>
