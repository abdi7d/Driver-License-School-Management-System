<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$user_id = $user['user_id'];

// Get enrollment and program info
$query = "
    SELECT e.id, e.program_id, tp.name as program_name, e.status, e.progress_percentage as progress,
           4 as duration_weeks, tp.id as tp_id
    FROM enrollments e
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE e.student_user_id = ?
    ORDER BY e.created_at DESC LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    echo json_encode(["success" => false, "message" => "No active enrollment found"]);
    exit;
}

$enrollmentId = $enrollment['id'];

// Get all lessons for this enrollment
$query2 = "
    SELECT l.id, l.session_date, l.lesson_type, l.duration_minutes, l.attendance, l.performance_score, l.notes,
           u.full_name as instructor_name
    FROM lessons l
    JOIN users u ON l.instructor_id = u.id
    WHERE l.enrollment_id = ?
    ORDER BY l.session_date DESC
";

$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("i", $enrollmentId);
$stmt2->execute();
$result2 = $stmt2->get_result();

$lessons = [];
$total_minutes = 0;
$completed_minutes = 0;
$theory_completed_mins = 0;
$practical_completed_mins = 0;

while ($row = $result2->fetch_assoc()) {
    $lessons[] = $row;
    $total_minutes += $row['duration_minutes'];
    if ($row['attendance'] == 1) {
        $completed_minutes += $row['duration_minutes'];
        if ($row['lesson_type'] === 'theory') {
            $theory_completed_mins += $row['duration_minutes'];
        } else {
            $practical_completed_mins += $row['duration_minutes'];
        }
    }
}

// Estimate total required hours (fallback to 40 hours = 2400 mins if not in training_programs)
$total_hours = 40;
$theory_hours = 16;
$practical_hours = 24;

echo json_encode([
    'success' => true,
    'data' => [
        'enrollment' => $enrollment,
        'lesson_history' => $lessons,
        'total_hours' => $total_hours,
        'completed_hours' => round($completed_minutes / 60, 1),
        'theory_hours' => $theory_hours,
        'theory_completed' => round($theory_completed_mins / 60, 1),
        'practical_hours' => $practical_hours,
        'practical_completed' => round($practical_completed_mins / 60, 1),
        'progress_percentage' => $enrollment['progress'] ?: round(($completed_minutes / ($total_hours * 60)) * 100)
    ]
]);
?>