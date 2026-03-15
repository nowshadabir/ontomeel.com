<?php
require_once 'includes/smtp_client.php';
require_once 'includes/smtp_config.php';

$smtp_config = require 'includes/smtp_config.php';

// Test Bengali characters in both Subject and Body
$test_email = "knabirofficial@gmail.com"; // Default for testing
if (isset($_GET['email'])) {
    $test_email = $_GET['email'];
}

$subject = "বইয়ের অর্ডার নিশ্চিতকরণ - Ontomeel Bookshop"; // Bengali Subject
$message = "
<div style='font-family: sans-serif; line-height: 1.6;'>
    <h2 style='color: #2563eb;'>আপনার অর্ডার সফলভাবে গ্রহণ করা হয়েছে!</h2>
    <p>প্রিয় গ্রাহক,</p>
    <p>আপনার বইয়ের অর্ডারটি আমরা পেয়েছি। নিচে বিস্তারিত দেওয়া হলো:</p>
    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
        <tr style='background: #f8fafc;'>
            <th style='padding: 10px; border: 1px solid #e2e8f0; text-align: left;'>বিবরণ</th>
            <th style='padding: 10px; border: 1px solid #e2e8f0; text-align: left;'>তথ্য</th>
        </tr>
        <tr>
            <td style='padding: 10px; border: 1px solid #e2e8f0;'>অর্ডার নম্বর</td>
            <td style='padding: 10px; border: 1px solid #e2e8f0;'>#ONT-12345</td>
        </tr>
        <tr>
            <td style='padding: 10px; border: 1px solid #e2e8f0;'>বইয়ের নাম</td>
            <td style='padding: 10px; border: 1px solid #e2e8f0;'>বাংলা উপন্যাস সম্ভার</td>
        </tr>
    </table>
    <p style='margin-top: 20px;'>ধন্যবাদ,<br>অনুতমীল বুকশপ</p>
</div>
";

echo "<h2>Bengali SMTP Delivery Test</h2>";
echo "Sending to: <b>$test_email</b><br><br>";

$result = send_smtp_email($test_email, $subject, $message, $smtp_config, true);

if ($result['success']) {
    echo "<h3 style='color:green'>SUCCESS! Email sent.</h3>";
    echo "<p>Please check your Gmail inbox and Spam folder.</p>";
}
else {
    echo "<h3 style='color:red'>FAILED!</h3>";
    echo "<pre>" . htmlspecialchars($result['message']) . "</pre>";
}

echo "<h4>Debug Log:</h4>";
echo "<pre>" . htmlspecialchars($result['debug'] ?? 'No debug log available.') . "</pre>";
