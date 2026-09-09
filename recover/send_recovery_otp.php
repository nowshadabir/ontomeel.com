<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';
require_once __DIR__ . '/../includes/smtp_client.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function normalize_input_id($input) {
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($bn_digits, $en_digits, trim($input));
}

function mask_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1];
    $len = strlen($name);
    if ($len <= 2) {
        $masked_name = $name[0] . '*';
    } else {
        $masked_name = substr($name, 0, 2) . str_repeat('*', max(3, $len - 3)) . substr($name, -1);
    }
    return $masked_name . '@' . $domain;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit recovery requests (5 attempts per 5 minutes)
    if (!check_rate_limit('recovery_otp', 5, 300)) {
        log_security_event('rate_limit_exceeded', ['identifier' => 'recovery_otp']);
        echo json_encode([
            'success' => false, 
            'message' => 'অতিরিক্ত অনুরোধ করা হয়েছে! কিছুক্ষণ পর আবার চেষ্টা করুন।'
        ]);
        exit();
    }

    $raw_input = $_POST['email'] ?? ($_POST['login_id'] ?? '');
    $login_id = normalize_input_id($raw_input);

    if (empty($login_id)) {
        echo json_encode(['success' => false, 'message' => 'আপনার ইমেইল বা মোবাইল নম্বর দিন।']);
        exit();
    }

    // Prepare phone variation
    $phone_cleaned = preg_replace('/[^0-9]/', '', $login_id);
    if (strlen($phone_cleaned) === 13 && strpos($phone_cleaned, '880') === 0) {
        $phone_cleaned = substr($phone_cleaned, 2);
    }

    // Check if user exists by Email or Phone
    $stmt = $pdo->prepare("SELECT id, full_name, email, phone FROM members WHERE email = ? OR phone = ? OR phone = ? LIMIT 1");
    $stmt->execute([$login_id, $login_id, $phone_cleaned]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'এই তথ্য দিয়ে কোনো মেম্বার একাউন্ট খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    $target_email = trim($user['email'] ?? '');
    if (empty($target_email) || !filter_var($target_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'অ্যাকাউন্টে কোনো বৈধ ইমেইল যুক্ত নেই। এডমিনের সাথে যোগাযোগ করুন।']);
        exit();
    }

    // Generate 6 digit OTP
    $otp = rand(100000, 999999);
    $expiry = time() + (15 * 60); // 15 minutes valid

    // Store in secure session
    $_SESSION['recovery_request'] = [
        'user_id' => $user['id'],
        'email' => $target_email,
        'otp' => $otp,
        'expiry' => $expiry,
        'verified' => false
    ];

    $notif_data = [
        'name' => $user['full_name'] ?: 'Member',
        'otp' => $otp
    ];

    $result = send_notification($target_email, 'password_recovery', $notif_data);

    if ($result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'ওটিপি সফলভাবে পাঠানো হয়েছে।',
            'email' => $target_email,
            'masked_email' => mask_email($target_email)
        ]);
    } else {
        error_log("Recovery OTP Error: " . ($result['message'] ?? 'Unknown Error'));
        echo json_encode([
            'success' => false, 
            'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
