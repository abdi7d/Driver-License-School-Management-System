<?php
include "../../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$first_name = $data["first_name"] ?? "";
$last_name  = $data["last_name"] ?? "";
$email      = $data["email"] ?? "";
$phone      = $data["phone"] ?? "";
$password   = $data["password"] ?? "";
$role       = $data["role"] ?? "student";

// Extra student fields
$national_id = $data["national_id"] ?? "";
$dob         = $data["date_of_birth"] ?? "";
$region      = $data["region"] ?? "";
$city        = $data["city"] ?? "";
$license     = $data["license_class"] ?? "";
$experience  = $data["experience"] ?? "";

if ($first_name === "" || $last_name === "" || $email === "" || $password === "") {
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, role)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $hash, $role);
    $stmt->execute();
    
    $userId = $conn->insert_id;

    if ($role === "student") {
        $stmt2 = $conn->prepare("
            INSERT INTO student_details (user_id, national_id, date_of_birth, region, city, license_class, experience_level)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param("issssss", $userId, $national_id, $dob, $region, $city, $license, $experience);
        $stmt2->execute();
    }

    $conn->commit();
    echo json_encode(["message" => "Registration successful"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["error" => $e->getMessage()]);
}
?>