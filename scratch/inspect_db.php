<?php
$conn = new mysqli('localhost', 'root', '', 'driver_license_school');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "--- USERS TABLE ---\n";
$res = $conn->query("DESCRIBE users");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- STUDENT_DETAILS TABLE ---\n";
$res = $conn->query("DESCRIBE student_details");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- DOCUMENTS TABLE ---\n";
$res = $conn->query("DESCRIBE documents");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- TRAINING_PROGRAMS TABLE ---\n";
$res = $conn->query("DESCRIBE training_programs");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- ENROLLMENTS TABLE ---\n";
$res = $conn->query("DESCRIBE enrollments");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Enrollments table does not exist.\n";
}
?>
