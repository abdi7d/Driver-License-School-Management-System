<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) as total_students FROM users WHERE role='student'");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(["total_students" => $row["total_students"] ?? 0]);
?>
