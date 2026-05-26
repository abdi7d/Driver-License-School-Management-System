<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../includes/auth.php';

$user = auth();
if (!in_array($user['role'], ['manager', 'supervisor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$params = [];
$types = '';
$where = '1=1';

if (!empty($_GET['user_id'])) {
    $where .= ' AND user_id = ?';
    $params[] = (int)$_GET['user_id'];
    $types .= 'i';
}
if (!empty($_GET['action'])) {
    $where .= ' AND action = ?';
    $params[] = $_GET['action'];
    $types .= 's';
}
if (!empty($_GET['from'])) {
    $where .= ' AND created_at >= ?';
    $params[] = $_GET['from'];
    $types .= 's';
}
if (!empty($_GET['to'])) {
    $where .= ' AND created_at <= ?';
    $params[] = $_GET['to'];
    $types .= 's';
}

$query = "SELECT a.id, a.created_at as timestamp, u.first_name, u.last_name, u.role, a.action, a.details, a.ip_address
          FROM audit_logs a
          JOIN users u ON a.user_id = u.id
          WHERE $where
          ORDER BY a.created_at DESC
          LIMIT 1000";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query prepare failed']);
    exit;
}

if (!empty($params)) {
    // bind_param requires references
    $bindNames[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindNames[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindNames);
}

$stmt->execute();
$res = $stmt->get_result();
$logs = [];
while ($row = $res->fetch_assoc()) {
    $logs[] = [
        'id' => (int)$row['id'],
        'timestamp' => $row['timestamp'],
        'user' => trim($row['first_name'] . ' ' . $row['last_name']),
        'role' => $row['role'],
        'action' => $row['action'],
        'details' => $row['details'],
        'ip' => $row['ip_address']
    ];
}

echo json_encode(['success' => true, 'data' => $logs]);

?>
