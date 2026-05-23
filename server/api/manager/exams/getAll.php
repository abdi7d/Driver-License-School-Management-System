<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$sql = "
    SELECT 
        e.id,
        u.id as student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email as student_email,
        e.exam_type,
        tp.name as program_name,
        e.scheduled_date,
        e.status,
        e.score,
        CONCAT(i.first_name, ' ', i.last_name) as instructor_name
    FROM exams e
    JOIN users u ON e.student_user_id = u.id
    LEFT JOIN training_programs tp ON u.id IN (SELECT student_user_id FROM enrollments WHERE program_id = tp.id)
    LEFT JOIN users i ON e.conducted_by = i.id
    ORDER BY e.scheduled_date DESC
";

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
