<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Approve readiness for exam
    if (!isset($data->exam_id) || !isset($data->approved)) {
        echo json_encode(["error" => "Missing exam_id or approved status"]);
        exit;
    }

    $query = "UPDATE exams SET approved = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $data->approved, $data->exam_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Exam readiness updated"]);
    } else {
        echo json_encode(["error" => "Failed to update exam readiness"]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    // List pending approvals for exams
    $query = "
        SELECT ex.*, u.full_name as student_name
        FROM exams ex
        JOIN users u ON ex.student_user_id = u.id
        WHERE ex.approved = false
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $exams
    ]);
}
?>
