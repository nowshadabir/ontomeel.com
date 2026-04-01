<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'সব তথ্য দিন।']);
        exit;
    }

    if (!isset($_SESSION['recovery_request']) || $_SESSION['recovery_request']['email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'রিকভারি সেশন শুরু হয়নি বা ইমেইল মেলেনি।']);
        exit;
    }

    if (time() > $_SESSION['recovery_request']['expiry']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি এর মেয়াদ শেষ। অনুগ্রহ করে নতুন করে নিন।']);
        exit;
    }

    if ((string)$_SESSION['recovery_request']['otp'] === (string)$otp) {
        $_SESSION['recovery_request']['verified'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ভুল ওটিপি! সঠিক কোডটি দিন।']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
