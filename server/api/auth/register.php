<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php'; // returns PDO instance as $pdo
include_once __DIR__ . '/../../includes/audit.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

$firstName = trim($data['first_name'] ?? $data['firstName'] ?? '');
$lastName = trim($data['last_name'] ?? $data['lastName'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = (string)($data['password'] ?? '');
$role = 'student';

// Additional student specific fields (optional)
$nationalId = trim($data['national_id'] ?? $data['nationalId'] ?? '');
$dob = trim($data['date_of_birth'] ?? $data['dateOfBirth'] ?? '');
$region = trim($data['region'] ?? '');
$city = trim($data['city'] ?? '');
$licenseClass = trim($data['license_class'] ?? $data['licenseClass'] ?? '');
$experience = trim($data['experience'] ?? '');

// Basic validation
if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}
if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

// Ensure email uniqueness
$checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->execute([$email]);
if ($checkStmt->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$status = 'pending'; // user status pending approval

$pdo->beginTransaction();
try {
    // Insert into users table
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$firstName, $lastName, $email, $phone, $passwordHash, $role, $status]);
    $userId = $pdo->lastInsertId();

    // Insert into students table (pending enrollment status)
    $studentStmt = $pdo->prepare('INSERT INTO students (user_id, program_id, enrollment_status, graduation_status) VALUES (?, NULL, ?, "in_progress")');
    $studentStmt->execute([$userId, 'pending']);
    $studentId = $pdo->lastInsertId();

    // Optional: store additional details in a separate table if needed (not required for pending flow)
    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful. Your account is pending approval.',
        'user_id' => $userId,
        'student_id' => $studentId,
        'role' => $role,
        'status' => $status
    ]);
    // Audit log (PDO)
    try {
        log_audit($pdo, (int)$userId, 'register', 'New user registered: ' . $email);
    } catch (Exception $e) {
        // ignore audit failures
    }
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>