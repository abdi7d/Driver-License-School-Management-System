<?php
include "../../config/db.php";
include "../../includes/auth.php";
include "../../includes/notifications.php";

header("Content-Type: application/json");

$user = auth();

if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"] ?? 0;

$stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE id=?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    notifyUser($conn, $user_id, 'Registration rejected', 'Your registration has been rejected by the manager. Please contact support for further information.');
    echo json_encode(["message" => "Student rejected"]);
} else {
    echo json_encode(["error" => "Failed"]);
}
?>