<?php
include_once __DIR__ . "/../../config/db.php";
include_once __DIR__ . "/../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();

if ($user["role"] !== "instructor" && $user["role"] !== "manager") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$instructor_id = $user["user_id"];

if ($user["role"] === "manager" && isset($_GET["instructor_id"])) {
    $instructor_id = $_GET["instructor_id"];
}

$query = "
    SELECT l.*, e.student_user_id, u.full_name as student_name, tp.name as program_name
    FROM lessons l
    JOIN enrollments e ON l.enrollment_id = e.id
    JOIN users u ON e.student_user_id = u.id
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE l.instructor_id = ?
    ORDER BY l.session_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$lessons = [];
while ($row = $result->fetch_assoc()) {
    $lessons[] = $row;
}

echo json_encode($lessons);
?>
