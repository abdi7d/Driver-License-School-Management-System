<?php
include "../../config/db.php";
include "../../includes/auth.php";

header("Content-Type: application/json");

$user = auth();

// CHECK FILE EXISTS
if (!isset($_FILES["file"])) {
    echo json_encode(["error" => "No file uploaded"]);
    exit;
}

// FIX: safe check for type
$type = $_POST["type"] ?? "other";

// CREATE UPLOAD PATH
$uploadDir = "../../uploads/";

// ENSURE FILE NAME IS SAFE
$fileName = time() . "_" . basename($_FILES["file"]["name"]);
$targetFile = $uploadDir . $fileName;

// MOVE FILE
if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {

    $stmt = $conn->prepare("
        INSERT INTO documents (user_id, document_type, file_path)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iss", $user["user_id"], $type, $fileName);

    $stmt->execute();

    echo json_encode([
        "message" => "File uploaded successfully",
        "file" => $fileName
    ]);

} else {
    echo json_encode([
        "error" => "Upload failed (check folder permissions or path)"
    ]);
}
?>