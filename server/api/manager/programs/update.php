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
$id = $data["id"] ?? null;
$name = $data["name"] ?? null;
$category = $data["category"] ?? null;
$theory_hours = $data["theory_hours"] ?? 0;
$practical_hours = $data["practical_hours"] ?? 0;
$duration_hours = $data["duration_hours"] ?? 0;
$min_age = $data["min_age"] ?? 18;
$fee = $data["fee"] ?? 0;
$description = $data["description"] ?? "";
$is_active = isset($data["active"]) ? ($data["active"] ? 1 : 0) : 1;

if (!$id || !$name) {
    echo json_encode(["success" => false, "message" => "ID and name are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE training_programs SET name = ?, license_category = ?, theory_hours = ?, practical_hours = ?, duration_hours = ?, min_age = ?, fee = ?, description = ?, is_active = ? WHERE id = ?");
$stmt->bind_param("ssiiiidsii", $name, $category, $theory_hours, $practical_hours, $duration_hours, $min_age, $fee, $description, $is_active, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Program updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update program: " . $stmt->error]);
}
?>
