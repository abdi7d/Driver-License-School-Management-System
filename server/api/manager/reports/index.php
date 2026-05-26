<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// 1. Overall Stats
$total_students   = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$active_instructors = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'instructor' AND status = 'active'")->fetch_row()[0];

$exam_row    = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'passed' THEN 1 ELSE 0 END) as passed_cnt FROM exams WHERE status IN ('passed', 'failed')")->fetch_assoc();
$total_exams = max(1, (int)$exam_row['total']);
$passed_exams = (int)$exam_row['passed_cnt'];
$pass_rate   = round(($passed_exams / $total_exams) * 100);

// Revenue = sum of fees per active enrollment
$rev_row     = $conn->query("SELECT SUM(tp.fee) FROM enrollments en JOIN training_programs tp ON en.program_id = tp.id")->fetch_row();
$total_revenue = floatval($rev_row[0] ?? 0);

// 2. Program-level breakdown
$programs_sql = "
    SELECT 
        tp.id,
        tp.name,
        tp.fee,
        COUNT(en.id) as enrolled,
        SUM(CASE WHEN en.status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN en.status = 'graduated' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN en.status IN ('dropped','cancelled') THEN 1 ELSE 0 END) as dropped,
        (COUNT(en.id) * tp.fee) as revenue
    FROM training_programs tp
    LEFT JOIN enrollments en ON tp.id = en.program_id
    GROUP BY tp.id
    ORDER BY tp.name
";
$res = $conn->query($programs_sql);
$programs = [];
while ($row = $res->fetch_assoc()) {
    $programs[] = $row;
}

// 3. Pass/Fail breakdown by program
$passfail_sql = "
    SELECT 
        tp.name as program,
        COUNT(ex.id) as total_exams,
        SUM(CASE WHEN ex.status = 'passed' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN ex.status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM training_programs tp
    LEFT JOIN enrollments en ON tp.id = en.program_id
    LEFT JOIN exams ex ON en.student_user_id = ex.student_user_id
    GROUP BY tp.id
    ORDER BY tp.name
";
$res_pf = $conn->query($passfail_sql);
$passfail = [];
while ($row = $res_pf->fetch_assoc()) {
    $total = max(1, (int)$row['total_exams']);
    $row['pass_rate'] = round(($row['passed'] / $total) * 100);
    $passfail[] = $row;
}

// 4. Instructor Performance
$instructor_sql = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) as name,
        COUNT(DISTINCT en.student_user_id) as assigned_students,
        COUNT(l.id) as sessions_completed,
        ROUND(AVG(l.performance_score), 1) as avg_score
    FROM users u
    LEFT JOIN enrollments en ON en.assigned_instructor_id = u.id
    LEFT JOIN lessons l ON l.instructor_id = u.id AND l.attendance = 1
    WHERE u.role = 'instructor'
    GROUP BY u.id
    ORDER BY assigned_students DESC
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
            "total_students"    => $total_students,
            "pass_rate"         => $pass_rate,
            "active_instructors" => $active_instructors,
            "total_revenue"     => $total_revenue
        ],
        "programs"    => $programs,
        "passfail"    => $passfail,
        "instructors" => $instructors
    ]
]);
?>
