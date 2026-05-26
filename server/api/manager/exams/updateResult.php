<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
if (file_exists("../../../includes/notifications.php")) {
    include "../../../includes/notifications.php";
}

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin", "instructor", "supervisor"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data   = json_decode(file_get_contents("php://input"), true);
$id     = intval($data["id"] ?? 0);
$score  = isset($data["score"]) ? floatval($data["score"]) : null;
$status = $data["status"] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Exam ID required"]);
    exit;
}

// Determine new status from score if not provided
if ($score !== null && $status === null) {
    $status = $score >= 70 ? "passed" : "failed";
}

if (!$status) {
    echo json_encode(["success" => false, "message" => "Score or status required"]);
    exit;
}

$allowed = ['passed', 'failed', 'cancelled', 'scheduled'];
if (!in_array($status, $allowed)) {
    echo json_encode(["success" => false, "message" => "Invalid status"]);
    exit;
}

// Fetch existing exam to get student info
$examRow = $conn->prepare("SELECT student_user_id, exam_type FROM exams WHERE id = ?");
$examRow->bind_param("i", $id);
$examRow->execute();
$exam = $examRow->get_result()->fetch_assoc();
if (!$exam) {
    echo json_encode(["success" => false, "message" => "Exam not found"]);
    exit;
}

if ($score !== null) {
    $stmt = $conn->prepare("UPDATE exams SET score = ?, status = ?, result_date = NOW(), conducted_by = ? WHERE id = ?");
    $conductedBy = $user["user_id"];
    $stmt->bind_param("dsii", $score, $status, $conductedBy, $id);
} else {
    $stmt = $conn->prepare("UPDATE exams SET status = ?, result_date = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
}

if ($stmt->execute()) {
    // Notify student of result
    if ($status !== 'scheduled' && $status !== 'cancelled' && function_exists('notifyUser')) {
        $typeName = ucfirst($exam['exam_type']);
        $resultMsg = $status === 'passed'
            ? "Congratulations! You passed your $typeName exam with a score of " . round($score) . "%."
            : "You did not pass your $typeName exam. Score: " . round($score) . "%. You may reschedule.";
        notifyUser($conn, $exam['student_user_id'], "$typeName Exam Result", $resultMsg);
    }
    echo json_encode(["success" => true, "message" => "Exam result updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update: " . $stmt->error]);
}
?>
