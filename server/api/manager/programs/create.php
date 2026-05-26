<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$name         = trim($data["name"] ?? "");
$theory_hours   = intval($data["theory_hours"] ?? 0);
$practical_hours = intval($data["practical_hours"] ?? 0);
$fee          = floatval($data["fee"] ?? 0);

if (!$name) {
    echo json_encode(["success" => false, "message" => "Program name is required"]);
    exit;
}

// Check for duplicate name
$check = $conn->prepare("SELECT id FROM training_programs WHERE name = ?");
$check->bind_param("s", $name);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "A program with this name already exists"]);
    exit;
}

$created_by = $user["user_id"];

$stmt = $conn->prepare("INSERT INTO training_programs (name, theory_hours, practical_hours, fee, created_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("siidd", $name, $theory_hours, $practical_hours, $fee, $created_by);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Program created successfully", "id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to create program: " . $stmt->error]);
}
?>
