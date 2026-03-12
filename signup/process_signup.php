<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submitted_otp = trim($_POST['otp'] ?? '');
    
    // Check if session data exists
    if (!isset($_SESSION['signup_data'])) {
        echo json_encode(['success' => false, 'message' => 'সেশন শেষ হয়ে গিয়েছে। আবার চেষ্টা করুন।']);
        exit();
    }

    $signup_data = $_SESSION['signup_data'];

    // Verify OTP and Expiry
    if ($submitted_otp != $signup_data['otp']) {
        echo json_encode(['success' => false, 'message' => 'ভুল ওটিপি দিয়েছেন।']);
        exit();
    }

    if (time() > $signup_data['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপির মেয়াদ শেষ হয়ে গিয়েছে।']);
        exit();
    }

    // Data from session
    $full_name = $signup_data['full_name'];
    $email = $signup_data['email'];
    $phone = $signup_data['phone'];
    $hashed_password = $signup_data['password'];
    $plan = 'None'; // Default plan as UI is hidden

    // Determine initial balance and expiry based on plan
    $initial_balance = 0;
    $expire_date = null;

    // Generate unique membership ID (e.g., OM-2026-XXXX)
    $year = date('Y');
    $random_id = strtoupper(substr(md5(uniqid()), 0, 4));
    $membership_id = "OM-$year-$random_id";

    try {
        $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance, plan_expire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$membership_id, $full_name, $email, $phone, $hashed_password, $plan, $initial_balance, $expire_date]);

        // Success - Clear signup session
        unset($_SESSION['signup_data']);

        echo json_encode(['success' => true, 'message' => 'রেজিস্ট্রেশন সফল হয়েছে!']);
        exit();
    } catch (PDOException $e) {
        error_log("Signup Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'একটি সমস্যা হয়েছে। পরে আবার চেষ্টা করুন।']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>