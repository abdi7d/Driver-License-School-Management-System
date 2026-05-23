<?php
include 'c:/xampp/htdocs/Driver-License-School-Management-System/server/config/db.php';
if ($conn->query("ALTER TABLE enrollments ADD COLUMN recommended_for_exam TINYINT(1) DEFAULT 0")) {
    echo "Column added successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
