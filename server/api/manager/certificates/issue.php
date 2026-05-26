<?php
include "../../../config/db.php";
include "../../../includes/auth.php";
include "../../../includes/notifications.php";

header('Content-Type: application/json');

$user = auth();
if ($user["role"] !== "manager") {
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data["student_id"] ?? null;
$program_id = $data["program_id"] ?? null;

if (!$student_id || !$program_id) {
    echo json_encode(["success" => false, "message" => "Student and Program are required"]);
    exit;
}

$certificate_number = "CERT-" . strtoupper(uniqid());
$issue_date = date("Y-m-d H:i:s");
$issued_by = $user["user_id"];

$stmt = $conn->prepare("INSERT INTO certificates (student_user_id, program_id, certificate_number, issue_date, issued_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iissi", $student_id, $program_id, $certificate_number, $issue_date, $issued_by);

if ($stmt->execute()) {
    // Update student_details status to graduated
    $update = $conn->prepare("UPDATE student_details SET enrollment_status = 'graduated' WHERE user_id = ?");
    $update->bind_param("i", $student_id);
    $update->execute();
    notifyUser($conn, $student_id, 'Certificate issued', 'Your certificate has been issued successfully. Please download it from the certificates page.');
    
    echo json_encode(["success" => true, "message" => "Certificate issued successfully", "certificate_number" => $certificate_number]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to issue certificate: " . $stmt->error]);
}
?>
