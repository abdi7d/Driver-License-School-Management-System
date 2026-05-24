<?php
require __DIR__ . '/../server/config/db.php';
$res = $conn->query("SELECT id, email, role, status FROM users");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
