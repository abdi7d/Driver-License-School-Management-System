<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Students
$students = [];
$res = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role='student' AND status='active'");
while ($row = $res->fetch_assoc()) $students[] = $row;

// Instructors
$instructors = [];
$res = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role='instructor' AND status='active'");
while ($row = $res->fetch_assoc()) $instructors[] = $row;

// Programs
$programs = [];
$res = $conn->query("SELECT id, name, license_category FROM training_programs WHERE is_active=1");
while ($row = $res->fetch_assoc()) $programs[] = $row;

echo json_encode([
    "success" => true,
    "data" => [
        "students" => $students,
        "instructors" => $instructors,
        "programs" => $programs
    ]
]);
?>
