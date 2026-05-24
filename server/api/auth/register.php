<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once __DIR__ . '/../../config/db.php';

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

$nationalId = trim($data['national_id'] ?? $data['nationalId'] ?? '');
$dob = trim($data['date_of_birth'] ?? $data['dateOfBirth'] ?? '');
$region = trim($data['region'] ?? '');
$city = trim($data['city'] ?? '');
$licenseClass = trim($data['license_class'] ?? $data['licenseClass'] ?? '');
$experience = trim($data['experience'] ?? '');

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

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();

if ($checkStmt->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$status = 'pending';

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssss', $firstName, $lastName, $email, $phone, $passwordHash, $role, $status);

    if (!$stmt->execute()) {
        throw new Exception('Failed to create user account');
    }

    $userId = $conn->insert_id;

    $detailsStmt = $conn->prepare('INSERT INTO student_details (user_id, national_id, date_of_birth, region, city, license_class, experience_level) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $detailsStmt->bind_param('issssss', $userId, $nationalId, $dob, $region, $city, $licenseClass, $experience);

    if (!$detailsStmt->execute()) {
        throw new Exception('Failed to save student details');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful. Your account is pending approval.',
        'user_id' => $userId,
        'role' => $role,
        'status' => $status,
        'next_step' => 'registration-pending',
        'redirect_to' => 'registration-pending.html'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>