<?php
/**
 * SMTP Configuration for Email System
 * 
 * ======================================================
 * Production-ready configuration with fallback credentials
 * ======================================================
 */

// Simple .env loader for credentials if not already loaded
if (!getenv('SMTP_PASS') && file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . "=" . trim($parts[1]));
        }
    }
}

// Check for environment variables first
$smtp_host = getenv('SMTP_HOST') ?: 'ontomeel.com';
$smtp_port = getenv('SMTP_PORT') ?: 465;
$smtp_user = getenv('SMTP_USER') ?: 'info@ontomeel.com';
$smtp_pass = getenv('SMTP_PASS');


return [
    // SMTP Server Settings
    'host' => $smtp_host,
    'port' => $smtp_port,

    // SMTP Authentication
    'user' => $smtp_user,
    'pass' => $smtp_pass,

    // Email Display Settings
    'from_name' => 'Ontomeel Bookshop',
    'reply_to' => $smtp_user,

    // Debug mode - set to true to log SMTP communication
    'debug' => false,
];
