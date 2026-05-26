<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

// Get all students (active users with role=student)
$students = [];
$res = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM users WHERE role = 'student' AND status = 'active' ORDER BY first_name");
while ($row = $res->fetch_assoc()) { $students[] = $row; }

// Get all instructors (active users with role=instructor)
$instructors = [];
$res = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'instructor' AND status = 'active' ORDER BY first_name");
while ($row = $res->fetch_assoc()) { $instructors[] = $row; }

// Get all programs
$programs = [];
$res = $conn->query("SELECT id, name, theory_hours, practical_hours, fee FROM training_programs ORDER BY name");
while ($row = $res->fetch_assoc()) { $programs[] = $row; }

echo json_encode([
    "success" => true,
    "data" => compact("students", "instructors", "programs")
]);
?>
