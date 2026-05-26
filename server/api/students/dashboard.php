<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$user_id = (int)$user['user_id'];

// ── 1. User basic info ────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, first_name, last_name, full_name, email, phone, status, created_at
     FROM users WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();

// ── 2. Student details ────────────────────────────────────────────────────────
$stmt2 = $conn->prepare(
    "SELECT national_id, date_of_birth, region, city, address,
            license_class, experience_level, enrollment_status
     FROM student_details WHERE user_id = ?"
);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$studentDetails = $stmt2->get_result()->fetch_assoc();

// ── 3. Active enrollment ──────────────────────────────────────────────────────
$stmt3 = $conn->prepare(
    "SELECT e.id as enrollment_id, e.status as enrollment_status,
            e.progress_percentage, e.start_date, e.enrollment_date,
            tp.id as program_id, tp.name as program_name,
            tp.theory_hours, tp.practical_hours,
            (tp.theory_hours + tp.practical_hours) as total_hours,
            tp.fee as program_fee,
            u.full_name as instructor_name,
            u.id as instructor_id
     FROM enrollments e
     JOIN training_programs tp ON e.program_id = tp.id
     LEFT JOIN users u ON e.assigned_instructor_id = u.id
     WHERE e.student_user_id = ?
     ORDER BY e.created_at DESC LIMIT 1"
);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$enrollment = $stmt3->get_result()->fetch_assoc();

$enrollmentId = $enrollment ? (int)$enrollment['enrollment_id'] : 0;

// ── 4. Lesson stats (completed hours) ────────────────────────────────────────
$theoryDone = 0;
$practicalDone = 0;
$theoryTotal = 0;
$practicalTotal = 0;

if ($enrollmentId) {
    $stmt4 = $conn->prepare(
        "SELECT lesson_type, SUM(duration_minutes) as total_mins
         FROM lessons
         WHERE enrollment_id = ? AND attendance = 1
         GROUP BY lesson_type"
    );
    $stmt4->bind_param("i", $enrollmentId);
    $stmt4->execute();
    $res4 = $stmt4->get_result();
    while ($row = $res4->fetch_assoc()) {
        if ($row['lesson_type'] === 'theory') {
            $theoryDone = round($row['total_mins'] / 60, 1);
        } else {
            $practicalDone = round($row['total_mins'] / 60, 1);
        }
    }
    $theoryTotal    = $enrollment['theory_hours']    ?? 16;
    $practicalTotal = $enrollment['practical_hours'] ?? 24;
}

$totalHours    = ($theoryTotal + $practicalTotal);
$completedHours = $theoryDone + $practicalDone;
$progressPct   = $enrollment ? (float)$enrollment['progress_percentage'] : 0;
if ($progressPct == 0 && $totalHours > 0) {
    $progressPct = round(($completedHours / $totalHours) * 100, 1);
}

// ── 5. Upcoming lessons (next 3) ─────────────────────────────────────────────
$upcomingLessons = [];
if ($enrollmentId) {
    $stmt5 = $conn->prepare(
        "SELECT l.id, l.session_date, l.lesson_type, l.duration_minutes, l.notes,
                u.full_name as instructor_name
         FROM lessons l
         JOIN users u ON l.instructor_id = u.id
         WHERE l.enrollment_id = ? AND l.session_date >= NOW() AND l.attendance = 0
         ORDER BY l.session_date ASC LIMIT 3"
    );
    $stmt5->bind_param("i", $enrollmentId);
    $stmt5->execute();
    $res5 = $stmt5->get_result();
    while ($row = $res5->fetch_assoc()) {
        $upcomingLessons[] = $row;
    }
}

// ── 6. Exam results ───────────────────────────────────────────────────────────
$stmt6 = $conn->prepare(
    "SELECT exam_type, status, score, scheduled_date, result_date
     FROM exams
     WHERE student_user_id = ?
     ORDER BY scheduled_date DESC"
);
$stmt6->bind_param("i", $user_id);
$stmt6->execute();
$res6 = $stmt6->get_result();
$theoryExam    = null;
$practicalExam = null;
while ($row = $res6->fetch_assoc()) {
    if ($row['exam_type'] === 'theory'    && !$theoryExam)    $theoryExam    = $row;
    if ($row['exam_type'] === 'practical' && !$practicalExam) $practicalExam = $row;
}

// ── 7. Certificate ────────────────────────────────────────────────────────────
$stmt7 = $conn->prepare(
    "SELECT c.id, c.certificate_number, c.issue_date,
            tp.name as program_name,
            u.full_name as issued_by_name
     FROM certificates c
     JOIN training_programs tp ON c.program_id = tp.id
     LEFT JOIN users u ON c.issued_by = u.id
     WHERE c.student_user_id = ?
     ORDER BY c.issue_date DESC LIMIT 1"
);
$stmt7->bind_param("i", $user_id);
$stmt7->execute();
$certificate = $stmt7->get_result()->fetch_assoc();

// ── 8. Recent notifications (last 5) ─────────────────────────────────────────
$stmt8 = $conn->prepare(
    "SELECT id, title, message, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC LIMIT 5"
);
$stmt8->bind_param("i", $user_id);
$stmt8->execute();
$notifications = $stmt8->get_result()->fetch_all(MYSQLI_ASSOC);

// ── 9. Recent lesson activity (last 5 attended) ───────────────────────────────
$recentActivity = [];
if ($enrollmentId) {
    $stmt9 = $conn->prepare(
        "SELECT l.id, l.session_date, l.lesson_type, l.duration_minutes,
                l.performance_score, l.notes, u.full_name as instructor_name
         FROM lessons l
         JOIN users u ON l.instructor_id = u.id
         WHERE l.enrollment_id = ? AND l.attendance = 1
         ORDER BY l.session_date DESC LIMIT 5"
    );
    $stmt9->bind_param("i", $enrollmentId);
    $stmt9->execute();
    $recentActivity = $stmt9->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── Build response ────────────────────────────────────────────────────────────
echo json_encode([
    "success" => true,
    "data"    => [
        "user"             => $userInfo,
        "student_details"  => $studentDetails,
        "enrollment"       => $enrollment,
        "stats" => [
            "total_hours"     => $totalHours,
            "completed_hours" => $completedHours,
            "remaining_hours" => max(0, $totalHours - $completedHours),
            "progress_percentage" => $progressPct,
            "theory_total"    => $theoryTotal,
            "theory_done"     => $theoryDone,
            "practical_total" => $practicalTotal,
            "practical_done"  => $practicalDone,
        ],
        "upcoming_lessons"  => $upcomingLessons,
        "theory_exam"       => $theoryExam,
        "practical_exam"    => $practicalExam,
        "certificate"       => $certificate,
        "notifications"     => $notifications,
        "recent_activity"   => $recentActivity,
    ]
]);
?>
