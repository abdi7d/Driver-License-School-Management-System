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
$examId = $data["id"] ?? null;
$scheduledDate = trim($data["scheduled_date"] ?? '');
$examType = trim($data["exam_type"] ?? '');
$conductor = $data["conducted_by"] ?? null;
$status = trim($data["status"] ?? '');

if (!$examId) {
    echo json_encode(["success" => false, "message" => "Exam ID is required"]);
    exit;
}

$fields = [];
$params = [];
$types = '';

if ($scheduledDate !== '') {
    $fields[] = 'scheduled_date = ?';
    $params[] = $scheduledDate;
    $types .= 's';
}
if ($examType !== '') {
    $fields[] = 'exam_type = ?';
    $params[] = $examType;
    $types .= 's';
}
if ($conductor !== null) {
    $fields[] = 'conducted_by = ?';
    $params[] = $conductor;
    $types .= 'i';
}
if ($status !== '') {
    $fields[] = 'status = ?';
    $params[] = $status;
    $types .= 's';
}

if (empty($fields)) {
    echo json_encode(["success" => false, "message" => "No fields to update"]);
    exit;
}

$query = "UPDATE exams SET " . implode(', ', $fields) . " WHERE id = ?";
$params[] = $examId;
$types .= 'i';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exam updated successfully"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Failed to update exam: " . $stmt->error]);
