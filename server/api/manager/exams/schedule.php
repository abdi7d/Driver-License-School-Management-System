<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
if (file_exists("../../../includes/notifications.php")) {
    include "../../../includes/notifications.php";
}

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data          = json_decode(file_get_contents("php://input"), true);
$student_id    = intval($data["student_id"] ?? 0);
$exam_type     = strtolower($data["exam_type"] ?? "");
$scheduled_date = $data["scheduled_date"] ?? "";
$conducted_by  = intval($data["conducted_by"] ?? 0) ?: null;

if (!$student_id || !in_array($exam_type, ["theory", "practical"]) || !$scheduled_date) {
    echo json_encode(["success" => false, "message" => "Student, exam type, and scheduled date are required"]);
    exit;
}

// Check for duplicate scheduled exam
$dup = $conn->prepare("SELECT id FROM exams WHERE student_user_id = ? AND exam_type = ? AND status = 'scheduled'");
$dup->bind_param("is", $student_id, $exam_type);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Student already has a scheduled $exam_type exam"]);
    exit;
}

if ($conducted_by) {
    $stmt = $conn->prepare("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status, conducted_by) VALUES (?, ?, ?, 'scheduled', ?)");
    $stmt->bind_param("issi", $student_id, $exam_type, $scheduled_date, $conducted_by);
} else {
    $stmt = $conn->prepare("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status) VALUES (?, ?, ?, 'scheduled')");
    $stmt->bind_param("iss", $student_id, $exam_type, $scheduled_date);
}

if ($stmt->execute()) {
    if (function_exists('notifyUser')) {
        $typeName = ucfirst($exam_type);
        $dateStr  = date("M d, Y", strtotime($scheduled_date));
        notifyUser($conn, $student_id, "$typeName Exam Scheduled", "Your $typeName exam has been scheduled for $dateStr. Please be prepared!");
    }
    echo json_encode(["success" => true, "message" => "Exam scheduled successfully", "id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to schedule exam: " . $stmt->error]);
}
?>
