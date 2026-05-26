<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
if (file_exists("../../../includes/notifications.php")) {
    include "../../../includes/notifications.php";
}

header('Content-Type: application/json');

$user = auth();
if (!in_array($user["role"], ["manager", "admin"])) {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id    = intval($data["student_id"] ?? 0);
$program_id    = intval($data["program_id"] ?? 0);
$instructor_id = intval($data["instructor_id"] ?? 0) ?: null;

if (!$student_id || !$program_id) {
    echo json_encode(["success" => false, "message" => "Student and Program are required"]);
    exit;
}

// Check if already enrolled in this program
$check = $conn->prepare("SELECT id FROM enrollments WHERE student_user_id = ? AND program_id = ? AND status NOT IN ('cancelled', 'dropped')");
$check->bind_param("ii", $student_id, $program_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Student is already enrolled in this program"]);
    exit;
}

// Check student exists and is a student
$studentCheck = $conn->prepare("SELECT id, CONCAT(first_name,' ',last_name) as name FROM users WHERE id = ? AND role = 'student'");
$studentCheck->bind_param("i", $student_id);
$studentCheck->execute();
$studentRow = $studentCheck->get_result()->fetch_assoc();
if (!$studentRow) {
    echo json_encode(["success" => false, "message" => "Invalid student"]);
    exit;
}

$today = date("Y-m-d");

if ($instructor_id) {
    $stmt = $conn->prepare("INSERT INTO enrollments (student_user_id, program_id, assigned_instructor_id, enrollment_date, start_date, status, progress_percentage) VALUES (?, ?, ?, ?, ?, 'active', 0)");
    $stmt->bind_param("iiiss", $student_id, $program_id, $instructor_id, $today, $today);
} else {
    $stmt = $conn->prepare("INSERT INTO enrollments (student_user_id, program_id, enrollment_date, start_date, status, progress_percentage) VALUES (?, ?, ?, ?, 'active', 0)");
    $stmt->bind_param("iiss", $student_id, $program_id, $today, $today);
}

if ($stmt->execute()) {
    // Also update student_details enrollment_status to active
    $upd = $conn->prepare("INSERT INTO student_details (user_id, enrollment_status) VALUES (?, 'active') ON DUPLICATE KEY UPDATE enrollment_status = 'active'");
    $upd->bind_param("i", $student_id);
    $upd->execute();

    // Notify student
    if (function_exists('notifyUser')) {
        $prog = $conn->prepare("SELECT name FROM training_programs WHERE id = ?");
        $prog->bind_param("i", $program_id);
        $prog->execute();
        $progName = $prog->get_result()->fetch_assoc()["name"] ?? "a program";
        notifyUser($conn, $student_id, "Enrolled in Program", "You have been enrolled in $progName. Your training starts today!");
    }

    echo json_encode(["success" => true, "message" => "Student enrolled successfully", "id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to enroll student: " . $stmt->error]);
}
?>
