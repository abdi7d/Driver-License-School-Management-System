<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        tp.*,
        COUNT(e.id) as enrolled,
        u.first_name as creator_name
    FROM training_programs tp
    LEFT JOIN enrollments e ON tp.id = e.program_id
    LEFT JOIN users u ON tp.created_by = u.id
    GROUP BY tp.id
    ORDER BY tp.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

$programs = [];
while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $programs
]);
?>
