<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';
include_once '../includes/audit.php';

$user = auth();
$userId = $user["user_id"];
$role = $user["role"];

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $status = $_GET["status"] ?? null;
    $type = $_GET["type"] ?? null;
    $studentIdFilter = $_GET["student_id"] ?? null;

    $query = "SELECT e.*, s.first_name as student_fname, s.last_name as student_lname,
                     ex.first_name as examiner_fname, ex.last_name as examiner_lname
              FROM exams e
              JOIN users s ON e.student_user_id = s.id
              LEFT JOIN users ex ON e.conducted_by = ex.id
              WHERE 1=1";
    
    $params = [];
    $types = "";

    if ($role === "student") {
        $query .= " AND e.student_user_id = ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($studentIdFilter) {
        $query .= " AND e.student_user_id = ?";
        $params[] = $studentIdFilter;
        $types .= "i";
    }

    if ($status) {
        $query .= " AND e.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($type) {
        $query .= " AND e.exam_type = ?";
        $params[] = $type;
        $types .= "s";
    }

    $query .= " ORDER BY e.scheduled_date DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
    
    echo json_encode(["success" => true, "data" => $exams]);

} elseif ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $studentId = ($role === "student") ? $userId : ($data["student_user_id"] ?? 0);
    $examType = $data["exam_type"] ?? "theory";
    $scheduledDate = $data["scheduled_date"] ?? "";
    
    if (empty($scheduledDate)) {
        echo json_encode(["success" => false, "message" => "Scheduled date is required"]);
        exit;
    }

    // Check for existing scheduled exam of same type
    $checkStmt = $conn->prepare("SELECT id FROM exams WHERE student_user_id = ? AND exam_type = ? AND status = 'scheduled'");
    $checkStmt->bind_param("is", $studentId, $examType);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "You already have a scheduled " . $examType . " exam"]);
        exit;
    }
    
    // Eligibility checks for student
    if ($examType === "practical") {
        $progressStmt = $conn->prepare("SELECT progress_percentage FROM enrollments WHERE student_user_id = ?");
        $progressStmt->bind_param("i", $studentId);
        $progressStmt->execute();
        $progress = $progressStmt->get_result()->fetch_assoc();
        
        if (!$progress || $progress["progress_percentage"] < 80) {
            echo json_encode(["success" => false, "message" => "Need at least 80% training progress for practical exam"]);
            exit;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO exams (student_user_id, exam_type, scheduled_date, status) VALUES (?, ?, ?, 'scheduled')");
    $stmt->bind_param("iss", $studentId, $examType, $scheduledDate);
    
    if ($stmt->execute()) {
        // Audit
        log_audit($conn, (int)$studentId, 'schedule_exam', 'Scheduled ' . $examType . ' exam for student id: ' . $studentId);
        echo json_encode(["success" => true, "message" => "Exam registered successfully", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register exam"]);
    }

} elseif ($method === "PUT") {
    // Record score or Approve
    if ($role === "student") {
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    $examId = $data["exam_id"] ?? 0;
    $score = $data["score"] ?? null;
    $approved = $data["approved"] ?? null;
    
    if ($examId == 0) {
        echo json_encode(["success" => false, "message" => "Exam ID required"]);
        exit;
    }

    if ($score !== null) {
        $status = ($score >= 70) ? "passed" : "failed";
        $stmt = $conn->prepare("UPDATE exams SET score = ?, status = ?, result_date = NOW(), conducted_by = ? WHERE id = ?");
        $stmt->bind_param("dsii", $score, $status, $userId, $examId);
    } elseif ($approved !== null && ($role === "manager" || $role === "supervisor")) {
        $stmt = $conn->prepare("UPDATE exams SET approved = ? WHERE id = ?");
        $stmt->bind_param("ii", $approved, $examId);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid update data or insufficient permissions"]);
        exit;
    }
    
    if ($stmt->execute()) {
        // Audit
        if ($score !== null) {
            log_audit($conn, $userId, 'update_exam_score', 'Updated exam id ' . $examId . ' with score ' . $score);
        } elseif ($approved !== null) {
            log_audit($conn, $userId, 'approve_exam', 'Set approved=' . $approved . ' for exam id ' . $examId);
        }
        echo json_encode(["success" => true, "message" => "Exam updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update exam"]);
    }

} elseif ($method === "DELETE") {
    $examId = $_GET["id"] ?? 0;
    
    if ($examId == 0) {
        echo json_encode(["success" => false, "message" => "Exam ID required"]);
        exit;
    }

    // Students can only cancel njihov scheduled exams
    if ($role === "student") {
        $stmt = $conn->prepare("DELETE FROM exams WHERE id = ? AND student_user_id = ? AND status = 'scheduled'");
        $stmt->bind_param("ii", $examId, $userId);
    } else {
        $stmt = $conn->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->bind_param("i", $examId);
    }

    if ($stmt->execute()) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Exam cancelled"]);
        } else {
            echo json_encode(["success" => false, "message" => "No scheduled exam found or unauthorized"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to cancel exam"]);
    }
}
?>

