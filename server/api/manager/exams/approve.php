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
$exam_id = $data["exam_id"] ?? null;

if (!$exam_id) {
    echo json_encode(["error" => "Exam ID is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE exams SET approved = 1 WHERE id = ?");
$stmt->bind_param("i", $exam_id);

if ($stmt->execute()) {
    $examStmt = $conn->prepare('SELECT student_user_id FROM exams WHERE id = ?');
    $examStmt->bind_param('i', $exam_id);
    $examStmt->execute();
    $exam = $examStmt->get_result()->fetch_assoc();
    if ($exam) {
        notifyUser($conn, $exam['student_user_id'], 'Exam approved', 'Your exam has been approved by the manager. Please review the exam details and next steps.');
    }
    echo json_encode(["message" => "Exam approved successfully"]);
} else {
    echo json_encode(["error" => "Failed to approve exam"]);
}
?>
