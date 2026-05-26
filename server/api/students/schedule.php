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

$query = "
    SELECT l.id, l.session_date, l.lesson_type,
           ROUND(l.duration_minutes / 60.0, 2) as duration,
           l.duration_minutes,
           l.attendance, l.performance_score, l.notes,
           u.full_name as instructor_name
    FROM lessons l
    JOIN users u ON l.instructor_id = u.id
    JOIN enrollments e ON l.enrollment_id = e.id
    WHERE e.student_user_id = ?
    ORDER BY l.session_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $start = $row['session_date'];
    $durationHours = $row['duration'] ?? 1;
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $durationHours . ' hours'));

    // Determine status from attendance flag and date
    $sessionTime = strtotime($start);
    $now = time();
    if ($row['attendance'] == 1) {
        $status = 'completed';
    } elseif ($sessionTime < $now) {
        $status = 'missed';
    } else {
        $status = 'upcoming';
    }

    $events[] = [
        'id'            => $row['id'],
        'session_date'  => $row['session_date'],
        'title'         => ucfirst($row['lesson_type']) . ' with ' . $row['instructor_name'],
        'start'         => $start,
        'end'           => $end,
        'lesson_type'   => $row['lesson_type'],
        'duration_minutes' => $row['duration_minutes'],
        'attendance'    => $row['attendance'],
        'performance_score' => $row['performance_score'],
        'notes'         => $row['notes'],
        'instructor_name' => $row['instructor_name'],
        'status'        => $status,
        'program_name'  => '',
    ];
}

echo json_encode(["success" => true, "data" => $events]);
?>