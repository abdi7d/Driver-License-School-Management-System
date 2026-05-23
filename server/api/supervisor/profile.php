<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

// Fetch supervisor profile details
$query = "SELECT id, full_name as name, email, phone, role, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user["id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    
    // Add stats
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM enrollments) as total_assignments,
        (SELECT COUNT(*) FROM exams WHERE approved = 1) as exams_approved,
        (SELECT COUNT(*) FROM complaints WHERE status = 'resolved') as complaints_resolved";
    $stats_result = $conn->query($stats_query);
    $profile['stats'] = $stats_result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "data" => $profile
    ]);
} else {
    echo json_encode(["error" => "User not found"]);
}
?>
