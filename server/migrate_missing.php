<?php
require __DIR__ . '/config/db.php';

$queries = [];
// Create students table if not exists
$queries[] = "CREATE TABLE IF NOT EXISTS students (\n    id INT AUTO_INCREMENT PRIMARY KEY,\n    user_id INT NOT NULL,\n    program_id INT NOT NULL,\n    enrollment_status ENUM('pending','approved','rejected') DEFAULT 'pending',\n    graduation_status ENUM('in_progress','graduated') DEFAULT 'in_progress',\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE RESTRICT\n) ENGINE=InnoDB;";

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "✓ Table created or already exists.\n";
    } else {
        echo "✗ Error creating table: " . $conn->error . "\n";
    }
}

$conn->close();
?>
