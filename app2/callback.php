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
$cookieOk = isset($_COOKIE[session_name()]) && hash_equals((string)$_COOKIE[session_name()], session_id());
$clients = require __DIR__ . '/../shared/sso_clients.php';
$client = $clients['app2'] ?? null;
if (!is_array($client)) {
    http_response_code(500);
    echo "Client config missing";
    exit;
}
$code = (string)($_GET['code'] ?? '');
$state = (string)($_GET['state'] ?? '');

if ($code === '' || $state === '' || !hash_equals((string)($_SESSION['oauth_state'] ?? ''), $state)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Invalid state/code\n";
    echo "hint: pastikan host sama (localhost vs 127.0.0.1) dan session cookie tersimpan.\n";
    echo "got_state={$state}\n";
    echo "sess_state=" . (string)($_SESSION['oauth_state'] ?? '') . "\n";
    echo "state_set_at=" . (string)($_SESSION['oauth_state_set_at'] ?? '') . "\n";
    echo "cookie_ok=" . ($cookieOk ? 'yes' : 'no') . "\n";
    echo "cookie_sid=" . (string)($_COOKIE[session_name()] ?? '') . "\n";
    echo "session_id=" . session_id() . "\n";
    echo "host=" . (string)($_SERVER['HTTP_HOST'] ?? '') . "\n";
    echo "base_url={$base}\n";
    exit;
}
unset($_SESSION['oauth_state']);

$tokenUrl = $base . '/sso/token.php';
$redirectUri = $base . '/app2/callback.php';
$resp = form_post($tokenUrl, [
    'client_id' => 'app2',
    'client_secret' => (string)$client['client_secret'],
    'code' => $code,
    'redirect_uri' => $redirectUri,
]);
if (!$resp['ok'] || !is_array($resp['data'])) {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Token exchange failed: " . ($resp['raw'] ?? '');
    exit;
}
$accessToken = (string)($resp['data']['access_token'] ?? '');
if ($accessToken === '') {
    http_response_code(502);
    echo "Missing access_token";
    exit;
}

$userinfoUrl = $base . '/sso/userinfo.php';
$u = http_get_json($userinfoUrl, ['Authorization' => "Bearer {$accessToken}"]);
$raw = $u['raw'] ?? '';
$data = is_array($u['data'] ?? null) ? $u['data'] : null;
if (!is_array($data) || ($data['username'] ?? '') === '') {
    http_response_code(502);
    echo "Userinfo failed: " . (is_string($raw) ? $raw : '');
    exit;
}

$_SESSION['access_token'] = $accessToken;
$_SESSION['user'] = [
    'username' => (string)$data['username'],
    'name' => (string)($data['name'] ?? ''),
];

header('Location: ' . $base . '/app2/');
exit;
