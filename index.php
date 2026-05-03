<?php
declare(strict_types=1);

require_once __DIR__ . '/shared/helpers.php';

$base = base_url();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Demo SSO - 2 Aplikasi</title>
  <link rel="stylesheet" href="<?= h($base) ?>/assets/style.php" />
</head>
<body>
  <main class="container">
    <header class="header">
      <h1>Demo SSO (PHP)</h1>
      <p class="muted">3 folder: <code>sso</code>, <code>app1</code>, <code>app2</code></p>
    </header>

    <section class="grid">
      <a class="card" href="<?= h($base) ?>/sso/">
        <h2>SSO Server</h2>
        <p>Login & API: authorize, token, userinfo, logout.</p>
      </a>
      <a class="card" href="<?= h($base) ?>/app1/">
        <h2>App 1</h2>
        <p>Mini Todo (butuh login SSO).</p>
      </a>
      <a class="card" href="<?= h($base) ?>/app2/">
        <h2>App 2</h2>
        <p>Mini Notes (butuh login SSO).</p>
      </a>
    </section>

    <section class="card">
      <h2>Akun demo</h2>
      <ul>
        <li><code>demo</code> / <code>demo123</code></li>
      </ul>
      <p class="muted">Ubah akun di <code>sso/config.php</code>.</p>
    </section>
  </main>
</body>
</html>
