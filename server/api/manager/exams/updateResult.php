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
$exam_id = $data["id"] ?? null;
$score = $data["score"] ?? 0;
$status = $data["status"] ?? 'passed';

if (!$exam_id) {
    echo json_encode(["success" => false, "message" => "Exam ID is required"]);
    exit;
}

$passed = ($status === 'passed') ? 1 : 0;
$result_date = date("Y-m-d H:i:s");

$stmt = $conn->prepare("UPDATE exams SET score = ?, status = ?, passed = ?, result_date = ?, approved = 1 WHERE id = ?");
$stmt->bind_param("dsisi", $score, $status, $passed, $result_date, $exam_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exam result updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update exam result: " . $stmt->error]);
}
?>
