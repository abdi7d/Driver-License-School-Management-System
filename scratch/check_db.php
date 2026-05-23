<?php
include "server/config/db.php";

$tables = ["users", "enrollments", "lessons", "exams", "complaints", "training_programs"];

echo "Checking tables:\n";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count_res = $conn->query("SELECT COUNT(*) as total FROM $table");
        $count = $count_res->fetch_assoc()['total'];
        echo "- Table '$table' exists with $count records.\n";
    } else {
        echo "- Table '$table' DOES NOT exist.\n";
    }
}
?>
