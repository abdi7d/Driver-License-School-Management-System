<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Students who passed both exams for a program and don't have a certificate
$sql = "
    SELECT 
        u.id as student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email as student_email,
        tp.id as program_id,
        tp.name as program_name,
        e1.score as theory_score,
        e2.score as practical_score,
        (e1.score + e2.score) / 2 as final_score,
        e2.result_date as completion_date
    FROM users u
    JOIN enrollments en ON u.id = en.student_user_id
    JOIN training_programs tp ON en.program_id = tp.id
    JOIN exams e1 ON u.id = e1.student_user_id AND e1.exam_type = 'theory' AND e1.passed = 1
    JOIN exams e2 ON u.id = e2.student_user_id AND e2.exam_type = 'practical' AND e2.passed = 1
    LEFT JOIN certificates c ON u.id = c.student_user_id AND tp.id = c.program_id
    WHERE c.id IS NULL
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
