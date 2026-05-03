<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/lib.php';

require_get();
sso_session_start();

$clientId = (string)($_GET['client_id'] ?? '');
$redirectUri = (string)($_GET['redirect_uri'] ?? '');
$state = (string)($_GET['state'] ?? '');

$client = sso_client($clientId);
if (!$client) {
    json_response(['error' => 'invalid_client'], 400);
}

// Redirect URI validation (only allow configured redirect)
$expected = $client['redirect_uri'];
$expectedAbs = sso_abs_url($expected);
// For safety and to avoid host-mismatch issues (localhost vs 127.0.0.1),
// we always redirect to the configured path on the current origin.
$redirectUri = $expectedAbs;

if (!isset($_SESSION['sso_user'])) {
    $loginUrl = sso_abs_url('/sso/login.php') . '?redirect=' . urlencode((string)($_SERVER['REQUEST_URI'] ?? '/'));
    header('Location: ' . $loginUrl);
    exit;
}

$cfg = sso_config();
$code = sso_create_code($clientId, (array)$_SESSION['sso_user'], (int)$cfg['ttl_seconds']['code']);

$sep = (str_contains($redirectUri, '?')) ? '&' : '?';
$location = $redirectUri . $sep . 'code=' . urlencode($code);
if ($state !== '') {
    $location .= '&state=' . urlencode($state);
}
header('Location: ' . $location);
exit;
