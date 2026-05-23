<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
if ($user["role"] !== "instructor" && $user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["enrollment_id"])) {
    echo json_encode(["success" => false, "message" => "Enrollment ID is required"]);
    exit;
}

$enrollmentId = $data["enrollment_id"];
$recommended = isset($data["recommended"]) ? ($data["recommended"] ? 1 : 0) : 1;

$stmt = $conn->prepare("UPDATE enrollments SET recommended_for_exam = ? WHERE id = ?");
$stmt->bind_param("ii", $recommended, $enrollmentId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => $recommended ? "Student recommended for exam" : "Recommendation withdrawn"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update recommendation"]);
}
?>
