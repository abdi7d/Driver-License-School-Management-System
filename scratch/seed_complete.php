<?php
require __DIR__ . '/../server/config/db.php';

echo "Running complete database seeder...\n";

// 1. Alter users role column to include 'finance'
$conn->query("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'supervisor', 'manager', 'admin', 'finance') NOT NULL DEFAULT 'student'");
echo "✓ Users table role column altered to include 'finance'.\n";

// Helper function to create user
function getOrCreateUser($conn, $firstName, $lastName, $email, $password, $role, $status = 'active') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        return $user['id'];
    }

    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $phone = '+251911234567';
    $stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $passHash, $role, $status);
    $stmt->execute();
    return $conn->insert_id;
}

// 2. Create testing accounts
$studentId = getOrCreateUser($conn, 'Abebe', 'Bekele', 'student@example.com', 'password', 'student', 'active');
$instructorId = getOrCreateUser($conn, 'Yonas', 'Tadesse', 'instructor@example.com', 'password', 'instructor', 'active');
$supervisorId = getOrCreateUser($conn, 'Hana', 'Girma', 'supervisor@example.com', 'password', 'supervisor', 'active');
$managerId = getOrCreateUser($conn, 'Samuel', 'Kassa', 'manager@example.com', 'password', 'manager', 'active');
$financeId = getOrCreateUser($conn, 'Leta', 'Negash', 'finance@example.com', 'password', 'finance', 'active');

echo "✓ Users seeded.\n";

// 3. Create student_details
$conn->query("INSERT IGNORE INTO student_details (user_id, national_id, date_of_birth, region, city, address, license_class, experience_level, enrollment_status) 
              VALUES ($studentId, 'ET-098877', '1998-05-15', 'Addis Ababa', 'Addis Ababa', 'Bole Subcity', '2', 'beginner', 'active')");
echo "✓ Student details seeded.\n";

// 4. Create instructor_details
$conn->query("INSERT IGNORE INTO instructor_details (user_id, license_number, experience_years, specialization, availability) 
              VALUES ($instructorId, 'INS-2026-99', 5, 'Practical & Theory Driving', 'available')");
echo "✓ Instructor details seeded.\n";

// 5. Create enrollment for Student in Private Car (Level 2)
// Private Car is ID 2 from seed data
$res = $conn->query("SELECT id FROM enrollments WHERE student_user_id = $studentId");
if ($res->num_rows == 0) {
    $enrollDate = date("Y-m-d");
    $conn->query("INSERT INTO enrollments (student_user_id, program_id, assigned_instructor_id, assigned_supervisor_id, start_date, enrollment_date, status, progress_percentage) 
                  VALUES ($studentId, 2, $instructorId, $supervisorId, '$enrollDate', '$enrollDate', 'active', 40.00)");
    $enrollmentId = $conn->insert_id;
    echo "✓ Enrollment seeded.\n";
} else {
    $enrollmentId = $res->fetch_assoc()['id'];
    echo "✓ Enrollment already exists.\n";
}

// 6. Seed lessons
$res = $conn->query("SELECT COUNT(*) as count FROM lessons WHERE enrollment_id = $enrollmentId");
if ($res->fetch_assoc()['count'] == 0) {
    $conn->query("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, attendance, performance_score, notes, created_by) 
                  VALUES ($enrollmentId, $instructorId, '2026-05-10 09:00:00', 'theory', 120, 1, 85.00, 'Understood road rules and traffic signs well.', $instructorId)");
    $conn->query("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, attendance, performance_score, notes, created_by) 
                  VALUES ($enrollmentId, $instructorId, '2026-05-12 10:00:00', 'theory', 120, 1, 90.00, 'Excellent performance in mock theoretical tests.', $instructorId)");
    $conn->query("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, attendance, performance_score, notes, created_by) 
                  VALUES ($enrollmentId, $instructorId, '2026-05-15 09:00:00', 'practical', 120, 1, 80.00, 'Good clutch control and steering synchronization.', $instructorId)");
    $conn->query("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, attendance, performance_score, notes, created_by) 
                  VALUES ($enrollmentId, $instructorId, '2026-05-28 14:00:00', 'practical', 120, 0, NULL, 'Upcoming parallel parking practice.', $instructorId)");
    echo "✓ Lessons seeded.\n";
}

// 7. Seed exams
$res = $conn->query("SELECT COUNT(*) as count FROM exams WHERE student_user_id = $studentId");
if ($res->fetch_assoc()['count'] == 0) {
    $conn->query("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status, score, result_date, conducted_by, approved) 
                  VALUES ($studentId, 'theory', '2026-05-29 10:00:00', 'scheduled', NULL, NULL, NULL, 0)");
    $conn->query("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status, score, result_date, conducted_by, approved) 
                  VALUES ($studentId, 'practical', '2026-06-05 14:00:00', 'scheduled', NULL, NULL, NULL, 0)");
    echo "✓ Exams seeded.\n";
}

// 8. Seed complaints
$res = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE student_id = $studentId");
if ($res->fetch_assoc()['count'] == 0) {
    $conn->query("INSERT INTO complaints (student_id, instructor_id, type, message, status, priority) 
                  VALUES ($studentId, $instructorId, 'Service Quality', 'Instructor arrived 15 minutes late for the practical session.', 'pending', 'medium')");
    $conn->query("INSERT INTO complaints (student_id, instructor_id, type, message, status, priority) 
                  VALUES ($studentId, $instructorId, 'Equipment Issue', 'Training car AC is not functioning properly during warm hours.', 'pending', 'low')");
    echo "✓ Complaints seeded.\n";
}

echo "Database seeding finished successfully!\n";
?>
