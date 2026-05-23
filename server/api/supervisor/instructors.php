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

$query = "
    SELECT u.id, u.full_name, u.email, u.phone, id.license_number, id.experience_years, id.specialization, id.availability,
           (SELECT COUNT(*) FROM lessons WHERE instructor_id = u.id) as total_lessons,
           (SELECT session_date FROM lessons WHERE instructor_id = u.id ORDER BY session_date DESC LIMIT 1) as last_lesson_date
    FROM users u
    LEFT JOIN instructor_details id ON u.id = id.user_id
    WHERE u.role = 'instructor'
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$instructors = [];
while ($row = $result->fetch_assoc()) {
    $instructors[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $instructors
]);
?>
