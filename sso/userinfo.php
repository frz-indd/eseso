<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/lib.php';

require_get();

$auth = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? '');
$token = '';
if (preg_match('/^Bearer\\s+(.+)$/i', $auth, $m)) {
    $token = trim($m[1]);
}
if ($token === '') {
    json_response(['error' => 'missing_token'], 401);
}

$row = sso_get_token($token);
if (!$row) {
    json_response(['error' => 'invalid_token'], 401);
}

$user = $row['user'] ?? null;
if (!is_array($user)) {
    json_response(['error' => 'invalid_user'], 500);
}

json_response([
    'username' => (string)($user['username'] ?? ''),
    'name' => (string)($user['name'] ?? ''),
]);
