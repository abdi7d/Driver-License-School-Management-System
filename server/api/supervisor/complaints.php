<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // List all complaints with student info
    $query = "
        SELECT 
            c.id,
            c.student_id,
            c.type as category,
            c.message as description,
            c.status,
            c.priority,
            c.created_at,
            u.full_name as student_name,
            u.email as student_email
        FROM complaints c
        JOIN users u ON c.student_id = u.id
        ORDER BY c.created_at DESC
    ";
    
    $result = $conn->query($query);
    if (!$result) {
        echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
        exit;
    }
    $complaints = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $complaints[] = $row;
        }
    }
    
    echo json_encode([
        "success" => true,
        "data" => $complaints
    ]);
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Update complaint status
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->complaint_id) || !isset($data->status)) {
        echo json_encode(["error" => "Missing complaint_id or status"]);
        exit;
    }
    
    $query = "UPDATE complaints SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $data->status, $data->complaint_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Complaint status updated"]);
    } else {
        echo json_encode(["error" => "Failed to update complaint"]);
    }
}
?>
