<?php
session_start();
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!isset($_SESSION['admin_recovery_request']) || !$_SESSION['admin_recovery_request']['verified']) {
        echo json_encode(['success' => false, 'message' => 'ওটিপি ভেরিফিকেশন সফল হয়নি।']);
        exit();
    }

    $req = $_SESSION['admin_recovery_request'];

    if ($req['email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'ইমেইল ম্যাচ করেনি।']);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।']);
        exit();
    }

    $new_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);

        // Success - clear session
        unset($_SESSION['admin_recovery_request']);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড আপডেট করতে সমস্যা হয়েছে।']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
