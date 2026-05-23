<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function auth() {
    return [
        "user_id" => 2, // The test student Samuel Zenebeee is user_id = 2. Instructor is 1.
        "role" => "student"
    ];
}

$mock_auth = true;
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';

$user_id = 2; // Test student

$query = "
    SELECT e.id, e.program_id, tp.name as program_name, e.status, e.progress_percentage as progress,
           tp.duration_weeks, tp.id as tp_id
    FROM enrollments e
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE e.student_user_id = ?
    ORDER BY e.created_at DESC LIMIT 1
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    die("No enrollment found");
}

$enrollmentId = $enrollment['id'];

$query2 = "
    SELECT l.id, l.session_date, l.lesson_type, l.duration_minutes, l.attendance, l.performance_score, l.notes,
           u.full_name as instructor_name
    FROM lessons l
    JOIN users u ON l.instructor_id = u.id
    WHERE l.enrollment_id = ?
    ORDER BY l.session_date DESC
";

$stmt2 = $conn->prepare($query2);
if (!$stmt2) {
    die("Prepare failed 2: " . $conn->error);
}
$stmt2->bind_param("i", $enrollmentId);
$stmt2->execute();
$result2 = $stmt2->get_result();

echo "Success! Found " . $result2->num_rows . " lessons.";
?>
