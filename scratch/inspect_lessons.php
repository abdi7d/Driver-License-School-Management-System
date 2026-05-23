<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
$res = $conn->query('DESCRIBE lessons');
if ($res) {
    while($row = $res->fetch_assoc()) echo $row['Field'] . ': ' . $row['Type'] . "\n";
} else {
    echo "Table 'lessons' does not exist or error: " . $conn->error;
}
?>
