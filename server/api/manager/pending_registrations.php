<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php'; // $pdo
require_once __DIR__ . '/../../includes/auth.php';

$user = auth();
if (!$user || $user['role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Fetch pending student registrations
$sql = "SELECT u.id AS user_id,
               CONCAT(u.first_name, ' ', u.last_name) AS name,
               u.email,
               u.created_at AS registration_date,
               s.program_id,
               p.name AS program_name,
               s.enrollment_status
        FROM users u
        JOIN students s ON u.id = s.user_id
        LEFT JOIN training_programs p ON s.program_id = p.id
        WHERE s.enrollment_status = 'pending'
        ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($registrations as &$reg) {
    // If program not assigned, show placeholder
    if (empty($reg['program_name'])) {
        $reg['program_name'] = null;
    }
    // Format dates
    $reg['registration_date'] = date('Y-m-d H:i:s', strtotime($reg['registration_date']));
}

echo json_encode(['success' => true, 'data' => $registrations]);
?>
