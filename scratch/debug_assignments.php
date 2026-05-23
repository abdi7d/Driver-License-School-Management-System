<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';

echo "--- DATABASE CHECK ---\n";
$res = $conn->query("SELECT COUNT(*) as count FROM enrollments");
echo "Enrollments: " . ($res ? $res->fetch_assoc()['count'] : "ERROR: " . $conn->error) . "\n";

$res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'instructor'");
echo "Instructors: " . ($res ? $res->fetch_assoc()['count'] : "ERROR: " . $conn->error) . "\n";

echo "\n--- API Logic Check (assignments.php query) ---\n";
$query = "
    SELECT e.id as enrollment_id, u_student.full_name as student_name, 
           u_student.email as student_email, p.name as program_name,
           u_instructor.full_name as instructor_name, e.assigned_instructor_id as instructor_id,
           e.status, e.progress_percentage as progress, e.created_at as enrolled_at
    FROM enrollments e
    JOIN users u_student ON e.student_user_id = u_student.id
    JOIN training_programs p ON e.program_id = p.id
    LEFT JOIN users u_instructor ON e.assigned_instructor_id = u_instructor.id
";
$res = $conn->query($query);
if (!$res) {
    echo "SQL ERROR: " . $conn->error . "\n";
} else {
    echo "Rows found by assignments query: " . $res->num_rows . "\n";
    while ($row = $res->fetch_assoc()) {
        echo "- Student: " . $row['student_name'] . ", Instr: " . ($row['instructor_name'] ?? 'NULL') . "\n";
    }
}
?>
