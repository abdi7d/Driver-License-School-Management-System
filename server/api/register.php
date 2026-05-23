<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data provided"]);
    exit;
}

$fullName = $data["name"] ?? "";
$email = $data["email"] ?? "";
$phone = $data["phone"] ?? "";
$password = $data["password"] ?? "";
$role = $data["role"] ?? "student";

// Validation
if (empty($fullName) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

// Split name into first and last name
$nameParts = explode(" ", $fullName, 2);
$firstName = $nameParts[0];
$lastName = $nameParts[1] ?? "";

// Check if email exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered"]);
    exit;
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$status = "pending"; // All new registrations are pending approval

// Start transaction
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $passwordHash, $role, $status);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create user account");
    }
    
    $userId = $conn->insert_id;
    
    // If student, add details
    if ($role === "student") {
        $nationalId = $data["national_id"] ?? "";
        $dob = $data["date_of_birth"] ?? "";
        $region = $data["region"] ?? "";
        $city = $data["city"] ?? "";
        $licenseClass = $data["license_class"] ?? "";
        $experience = $data["experience"] ?? "";
        
        $detailsStmt = $conn->prepare("INSERT INTO student_details (user_id, national_id, date_of_birth, region, city, license_class, experience_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $detailsStmt->bind_param("issssss", $userId, $nationalId, $dob, $region, $city, $licenseClass, $experience);
        
        if (!$detailsStmt->execute()) {
            throw new Exception("Failed to save student details");
        }
    }
    
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Registration successful. Pending admin approval."]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
