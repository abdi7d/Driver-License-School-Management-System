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
$res = $conn->query("SELECT COUNT(*) as total FROM student_details WHERE enrollment_status='pending'");
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
        name, 
        (SELECT COUNT(*) FROM student_details sd WHERE sd.license_class = tp.license_category) as students
    FROM training_programs tp
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
