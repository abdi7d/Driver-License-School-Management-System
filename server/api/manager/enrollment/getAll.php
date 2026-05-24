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
        tp.name as program_name,
        SUBSTRING_INDEX(tp.name, ' - ', 1) as program_category,
        CONCAT(i.first_name, ' ', i.last_name) as instructor_name,
        e.enrollment_date,
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
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
