<?php
$conn = new mysqli('localhost', 'root', '', 'driver_license_school');
$sql = "ALTER TABLE training_programs 
        ADD COLUMN IF NOT EXISTS license_category VARCHAR(10) AFTER name, 
        ADD COLUMN IF NOT EXISTS duration_hours INT AFTER theory_hours, 
        ADD COLUMN IF NOT EXISTS min_age INT AFTER practical_hours, 
        ADD COLUMN IF NOT EXISTS description TEXT AFTER fee, 
        ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER description";
if ($conn->query($sql)) echo "Table updated successfully";
else echo "Error: " . $conn->error;
?>
