<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("SELECT instructor_id, AVG(performance_score) as avg_score FROM lessons GROUP BY instructor_id");
$stmt->execute();
$result = $stmt->get_result();

$instructor_performance = [];
while ($row = $result->fetch_assoc()) {
    $instructor_performance[] = $row;
}

echo json_encode(["instructor_performance" => $instructor_performance]);
?>
