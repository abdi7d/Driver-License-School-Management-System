<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/db.php';
include_once '../includes/auth.php';

$user = auth();
$userId = $user["user_id"];
$role = $user["role"];

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    // Get basic user info
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    
    if (!$userData) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }
    
    // Get role-specific details
    $details = null;
    if ($role === "student") {
        $detailStmt = $conn->prepare("SELECT * FROM student_details WHERE user_id = ?");
        $detailStmt->bind_param("i", $userId);
        $detailStmt->execute();
        $details = $detailStmt->get_result()->fetch_assoc();
    } elseif ($role === "instructor") {
        $detailStmt = $conn->prepare("SELECT * FROM instructor_details WHERE user_id = ?");
        $detailStmt->bind_param("i", $userId);
        $detailStmt->execute();
        $details = $detailStmt->get_result()->fetch_assoc();
    }

    // Get documents
    $docStmt = $conn->prepare("SELECT document_type, file_path FROM documents WHERE user_id = ?");
    $docStmt->bind_param("i", $userId);
    $docStmt->execute();
    $docResult = $docStmt->get_result();
    $documents = [];
    while ($doc = $docResult->fetch_assoc()) {
        $documents[$doc['document_type']] = $doc['file_path'];
    }
    
    echo json_encode([
        "success" => true,
        "data" => [
            "user" => $userData,
            "details" => $details,
            "documents" => $documents
        ]
    ]);
} elseif ($method === "POST" || $method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No data provided"]);
        exit;
    }
    
    $firstName = $data["first_name"] ?? "";
    $lastName = $data["last_name"] ?? "";
    $phone = $data["phone"] ?? "";
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update basic info
        if (!empty($firstName) || !empty($lastName) || !empty($phone)) {
            $updateFields = [];
            $params = [];
            $types = "";
            
            if (!empty($firstName)) { $updateFields[] = "first_name = ?"; $params[] = $firstName; $types .= "s"; }
            if (!empty($lastName)) { $updateFields[] = "last_name = ?"; $params[] = $lastName; $types .= "s"; }
            if (!empty($phone)) { $updateFields[] = "phone = ?"; $params[] = $phone; $types .= "s"; }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $params[] = $userId;
                $types .= "i";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
            }
        }
        
        // Update role-specific details
        if ($role === "student" && isset($data["details"])) {
            $d = $data["details"];
            $nationalId = $d["national_id"] ?? "";
            $dob = $d["date_of_birth"] ?? "";
            $region = $d["region"] ?? "";
            $city = $d["city"] ?? "";
            $address = $d["address"] ?? "";
            $licenseClass = $d["license_class"] ?? "";
            $expLevel = $d["experience_level"] ?? "";
            
            $stmt = $conn->prepare("INSERT INTO student_details (user_id, national_id, date_of_birth, region, city, address, license_class, experience_level) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE 
                                   national_id = VALUES(national_id), 
                                   date_of_birth = VALUES(date_of_birth), 
                                   region = VALUES(region), 
                                   city = VALUES(city), 
                                   address = VALUES(address), 
                                   license_class = VALUES(license_class),
                                   experience_level = VALUES(experience_level)");
            $stmt->bind_param("isssssss", $userId, $nationalId, $dob, $region, $city, $address, $licenseClass, $expLevel);
            $stmt->execute();
        } elseif ($role === "instructor" && isset($data["details"])) {
            $d = $data["details"];
            $licenseNumber = $d["license_number"] ?? "";
            $expYears = $d["experience_years"] ?? 0;
            $specialization = $d["specialization"] ?? "";
            $availability = $d["availability"] ?? "available";
            
            $stmt = $conn->prepare("INSERT INTO instructor_details (user_id, license_number, experience_years, specialization, availability) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE 
                                   license_number = VALUES(license_number), 
                                   experience_years = VALUES(experience_years), 
                                   specialization = VALUES(specialization), 
                                   availability = VALUES(availability)");
            $stmt->bind_param("isiss", $userId, $licenseNumber, $expYears, $specialization, $availability);
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
