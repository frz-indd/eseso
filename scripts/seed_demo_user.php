<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/db.php';

$pdo = db();

$username = 'demo';
$password = 'demo123';
$name = 'DEMO';
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (username, password_hash, name) VALUES (:u, :h, :n)
  ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), name=VALUES(name)');
$stmt->execute([':u' => $username, ':h' => $hash, ':n' => $name]);

echo "Seeded user: {$username}\n";
