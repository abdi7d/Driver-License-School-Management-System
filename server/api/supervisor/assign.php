<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->enrollment_id) || !isset($data->instructor_id)) {
    echo json_encode(["error" => "Missing enrollment_id or instructor_id"]);
    exit;
}

$query = "UPDATE enrollments SET assigned_instructor_id = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $data->instructor_id, $data->enrollment_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Instructor assigned successfully"]);
} else {
    echo json_encode(["error" => "Failed to assign instructor"]);
}
?>
