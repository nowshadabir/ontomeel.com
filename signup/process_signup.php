<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function normalize_otp_digits($input) {
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $str = str_replace($bn_digits, $en_digits, trim($input));
    return preg_replace('/[^0-9]/', '', $str);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_otp = $_POST['otp'] ?? '';
    $submitted_otp = normalize_otp_digits($raw_otp);
    
    // Check if session data exists
    if (!isset($_SESSION['signup_data'])) {
        echo json_encode(['success' => false, 'message' => 'সেশন শেষ হয়ে গিয়েছে। দয়া করে আবার চেষ্টা করুন।']);
        exit();
    }

    $signup_data = $_SESSION['signup_data'];

    // Verify OTP and Expiry
    if ((string)$submitted_otp !== (string)$signup_data['otp']) {
        echo json_encode(['success' => false, 'message' => 'ভুল ওটিপি কোড দিয়েছেন।']);
        exit();
    }

    if (time() > $signup_data['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপির মেয়াদ শেষ হয়ে গিয়েছে। নতুন কোড পাঠান।']);
        exit();
    }

    // Data from session
    $full_name = $signup_data['full_name'];
    $email = $signup_data['email'];
    $phone = $signup_data['phone'];
    $hashed_password = $signup_data['password'];
    $plan = 'None';
    $initial_balance = 0;
    $expire_date = null;

    // Generate unique membership ID (e.g., OM-2026-XXXX)
    $year = date('Y');
    $random_id = strtoupper(substr(md5(uniqid()), 0, 4));
    $membership_id = "OM-$year-$random_id";

    try {
        $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance, plan_expire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$membership_id, $full_name, $email, $phone, $hashed_password, $plan, $initial_balance, $expire_date]);
        $new_user_id = (int)$pdo->lastInsertId();

        // Clear signup session
        unset($_SESSION['signup_data']);

        // Auto-login newly registered user
        if (!headers_sent()) {
            @session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['membership_id'] = $membership_id;
        $_SESSION['membership_plan'] = $plan;
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();

        echo json_encode([
            'success' => true, 
            'message' => 'রেজিস্ট্রেশন সফল হয়েছে!',
            'auto_login' => true
        ]);
        exit();
    } catch (PDOException $e) {
        error_log("Signup Error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'এই মোবাইল বা ইমেইল দিয়ে ইতিমধ্যে একাউন্ট রয়েছে।']);
        } else {
            echo json_encode(['success' => false, 'message' => 'সার্ভার সমস্যা হয়েছে। পরে আবার চেষ্টা করুন।']);
        }
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>