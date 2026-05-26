<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$user_id = (int)$user['user_id'];
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        // Fetch all notifications for this user
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $unread_only = isset($_GET['unread']) && $_GET['unread'] == '1';

        $sql = "SELECT id, title, message, is_read, created_at
                FROM notifications
                WHERE user_id = ?";
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Count unread
        $countStmt = $conn->prepare(
            "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $countStmt->bind_param("i", $user_id);
        $countStmt->execute();
        $countRow = $countStmt->get_result()->fetch_assoc();
        $unread_count = (int)($countRow['unread_count'] ?? 0);

        echo json_encode([
            "success"      => true,
            "data"         => $notifications,
            "unread_count" => $unread_count,
        ]);
        break;

    case 'POST':
        // Create notification — only admin/manager/supervisor/system
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['title'], $data['message'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing title or message"]);
            exit;
        }

        // Allow specifying target user_id for admin/manager roles
        $target_user_id = $user_id;
        if (isset($data['user_id']) && in_array($user['role'], ['admin', 'manager', 'supervisor'])) {
            $target_user_id = (int)$data['user_id'];
        }

        $stmt = $conn->prepare(
            "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $target_user_id, $data['title'], $data['message']);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to create notification"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        // Mark ALL as read
        if (isset($data['mark_all_read']) && $data['mark_all_read']) {
            $stmt = $conn->prepare(
                "UPDATE notifications SET is_read = 1 WHERE user_id = ?"
            );
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "All notifications marked as read"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Failed to update"]);
            }
            break;
        }

        // Toggle single notification read/unread
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing notification id"]);
            exit;
        }

        $id = (int)$data['id'];

        // Toggle or set explicit value
        if (isset($data['is_read'])) {
            $is_read = $data['is_read'] ? 1 : 0;
        } else {
            // Toggle current value
            $current = $conn->prepare(
                "SELECT is_read FROM notifications WHERE id = ? AND user_id = ?"
            );
            $current->bind_param("ii", $id, $user_id);
            $current->execute();
            $cur = $current->get_result()->fetch_assoc();
            $is_read = $cur ? ($cur['is_read'] == 1 ? 0 : 1) : 1;
        }

        $stmt = $conn->prepare(
            "UPDATE notifications SET is_read = ? WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("iii", $is_read, $id, $user_id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "is_read" => $is_read,
                "message" => $is_read ? "Marked as read" : "Marked as unread",
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to update"]);
        }
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing notification id"]);
            exit;
        }

        $stmt = $conn->prepare(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("ii", $id, $user_id);

        if ($stmt->execute() && $conn->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Notification deleted"]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Not found or unauthorized"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
