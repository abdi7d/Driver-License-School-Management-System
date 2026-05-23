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
$total_instructors = $instructor_result->fetch_assoc()['total'];

// Get active instructors (those with at least one active enrollment)
$active_instructor_query = "SELECT COUNT(DISTINCT instructor_id) as total FROM enrollments WHERE status = 'approved'";
$active_instructor_result = $conn->query($active_instructor_query);
$active_instructors = $active_instructor_result->fetch_assoc()['total'];

// Get pending assignments (enrollments without an instructor)
$pending_assignment_query = "SELECT COUNT(*) as total FROM enrollments WHERE (instructor_id IS NULL OR instructor_id = 0) AND status = 'approved'";
$pending_assignment_result = $conn->query($pending_assignment_query);
$pending_assignments = $pending_assignment_result->fetch_assoc()['total'];

// Get pending exam approvals
$exam_approval_query = "SELECT COUNT(*) as total FROM exams WHERE approved = 0";
$exam_approval_result = $conn->query($exam_approval_query);
$exam_approvals = $exam_approval_result->fetch_assoc()['total'];

echo json_encode([
    "success" => true,
    "data" => [
        "total_instructors" => $total_instructors,
        "active_instructors" => $active_instructors,
        "pending_assignments" => $pending_assignments,
        "exam_approvals" => $exam_approvals
    ]
]);
?>
