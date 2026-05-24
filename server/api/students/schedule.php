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

$query = "
    SELECT l.id, l.session_date, l.lesson_type, (l.duration_minutes / 60.0) as duration, u.full_name as instructor_name
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
    $start = $row['lesson_date'];
    // Assume duration in hours, add to date
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $row['duration'] . ' hours'));
    $events[] = [
        'id' => $row['id'],
        'title' => ucfirst($row['lesson_type']) . ' with ' . $row['instructor_name'],
        'start' => $start,
        'end' => $end,
        'lesson_type' => $row['lesson_type']
    ];
}

echo json_encode($events);
?>