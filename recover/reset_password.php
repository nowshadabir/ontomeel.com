<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'তথ্য অসম্পূর্ণ।']);
        exit;
    }

    if (!isset($_SESSION['recovery_request']) || $_SESSION['recovery_request']['email'] !== $email || !$_SESSION['recovery_request']['verified']) {
        echo json_encode(['success' => false, 'message' => 'ভেরিফিকেশন সম্পন্ন হয়নি। দয়া করে আবার শুরু করুন।']);
        exit;
    }

    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        // Success - clear session
        unset($_SESSION['recovery_request']);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড পরিবর্তন করতে ডাটাবেস এর সমস্যা হয়েছে।']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
