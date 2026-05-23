<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$stmt = $conn->prepare("
 SELECT 
        id, 
        CONCAT(first_name, ' ', last_name) AS name, 
        email,
        role, 
        status,
        created_at as joined
    FROM users
    ORDER BY created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $users
]);
?>
