<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM training_programs ORDER BY created_at DESC");
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
