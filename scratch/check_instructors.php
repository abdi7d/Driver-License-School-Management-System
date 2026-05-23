<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';

echo "Table: users (instructors)\n";
$res = $conn->query("SELECT id, full_name, email FROM users WHERE role = 'instructor'");
while($row = $res->fetch_assoc()) {
    echo "- " . $row['full_name'] . " (" . $row['email'] . ")\n";
}

echo "\nTable: instructor_details\n";
$res = $conn->query("SELECT * FROM instructor_details");
while($row = $res->fetch_assoc()) {
    echo "- UserID: " . $row['user_id'] . ", Specialization: " . $row['specialization'] . "\n";
}
?>
