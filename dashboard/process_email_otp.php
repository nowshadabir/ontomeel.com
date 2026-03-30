<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_email_otp') {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'একটি সঠিক ইমেইল ঠিকানা দিন।']);
            exit();
        }

        $check = $pdo->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $check->execute([$email, $_SESSION['user_id']]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'এই ইমেইল ঠিকানাটি ইতিমধ্যে অন্য একটি অ্যাকাউন্টে ব্যবহৃত হচ্ছে।']);
            exit();
        }

        $otp = rand(100000, 999999);
        $expiry = time() + (10 * 60);

        $_SESSION['member_email_otp'] = [
            'email' => $email,
            'otp' => $otp,
            'expiry' => $expiry,
        ];
        
        // --- DEBUG: Log OTP to a file for local testing if email fails to arrive ---
        file_put_contents(__DIR__ . '/otp_debug.txt', date('Y-m-d H:i:s') . " - OTP for $email is: $otp\n", FILE_APPEND);
        // --------------------------------------------------------------------------

        require_once '../includes/smtp_client.php';
        $smtp_config = [
            'host' => getenv('SMTP_HOST') ?: 'ontomeel.com',
            'port' => getenv('SMTP_PORT') ?: 465,
            'user' => getenv('SMTP_USER') ?: 'auth@ontomeel.com',
            'pass' => getenv('SMTP_PASS'),
            'from_name' => 'Ontomeel',
        ];

        $subject = "Your Email Verification Code: $otp";
        $message = "Your email verification code for Ontomeel is:\r\n\r\n" .
                   "    $otp\r\n\r\n" .
                   "This code will expire in 10 minutes.\r\n" .
                   "If you did not request this, please ignore this email.\r\n\r\n" .
                   "— Ontomeel Team";

        $result = send_smtp_email($email, $subject, $message, $smtp_config);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'OTP পাঠানো হয়েছে।']);
        } else {
            error_log("Member Email OTP SMTP Error: " . ($result['message'] ?? 'unknown'));
            echo json_encode(['success' => false, 'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে।']);
        }
        exit();
    }

    if ($action === 'verify_email_otp') {
        $input_otp = trim($_POST['otp'] ?? '');

        if (!isset($_SESSION['member_email_otp'])) {
            echo json_encode(['success' => false, 'message' => 'OTP সেশন পাওয়া যায়নি। আবার OTP পাঠান।']);
            exit();
        }

        $session_data = $_SESSION['member_email_otp'];

        if (time() > $session_data['expiry']) {
            unset($_SESSION['member_email_otp']);
            echo json_encode(['success' => false, 'message' => 'OTP মেয়াদ শেষ হয়ে গেছে। নতুন OTP আনুন।']);
            exit();
        }

        if ($input_otp != $session_data['otp']) {
            echo json_encode(['success' => false, 'message' => 'OTP কোড সঠিক নয়। আবার চেষ্টা করুন।']);
            exit();
        }

        $email = $session_data['email'];
        $stmt = $pdo->prepare("UPDATE members SET email = ? WHERE id = ?");
        $stmt->execute([$email, $_SESSION['user_id']]);

        unset($_SESSION['member_email_otp']);

        echo json_encode(['success' => true, 'message' => 'ইমেইল সফলভাবে সেভ করা হয়েছে।']);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>
