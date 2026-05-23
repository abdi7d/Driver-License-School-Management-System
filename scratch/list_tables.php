<?php
$conn = new mysqli('localhost', 'root', '', 'driver_license_school');
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_row()) echo $row[0]."\n";
?>
