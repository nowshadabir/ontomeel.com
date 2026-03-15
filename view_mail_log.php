<?php
header('Content-Type: text/plain; charset=UTF-8');
$log_file = __DIR__ . '/mail_debug.log';

if (file_exists($log_file)) {
    echo "--- LAST 50 MAIL LOG ENTRIES ---\n\n";
    $lines = file($log_file);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
} else {
    echo "Log file not found at: $log_file\n";
}

echo "\n\n--- ENVIRONMENT CHECK ---\n";
echo "SMTP_USER: " . (getenv('SMTP_USER') ?: 'NOT SET') . "\n";
echo "Working User (Hardcoded Fallback): auth@ontomeel.com\n";
