<?php
$conn = new mysqli('localhost', 'root', '', 'driver_license_school');
$res = $conn->query("DESCRIBE exams");
while($row = $res->fetch_assoc()) echo $row['Field'] . " - " . $row['Type'] . "\n";
?>
