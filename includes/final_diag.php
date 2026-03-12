<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/notification_helper.php';

header('Content-Type: text/plain; charset=UTF-8');

$recipient = 'knabirofficial@gmail.com';
$pass = getenv('SMTP_PASS');

echo "DIAGNOSTIC START\n";
echo "Date: " . date('r') . "\n";
echo "Recipient: $recipient\n";
echo "---------------------------\n";

// Test 1: Notification Helper Instant (The method typically used by worker)
echo "TEST 1: Calling send_notification_instantly (HTML, Bengali Subject)\n";
$data = [
    'name' => 'Diagnostic Test',
    'invoice_no' => 'DIAG-' . time(),
    'amount' => '100',
    'address' => 'Test Location'
];
$result1 = send_notification_instantly($recipient, 'order_placed', $data);
if ($result1['success']) {
    echo "TEST 1 RESULT: SUCCESS (Server accepted)\n";
} else {
    echo "TEST 1 RESULT: FAILED - " . $result1['message'] . "\n";
}

echo "---------------------------\n";

// Test 2: Notification Helper Queue (To see if worker triggers and completes)
echo "TEST 2: Calling send_notification (Queued)\n";
$result2 = send_notification($recipient, 'order_placed', $data);
echo "TEST 2 RESULT: " . ($result2['success'] ? "Email Queued" : "Queue Failed") . "\n";
echo "Note: Check if this arrives in the next 10-20 seconds.\n";

echo "---------------------------\n";
echo "DIAGNOSTIC END\n";
