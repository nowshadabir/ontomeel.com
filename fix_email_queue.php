<?php
/**
 * Fix Script: Add error_message column to email_queue table
 * Run this file once to fix the database schema
 */

require_once 'includes/db_connect.php';

try {
    // Check if error_message column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM email_queue LIKE 'error_message'");
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE email_queue ADD COLUMN error_message TEXT DEFAULT NULL AFTER attempts");
        echo "✓ Added error_message column to email_queue table\n";
    }
    else {
        echo "✓ error_message column already exists\n";
    }

    // Show current queue status
    echo "\n--- Email Queue Status ---\n";
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM email_queue GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Status: " . $row['status'] . " - Count: " . $row['count'] . "\n";
    }

    // Show pending emails
    echo "\n--- Pending Emails ---\n";
    $stmt = $pdo->query("SELECT id, recipient, type, attempts, created_at FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | To: " . $row['recipient'] . " | Type: " . $row['type'] . " | Attempts: " . $row['attempts'] . " | Created: " . $row['created_at'] . "\n";
    }


}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
