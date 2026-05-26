<?php
function createNotification($conn, $userId, $title, $message) {
    if (!$userId || !$title || !$message) {
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("iss", $userId, $title, $message);
    return $stmt->execute();
}

function notifyUser($conn, $userId, $title, $message) {
    return createNotification($conn, $userId, $title, $message);
}

function notifyManager($conn, $managerId, $title, $message) {
    return createNotification($conn, $managerId, $title, $message);
}

function notifyUsers($conn, $userIds, $title, $message) {
    if (!is_array($userIds)) {
        return false;
    }

    foreach ($userIds as $userId) {
        createNotification($conn, $userId, $title, $message);
    }

    return true;
}
