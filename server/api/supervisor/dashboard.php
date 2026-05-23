<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/db.php';
include_once '../../includes/auth.php';

$user = auth();

if ($user["role"] !== "supervisor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Access denied"]);
    exit;
}

// Get total instructors
$instructor_query = "SELECT COUNT(*) as total FROM users WHERE role = 'instructor'";
$instructor_result = $conn->query($instructor_query);
if (!$instructor_result) {
    echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
    exit;
}
$total_instructors = $instructor_result->fetch_assoc()['total'];

// Get active instructors (those with at least one approved enrollment)
$active_instructor_query = "SELECT COUNT(DISTINCT assigned_instructor_id) as total FROM enrollments WHERE status = 'approved' AND assigned_instructor_id > 0";
$active_instructor_result = $conn->query($active_instructor_query);
$active_instructors = $active_instructor_result ? $active_instructor_result->fetch_assoc()['total'] : 0;

// Get total students supervised
$student_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$student_result = $conn->query($student_query);
$total_students = $student_result ? $student_result->fetch_assoc()['total'] : 0;

// Get pending exam approvals
$exam_approval_query = "SELECT COUNT(*) as total FROM exams WHERE approved = 0";
$exam_approval_result = $conn->query($exam_approval_query);
$exam_approvals = $exam_approval_result ? $exam_approval_result->fetch_assoc()['total'] : 0;

// Get open complaints
$complaints_query = "SELECT COUNT(*) as total FROM complaints WHERE status != 'resolved'";
$complaints_result = $conn->query($complaints_query);
$open_complaints = $complaints_result ? $complaints_result->fetch_assoc()['total'] : 0;

// Get recent exam approval requests
$recent_approvals_query = "
    SELECT ex.*, u.full_name as student_name, p.name as program_name
    FROM exams ex
    JOIN users u ON ex.student_user_id = u.id
    LEFT JOIN enrollments e ON e.student_user_id = u.id
    LEFT JOIN training_programs p ON e.program_id = p.id
    WHERE ex.approved = 0
    ORDER BY ex.created_at DESC
    LIMIT 3
";
$recent_approvals_result = $conn->query($recent_approvals_query);
$recent_approvals = [];
if ($recent_approvals_result) {
    while ($row = $recent_approvals_result->fetch_assoc()) {
        $recent_approvals[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data" => [
        "stats" => [
            "active_instructors" => $active_instructors,
            "total_students" => $total_students,
            "pending_approvals" => $exam_approvals,
            "open_complaints" => $open_complaints
        ],
        "recent_approvals" => $recent_approvals
    ]
]);
?>
