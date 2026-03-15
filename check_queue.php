<?php
require_once 'includes/db_connect.php';

echo "<h2>Email Queue Status</h2>";

$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM email_queue GROUP BY status");
$stmt->execute();
$stats = $stmt->fetchAll();

if (empty($stats)) {
    echo "Queue is empty.<br>";
} else {
    echo "<table border='1'><tr><th>Status</th><th>Count</th></tr>";
    foreach ($stats as $row) {
        echo "<tr><td>{$row['status']}</td><td>{$row['count']}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>Recent Emails (Last 10)</h3>";
$stmt = $pdo->prepare("SELECT * FROM email_queue ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$emails = $stmt->fetchAll();

if (empty($emails)) {
    echo "No emails in queue.<br>";
} else {
    echo "<table border='1'><tr><th>ID</th><th>Recipient</th><th>Status</th><th>Attempts</th><th>Error</th><th>Created At</th></tr>";
    foreach ($emails as $email) {
        $payload = json_decode($email['payload'], true);
        echo "<tr>
                <td>{$email['id']}</td>
                <td>{$email['recipient']}</td>
                <td>{$email['status']}</td>
                <td>{$email['attempts']}</td>
                <td>" . htmlspecialchars($email['error_message'] ?? 'None') . "</td>
                <td>{$email['created_at']}</td>
              </tr>";
    }
    echo "</table>";
}

echo "<h3>Environment Test</h3>";
echo "SMTP_HOST: " . (getenv('SMTP_HOST') ?: 'NOT SET') . "<br>";
echo "SMTP_USER: " . (getenv('SMTP_USER') ?: 'NOT SET') . "<br>";
echo "SMTP_PASS: " . (getenv('SMTP_PASS') ? 'FOUND' : 'NOT SET') . "<br>";
echo "SMTP_AUTH_USER: " . (getenv('SMTP_AUTH_USER') ?: 'NOT SET') . "<br>";
echo "SMTP_AUTH_PASS: " . (getenv('SMTP_AUTH_PASS') ? 'FOUND' : 'NOT SET') . "<br>";
