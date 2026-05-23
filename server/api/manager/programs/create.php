<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$name = $data["name"] ?? null;
$category = $data["category"] ?? null;
$theory_hours = $data["theory_hours"] ?? 0;
$practical_hours = $data["practical_hours"] ?? 0;
$duration_hours = $data["duration_hours"] ?? 0;
$min_age = $data["min_age"] ?? 18;
$fee = $data["fee"] ?? 0;
$description = $data["description"] ?? "";

if (!$name) {
    echo json_encode(["success" => false, "message" => "Program name is required"]);
    exit;
}

$created_by = $user["user_id"];

$stmt = $conn->prepare("INSERT INTO training_programs (name, license_category, theory_hours, practical_hours, duration_hours, min_age, fee, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiiidsi", $name, $category, $theory_hours, $practical_hours, $duration_hours, $min_age, $fee, $description, $created_by);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Program created successfully", "id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to create program: " . $stmt->error]);
}
?>
