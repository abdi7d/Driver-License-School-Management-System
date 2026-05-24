<?php
require __DIR__ . '/../server/config/db.php';

echo "Starting migrations...\n";

// 1. Create complaints table if not exists
$q1 = "CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    instructor_id INT UNSIGNED,
    type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_complaints_instructor FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB";

if ($conn->query($q1)) {
    echo "✓ Complaints table checked/created.\n";
} else {
    echo "✗ Error creating complaints table: " . $conn->error . "\n";
}

// 2. Add recommended_for_exam column to enrollments table if not exists
$res = $conn->query("SHOW COLUMNS FROM enrollments LIKE 'recommended_for_exam'");
if ($res->num_rows == 0) {
    if ($conn->query("ALTER TABLE enrollments ADD COLUMN recommended_for_exam TINYINT(1) DEFAULT 0")) {
        echo "✓ Column 'recommended_for_exam' added to 'enrollments'.\n";
    } else {
        echo "✗ Error adding column to enrollments: " . $conn->error . "\n";
    }
} else {
    echo "✓ Column 'recommended_for_exam' already exists in 'enrollments'.\n";
}

echo "Migrations completed.\n";
?>
