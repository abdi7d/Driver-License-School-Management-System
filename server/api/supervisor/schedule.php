<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create new lesson schedule
    if (!isset($data->enrollment_id) || !isset($data->instructor_id) || !isset($data->session_date) || !isset($data->lesson_type) || !isset($data->duration_minutes)) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $query = "INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, created_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissii", $data->enrollment_id, $data->instructor_id, $data->session_date, $data->lesson_type, $data->duration_minutes, $user["user_id"]);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Lesson scheduled successfully"]);
    } else {
        echo json_encode(["error" => "Failed to schedule lesson"]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
    // Adjust existing schedule
    if (!isset($data->lesson_id) || !isset($data->session_date)) {
        echo json_encode(["error" => "Missing lesson_id or session_date"]);
        exit;
    }

    $query = "UPDATE lessons SET session_date = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $data->session_date, $data->lesson_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Schedule adjusted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to adjust schedule"]);
    }
}
?>
