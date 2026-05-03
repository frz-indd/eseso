<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/db.php';

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/create_user.php <username> <password> [name]\n");
    exit(2);
}

$username = (string)$argv[1];
$password = (string)$argv[2];
$name = (string)($argv[3] ?? strtoupper($username));

$pdo = db();
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (username, password_hash, name) VALUES (:u, :h, :n)
  ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), name=VALUES(name)');
$stmt->execute([':u' => $username, ':h' => $hash, ':n' => $name]);

echo "Upserted user: {$username}\n";

