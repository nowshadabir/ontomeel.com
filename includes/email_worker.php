<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/notification_helper.php';

// Set time limit to infinity for background process
set_time_limit(0);
ignore_user_abort(true);

function process_email_queue() {
    global $pdo;

    try {
        // Fetch pending emails
        $stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 5");
        $stmt->execute();
        $emails = $stmt->fetchAll();

        if (empty($emails)) return;

        foreach ($emails as $email) {
            // Update status to processing to avoid double sending
            $pdo->prepare("UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?")
                ->execute([$email['id']]);

            $data = json_decode($email['payload'], true);
            $result = send_notification_instantly($email['recipient'], $email['type'], $data);

            if ($result['success']) {
                $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = CURRENT_TIMESTAMP, error_message = NULL WHERE id = ?")
                    ->execute([$email['id']]);
            } else {
                // If failed, mark back to pending if attempts < 3
                $status = ($email['attempts'] < 3) ? 'pending' : 'failed';
                $pdo->prepare("UPDATE email_queue SET status = ?, error_message = ? WHERE id = ?")
                    ->execute([$status, $result['message'], $email['id']]);
                
                error_log("Worker Mail Error (ID: {$email['id']}): " . $result['message']);
            }
        }
    } catch (Exception $e) {
        error_log("Worker Error: " . $e->getMessage());
    }
}

// Always process when called (triggered by socket or direct access)
process_email_queue();
