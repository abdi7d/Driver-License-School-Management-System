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
$firstName = trim($data["first_name"] ?? '');
$lastName = trim($data["last_name"] ?? '');
$email = trim($data["email"] ?? '');
$password = (string)($data["password"] ?? '');
$role = trim(strtolower($data["role"] ?? 'student'));
$status = trim(strtolower($data["status"] ?? 'active'));

$validRoles = ['student', 'instructor', 'supervisor', 'manager'];
$validStatuses = ['active', 'inactive', 'pending', 'suspended'];

if (!$firstName || !$lastName || !$email || !$password || !$role) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(["success" => false, "message" => "Password must be at least 8 characters"]);
    exit;
}

if (!in_array($role, $validRoles, true)) {
    echo json_encode(["success" => false, "message" => "Invalid role specified"]);
    exit;
}

if (!in_array($status, $validStatuses, true)) {
    $status = 'active';
}

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssss', $firstName, $lastName, $email, $passwordHash, $role, $status);

if ($stmt->execute()) {
    $newUserId = $stmt->insert_id;

    if ($role === 'student') {
        $detailsStmt = $conn->prepare('INSERT INTO student_details (user_id, national_id, date_of_birth, region, city, license_class, experience_level, enrollment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $empty = '';
        $detailsStmt->bind_param('isssssss', $newUserId, $empty, $empty, $empty, $empty, $empty, $empty, $status);
        $detailsStmt->execute();
    }

    notifyUser($conn, $newUserId, 'Welcome to DLSM', "Your account has been created by the manager. You can now log in with your credentials.");
    notifyManager($conn, $user['user_id'], 'User created', "{$firstName} {$lastName} ({$role}) was successfully created.");

    echo json_encode(["success" => true, "message" => "User created successfully", "user_id" => $newUserId]);
    exit;
}

echo json_encode(["success" => false, "message" => "Failed to create user: " . $stmt->error]);
