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

// Fetch active sessions (lessons happening today or currently active)
$today = date('Y-m-d');
$query = "
    SELECT 
        l.id,
        l.lesson_type as session_type,
        l.session_date,
        l.duration_minutes as duration,
        l.created_at as start_time,
        u_instructor.full_name as instructor_name,
        u_student.full_name as student_name,
        p.name as program_name
    FROM lessons l
    JOIN users u_instructor ON l.instructor_id = u_instructor.id
    LEFT JOIN enrollments e ON l.enrollment_id = e.id
    LEFT JOIN users u_student ON e.student_user_id = u_student.id
    LEFT JOIN training_programs p ON e.program_id = p.id
    WHERE l.session_date = ?
    ORDER BY l.created_at ASC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Database error (Prepare): " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $row['status'] = 'scheduled'; // Default since column missing
    $sessions[] = $row;
}

// Calculate stats
$active_count = 0;
$completed_today = 0;
foreach ($sessions as $s) {
    if ($s['status'] === 'active') $active_count++;
    if ($s['status'] === 'completed') $completed_today++;
}

echo json_encode([
    "success" => true,
    "data" => [
        "sessions" => $sessions,
        "stats" => [
            "active_sessions" => $active_count,
            "instructors_online" => $active_count, // Simplified: instructors in active sessions
            "today_sessions" => count($sessions),
            "completed_today" => $completed_today
        ]
    ]
]);
?>
