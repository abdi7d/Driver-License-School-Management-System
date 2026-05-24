<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$user_id = $user['user_id'];

$query = "
    SELECT id, exam_type, scheduled_date as exam_date, score, status
    FROM exams
    WHERE student_user_id = ?
    ORDER BY scheduled_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

echo json_encode($exams);
?>