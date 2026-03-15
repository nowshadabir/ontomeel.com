<?php
// Mocking the environment if not running in web
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['SCRIPT_NAME'] = '/debug.php';
}

require_once 'includes/db_connect.php';

echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";

$vars = ['DB_HOST', 'DB_NAME', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_AUTH_USER'];

echo "\n--- Environment Check ---\n";
foreach ($vars as $v) {
    $val = getenv($v);
    echo "$v: " . ($val === false ? "NOT FOUND" : "'$val'") . "\n";
}

echo "\n--- $_ENV Check ---\n";
foreach ($vars as $v) {
    echo "$v: " . (isset($_ENV[$v]) ? "'".$_ENV[$v]."'" : "NOT SET") . "\n";
}

echo "\n--- SMTP Test Attempt ---\n";
require_once 'includes/smtp_client.php';
$smtp_config = require 'includes/smtp_config.php';

echo "Using Host: " . $smtp_config['host'] . "\n";
echo "Using User: " . $smtp_config['user'] . "\n";
echo "Using Port: " . $smtp_config['port'] . "\n";
echo "Using Pass Length: " . strlen($smtp_config['pass']) . "\n";

if ($smtp_config['pass'] === 'REDACTED_PASSWORD') {
    echo "WARNING: REDACTED_PASSWORD detected in fallback!\n";
}

// Try a dry run connect to see if the server responds
$host = "ssl://" . $smtp_config['host'];
$port = $smtp_config['port'];
echo "Attempting to connect to $host:$port...\n";

$timeout = 5;
$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (!$fp) {
    echo "FAILURE: Could not connect to SMTP server: $errstr ($errno)\n";
} else {
    echo "SUCCESS: Connected to SMTP server socket.\n";
    $res = fgets($fp, 515);
    echo "Server Response: " . trim($res) . "\n";
    fclose($fp);
}
