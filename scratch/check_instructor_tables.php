<?php
include "server/config/db.php";
$tables = ['lessons', 'attendance', 'evaluations', 'schedules'];
foreach($tables as $table) {
    echo "--- Checking $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if($res) {
        while($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "Error or table does not exist: " . $conn->error . "\n";
    }
    echo "\n";
}
?>
