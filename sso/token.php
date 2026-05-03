<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/lib.php';

require_post();

$clientId = (string)($_POST['client_id'] ?? '');
$clientSecret = (string)($_POST['client_secret'] ?? '');
$code = (string)($_POST['code'] ?? '');
$redirectUri = (string)($_POST['redirect_uri'] ?? '');

$client = sso_client($clientId);
if (!$client) {
    json_response(['error' => 'invalid_client'], 400);
}
if (!hash_equals((string)$client['client_secret'], $clientSecret)) {
    json_response(['error' => 'invalid_client_secret'], 401);
}

$expectedAbs = sso_abs_url((string)$client['redirect_uri']);
if ($redirectUri === '') {
    $redirectUri = $expectedAbs;
}
if (!hash_equals($expectedAbs, $redirectUri)) {
    json_response(['error' => 'invalid_redirect_uri'], 400);
}

$cfg = sso_config();
$consumed = sso_consume_code($clientId, $code);
if (!$consumed || !is_array($consumed['user'] ?? null)) {
    json_response(['error' => 'invalid_code'], 400);
}

$token = sso_create_token($clientId, (array)$consumed['user'], (int)$cfg['ttl_seconds']['token']);

json_response([
    'access_token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => (int)$cfg['ttl_seconds']['token'],
]);
