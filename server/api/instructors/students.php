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

// Fetch students assigned to this instructor
$query = "
    SELECT 
        e.id as enrollment_id,
        u.id as user_id,
        u.full_name,
        u.email,
        u.phone,
        tp.name as program_name,
        e.progress_percentage as progress,
        e.status,
        e.recommended_for_exam,
        (SELECT COUNT(*) FROM lessons WHERE enrollment_id = e.id AND attendance = 1) as completed_sessions
    FROM enrollments e
    JOIN users u ON e.student_user_id = u.id
    JOIN training_programs tp ON e.program_id = tp.id
    WHERE e.assigned_instructor_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while($row = $result->fetch_assoc()) {
    $students[] = [
        "id" => $row["enrollment_id"],
        "name" => $row["full_name"],
        "email" => $row["email"],
        "phone" => $row["phone"],
        "program" => $row["program_name"],
        "progress" => (float)$row["progress"],
        "sessions" => (int)$row["completed_sessions"],
        "status" => $row["status"],
        "recommended" => (bool)$row["recommended_for_exam"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $students
]);
?>
