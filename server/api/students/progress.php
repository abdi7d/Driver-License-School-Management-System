<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$user_id = (int)$user['user_id'];

// Get enrollment
$stmt = $conn->prepare(
    "SELECT e.id, e.program_id, tp.name as program_name, e.status,
            e.progress_percentage as progress,
            tp.theory_hours, tp.practical_hours
     FROM enrollments e
     JOIN training_programs tp ON e.program_id = tp.id
     WHERE e.student_user_id = ?
     ORDER BY e.created_at DESC LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    echo json_encode(["success" => false, "message" => "No active enrollment found"]);
    exit;
}

$enrollmentId = (int)$enrollment['id'];

// Lesson stats
$theory_hours     = (int)$enrollment['theory_hours'];
$practical_hours  = (int)$enrollment['practical_hours'];
$total_hours      = $theory_hours + $practical_hours;

$stmt2 = $conn->prepare(
    "SELECT lesson_type, SUM(duration_minutes) as total_mins
     FROM lessons WHERE enrollment_id = ? AND attendance = 1
     GROUP BY lesson_type"
);
$stmt2->bind_param("i", $enrollmentId);
$stmt2->execute();
$res2 = $stmt2->get_result();

$theory_done_mins   = 0;
$practical_done_mins = 0;
while ($row = $res2->fetch_assoc()) {
    if ($row['lesson_type'] === 'theory')    $theory_done_mins   = (int)$row['total_mins'];
    if ($row['lesson_type'] === 'practical') $practical_done_mins = (int)$row['total_mins'];
}

$completed_hours  = round(($theory_done_mins + $practical_done_mins) / 60, 1);
$theory_done      = round($theory_done_mins / 60, 1);
$practical_done   = round($practical_done_mins / 60, 1);

$progress = (float)$enrollment['progress'];
if ($progress == 0 && $total_hours > 0) {
    $progress = round(($completed_hours / $total_hours) * 100, 1);
}

// Lesson history
$stmt3 = $conn->prepare(
    "SELECT l.id, l.session_date, l.lesson_type, l.duration_minutes,
            l.attendance, l.performance_score, l.notes,
            u.full_name as instructor_name
     FROM lessons l
     JOIN users u ON l.instructor_id = u.id
     WHERE l.enrollment_id = ?
     ORDER BY l.session_date DESC"
);
$stmt3->bind_param("i", $enrollmentId);
$stmt3->execute();
$lessons = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "success" => true,
    "data"    => [
        "enrollment"          => $enrollment,
        "lesson_history"      => $lessons,
        "total_hours"         => $total_hours,
        "completed_hours"     => $completed_hours,
        "theory_hours"        => $theory_hours,
        "theory_completed"    => $theory_done,
        "practical_hours"     => $practical_hours,
        "practical_completed" => $practical_done,
        "progress_percentage" => $progress,
    ]
]);
?>