<?php
/**
 * SMTP Configuration for Email System
 * 
 * ======================================================
 * Production-ready configuration with fallback credentials
 * ======================================================
 */

// Check for environment variables first (for hosting control panels)
$smtp_host = getenv('SMTP_HOST');
$smtp_port = getenv('SMTP_PORT');
$smtp_user = getenv('SMTP_USER');
$smtp_pass = getenv('SMTP_PASS');

// Fallback to hardcoded credentials if environment variables not set
if (empty($smtp_host)) {
    $smtp_host = 'ontomeel.com';
}
if (empty($smtp_port)) {
    $smtp_port = 465;
}
if (empty($smtp_user)) {
    $smtp_user = 'auth@ontomeel.com';
}
if (empty($smtp_pass)) {
    // Production credentials - DO NOT SHARE
    $smtp_pass = 'REDACTED_PASSWORD';
}

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
