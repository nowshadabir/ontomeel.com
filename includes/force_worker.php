<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/notification_helper.php';

header('Content-Type: text/plain');

try {
    $stmt = $pdo->query("SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC");
    $emails = $stmt->fetchAll();

    if (empty($emails)) {
        echo "No pending emails found in queue.\n";
        
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM email_queue GROUP BY status");
        echo "Queue Statistics:\n";
        while($row = $stmt->fetch()) {
            echo "- {$row['status']}: {$row['count']}\n";
        }
    } else {
        echo "Found " . count($emails) . " pending emails.\n";
        foreach ($emails as $email) {
            echo "Processing ID: {$email['id']} to {$email['recipient']}...\n";
            
            // Update to processing
            $pdo->prepare("UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?")
                ->execute([$email['id']]);

            $data = json_decode($email['payload'], true);
            $result = send_notification_instantly($email['recipient'], $email['type'], $data);

            if ($result['success']) {
                echo "SUCCESS: Email sent to {$email['recipient']}\n";
                $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$email['id']]);
            } else {
                echo "FAILED: " . $result['message'] . "\n";
                $pdo->prepare("UPDATE email_queue SET status = 'pending' WHERE id = ?")
                    ->execute([$email['id']]);
            }
            echo "-------------------\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
