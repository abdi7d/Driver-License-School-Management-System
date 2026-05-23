<?php
include "../../../config/db.php";
include "../../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data["student_id"] ?? null;
$program_id = $data["program_id"] ?? null;
$instructor_id = $data["instructor_id"] ?? null;

if (!$student_id || !$program_id) {
    echo json_encode(["success" => false, "message" => "Student and Program are required"]);
    exit;
}

// Check if already enrolled
$check = $conn->prepare("SELECT id FROM enrollments WHERE student_user_id = ? AND program_id = ? AND status != 'graduated'");
$check->bind_param("ii", $student_id, $program_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Student is already enrolled in this program"]);
    exit;
}

$enrollment_date = date("Y-m-d");
$status = 'pending';

$stmt = $conn->prepare("INSERT INTO enrollments (student_user_id, program_id, assigned_instructor_id, enrollment_date, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $student_id, $program_id, $instructor_id, $enrollment_date, $status);

if ($stmt->execute()) {
    // Also update student_details status
    $update = $conn->prepare("UPDATE student_details SET enrollment_status = 'pending' WHERE user_id = ?");
    $update->bind_param("i", $student_id);
    $update->execute();
    
    echo json_encode(["success" => true, "message" => "Student enrolled successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to enroll student: " . $stmt->error]);
}
?>
