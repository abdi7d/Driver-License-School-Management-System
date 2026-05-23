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

// Summary of training standards (average scores, attendance)
$query = "
    SELECT 
        tp.name as program_name,
        AVG(l.performance_score) as avg_score,
        COUNT(l.id) as total_lessons,
        SUM(CASE WHEN l.attendance = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(l.id) as attendance_rate
    FROM lessons l
    JOIN enrollments e ON l.enrollment_id = e.id
    JOIN training_programs tp ON e.program_id = tp.id
    GROUP BY tp.id
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $stats
]);
?>
