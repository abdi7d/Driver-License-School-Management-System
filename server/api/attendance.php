<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
if ($user["role"] === "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["lesson_id"]) || !isset($data["attendance"])) {
    echo json_encode(["success" => false, "message" => "Lesson ID and attendance are required"]);
    exit;
}

$lessonId = $data["lesson_id"];
$attendance = $data["attendance"] ? 1 : 0;

$stmt = $conn->prepare("UPDATE lessons SET attendance = ? WHERE id = ?");
$stmt->bind_param("ii", $attendance, $lessonId);

if ($stmt->execute()) {
    // Update progress if present
    if ($attendance == 1) {
        $conn->query("UPDATE enrollments e 
                     JOIN lessons l ON e.id = l.enrollment_id 
                     SET e.progress_percentage = LEAST(100, (SELECT COUNT(*) FROM lessons WHERE enrollment_id = e.id AND attendance = 1) * 10) 
                     WHERE l.id = $lessonId");
    }
    echo json_encode(["success" => true, "message" => "Attendance recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record attendance"]);
}
?>
