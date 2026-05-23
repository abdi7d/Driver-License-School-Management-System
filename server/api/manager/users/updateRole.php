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
$role = $data["role"] ?? null;

if (!$user_id || !$role) {
    echo json_encode(["error" => "User ID and role are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $role, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Role updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update role"]);
}
?>
