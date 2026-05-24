<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();
if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$user_id = $user['user_id'];

$query = "
    SELECT c.id, c.certificate_number, c.issue_date,
           p.name as program_name, sd.license_class as license_category
    FROM certificates c
    JOIN enrollments e ON c.student_user_id = e.student_user_id AND c.program_id = e.program_id
    JOIN training_programs p ON e.program_id = p.id
    LEFT JOIN student_details sd ON c.student_user_id = sd.user_id
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

echo json_encode($certificates);
?>