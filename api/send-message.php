<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'সবগুলো ঘর পূরণ করুন।']);
        exit;
    }

    // Since there's no messages table yet, we can log it or just return success
    // In a real app, you'd insert into a table:
    /*
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);
    */

    // For now, let's just simulate success
    echo json_encode(['status' => 'success', 'message' => 'আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
