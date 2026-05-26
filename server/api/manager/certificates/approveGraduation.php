<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
include "../../../includes/notifications.php";

header('Content-Type: application/json');

$user = auth();

// 🔐 SAFE CHECK
if (!$user || !isset($user["role"]) || $user["role"] !== "manager") {
    http_response_code(403);
    echo json_encode(["error" => "Access denied or invalid session"]);
    exit;
}

// 📥 INPUT
$data = json_decode(file_get_contents("php://input"), true);

$student_user_id = $data["student_user_id"] ?? null;

if (!$student_user_id) {
    http_response_code(400);
    echo json_encode(["error" => "Student User ID is required"]);
    exit;
}

// 🛠️ UPDATE
$stmt = $conn->prepare("
    UPDATE student_details 
    SET enrollment_status = 'graduated' 
    WHERE user_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database prepare failed"]);
    exit;
}

$stmt->bind_param("i", $student_user_id);

if ($stmt->execute()) {
    notifyUser($conn, $student_user_id, 'Graduation approved', 'Your graduation status has been approved by the manager. Congratulations!');
    echo json_encode(["message" => "Graduation approved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
?>