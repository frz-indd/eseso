<?php
declare(strict_types=1);

require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "base_url=" . base_url() . "\n";
echo "origin_url=" . origin_url() . "\n";
echo "host=" . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo "script=" . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "php_sapi=" . PHP_SAPI . "\n";
echo "session.save_path=" . (string)ini_get('session.save_path') . "\n";
echo "session.use_cookies=" . (string)ini_get('session.use_cookies') . "\n";
echo "session.cookie_samesite=" . (string)ini_get('session.cookie_samesite') . "\n";
echo "session.cookie_path=" . (string)ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain=" . (string)ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure=" . (string)ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly=" . (string)ini_get('session.cookie_httponly') . "\n";
echo "pdo_mysql_loaded=" . (extension_loaded('pdo_mysql') ? 'yes' : 'no') . "\n";
echo "mysqli_loaded=" . (extension_loaded('mysqli') ? 'yes' : 'no') . "\n";

$pdo = db_try();
echo "db_connect=" . ($pdo ? 'ok' : 'fail') . "\n";
if ($pdo) {
    try {
        $row = $pdo->query("SELECT DATABASE() AS db, @@version AS ver")->fetch();
        echo "db=" . ($row['db'] ?? '') . "\n";
        echo "mysql_version=" . ($row['ver'] ?? '') . "\n";
        $count = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch();
        echo "users_count=" . ($count['c'] ?? '') . "\n";
    } catch (Throwable $e) {
        echo "db_query_error=" . $e->getMessage() . "\n";
    }
}
