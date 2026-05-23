<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
$res = $conn->query('DESCRIBE training_programs');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
