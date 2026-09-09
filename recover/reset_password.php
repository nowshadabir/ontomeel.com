<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'নতুন পাসওয়ার্ড দিন।']);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।']);
        exit();
    }

    if (!isset($_SESSION['recovery_request']) || empty($_SESSION['recovery_request']['verified'])) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি ভেরিফিকেশন সম্পন্ন হয়নি। দয়া করে আবার শুরু করুন।']);
        exit();
    }

    $target_email = $_SESSION['recovery_request']['email'];

    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $target_email]);

        // Clear recovery session state
        unset($_SESSION['recovery_request']);

        // Clear rate limits
        clear_rate_limit('login');
        clear_rate_limit('recovery_otp');

        echo json_encode([
            'success' => true,
            'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।'
        ]);
        exit();
    } catch (PDOException $e) {
        error_log("Password Reset DB Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড সংরক্ষণে সমস্যা হয়েছে। আবার চেষ্টা করুন।']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
