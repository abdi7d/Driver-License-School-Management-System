<?php
require __DIR__ . '/config/db.php';

$migrationsDir = __DIR__ . '/database/migrations';
if (!is_dir($migrationsDir)) {
    exit('Migrations directory not found');
}

$files = scandir($migrationsDir);
// Sort to ensure numeric ordering (001, 002, 003...)
sort($files, SORT_STRING);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'sql') continue;
    if (stripos($file, 'sqlite') !== false) continue; // skip SQLite-specific migrations
    $sqlFile = $migrationsDir . '/' . $file;
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        echo "Failed to read $sqlFile\n";
        continue;
    }

    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (empty($query)) continue;
        if (!$conn->query($query)) {
            echo "Error executing query: " . $conn->error . "\nQuery: $query\n";
        }
    }
}

echo "Migrations completed.\n";
?>
