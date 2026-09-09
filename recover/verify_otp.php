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
    $email = trim($_POST['email'] ?? '');
    $raw_otp = $_POST['otp'] ?? '';
    $otp = normalize_otp_digits($raw_otp);

    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি কোডটি লিখুন।']);
        exit();
    }

    if (!isset($_SESSION['recovery_request'])) {
        echo json_encode(['success' => false, 'message' => 'রিকভারি সেশন শুরু হয়নি বা শেষ হয়ে গেছে। আবার চেষ্টা করুন।']);
        exit();
    }

    $recovery = $_SESSION['recovery_request'];

    if (!empty($email) && strtolower($recovery['email']) !== strtolower($email)) {
        echo json_encode(['success' => false, 'message' => 'ইমেইল অ্যাড্রেস মেলেনি।']);
        exit();
    }

    if (time() > $recovery['expiry']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি এর মেয়াদ শেষ হয়ে গেছে। নতুন ওটিপি পাঠান।']);
        exit();
    }

    if ((string)$recovery['otp'] === (string)$otp) {
        $_SESSION['recovery_request']['verified'] = true;
        echo json_encode([
            'success' => true,
            'message' => 'ওটিপি সফলভাবে যাচাই হয়েছে।'
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'ভুল ওটিপি কোড! সঠিক কোডটি দিন।']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
