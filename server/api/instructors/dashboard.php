<?php
include_once __DIR__ . "/../../config/db.php";
include_once __DIR__ . "/../../includes/auth.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "instructor" && $user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$instructor_id = $user["user_id"];
if ($user["role"] === "manager" && isset($_GET["instructor_id"])) {
    $instructor_id = $_GET["instructor_id"];
}

// Get total students
$stmt = $conn->prepare("SELECT COUNT(DISTINCT student_user_id) as count FROM enrollments WHERE assigned_instructor_id = ?");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()["count"];

// Get lessons today
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM lessons WHERE instructor_id = ? AND DATE(session_date) = ?");
$stmt->bind_param("is", $instructor_id, $today);
$stmt->execute();
$lessons_today = $stmt->get_result()->fetch_assoc()["count"];

// Get pending evaluations (lessons completed but no score)
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM lessons WHERE instructor_id = ? AND attendance = 1 AND performance_score IS NULL");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$pending_evals = $stmt->get_result()->fetch_assoc()["count"];

// Get average rating
$stmt = $conn->prepare("SELECT AVG(performance_score) as avg FROM lessons WHERE instructor_id = ? AND performance_score IS NOT NULL");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$avg_rating = $stmt->get_result()->fetch_assoc()["avg"] ?? 0;

// Recent lessons
$stmt = $conn->prepare("
    SELECT l.*, u.first_name, u.last_name, tp.name as program_name
    FROM lessons l
    JOIN enrollments e ON l.enrollment_id = e.id
    JOIN users u ON e.student_user_id = u.id
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE l.instructor_id = ?
    ORDER BY l.session_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$recent_lessons = [];
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    $recent_lessons[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => [
        "stats" => [
            "total_students" => $total_students,
            "lessons_today" => $lessons_today,
            "pending_evaluations" => $pending_evals,
            "average_rating" => round($avg_rating, 1)
        ],
        "recent_lessons" => $recent_lessons
    ]
]);
?>
