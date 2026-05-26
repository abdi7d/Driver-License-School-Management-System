<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Fix the correlated subquery that was causing issues on some MySQL versions
$sql = "
    SELECT 
        e.id,
        u.id as student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email as student_email,
        tp.id as program_id,
        tp.name as program_name,
        e.assigned_instructor_id,
        CONCAT(i.first_name, ' ', i.last_name) as instructor_name,
        e.enrollment_date,
        e.start_date,
        e.progress_percentage as progress,
        e.status
    FROM enrollments e
    JOIN users u ON e.student_user_id = u.id
    JOIN training_programs tp ON e.program_id = tp.id
    LEFT JOIN users i ON e.assigned_instructor_id = i.id
    ORDER BY e.created_at DESC
";

$res = $conn->query($sql);
$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
