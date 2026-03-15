<?php
require_once 'includes/smtp_config.php';
require_once 'includes/smtp_client.php';

$to = "knabirofficial@gmail.com"; // User's email from summary
$subject = "SMTP Environment Test";
$message = "This is a test to verify if SMTP is working with the current credentials.\r\n\r\nSMTP_HOST: " . (getenv('SMTP_HOST') ?: 'default') . "\r\nSMTP_USER: " . (getenv('SMTP_USER') ?: 'default');

$smtp_config = require 'includes/smtp_config.php';

echo "<h2>SMTP Test</h2>";
echo "Recipient: $to<br>";
echo "Host: " . $smtp_config['host'] . "<br>";
echo "User: " . $smtp_config['user'] . "<br>";
echo "Port: " . $smtp_config['port'] . "<br>";

$result = send_smtp_email($to, $subject, $message, $smtp_config);

if ($result['success']) {
    echo "<h3 style='color:green'>SUCCESS! Email sent.</h3>";
}
else {
    echo "<h3 style='color:red'>FAILED!</h3>";
    echo "<pre>" . htmlspecialchars($result['message']) . "</pre>";
    echo "<h4>Debug Log:</h4>";
    echo "<pre>" . htmlspecialchars($result['debug']) . "</pre>";
}
echo "<hr>";
echo "<h4>Environment Variables Check:</h4>";
echo "SMTP_HOST: " . var_export(getenv('SMTP_HOST'), true) . "<br>";
echo "SMTP_PORT: " . var_export(getenv('SMTP_PORT'), true) . "<br>";
echo "SMTP_USER: " . var_export(getenv('SMTP_USER'), true) . "<br>";
echo "SMTP_PASS: " . (getenv('SMTP_PASS') ? "SECRESET" : "EMPTY") . "<br>";
echo "SMTP_AUTH_USER: " . var_export(getenv('SMTP_AUTH_USER'), true) . "<br>";
echo "SMTP_AUTH_PASS: " . (getenv('SMTP_AUTH_PASS') ? "SECRESET" : "EMPTY") . "<br>";
