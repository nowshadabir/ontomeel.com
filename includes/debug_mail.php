<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/notification_helper.php';

header('Content-Type: text/plain; charset=UTF-8');

$recipient = 'knabirofficial@gmail.com';
$pass = getenv('SMTP_PASS');

$test_cases = [
    [
        'label' => 'Billing Account - Plain Text - English Subject',
        'user' => getenv('SMTP_BILLING_USER') ?: 'billing@ontomeel.com',
        'subject' => 'Plain Text Test from Billing',
        'html' => false
    ],
    [
        'label' => 'Billing Account - HTML - Bengali Subject',
        'user' => getenv('SMTP_BILLING_USER') ?: 'billing@ontomeel.com',
        'subject' => 'অর্ডার পরীক্ষা (HTML)',
        'html' => true
    ]
];

foreach ($test_cases as $test) {
    echo "Running: {$test['label']}\n";
    $config = [
        'host' => getenv('SMTP_HOST') ?: 'ontomeel.com',
        'port' => getenv('SMTP_PORT') ?: 465,
        'user' => $test['user'],
        'pass' => $pass
    ];

    $msg = $test['html'] ? "<h1>Test</h1><p>This is an HTML test from {$test['user']}</p>" : "This is a plain text test from {$test['user']}";
    
    $result = send_smtp_email($recipient, $test['subject'], $msg, $config, $test['html']);
    
    if ($result['success']) {
        echo "RESULT: SUCCESS (Server accepted mail)\n";
    } else {
        echo "RESULT: FAILED - " . $result['message'] . "\n";
    }
    echo "-------------------\n";
}
