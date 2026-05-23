<?php
include_once __DIR__ . "/../../config/db.php";
include_once __DIR__ . "/../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();

if ($user["role"] !== "instructor" && $user["role"] !== "manager" && $user["role"] !== "supervisor") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$target_user_id = $user["user_id"];

if (isset($_GET["user_id"]) && ($user["role"] === "manager" || $user["role"] === "supervisor")) {
    $target_user_id = $_GET["user_id"];
}

if ($method === "GET") {
    // Fetch user details + instructor details
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at as join_date,
               id.license_number, id.experience_years, id.specialization, id.availability,
               (SELECT COUNT(DISTINCT student_user_id) FROM enrollments WHERE assigned_instructor_id = u.id) as total_students,
               (SELECT COUNT(*) FROM lessons WHERE instructor_id = u.id AND attendance = 1) as total_sessions,
               (SELECT AVG(performance_score) FROM lessons WHERE instructor_id = u.id AND performance_score IS NOT NULL) as avg_score
        FROM users u
        LEFT JOIN instructor_details id ON u.id = id.user_id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $target_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $profile]);
    } else {
        echo json_encode(["success" => false, "message" => "Profile not found"]);
    }
    exit;
}

if ($method === "POST" || $method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $first_name = $data["first_name"] ?? "";
    $last_name = $data["last_name"] ?? "";
    $phone = $data["phone"] ?? "";
    $license_number = $data["license_number"] ?? "";
    $experience_years = $data["experience_years"] ?? 0;
    $specialization = $data["specialization"] ?? "";
    $availability = $data["availability"] ?? "available";

    // Update basic user info
    $stmt_u = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt_u->bind_param("sssi", $first_name, $last_name, $phone, $target_user_id);
    $stmt_u->execute();

    // Check if instructor details exist
    $check = $conn->prepare("SELECT id FROM instructor_details WHERE user_id = ?");
    $check->bind_param("i", $target_user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $stmt_i = $conn->prepare("
            UPDATE instructor_details 
            SET license_number=?, experience_years=?, specialization=?, availability=?
            WHERE user_id=?
        ");
        $stmt_i->bind_param("sissi", $license_number, $experience_years, $specialization, $availability, $target_user_id);
    } else {
        $stmt_i = $conn->prepare("
            INSERT INTO instructor_details (user_id, license_number, experience_years, specialization, availability)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_i->bind_param("isiss", $target_user_id, $license_number, $experience_years, $specialization, $availability);
    }

    if ($stmt_i->execute()) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update profile", "error" => $stmt_i->error]);
    }
}
?>
