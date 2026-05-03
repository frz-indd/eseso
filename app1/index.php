<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/helpers.php';
require_once __DIR__ . '/../shared/db.php';

session_name('APP1_DEMO');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => false,
    'path' => '/',
]);
session_start();

$base = base_url();
$user = $_SESSION['user'] ?? null;
$error = null;
$pdo = db_try();

// Ensure the browser has a session cookie before starting OAuth flow.
if (!$user && (empty($_COOKIE[session_name()] ?? '') || !hash_equals((string)($_COOKIE[session_name()] ?? ''), session_id()))) {
    $checked = (string)($_GET['cookie_check'] ?? '') === '1';
    if (!$checked) {
        $_SESSION['cookie_test'] = time();
        header('Location: ' . $base . '/app1/?cookie_check=1');
        exit;
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Cookie session tidak tersimpan.\n";
    echo "Solusi: aktifkan cookies untuk localhost, jangan pakai mode incognito/strict, lalu refresh.\n";
    exit;
}

if (($user !== null) && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $todo = trim((string)($_POST['todo'] ?? ''));
    if ($todo === '') {
        $error = 'Todo tidak boleh kosong.';
    } else {
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO todos (username, text) VALUES (:u, :t)');
            $stmt->execute([':u' => (string)$user['username'], ':t' => $todo]);
        } else {
            $_SESSION['todos'] = $_SESSION['todos'] ?? [];
            $_SESSION['todos'][] = ['text' => $todo, 'at' => date('c')];
        }
        header('Location: ' . $base . '/app1/');
        exit;
    }
}

$todos = [];
if ($user && $pdo) {
    $stmt = $pdo->prepare('SELECT text, created_at FROM todos WHERE username=:u ORDER BY id DESC LIMIT 50');
    $stmt->execute([':u' => (string)$user['username']]);
    $todos = $stmt->fetchAll();
} else {
    $todos = $_SESSION['todos'] ?? [];
}
$clientId = 'app1';
$redirectUri = $base . '/app1/callback.php';
$state = (string)($_SESSION['oauth_state'] ?? '');
if ($state === '') {
    $state = random_token(10);
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_state_set_at'] = time();
}
$authUrl = $base . '/sso/authorize.php?client_id=' . urlencode($clientId)
    . '&redirect_uri=' . urlencode($redirectUri)
    . '&state=' . urlencode($state);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>App 1 - Todo</title>
  <link rel="stylesheet" href="<?= h($base) ?>/assets/style.php?app=app1" />
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>App 1: Todo</h1>
        <p class="muted">Login via SSO API</p>
      </div>
      <div class="row">
        <a class="btn" href="<?= h($base) ?>/">Home</a>
        <a class="btn" href="<?= h($base) ?>/app2/">Ke App 2</a>
        <?php if ($user): ?>
          <a class="btn" href="<?= h($base) ?>/app1/logout.php">Logout</a>
        <?php endif; ?>
      </div>
    </header>

    <section class="card">
      <?php if (!$user): ?>
        <p class="muted">Kamu belum login.</p>
        <div class="row">
          <a class="btn primary" href="<?= h($authUrl) ?>">Login dengan SSO</a>
          <a class="btn" href="<?= h($base) ?>/sso/">Buka SSO Server</a>
        </div>
      <?php else: ?>
        <div class="row" style="justify-content:space-between">
          <div class="pill">Login sebagai <code><?= h((string)$user['username']) ?></code></div>
          <div class="pill">SSO token tersimpan di session</div>
        </div>

        <?php if ($error): ?>
          <div class="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <div class="divider"></div>

        <form method="post" action="">
          <label class="label" for="todo">Tambah Todo</label>
          <input class="input" id="todo" name="todo" placeholder="Contoh: Belajar SSO..." />
          <div class="row" style="margin-top:12px">
            <button class="btn primary" type="submit">Tambah</button>
          </div>
        </form>

        <div class="divider"></div>
        <h2 style="margin:0 0 10px 0;font-size:18px">Daftar Todo</h2>
        <?php if (!$todos): ?>
          <p class="muted">Belum ada todo.</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>Todo</th><th>Waktu</th></tr></thead>
            <tbody>
              <?php foreach ($todos as $t): ?>
                <tr>
                  <td><?= h((string)($t['text'] ?? '')) ?></td>
                  <td class="muted"><code><?= h((string)($t['created_at'] ?? ($t['at'] ?? ''))) ?></code></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
