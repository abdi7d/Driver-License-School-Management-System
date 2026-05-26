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
include_once __DIR__ . '/../../includes/auth.php';

$user = auth();
$userId = $user['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$current = (string)($data['current_password'] ?? '');
$new = (string)($data['new_password'] ?? '');

if ($current === '' || $new === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Both current and new passwords are required']);
    exit;
}

if (strlen($new) < 6) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $row = $result->fetch_assoc();
    $hash = $row['password_hash'] ?? '';

    if (!password_verify($current, $hash)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $up = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $up->bind_param('si', $newHash, $userId);
    $up->execute();

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

?>
