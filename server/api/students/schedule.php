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
    SELECT l.id, l.lesson_date, l.lesson_type, l.duration, u.full_name as instructor_name
    FROM lessons l
    JOIN users u ON l.instructor_id = u.id
    WHERE l.student_id = ?
    ORDER BY l.lesson_date ASC
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