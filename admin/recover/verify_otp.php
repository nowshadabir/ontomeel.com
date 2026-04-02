<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';

    if (!isset($_SESSION['admin_recovery_request'])) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি সেশন শেষ হয়ে গেছে। পুনরায় চেষ্টা করুন।']);
        exit();
    }

    $req = $_SESSION['admin_recovery_request'];

    if ($req['email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'ইমেইল ম্যাচ করেনি।']);
        exit();
    }

    if (time() > $req['expiry']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি-র মেয়াদ শেষ হয়ে গেছে।']);
        exit();
    }

    if ($req['otp'] != $otp) {
        echo json_encode(['success' => false, 'message' => 'ভুল ওটিপি দিয়েছেন।']);
        exit();
    }

    // Mark as verified
    $_SESSION['admin_recovery_request']['verified'] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
