<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$sql = "
    SELECT 
        c.id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        tp.name as program_name,
        c.certificate_number,
        c.issue_date
    FROM certificates c
    JOIN users u ON c.student_user_id = u.id
    JOIN training_programs tp ON c.program_id = tp.id
    ORDER BY c.issue_date DESC
";

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
