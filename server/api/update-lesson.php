<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/auth.php';

$user = auth();
if ($user["role"] === "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["lesson_id"])) {
    echo json_encode(["success" => false, "message" => "Lesson ID is required"]);
    exit;
}

$lessonId = $data["lesson_id"];
$attendance = isset($data["attendance"]) ? ($data["attendance"] ? 1 : 0) : null;
$score = $data["performance_score"] ?? null;
$notes = $data["notes"] ?? null;
$duration = $data["duration_minutes"] ?? null;

$updateFields = [];
$params = [];
$types = "";

if ($attendance !== null) {
    $updateFields[] = "attendance = ?";
    $params[] = $attendance;
    $types .= "i";
}
if ($score !== null) {
    $updateFields[] = "performance_score = ?";
    $params[] = $score;
    $types .= "d";
}
if ($notes !== null) {
    $updateFields[] = "notes = ?";
    $params[] = $notes;
    $types .= "s";
}
if ($duration !== null) {
    $updateFields[] = "duration_minutes = ?";
    $params[] = $duration;
    $types .= "i";
}

if (empty($updateFields)) {
    echo json_encode(["success" => false, "message" => "No fields to update"]);
    exit;
}

$sql = "UPDATE lessons SET " . implode(", ", $updateFields) . " WHERE id = ?";
$params[] = $lessonId;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // If attendance was marked present, update progress
    if ($attendance == 1) {
        $conn->query("UPDATE enrollments e 
                     JOIN lessons l ON e.id = l.enrollment_id 
                     SET e.progress_percentage = LEAST(100, (SELECT COUNT(*) FROM lessons WHERE enrollment_id = e.id AND attendance = 1) * 10) 
                     WHERE l.id = $lessonId");
    }
    echo json_encode(["success" => true, "message" => "Lesson updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update lesson"]);
}
?>
