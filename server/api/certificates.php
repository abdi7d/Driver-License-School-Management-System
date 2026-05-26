<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';
include_once '../includes/audit.php';

$user = auth();
$userId = $user["user_id"];
$role = $user["role"];

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $studentIdFilter = $_GET["student_id"] ?? null;
    $programIdFilter = $_GET["program_id"] ?? null;

    $query = "SELECT c.*, u.first_name, u.last_name, tp.name as program_name,
                     ib.first_name as issuer_fname, ib.last_name as issuer_lname
              FROM certificates c
              JOIN users u ON c.student_user_id = u.id
              JOIN training_programs tp ON c.program_id = tp.id
              LEFT JOIN users ib ON c.issued_by = ib.id
              WHERE 1=1";
    
    $params = [];
    $types = "";

    if ($role === "student") {
        $query .= " AND c.student_user_id = ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($studentIdFilter) {
        $query .= " AND c.student_user_id = ?";
        $params[] = $studentIdFilter;
        $types .= "i";
    }

    if ($programIdFilter) {
        $query .= " AND c.program_id = ?";
        $params[] = $programIdFilter;
        $types .= "i";
    }

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $certs = [];
    while ($row = $result->fetch_assoc()) {
        $certs[] = $row;
    }
    
    echo json_encode(["success" => true, "data" => $certs]);

} elseif ($method === "POST") {
    // Only manager or admin can issue certificates/approve graduation
    if ($role !== "manager" && $role !== "admin") {
        echo json_encode(["success" => false, "message" => "Unauthorized to issue certificates"]);
        exit;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    $studentId = $data["student_user_id"] ?? 0;
    $programId = $data["program_id"] ?? 0;
    
    if ($studentId == 0 || $programId == 0) {
        echo json_encode(["success" => false, "message" => "Student ID and Program ID are required"]);
        exit;
    }

    // 1. Check if student passed both theory and practical exams
    $stmt = $conn->prepare("SELECT COUNT(*) as passed_count FROM exams WHERE student_user_id = ? AND status = 'passed' AND approved = 1");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $passedCount = $stmt->get_result()->fetch_assoc()["passed_count"];
    
    if ($passedCount < 2) {
        echo json_encode(["success" => false, "message" => "Student must pass and have approved both theory and practical exams to graduate"]);
        exit;
    }

    // 2. Check training progress
    $progressStmt = $conn->prepare("SELECT progress_percentage FROM enrollments WHERE student_user_id = ? AND program_id = ?");
    $progressStmt->bind_param("ii", $studentId, $programId);
    $progressStmt->execute();
    $enrollment = $progressStmt->get_result()->fetch_assoc();
    
    if (!$enrollment) {
        echo json_encode(["success" => false, "message" => "Enrollment not found"]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update enrollment status
        $updateStmt = $conn->prepare("UPDATE enrollments SET status = 'graduated', progress_percentage = 100 WHERE student_user_id = ? AND program_id = ?");
        $updateStmt->bind_param("ii", $studentId, $programId);
        $updateStmt->execute();

        // Update student_details status
        $detailStmt = $conn->prepare("UPDATE student_details SET enrollment_status = 'graduated' WHERE user_id = ?");
        $detailStmt->bind_param("i", $studentId);
        $detailStmt->execute();
        
        // Issue certificate
        $certNumber = "DLSM-" . date("Y") . "-" . str_pad($studentId, 5, "0", STR_PAD_LEFT);
        $certStmt = $conn->prepare("INSERT INTO certificates (student_user_id, program_id, certificate_number, issue_date, issued_by) VALUES (?, ?, ?, NOW(), ?)");
        $certStmt->bind_param("iisi", $studentId, $programId, $certNumber, $userId);
        $certStmt->execute();
        
        $conn->commit();
        // Audit
        log_audit($conn, $userId, 'issue_certificate', 'Issued certificate ' . $certNumber . ' to student id ' . $studentId);
        echo json_encode(["success" => true, "message" => "Graduation approved and certificate issued", "certificate_number" => $certNumber]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "System error: " . $e->getMessage()]);
    }
}
?>

