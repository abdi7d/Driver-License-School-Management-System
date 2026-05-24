<?php
if (!defined('JWT_SECRET')) {
	define('JWT_SECRET', getenv('JWT_SECRET') ?: 'drivepro_secret_key_2026');
}

if (!defined('JWT_EXPIRATION')) {
	define('JWT_EXPIRATION', (int)(getenv('JWT_EXPIRATION') ?: 86400));
}
?>
