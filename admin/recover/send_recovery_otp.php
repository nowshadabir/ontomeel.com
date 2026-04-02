<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/smtp_client.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'ইমেইল এড্রেস প্রয়োজন।']);
        exit();
    }

    // Check if admin exists in admins table
    $stmt = $pdo->prepare("SELECT id, full_name FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'এই অফিসিয়াল ইমেইল দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি।']);
        exit();
    }

    require_once '../../includes/notification_helper.php';

    // Generate 6 digit OTP
    $otp = rand(100000, 999999);
    $expiry = time() + (15 * 60); // 15 minutes valid

    // Store in session specifically for admin recovery
    $_SESSION['admin_recovery_request'] = [
        'email' => $email,
        'otp' => $otp,
        'expiry' => $expiry,
        'verified' => false
    ];

    $notif_data = [
        'name' => $admin['full_name'],
        'otp' => $otp
    ];

    // Assuming send_notification handles 'admin_password_recovery' or similar
    // If not, I can use a generic one or send email directly
    $result = send_notification($email, 'password_recovery', $notif_data);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে।']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
