<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$user_id = (int)$user['user_id'];

$query = "
    SELECT e.id as enrollment_id, e.status as exam_status,
           e.exam_type, e.scheduled_date, e.status, e.score, e.result_date, e.approved,
           u.full_name as examiner_name
    FROM exams e
    LEFT JOIN users u ON e.conducted_by = u.id
    WHERE e.student_user_id = ?
    ORDER BY e.scheduled_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

echo json_encode(["success" => true, "data" => $exams]);
?>