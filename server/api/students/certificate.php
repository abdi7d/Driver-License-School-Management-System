<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$user_id = (int)$user['user_id'];

// Get all certificates for this student
$query = "
    SELECT c.id, c.certificate_number, c.issue_date,
           tp.name as program_name,
           sd.license_class,
           ub.full_name as issued_by_name
    FROM certificates c
    LEFT JOIN training_programs tp ON c.program_id = tp.id
    LEFT JOIN student_details sd ON c.student_user_id = sd.user_id
    LEFT JOIN users ub ON c.issued_by = ub.id
    WHERE c.student_user_id = ?
    ORDER BY c.issue_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$certificates = [];
while ($row = $result->fetch_assoc()) {
    $certificates[] = $row;
}

echo json_encode(["success" => true, "data" => $certificates]);
?>