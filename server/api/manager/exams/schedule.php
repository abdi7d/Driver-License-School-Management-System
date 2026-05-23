<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data["student_id"] ?? null;
$exam_type = $data["exam_type"] ?? 'theory';
$scheduled_date = $data["scheduled_date"] ?? null;
$instructor_id = $data["instructor_id"] ?? null;

if (!$student_id || !$scheduled_date) {
    echo json_encode(["success" => false, "message" => "Student and Date are required"]);
    exit;
}

$status = 'scheduled';

$stmt = $conn->prepare("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status, conducted_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isssi", $student_id, $exam_type, $scheduled_date, $status, $instructor_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exam scheduled successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to schedule exam: " . $stmt->error]);
}
?>
