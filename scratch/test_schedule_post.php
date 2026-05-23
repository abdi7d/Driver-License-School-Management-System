<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$enrollmentId = 1;
$instructorId = 1;
$sessionDate = "2026-05-08 10:00:00";
$lessonType = "practical";
$duration = 60;
$userId = 1;

$stmt = $conn->prepare("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("iissii", $enrollmentId, $instructorId, $sessionDate, $lessonType, $duration, $userId);
    if ($stmt->execute()) {
        echo "Insert success.";
    } else {
        echo "Execute failed: " . $stmt->error;
    }
}
?>
