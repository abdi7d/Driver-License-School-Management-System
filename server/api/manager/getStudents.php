<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

// 🔐 AUTH CHECK (only manager)
$user = auth();

if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

// 📌 GET FILTER (optional)
$status = $_GET['status'] ?? "";

// 🧠 BUILD QUERY DYNAMICALLY
if ($status !== "") {
    $query = "
        SELECT id, first_name, last_name, email, phone, status, created_at 
        FROM users 
        WHERE role = 'student' AND status = ?
        ORDER BY created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status);
} else {
    $query = "
        SELECT id, first_name, last_name, email, phone, status, created_at 
        FROM users 
        WHERE role = 'student'
        ORDER BY created_at DESC
    ";
    $stmt = $conn->prepare($query);
}

// ▶️ EXECUTE
$stmt->execute();
$result = $stmt->get_result();

// 📦 FETCH DATA
$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// 📤 RESPONSE
echo json_encode([
    "message" => "Students fetched successfully",
    "count" => count($students),
    "data" => $students
]);
?>