<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/lib.php';

sso_session_start();
$base = base_url();
$me = $_SESSION['sso_user'] ?? null;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SSO Server</title>
  <link rel="stylesheet" href="<?= h($base) ?>/assets/style.php?app=sso" />
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>SSO Server</h1>
        <p class="muted">Endpoint demo: <code>/authorize.php</code>, <code>/token.php</code>, <code>/userinfo.php</code>, <code>/logout.php</code></p>
      </div>
      <div class="row">
        <a class="btn" href="<?= h($base) ?>/">Home</a>
      </div>
    </header>

    <section class="card">
      <div class="row" style="justify-content:space-between">
        <div>
          <div class="pill">Status: <?= $me ? 'Login sebagai <code>' . h($me['username']) . '</code>' : 'Belum login' ?></div>
        </div>
        <div class="row">
          <?php if ($me): ?>
            <a class="btn" href="<?= h($base) ?>/sso/logout.php">Logout</a>
          <?php else: ?>
            <a class="btn primary" href="<?= h($base) ?>/sso/login.php">Login</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="divider"></div>
      <p class="muted">
        Aplikasi client: <code>app1</code> dan <code>app2</code>. Konfigurasi ada di <code>sso/config.php</code>.
      </p>
    </section>
  </main>
</body>
</html>
