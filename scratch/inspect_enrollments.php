<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
$res = $conn->query('DESCRIBE enrollments');
if ($res) {
    while($row = $res->fetch_assoc()) echo $row['Field'] . ': ' . $row['Type'] . "\n";
} else {
    echo "Table 'enrollments' does not exist or error: " . $conn->error;
}
?>
