<?php
function log_audit($conn, $userId, $action, $details = null, $ip = null) {
    if (!$conn || !$userId || !$action) return false;
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param('isss', $userId, $action, $details, $ip);
    return $stmt->execute();
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return null;
}

?>
