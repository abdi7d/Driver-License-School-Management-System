<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$query = "
    SELECT u.id, u.full_name, u.email, u.phone, e.progress_percentage, e.status as enrollment_status, tp.name as program_name,
           (SELECT COUNT(*) FROM lessons WHERE enrollment_id = e.id) as attended_lessons,
           (SELECT session_date FROM lessons WHERE enrollment_id = e.id ORDER BY session_date DESC LIMIT 1) as last_lesson_date
    FROM users u
    JOIN enrollments e ON u.id = e.student_user_id
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE u.role = 'student'
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $students
]);
?>
