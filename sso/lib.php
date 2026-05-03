<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/../shared/db.php';

function sso_session_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('SSO_DEMO');
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => false,
            'path' => '/',
        ]);
        session_start();
    }
}

function sso_config(): array
{
    /** @var array $cfg */
    $cfg = require __DIR__ . '/config.php';
    return $cfg;
}

function sso_data_path(): string
{
    return dirname(__DIR__) . '/data/sso_store.json';
}

function sso_db(): ?PDO
{
    return db_try();
}

function sso_dt(int $unix): string
{
    return gmdate('Y-m-d H:i:s', $unix);
}

function sso_store_cleanup_db(PDO $pdo): void
{
    $now = gmdate('Y-m-d H:i:s');
    $pdo->prepare('DELETE FROM oauth_codes WHERE expires_at < :now')->execute([':now' => $now]);
    $pdo->prepare('DELETE FROM oauth_tokens WHERE expires_at < :now')->execute([':now' => $now]);
}

function sso_create_code(string $clientId, array $user, int $ttlSeconds): string
{
    $code = random_token(18);
    $exp = time() + $ttlSeconds;
    $pdo = sso_db();
    if ($pdo) {
        sso_store_cleanup_db($pdo);
        $stmt = $pdo->prepare('INSERT INTO oauth_codes (code, client_id, user_json, expires_at) VALUES (:c,:cid,:u,:exp)');
        $stmt->execute([
            ':c' => $code,
            ':cid' => $clientId,
            ':u' => json_encode($user, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':exp' => sso_dt($exp),
        ]);
        return $code;
    }

    $store = sso_load_store();
    sso_cleanup($store);
    $store['codes'][$code] = [
        'client_id' => $clientId,
        'user' => $user,
        'exp' => $exp,
    ];
    sso_save_store($store);
    return $code;
}

function sso_consume_code(string $clientId, string $code): ?array
{
    $pdo = sso_db();
    if ($pdo) {
        sso_store_cleanup_db($pdo);
        $stmt = $pdo->prepare('SELECT code, client_id, user_json, expires_at FROM oauth_codes WHERE code=:c LIMIT 1');
        $stmt->execute([':c' => $code]);
        $row = $stmt->fetch();
        if (!$row || ($row['client_id'] ?? '') !== $clientId) {
            return null;
        }
        // single use
        $pdo->prepare('DELETE FROM oauth_codes WHERE code=:c')->execute([':c' => $code]);
        $user = json_decode((string)$row['user_json'], true);
        if (!is_array($user)) {
            return null;
        }
        return [
            'user' => $user,
            'expires_at' => (string)$row['expires_at'],
        ];
    }

    $store = sso_load_store();
    sso_cleanup($store);
    if (!isset($store['codes'][$code])) {
        sso_save_store($store);
        return null;
    }
    $row = $store['codes'][$code];
    unset($store['codes'][$code]);
    sso_save_store($store);
    if (!is_array($row) || ($row['client_id'] ?? '') !== $clientId) {
        return null;
    }
    if (($row['exp'] ?? 0) < time()) {
        return null;
    }
    return ['user' => $row['user'] ?? null, 'exp' => (int)($row['exp'] ?? 0)];
}

function sso_create_token(string $clientId, array $user, int $ttlSeconds): string
{
    $token = random_token(24);
    $exp = time() + $ttlSeconds;
    $pdo = sso_db();
    if ($pdo) {
        sso_store_cleanup_db($pdo);
        $stmt = $pdo->prepare('INSERT INTO oauth_tokens (token, client_id, user_json, expires_at) VALUES (:t,:cid,:u,:exp)');
        $stmt->execute([
            ':t' => $token,
            ':cid' => $clientId,
            ':u' => json_encode($user, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':exp' => sso_dt($exp),
        ]);
        return $token;
    }

    $store = sso_load_store();
    sso_cleanup($store);
    $store['tokens'][$token] = [
        'client_id' => $clientId,
        'user' => $user,
        'exp' => $exp,
    ];
    sso_save_store($store);
    return $token;
}

function sso_get_token(string $token): ?array
{
    $pdo = sso_db();
    if ($pdo) {
        sso_store_cleanup_db($pdo);
        $stmt = $pdo->prepare('SELECT token, client_id, user_json, expires_at FROM oauth_tokens WHERE token=:t LIMIT 1');
        $stmt->execute([':t' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $user = json_decode((string)$row['user_json'], true);
        if (!is_array($user)) {
            return null;
        }
        return [
            'client_id' => (string)$row['client_id'],
            'user' => $user,
            'expires_at' => (string)$row['expires_at'],
        ];
    }

    $store = sso_load_store();
    sso_cleanup($store);
    if (!isset($store['tokens'][$token])) {
        sso_save_store($store);
        return null;
    }
    $row = $store['tokens'][$token];
    sso_save_store($store);
    if (!is_array($row) || ($row['exp'] ?? 0) < time()) {
        return null;
    }
    return $row;
}

function sso_load_store(): array
{
    $path = sso_data_path();
    if (!file_exists($path)) {
        return ['codes' => [], 'tokens' => []];
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) {
        return ['codes' => [], 'tokens' => []];
    }
    $data['codes'] = is_array($data['codes'] ?? null) ? $data['codes'] : [];
    $data['tokens'] = is_array($data['tokens'] ?? null) ? $data['tokens'] : [];
    return $data;
}

function sso_save_store(array $store): void
{
    $path = sso_data_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, json_encode($store, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function sso_cleanup(array &$store): void
{
    $now = time();
    foreach ($store['codes'] as $code => $row) {
        if (!is_array($row) || ($row['exp'] ?? 0) < $now) {
            unset($store['codes'][$code]);
        }
    }
    foreach ($store['tokens'] as $token => $row) {
        if (!is_array($row) || ($row['exp'] ?? 0) < $now) {
            unset($store['tokens'][$token]);
        }
    }
}

function sso_client(string $clientId): ?array
{
    $cfg = sso_config();
    return $cfg['clients'][$clientId] ?? null;
}

function sso_origin(): string
{
    return base_url();
}

function sso_abs_url(string $path): string
{
    return rtrim(sso_origin(), '/') . $path;
}
