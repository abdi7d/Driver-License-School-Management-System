<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("SELECT SUM(passed=1) AS passed, SUM(passed=0) AS failed FROM exams");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    "passed" => $row["passed"] ?? 0,
    "failed" => $row["failed"] ?? 0
]);
?>
