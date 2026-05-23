<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
$query = "SELECT l.*, u.full_name as student_name, tp.name as program_name 
          FROM lessons l
          JOIN enrollments e ON l.enrollment_id = e.id
          JOIN users u ON e.student_user_id = u.id
          JOIN training_programs tp ON e.program_id = tp.id
          WHERE l.instructor_id = 1
          ORDER BY l.session_date DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->execute();
    $result = $stmt->get_result();
    echo "Success. Rows: " . $result->num_rows;
}
?>
