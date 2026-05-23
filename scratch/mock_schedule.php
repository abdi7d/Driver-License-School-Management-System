<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock the auth function
function auth() {
    return [
        "user_id" => 1,
        "role" => "instructor"
    ];
}

// Bypass include auth.php
$mock_auth = true;

// Include db
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';

$userId = 1;
$role = "instructor";

$query = "SELECT l.*, u.full_name as student_name, tp.name as program_name 
          FROM lessons l
          JOIN enrollments e ON l.enrollment_id = e.id
          JOIN users u ON e.student_user_id = u.id
          JOIN training_programs tp ON e.program_id = tp.id
          WHERE l.instructor_id = ?
          ORDER BY l.session_date DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    echo json_encode(["success" => true, "data" => $schedules]);
}
?>
