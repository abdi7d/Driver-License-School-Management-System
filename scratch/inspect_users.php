<?php
include "server/config/db.php";
$res = $conn->query("DESCRIBE users");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$res = $conn->query("SELECT id, first_name, last_name FROM users LIMIT 5");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
