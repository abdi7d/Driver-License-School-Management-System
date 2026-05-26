<?php
function log_audit($db, $userId, $action, $details = null, $ip = null) {
    if (!$db || !$userId || !$action) return false;
    $ip = $ip ?? get_client_ip();

    // mysqli
    if (is_object($db) && get_class($db) === 'mysqli') {
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('isss', $userId, $action, $details, $ip);
        return $stmt->execute();
    }

    // PDO
    if (is_object($db) && ($db instanceof PDO)) {
        $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':details' => $details,
            ':ip' => $ip
        ]);
    }

    return false;
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
