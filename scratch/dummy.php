<?php
// We will set $_SERVER['REQUEST_METHOD'] and mock auth headers to see if schedule.php throws a warning.
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer dummy'; // This won't work since auth() validates JWT.
?>
