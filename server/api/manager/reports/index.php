<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// 1. Overall Stats
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$active_instructors = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'instructor' AND status = 'active'")->fetch_row()[0];

$exam_stats = $conn->query("SELECT COUNT(*), SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) FROM exams WHERE status IN ('passed', 'failed')")->fetch_row();
$total_exams = $exam_stats[0] ?: 1;
$passed_exams = $exam_stats[1] ?: 0;
$pass_rate = round(($passed_exams / $total_exams) * 100);

$total_revenue = $conn->query("SELECT SUM(tp.price) FROM enrollments en JOIN training_programs tp ON en.program_id = tp.id")->fetch_row()[0] ?: 0;

// 2. Program Details
$programs_sql = "
    SELECT 
        tp.id,
        tp.name,
        tp.category,
        tp.price,
        (SELECT COUNT(*) FROM enrollments WHERE program_id = tp.id) as enrolled,
        (SELECT COUNT(*) FROM enrollments WHERE program_id = tp.id AND status = 'active') as active,
        (SELECT COUNT(*) FROM enrollments WHERE program_id = tp.id AND status = 'graduated') as completed,
        (SELECT COUNT(*) FROM enrollments WHERE program_id = tp.id AND status = 'dropped') as dropped,
        ((SELECT COUNT(*) FROM enrollments WHERE program_id = tp.id) * tp.price) as revenue
    FROM training_programs tp
";
$res = $conn->query($programs_sql);
$programs = [];
while ($row = $res->fetch_assoc()) {
    $programs[] = $row;
}

// 3. Instructor Performance (Simplified)
$instructor_sql = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) as name,
        (SELECT COUNT(*) FROM enrollments WHERE assigned_instructor_id = u.id) as assigned_students,
        (SELECT COUNT(*) FROM lessons WHERE instructor_id = u.id AND status = 'completed') as sessions_completed
    FROM users u
    WHERE u.role = 'instructor'
";
$res_i = $conn->query($instructor_sql);
$instructors = [];
while ($row = $res_i->fetch_assoc()) {
    $instructors[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => [
        "stats" => [
            "total_students" => (int)$total_students,
            "pass_rate" => (int)$pass_rate,
            "active_instructors" => (int)$active_instructors,
            "total_revenue" => (float)$total_revenue
        ],
        "programs" => $programs,
        "instructors" => $instructors
    ]
]);
?>
