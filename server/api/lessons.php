<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
$userId = $user["user_id"];
$role = $user["role"];

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST" || $method === "PUT") {
    // Only instructor or supervisor can record lesson results
    if ($role === "student") {
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No data provided"]);
        exit;
    }
    
    $lessonId = $data["lesson_id"] ?? 0;
    $attendance = isset($data["attendance"]) ? ($data["attendance"] ? 1 : 0) : 0;
    $performanceScore = $data["performance_score"] ?? null;
    $notes = $data["notes"] ?? "";
    $duration = $data["duration_minutes"] ?? null;
    
    if ($lessonId == 0) {
        echo json_encode(["success" => false, "message" => "Lesson ID is required"]);
        exit;
    }
    
    // Check if lesson exists and belongs to instructor
    $checkStmt = $conn->prepare("SELECT id FROM lessons WHERE id = ? AND instructor_id = ?");
    $checkStmt->bind_param("ii", $lessonId, $userId);
    if ($role === "instructor") {
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Lesson not found or not assigned to you"]);
            exit;
        }
    }

    $sql = "UPDATE lessons SET attendance = ?, performance_score = ?, notes = ?";
    $params = [$attendance, $performanceScore, $notes];
    $types = "ids";

    if ($duration !== null) {
        $sql .= ", duration_minutes = ?";
        $params[] = $duration;
        $types .= "i";
    }

    $sql .= " WHERE id = ?";
    $params[] = $lessonId;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
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
} elseif ($method === "GET") {
    $lessonId = $_GET["id"] ?? 0;
    $enrollmentId = $_GET["enrollment_id"] ?? 0;

    if ($lessonId != 0) {
        $query = "SELECT l.*, u.first_name, u.last_name, sd.national_id, tp.name as program_name, tp.license_category
                  FROM lessons l 
                  JOIN enrollments e ON l.enrollment_id = e.id 
                  JOIN users u ON e.student_user_id = u.id 
                  LEFT JOIN student_details sd ON u.id = sd.user_id
                  JOIN training_programs tp ON e.program_id = tp.id
                  WHERE l.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $lessonId);
    } elseif ($enrollmentId != 0) {
        $query = "SELECT l.*, u.first_name, u.last_name
                  FROM lessons l 
                  JOIN enrollments e ON l.enrollment_id = e.id 
                  JOIN users u ON e.student_user_id = u.id 
                  WHERE l.enrollment_id = ?
                  ORDER BY l.session_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $enrollmentId);
    } else {
        echo json_encode(["success" => false, "message" => "ID or Enrollment ID required"]);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($lessonId != 0) {
        if ($result->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Lesson not found"]);
        } else {
            echo json_encode(["success" => true, "data" => $result->fetch_assoc()]);
        }
    } else {
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }
        echo json_encode(["success" => true, "data" => $lessons]);
    }
}
?>
