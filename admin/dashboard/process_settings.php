<?php
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $action = $_POST['action'] ?? '';

    // ─────────────────────────────────────────────
    // ACTION: Send OTP to the provided email address
    // ─────────────────────────────────────────────
    if ($action === 'send_email_otp') {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'একটি সঠিক ইমেইল ঠিকানা দিন।']);
            exit();
        }

        // Check if email already used by another admin
        $check = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $check->execute([$email, $_SESSION['admin_id']]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'এই ইমেইল ঠিকানাটি ইতিমধ্যে অন্য একটি অ্যাকাউন্টে ব্যবহৃত হচ্ছে।']);
            exit();
        }

        // Generate 6-digit OTP
        $otp     = rand(100000, 999999);
        $expiry  = time() + (10 * 60); // 10 minutes

        // Store in session
        $_SESSION['admin_email_otp'] = [
            'email'  => $email,
            'otp'    => $otp,
            'expiry' => $expiry,
        ];

        // Load SMTP config
        require_once '../../includes/smtp_client.php';
        $smtp_config = [
            'host'      => getenv('SMTP_HOST') ?: 'ontomeel.com',
            'port'      => getenv('SMTP_PORT') ?: 465,
            'user'      => getenv('SMTP_USER') ?: 'auth@ontomeel.com',
            'pass'      => getenv('SMTP_PASS'),
            'from_name' => 'Ontomeel Admin',
        ];

        $subject = "Admin Email Verification Code: $otp";
        $message = "আপনার এডমিন প্যানেল ইমেইল যাচাইকরণ কোড হলো:\r\n\r\n" .
                   "    $otp\r\n\r\n" .
                   "এই কোডটি ১০ মিনিটের মধ্যে মেয়াদ শেষ হবে।\r\n" .
                   "যদি আপনি এই অনুরোধ না করে থাকেন, এই ইমেইলটি উপেক্ষা করুন।\r\n\r\n" .
                   "— Ontomeel Admin Panel";

        $result = send_smtp_email($email, $subject, $message, $smtp_config);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'OTP পাঠানো হয়েছে।']);
        } else {
            error_log("Admin Email OTP SMTP Error: " . ($result['message'] ?? 'unknown'));
            echo json_encode(['success' => false, 'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে। SMTP কনফিগ চেক করুন।']);
        }
        exit();
    }

    // ─────────────────────────────────────────────
    // ACTION: Update admin full name (no OTP)
    // ─────────────────────────────────────────────
    if ($action === 'update_profile_name') {
        $full_name = trim($_POST['full_name'] ?? '');

        if (empty($full_name)) {
            echo json_encode(['success' => false, 'message' => 'নাম খালি রাখা যাবে না।']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE admins SET full_name = ? WHERE id = ?");
        $stmt->execute([$full_name, $_SESSION['admin_id']]);

        // Update session so header updates without reload
        $_SESSION['admin_full_name'] = $full_name;

        echo json_encode(['success' => true, 'message' => 'নাম সফলভাবে আপডেট হয়েছে।']);
        exit();
    }

    // ─────────────────────────────────────────────
    // ACTION: Verify OTP and save email to DB
    // ─────────────────────────────────────────────
    if ($action === 'verify_email_otp') {
        $input_otp = trim($_POST['otp'] ?? '');

        if (!isset($_SESSION['admin_email_otp'])) {
            echo json_encode(['success' => false, 'message' => 'OTP সেশন পাওয়া যায়নি। আবার OTP পাঠান।']);
            exit();
        }

        $session_data = $_SESSION['admin_email_otp'];

        // Check expiry
        if (time() > $session_data['expiry']) {
            unset($_SESSION['admin_email_otp']);
            echo json_encode(['success' => false, 'message' => 'OTP মেয়াদ শেষ হয়ে গেছে। নতুন OTP আনুন।']);
            exit();
        }

        // Check OTP match
        if ($input_otp != $session_data['otp']) {
            echo json_encode(['success' => false, 'message' => 'OTP কোড সঠিক নয়। আবার চেষ্টা করুন।']);
            exit();
        }

        // Save email to database
        $email = $session_data['email'];
        $stmt = $pdo->prepare("UPDATE admins SET email = ? WHERE id = ?");
        $stmt->execute([$email, $_SESSION['admin_id']]);

        // Clear OTP session
        unset($_SESSION['admin_email_otp']);

        echo json_encode(['success' => true, 'message' => 'ইমেইল সফলভাবে সেভ করা হয়েছে।']);
        exit();
    }

    // ─────────────────────────────────────────────
    // ACTION: Update password (NO OTP required)
    // ─────────────────────────────────────────────
    if ($action === 'update_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'সবগুলো ফিল্ড পূরণ করুন।']);
            exit();
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'নতুন পাসওয়ার্ড দুটি মিলছে না।']);
            exit();
        }

        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।']);
            exit();
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($current_password, $admin['password'])) {
            echo json_encode(['success' => false, 'message' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
            exit();
        }

        // Update password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update   = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $update->execute([$new_hash, $_SESSION['admin_id']]);

        echo json_encode(['success' => true, 'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।']);
        exit();
    }

    // ─────────────────────────────────────────────
    // ACTION: Update delivery charges
    // ─────────────────────────────────────────────
    if ($action === 'update_charges') {
        $inside  = $_POST['inside']  ?? '60';
        $outside = $_POST['outside'] ?? '120';

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_charge_inside', ?) 
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$inside]);

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_charge_outside', ?) 
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$outside]);

        echo json_encode(['success' => true, 'message' => 'ডেলিভারি চার্জ আপডেট করা হয়েছে।']);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>
