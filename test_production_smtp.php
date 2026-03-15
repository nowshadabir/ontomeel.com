<?php
/**
 * Production SMTP Debugger
 * Run this in your browser to see why live emails are failing.
 */
header('Content-Type: text/plain; charset=UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "ONTOMEEL SMTP DEBUGGER\n";
echo "=====================\n\n";

// 1. Check if .env exists
$env_path = __DIR__ . '/.env';
echo "Checking .env file: " . ($env_path) . "\n";
if (file_exists($env_path)) {
    echo "STATUS: .env file found.\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($env_path)), -4) . "\n";
} else {
    echo "STATUS: .env file NOT FOUND in root directory!\n";
}

// 2. Load environment (as done in the app)
echo "\nLoading environment variables...\n";
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . "=" . trim($parts[1]));
        }
    }
}

// 3. Inspect Variables
$vars = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS'];
foreach ($vars as $v) {
    $val = getenv($v);
    if ($v === 'SMTP_PASS') {
        echo "$v: " . ($val ? "[SET - length " . strlen($val) . "]" : "[NOT SET]") . "\n";
    } else {
        echo "$v: " . ($val ?: "[NOT SET]") . "\n";
    }
}

// 4. Test Socket Connection
$host = getenv('SMTP_HOST') ?: 'ontomeel.com';
$port = getenv('SMTP_PORT') ?: 465;

function test_connection($host, $port, $use_ssl = true) {
    $prefix = $use_ssl ? 'ssl://' : '';
    echo "\nAttempting connection to {$prefix}{$host}:{$port}...\n";
    $timeout = 10;
    $context = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ]);
    
    $start = microtime(true);
    $socket = @stream_socket_client("{$prefix}{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    $end = microtime(true);

    if (!$socket) {
        echo "CONNECTION FAILED: $errstr ($errno)\n";
        return false;
    } else {
        echo "CONNECTION SUCCESSFUL! (Time: " . round($end - $start, 4) . "s)\n";
        $greeting = fgets($socket, 1024);
        echo "Server Greeting: " . trim($greeting) . "\n";
        fclose($socket);
        return true;
    }
}

$conn465 = test_connection($host, 465, true);
$conn587 = test_connection($host, 587, false);
$connLocal465 = test_connection('localhost', 465, true);
$connLocal25 = test_connection('localhost', 25, false);

echo "\nANALYSIS & RECOMMENDATIONS:\n";
echo "==========================\n";

if (!$conn465 && !$conn587) {
    echo "CRITICAL: The server cannot connect to '{$host}' on common SMTP ports.\n";
    if ($connLocal465 || $connLocal25) {
        echo "FIX: Change SMTP_HOST to 'localhost' in your .env file.\n";
    }
}

if (!getenv('SMTP_PASS')) {
    echo "WARNING: SMTP_PASS is empty. Ensure your .env file is uploaded to the production server.\n";
}

echo "\nCheck 'mail_debug.log' in your root directory for recent transaction logs.\n";
echo "\n--- End of Debug ---\n";
