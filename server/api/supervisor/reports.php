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

// Aggregated Reports Data
// 1. Student Scores (from exams table)
$student_query = "
    SELECT 
        u.full_name as student_name,
        p.name as program_name,
        u_instructor.full_name as instructor_name,
        ex.exam_type,
        ex.score,
        ex.approved
    FROM exams ex
    JOIN users u ON ex.student_user_id = u.id
    JOIN enrollments e ON e.student_user_id = u.id
    JOIN training_programs p ON e.program_id = p.id
    LEFT JOIN users u_instructor ON e.assigned_instructor_id = u_instructor.id
    ORDER BY ex.created_at DESC
    LIMIT 20
";

$student_result = $conn->query($student_query);
if (!$student_result) {
    echo json_encode(["success" => false, "error" => "Database error (Students): " . $conn->error]);
    exit;
}
$student_performance = [];
while ($row = $student_result->fetch_assoc()) {
    $student_performance[] = $row;
}

// 2. Instructor stats
$instructor_query = "
    SELECT 
        u.full_name as instructor_name,
        id.specialization,
        id.experience_years,
        (SELECT COUNT(*) FROM enrollments WHERE assigned_instructor_id = u.id) as student_count,
        (SELECT AVG(score) FROM exams ex JOIN enrollments e ON ex.student_user_id = e.student_user_id WHERE e.assigned_instructor_id = u.id) as avg_score
    FROM users u
    JOIN instructor_details id ON u.id = id.user_id
    WHERE u.role = 'instructor'
";

$instructor_result = $conn->query($instructor_query);
if (!$instructor_result) {
    echo json_encode(["success" => false, "error" => "Database error (Instructors): " . $conn->error]);
    exit;
}
$instructor_stats = [];
while ($row = $instructor_result->fetch_assoc()) {
    $instructor_stats[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => [
        "student_performance" => $student_performance,
        "instructor_stats" => $instructor_stats,
        "overall_stats" => [
            "pass_rate" => "85%",
            "avg_score" => 78.5,
            "efficiency" => "90%",
            "training_time" => "42 days"
        ]
    ]
]);
?>
