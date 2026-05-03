<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/lib.php';

sso_session_start();
$cfg = sso_config();
$base = base_url();
$origin = origin_url();

$error = null;
$redirect = (string)($_POST['redirect'] ?? ($_GET['redirect'] ?? ''));
$dbWarning = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $valid = false;
    $name = strtoupper($username);

    $pdo = sso_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT username, password_hash, name FROM users WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $row = $stmt->fetch();
            if (is_array($row) && isset($row['password_hash'])) {
                $stored = (string)$row['password_hash'];
                if (preg_match('/^\\$2[aby]\\$|^\\$argon2/i', $stored)) {
                    $valid = password_verify($password, $stored);
                } else {
                    // Legacy/plain-text demo support
                    $valid = hash_equals($stored, $password);
                }
                $name = (string)($row['name'] ?? $name);
            }
        } catch (Throwable $e) {
            $dbWarning = 'DB error, fallback ke user config.';
        }
    } else {
        $dbWarning = 'DB tidak terhubung, hanya user demo di config yang bisa login.';
    }
    if (!$valid) {
        $valid = isset($cfg['users'][$username]) && hash_equals((string)$cfg['users'][$username], $password);
    }

    if ($valid) {
        $_SESSION['sso_user'] = [
            'username' => $username,
            'name' => $name,
        ];
        $target = $base . '/sso/';
        if ($redirect !== '' && str_starts_with($redirect, '/') && !str_contains($redirect, '://')) {
            $target = $origin . $redirect;
        }
        header('Location: ' . $target);
        exit;
    }
    $error = 'Username/password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login SSO</title>
  <link rel="stylesheet" href="<?= h($base) ?>/assets/style.php?app=sso" />
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Login SSO</h1>
        <p class="muted">Demo akun: <code>demo</code>/<code>demo123</code></p>
      </div>
      <div class="row">
        <a class="btn" href="<?= h($base) ?>/sso/">Kembali</a>
      </div>
    </header>

    <section class="card">
      <?php if ($dbWarning): ?>
        <div class="alert"><?= h($dbWarning) ?> Cek <code>/web12/debug.php</code> (host/port/db/user/pass).</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="redirect" value="<?= h($redirect) ?>" />

        <label class="label" for="username">Username</label>
        <input class="input" id="username" name="username" autocomplete="username" required />

        <label class="label" for="password">Password</label>
        <input class="input" id="password" name="password" type="password" autocomplete="current-password" required />

        <div class="row" style="margin-top:12px">
          <button class="btn primary" type="submit">Login</button>
          <a class="btn" href="<?= h($base) ?>/">Home</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
