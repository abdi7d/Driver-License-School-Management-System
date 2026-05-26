<?php
include "../../config/db.php";
include "../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Stats
$stats = [];

// Total Students
$res = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student'");
$stats['total_students'] = $res->fetch_assoc()['total'];

// Active Enrollments
$res = $conn->query("SELECT COUNT(*) as total FROM student_details WHERE enrollment_status='active'");
$stats['active_enrollments'] = $res->fetch_assoc()['total'];

// Pending Approvals
$res = $conn->query("SELECT COUNT(*) as total FROM students WHERE enrollment_status='pending'");
$stats['pending_approvals'] = $res->fetch_assoc()['total'];

// Certificates Issued
$res = $conn->query("SELECT COUNT(*) as total FROM certificates");
$stats['certificates_issued'] = $res->fetch_assoc()['total'];

// Pending List
$pendingList = [];
$res = $conn->query("
    SELECT 
        u.id, 
        CONCAT(u.first_name, ' ', u.last_name) as name, 
        sd.license_class as program, 
        sd.created_at as date
    FROM users u
    JOIN student_details sd ON u.id = sd.user_id
    WHERE sd.enrollment_status = 'pending'
    ORDER BY sd.created_at DESC
    LIMIT 5
");
while ($row = $res->fetch_assoc()) {
    $pendingList[] = $row;
}

// Enrollment by Program
$programs = [];
$res = $conn->query("
    SELECT 
        tp.name, 
        COUNT(e.id) as students
    FROM training_programs tp
    LEFT JOIN enrollments e ON tp.id = e.program_id
    GROUP BY tp.id
    ORDER BY students DESC
    LIMIT 5
");
while ($row = $res->fetch_assoc()) {
    $programs[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => [
        "stats" => $stats,
        "pending" => $pendingList,
        "programs" => $programs
    ]
]);
?>
