<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$sql = "
    SELECT 
        c.id,
        c.certificate_number,
        c.issue_date,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email as student_email,
        tp.name as program_name,
        CONCAT(ib.first_name, ' ', ib.last_name) as issued_by_name
    FROM certificates c
    JOIN users u ON c.student_user_id = u.id
    LEFT JOIN training_programs tp ON c.program_id = tp.id
    LEFT JOIN users ib ON c.issued_by = ib.id
    ORDER BY c.issue_date DESC
";

$res  = $conn->query($sql);
$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data"    => $data
]);
?>
