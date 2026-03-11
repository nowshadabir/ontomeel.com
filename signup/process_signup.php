<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $plan = $_POST['plan'];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?error=invalid_email");
        exit();
    }

    // Validate password length
    if (strlen($password) < 6) {
        header("Location: index.php?error=weak_password");
        exit();
    }

    // Determine initial balance and expiry based on plan
    $initial_balance = 0; // Balance is always 0 by default now
    $expire_date = null;
    if ($plan != 'None') {
        $expire_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate unique membership ID (e.g., OM-2026-XXXX)
    $year = date('Y');
    $random_id = strtoupper(substr(md5(uniqid()), 0, 4));
    $membership_id = "OM-$year-$random_id";

    try {
        $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance, plan_expire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$membership_id, $full_name, $email, $phone, $hashed_password, $plan, $initial_balance, $expire_date]);

        // Redirect to login or success page
        header("Location: ../login/index.php?signup=success");
        exit();
    } catch (PDOException $e) {
        error_log("Signup Error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            die("Error: Email or Phone already registered.");
        } else {
            die("An error occurred. Please try again later.");
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>