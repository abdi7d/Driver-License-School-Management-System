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
$id                   = intval($data["id"] ?? 0);
$status               = $data["status"] ?? null;
$instructor_id        = isset($data["assigned_instructor_id"]) ? (intval($data["assigned_instructor_id"]) ?: null) : null;
$progress             = isset($data["progress_percentage"]) ? floatval($data["progress_percentage"]) : null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Enrollment ID required"]);
    exit;
}

$setParts = [];
$params   = [];
$types    = "";

if ($status !== null) {
    $allowed = ['pending', 'active', 'graduated', 'suspended', 'cancelled', 'dropped'];
    if (!in_array($status, $allowed)) {
        echo json_encode(["success" => false, "message" => "Invalid status"]);
        exit;
    }
    $setParts[] = "status = ?";
    $params[]   = $status;
    $types      .= "s";
}

if ($instructor_id !== null) {
    $setParts[] = "assigned_instructor_id = ?";
    $params[]   = $instructor_id;
    $types      .= "i";
}

if ($progress !== null) {
    $setParts[] = "progress_percentage = ?";
    $params[]   = min(100, max(0, $progress));
    $types      .= "d";
}

if (empty($setParts)) {
    echo json_encode(["success" => false, "message" => "Nothing to update"]);
    exit;
}

$params[] = $id;
$types   .= "i";

$sql  = "UPDATE enrollments SET " . implode(", ", $setParts) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        echo json_encode(["success" => false, "message" => "No enrollment found with that ID"]);
    } else {
        // Sync student_details enrollment_status if status changed
        if ($status !== null) {
            $enrRow = $conn->prepare("SELECT student_user_id FROM enrollments WHERE id = ?");
            $enrRow->bind_param("i", $id);
            $enrRow->execute();
            $enrData = $enrRow->get_result()->fetch_assoc();
            if ($enrData) {
                $sdStatus = in_array($status, ['active', 'pending']) ? $status : ($status === 'graduated' ? 'graduated' : 'dropped');
                $upd = $conn->prepare("INSERT INTO student_details (user_id, enrollment_status) VALUES (?, ?) ON DUPLICATE KEY UPDATE enrollment_status = ?");
                $upd->bind_param("iss", $enrData['student_user_id'], $sdStatus, $sdStatus);
                $upd->execute();
            }
        }
        echo json_encode(["success" => true, "message" => "Enrollment updated successfully"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Failed to update: " . $stmt->error]);
}
?>
