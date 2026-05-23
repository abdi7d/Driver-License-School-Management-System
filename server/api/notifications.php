<?php
include "../config/db.php";
include "../includes/auth.php";

header("Content-Type: application/json");

$user = auth();
if (!$user) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch notifications for the authenticated user
        $user_id = $user['user_id'];
        $query = "SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($notifications);
        break;

    case 'POST':
        // Create a new notification
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['title'], $data['message'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing title or message"]);
            exit;
        }
        // For simplicity, allow creating notifications for the authenticated user or specify user_id if admin
        $target_user_id = $user['user_id'];
        if (isset($data['user_id']) && $user['role'] === 'admin') {
            $target_user_id = $data['user_id'];
        }
        $title = $data['title'];
        $message = $data['message'];
        $query = "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $target_user_id, $title, $message);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create notification"]);
        }
        break;

    case 'PUT':
        // Mark notification as read
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing notification id"]);
            exit;
        }
        $id = $data['id'];
        $user_id = $user['user_id'];
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update notification"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}
?>
