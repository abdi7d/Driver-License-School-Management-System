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
$id           = intval($data["id"] ?? 0);
$name         = trim($data["name"] ?? "");
$theory_hours   = intval($data["theory_hours"] ?? 0);
$practical_hours = intval($data["practical_hours"] ?? 0);
$fee          = floatval($data["fee"] ?? 0);
$delete_flag  = isset($data["delete"]) && $data["delete"] == true;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Program ID is required"]);
    exit;
}

if ($delete_flag) {
    // Soft delete: check if any active enrollments exist
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE program_id = ? AND status = 'active'");
    $check->bind_param("i", $id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()["cnt"];
    if ($cnt > 0) {
        echo json_encode(["success" => false, "message" => "Cannot delete program with active enrollments ($cnt students enrolled)"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM training_programs WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Program deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete program: " . $stmt->error]);
    }
    exit;
}

if (!$name) {
    echo json_encode(["success" => false, "message" => "Program name is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE training_programs SET name = ?, theory_hours = ?, practical_hours = ?, fee = ? WHERE id = ?");
$stmt->bind_param("siidi", $name, $theory_hours, $practical_hours, $fee, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Program updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update program: " . $stmt->error]);
}
?>
