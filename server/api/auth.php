<?php
// server/api/auth.php
// Login endpoint: expects JSON {email, password}

require_once __DIR__ . '/../config/database.php'; // $pdo

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password required']);
    exit;
}

$email = $input['email'];
$password = $input['password'];

$stmt = $pdo->prepare('SELECT id, password_hash, role_id FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    exit;
}

// Fetch role name
$stmt = $pdo->prepare('SELECT name FROM roles WHERE id = :id');
$stmt->execute(['id' => $user['role_id']]);
$role = $stmt->fetchColumn();

// Simple JWT generation (header.payload.signature)
$header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
$payload = base64_encode(json_encode([
    'sub' => $user['id'],
    'role' => $role,
    'exp' => time() + 3600 * 8 // 8‑hour token
]));
$secret = 'CHANGE_THIS_SECRET_KEY'; // In production move to .env
$signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
$jwt = "$header.$payload.$signature";

echo json_encode([
    'success' => true,
    'token' => $jwt,
    'role' => $role,
    'userId' => $user['id']
]);
?>
