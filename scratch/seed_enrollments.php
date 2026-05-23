<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';

// 1. Get a student
$student = $conn->query("SELECT id FROM users WHERE role = 'student' LIMIT 1")->fetch_assoc();
if (!$student) {
    echo "No student found. Creating one...\n";
    $pass = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (full_name, email, password_hash, role, status) VALUES ('Test Student', 'student@test.com', '$pass', 'student', 'active')");
    $student_id = $conn->insert_id;
} else {
    $student_id = $student['id'];
}

// 2. Get a program
$program = $conn->query("SELECT id FROM training_programs LIMIT 1")->fetch_assoc();
if (!$program) {
    echo "No program found. Creating one...\n";
    $conn->query("INSERT INTO training_programs (name, description, duration_days, price) VALUES ('Regular Driving', 'Full course', 30, 5000)");
    $program_id = $conn->insert_id;
} else {
    $program_id = $program['id'];
}

// 3. Create Enrollment
$check = $conn->query("SELECT id FROM enrollments WHERE student_user_id = $student_id AND program_id = $program_id");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO enrollments (student_user_id, program_id, status, progress_percentage) VALUES ($student_id, $program_id, 'active', 10)");
    echo "Enrollment created for Student ID $student_id and Program ID $program_id\n";
} else {
    echo "Enrollment already exists.\n";
}
?>
