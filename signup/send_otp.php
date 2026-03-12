<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'সবগুলো তথ্য পূরণ করুন।']);
        exit();
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'এই ইমেইল বা মোবাইল দিয়ে ইতিমধ্যে একাউন্ট খোলা হয়েছে।']);
        exit();
    }

    // Load SMTP settings from .env
    $smtp_config = [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'user' => getenv('SMTP_USER'),
        'pass' => getenv('SMTP_PASS')
    ];

    // Generate 6 digit OTP
    $otp = rand(100000, 999999);
    $expiry = time() + (10 * 60); // 10 minutes valid

    // Store in session
    $_SESSION['signup_data'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'otp' => $otp,
        'otp_expiry' => $expiry
    ];

    require_once '../includes/smtp_client.php';
    
    $subject = "Verification Code: $otp - Antyamil";
    $message = "Your 6-digit verification code is: $otp\r\n\r\nThis code will expire in 10 minutes.";
    
    $result = send_smtp_email($email, $subject, $message, $smtp_config);

    if ($result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'ওটিপি আপনার ইমেইলে পাঠানো হয়েছে।'
        ]);
    } else {
        // Log the error for debugging
        error_log("SMTP Error: " . $result['message']);
        
        echo json_encode([
            'success' => false, 
            'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে। দয়া করে এডমিনের সাথে যোগাযোগ করুন।'
        ]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
