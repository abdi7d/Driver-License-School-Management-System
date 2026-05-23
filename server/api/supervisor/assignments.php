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
    // List all enrollments with student and instructor info
    $query = "
        SELECT 
            e.id as enrollment_id,
            e.assigned_instructor_id as instructor_id,
            u_student.full_name as student_name,
            u_student.email as student_email,
            p.name as program_name,
            u_instructor.full_name as instructor_name,
            e.enrollment_date as enrolled_at,
            e.progress_percentage as progress,
            e.status
        FROM enrollments e
        JOIN users u_student ON e.student_user_id = u_student.id
        JOIN training_programs p ON e.program_id = p.id
        LEFT JOIN users u_instructor ON e.assigned_instructor_id = u_instructor.id
        ORDER BY e.enrollment_date DESC
    ";
    
    $result = $conn->query($query);
    if (!$result) {
        echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
        exit;
    }
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "data" => $assignments
    ]);
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assign an instructor to an enrollment
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->enrollment_id) || !isset($data->instructor_id)) {
        echo json_encode(["error" => "Missing enrollment_id or instructor_id"]);
        exit;
    }
    
    $instructor_id = ($data->instructor_id == 0) ? null : $data->instructor_id;
    
    $query = "UPDATE enrollments SET assigned_instructor_id = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $instructor_id, $data->enrollment_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Instructor assigned successfully"]);
    } else {
        echo json_encode(["error" => "Failed to assign instructor: " . $conn->error]);
    }
}
?>
