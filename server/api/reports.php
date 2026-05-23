<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
if ($user["role"] !== "manager" && $user["role"] !== "supervisor") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$type = $_GET["type"] ?? "overview";

$data = [];

if ($type === "enrollment") {
    $query = "SELECT tp.name as program_name, COUNT(e.id) as count 
              FROM training_programs tp
              LEFT JOIN enrollments e ON tp.id = e.program_id
              GROUP BY tp.id";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) { $data["by_program"][] = $row; }
    
    $query = "SELECT status, COUNT(*) as count FROM enrollments GROUP BY status";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) { $data["by_status"][] = $row; }

} elseif ($type === "exams") {
    $query = "SELECT status, COUNT(*) as count FROM exams GROUP BY status";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) { $data["pass_fail"][] = $row; }
    
    $query = "SELECT exam_type, status, COUNT(*) as count FROM exams GROUP BY exam_type, status";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) { $data["by_type"][] = $row; }

} elseif ($type === "instructors") {
    $query = "SELECT u.first_name, u.last_name, AVG(l.performance_score) as avg_score, COUNT(l.id) as sessions
              FROM users u
              JOIN lessons l ON u.id = l.instructor_id
              WHERE u.role = 'instructor'
              GROUP BY u.id";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) { $data[] = $row; }

} else { // Overview
    $data["counts"] = [
        "students" => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0],
        "instructors" => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetch_row()[0],
        "active_enrollments" => $conn->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetch_row()[0],
        "graduated" => $conn->query("SELECT COUNT(*) FROM enrollments WHERE status = 'graduated'")->fetch_row()[0]
    ];
}

echo json_encode(["success" => true, "data" => $data]);
?>
