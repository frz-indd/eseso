<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';

session_name('APP2_DEMO');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => false,
    'path' => '/',
]);
session_start();

$base = base_url();
$_SESSION = [];
session_destroy();

header('Location: ' . $base . '/app2/');
exit;
