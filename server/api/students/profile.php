<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth(); // JWT user

$data = json_decode(file_get_contents("php://input"), true);

$national_id = $data["national_id"] ?? "";
$dob = $data["date_of_birth"] ?? "";
$region = $data["region"] ?? "";
$city = $data["city"] ?? "";
$address = $data["address"] ?? "";
$license_class = $data["license_class"] ?? "B";

if ($national_id == "" || $dob == "") {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// CHECK IF PROFILE EXISTS
$check = $conn->prepare("SELECT id FROM student_details WHERE user_id = ?");
$check->bind_param("i", $user["user_id"]);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // UPDATE
    $stmt = $conn->prepare("
        UPDATE student_details 
        SET national_id=?, date_of_birth=?, region=?, city=?, address=?, license_class=?
        WHERE user_id=?
    ");
    $stmt->bind_param("ssssssi", $national_id, $dob, $region, $city, $address, $license_class, $user["user_id"]);
} else {
    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO student_details 
        (user_id, national_id, date_of_birth, region, city, address, license_class)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssss", $user["user_id"], $national_id, $dob, $region, $city, $address, $license_class);
}

if ($stmt->execute()) {
    echo json_encode(["message" => "Profile saved"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}
?>