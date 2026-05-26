<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
include "../../../includes/notifications.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data["user_id"] ?? null;
$status = $data["status"] ?? null;

if (!$user_id || !$status) {
    echo json_encode(["error" => "User ID and status are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $user_id);

if ($stmt->execute()) {
    notifyUser($conn, $user_id, 'Account status changed', "Your account status has been updated to {$status} by the manager.");
    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update status"]);
}
?>
