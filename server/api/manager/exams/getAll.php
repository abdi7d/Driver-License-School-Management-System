<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Use a simpler, correlated-subquery-free approach
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
        e.conducted_by,
        CONCAT(i.first_name, ' ', i.last_name) as instructor_name
    FROM exams e
    JOIN users u ON e.student_user_id = u.id
    LEFT JOIN enrollments en ON en.student_user_id = u.id
    LEFT JOIN training_programs tp ON en.program_id = tp.id
    LEFT JOIN users i ON e.conducted_by = i.id
    GROUP BY e.id
    ORDER BY e.scheduled_date DESC
";

$res  = $conn->query($sql);
$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data"    => $data
]);
?>
