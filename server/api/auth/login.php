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
include_once __DIR__ . '/../../includes/jwt.php';

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

$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$stmt = $conn->prepare('SELECT id, first_name, last_name, email, role, status, password_hash FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

if ($user['status'] === 'pending') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account is pending approval. Please contact administration.']);
    exit;
}

if ($user['status'] === 'inactive') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact administration.']);
    exit;
}

$payload = [
    'user_id' => (int)$user['id'],
    'role' => $user['role'],
    'email' => $user['email'],
    'exp' => time() + (defined('JWT_EXPIRATION') ? JWT_EXPIRATION : 86400)
];

$token = JWT::generate($payload);

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'token' => $token,
    'role' => $user['role'],
    'status' => $user['status'],
    'id' => (int)$user['id'],
    'name' => trim($user['first_name'] . ' ' . $user['last_name']),
    'user' => [
        'id' => (int)$user['id'],
        'name' => trim($user['first_name'] . ' ' . $user['last_name']),
        'email' => $user['email'],
        'role' => $user['role'],
        'status' => $user['status']
    ]
]);
?>