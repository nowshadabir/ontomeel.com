<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';
require_once __DIR__ . '/../includes/smtp_client.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function normalize_phone_number($input) {
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $str = str_replace($bn_digits, $en_digits, trim($input));
    $clean = preg_replace('/[^0-9]/', '', $str);
    if (strlen($clean) === 15 && strpos($clean, '00880') === 0) {
        $clean = substr($clean, 4);
    }
    if (strlen($clean) === 13 && strpos($clean, '880') === 0) {
        $clean = substr($clean, 2);
    }
    if (strlen($clean) === 10 && strpos($clean, '1') === 0) {
        $clean = '0' . $clean;
    }
    return $clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit check: 6 attempts per 5 minutes
    if (!check_rate_limit('signup_otp', 6, 300)) {
        log_security_event('rate_limit_exceeded', ['identifier' => 'signup_otp']);
        echo json_encode([
            'success' => false, 
            'message' => 'অতিরিক্ত অনুরোধ করা হয়েছে! ৫ মিনিট পর আবার চেষ্টা করুন।'
        ]);
        exit();
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $raw_phone = $_POST['phone'] ?? '';
    $phone = normalize_phone_number($raw_phone);
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'সবগুলো তথ্য সঠিকভাবে পূরণ করুন।']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'সঠিক ইমেইল এড্রেস লিখুন।']);
        exit();
    }

    if (!preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => '১১ সংখ্যার সঠিক মোবাইল নম্বর লিখুন (যেমন: 017XXXXXXXX)।']);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।']);
        exit();
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id, email, phone FROM members WHERE LOWER(email) = ? OR phone = ? LIMIT 1");
    $stmt->execute([$email, $phone]);
    $existing = $stmt->fetch();
    if ($existing) {
        if (strtolower($existing['email']) === $email) {
            echo json_encode(['success' => false, 'message' => 'এই ইমেইল দিয়ে ইতিমধ্যে একটি একাউন্ট খোলা রয়েছে। লগইন করুন।']);
        } else {
            echo json_encode(['success' => false, 'message' => 'এই মোবাইল নম্বর দিয়ে ইতিমধ্যে একটি একাউন্ট খোলা রয়েছে।']);
        }
        exit();
    }

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

    $notif_data = [
        'name' => $full_name,
        'otp' => $otp
    ];

    $result = send_notification($email, 'signup_otp', $notif_data);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'আপনার ইমেইলে ওটিপি পাঠানো হয়েছে।'
        ]);
    } else {
        error_log("Signup OTP Error: " . ($result['message'] ?? 'Unknown error'));
        echo json_encode([
            'success' => false,
            'message' => 'ইমেইল পাঠাতে সাময়িক সমস্যা হয়েছে। তথ্য যাচাই করে আবার চেষ্টা করুন।'
        ]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
