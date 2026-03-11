<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($full_name) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'নাম, ইমেইল এবং মোবাইল প্রদান করা আবশ্যক।']);
    exit;
}

try {
    // Check if email or phone is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM members WHERE (email = ? OR phone = ?) AND id != ?");
    $stmt->execute([$email, $phone, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'এই ইমেইল বা মোবাইল নম্বরটি ইতিমধ্যে অন্য অ্যাকাউন্ট দ্বারা ব্যবহৃত হচ্ছে।']);
        exit;
    }

    if (!empty($password)) {
        // Update with password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE members SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $user_id]);
    } else {
        // Update without password
        $stmt = $pdo->prepare("UPDATE members SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $user_id]);
    }

    echo json_encode(['success' => true, 'message' => 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে।']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>