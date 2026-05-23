<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/auth.php';

$user = auth();
$userId = $user["user_id"];
$role = $user["role"];

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    // View schedules
    if ($role === "student") {
        $query = "SELECT l.*, u.full_name as instructor_name, tp.name as program_name 
                  FROM lessons l
                  JOIN enrollments e ON l.enrollment_id = e.id
                  JOIN users u ON l.instructor_id = u.id
                  JOIN training_programs tp ON e.program_id = tp.id
                  WHERE e.student_user_id = ?
                  ORDER BY l.session_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    } elseif ($role === "instructor") {
        $query = "SELECT l.*, u.full_name as student_name, tp.name as program_name 
                  FROM lessons l
                  JOIN enrollments e ON l.enrollment_id = e.id
                  JOIN users u ON e.student_user_id = u.id
                  JOIN training_programs tp ON e.program_id = tp.id
                  WHERE l.instructor_id = ?
                  ORDER BY l.session_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    } else { // Manager or Supervisor view all
        $query = "SELECT l.*, si.full_name as instructor_name, 
                         st.full_name as student_name, tp.name as program_name 
                  FROM lessons l
                  JOIN enrollments e ON l.enrollment_id = e.id
                  JOIN users si ON l.instructor_id = si.id
                  JOIN users st ON e.student_user_id = st.id
                  JOIN training_programs tp ON e.program_id = tp.id
                  ORDER BY l.session_date DESC";
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    
    echo json_encode(["success" => true, "data" => $schedules]);

} elseif ($method === "POST") {
    // Only instructor, supervisor, or manager can schedule
    if ($role === "student") {
        echo json_encode(["success" => false, "message" => "Only instructors or staff can schedule lessons"]);
        exit;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No data provided"]);
        exit;
    }
    
    $enrollmentId = $data["enrollment_id"] ?? 0;
    $instructorId = $data["instructor_id"] ?? $userId; // Default to self if instructor
    $sessionDate = $data["session_date"] ?? "";
    $lessonType = $data["lesson_type"] ?? "practical";
    $duration = $data["duration_minutes"] ?? 60;
    
    if ($enrollmentId == 0 || empty($sessionDate)) {
        echo json_encode(["success" => false, "message" => "Enrollment ID and session date are required"]);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO lessons (enrollment_id, instructor_id, session_date, lesson_type, duration_minutes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissii", $enrollmentId, $instructorId, $sessionDate, $lessonType, $duration, $userId);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Lesson scheduled successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to schedule lesson"]);
    }
}
?>
