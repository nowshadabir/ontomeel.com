<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/smtp_client.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'ইমেইল এড্রেস প্রয়োজন।']);
        exit();
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'এই ইমেইল দিয়ে কোনো একাউন্ট খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    require_once '../includes/notification_helper.php';

    // Generate 6 digit OTP
    $otp = rand(100000, 999999);
    $expiry = time() + (15 * 60); // 15 minutes valid

    // Store in session specifically for recovery
    $_SESSION['recovery_request'] = [
        'email' => $email,
        'otp' => $otp,
        'expiry' => $expiry,
        'verified' => false
    ];

    $notif_data = [
        'name' => $user['full_name'],
        'otp' => $otp
    ];

    $result = send_notification($email, 'password_recovery', $notif_data);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে। দয়া করে এডমিনের সাথে যোগাযোগ করুন।']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
