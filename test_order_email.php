<?php
require_once 'includes/notification_helper.php';
require_once 'includes/db_connect.php';

header('Content-Type: text/plain; charset=UTF-8');

$test_email = "cmhs2278@gmail.com";
echo "Testing Order Status Update Email to: $test_email\n";
echo "================================================\n\n";

$data = [
    'name' => 'Test User',
    'invoice_no' => 'ONT-TEST-' . time(),
    'status' => 'Shipped',
    'book_title_en' => 'The Great Gatsby',
    'book_author_en' => 'F. Scott Fitzgerald'
];

$result = send_notification($test_email, 'order_status_update', $data);

if ($result['success']) {
    echo "✅ SUCCESS! Order update email triggered.\n";
    echo "Check cmhs2278@gmail.com (and Spam).\n";
} else {
    echo "❌ FAILED!\n";
    echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
}

echo "\nCheck 'view_mail_log.php' for detailed transaction status.\n";
