<?php
include "server/config/db.php";

// 1. Create complaints table
$conn->query("
    CREATE TABLE IF NOT EXISTS complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        instructor_id INT,
        type VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "Complaints table checked/created.\n";

// 2. Get some user IDs
$res = $conn->query("SELECT id, role FROM users");
$users = [];
while($row = $res->fetch_assoc()) {
    $users[$row['role']][] = $row['id'];
}

$student_id = $users['student'][0] ?? 0;
$instructor_id = $users['instructor'][0] ?? 0;

if ($student_id > 0) {
    // 3. Seed some enrollments if empty
    $check_en = $conn->query("SELECT COUNT(*) as total FROM enrollments");
    if ($check_en->fetch_assoc()['total'] == 0) {
        $conn->query("INSERT INTO enrollments (student_user_id, program_id, status, instructor_id) VALUES ($student_id, 1, 'approved', $instructor_id)");
        echo "Seeded 1 enrollment.\n";
    }

    // 4. Seed some exams if empty
    $check_ex = $conn->query("SELECT COUNT(*) as total FROM exams");
    if ($check_ex->fetch_assoc()['total'] == 0) {
        $conn->query("INSERT INTO exams (student_user_id, exam_type, approved) VALUES ($student_id, 'Theory', 0)");
        $conn->query("INSERT INTO exams (student_user_id, exam_type, approved) VALUES ($student_id, 'Practical', 0)");
        echo "Seeded 2 exam requests.\n";
    }

    // 5. Seed some complaints if empty
    $check_cm = $conn->query("SELECT COUNT(*) as total FROM complaints");
    if ($check_cm->fetch_assoc()['total'] == 0) {
        $conn->query("INSERT INTO complaints (student_id, instructor_id, type, message, status, priority) VALUES ($student_id, $instructor_id, 'Service Quality', 'Instructor was late for the session.', 'pending', 'high')");
        $conn->query("INSERT INTO complaints (student_id, instructor_id, type, message, status, priority) VALUES ($student_id, $instructor_id, 'Equipment Issue', 'Car AC was not working.', 'pending', 'medium')");
        echo "Seeded 2 complaints.\n";
    }
} else {
    echo "No students found to seed data for.\n";
}
?>
