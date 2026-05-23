<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("SELECT SUM(fee) as total_revenue FROM training_programs");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(["total_revenue" => $row["total_revenue"] ?? 0]);
?>
