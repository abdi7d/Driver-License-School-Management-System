<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
if ($user["role"] === "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["lesson_id"]) || !isset($data["performance_score"])) {
    echo json_encode(["success" => false, "message" => "Lesson ID and performance score are required"]);
    exit;
}

$lessonId = $data["lesson_id"];
$score = $data["performance_score"];
$notes = $data["notes"] ?? "";

$stmt = $conn->prepare("UPDATE lessons SET performance_score = ?, notes = ? WHERE id = ?");
$stmt->bind_param("dsi", $score, $notes, $lessonId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Evaluation recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record evaluation"]);
}
?>
