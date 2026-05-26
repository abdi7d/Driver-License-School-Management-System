<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Students who passed both exams and don't have a certificate yet
$sql = "
    SELECT 
        u.id as student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email as student_email,
        tp.id as program_id,
        tp.name as program_name,
        COALESCE(e1.score, 0) as theory_score,
        COALESCE(e2.score, 0) as practical_score,
        (COALESCE(e1.score, 0) + COALESCE(e2.score, 0)) / 2 as final_score,
        COALESCE(e2.result_date, e1.result_date) as completion_date
    FROM users u
    JOIN enrollments en ON u.id = en.student_user_id AND en.status IN ('active', 'graduated')
    JOIN training_programs tp ON en.program_id = tp.id
    LEFT JOIN exams e1 ON u.id = e1.student_user_id AND e1.exam_type = 'theory' AND e1.status = 'passed'
    LEFT JOIN exams e2 ON u.id = e2.student_user_id AND e2.exam_type = 'practical' AND e2.status = 'passed'
    LEFT JOIN certificates c ON u.id = c.student_user_id AND tp.id = c.program_id
    WHERE c.id IS NULL
      AND u.role = 'student'
      AND (e1.id IS NOT NULL OR e2.id IS NOT NULL)
    GROUP BY u.id, tp.id
    ORDER BY completion_date DESC
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
