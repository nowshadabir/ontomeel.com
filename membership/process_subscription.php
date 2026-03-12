<?php
require_once '../includes/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $plan = $_POST['plan'];

    // Basic internal validation
    $valid_plans = ['General', 'BookLover', 'Collector'];
    if (!in_array($plan, $valid_plans)) {
        die("Invalid plan selected.");
    }

    $expire_date = date('Y-m-d H:i:s', strtotime('+30 days'));

    try {
        // Just set the plan and expiry. Balance is handled separately via Add Fund.
        $stmt = $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = ? WHERE id = ?");
        $stmt->execute([$plan, $expire_date, $user_id]);

        // Update Session
        $_SESSION['membership_plan'] = $plan;

        header("Location: index.php?subscription=success");
        exit();
    } catch (PDOException $e) {
        error_log("Subscription Error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
} else {
    header("Location: index.php");
    exit();
}
?>