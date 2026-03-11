<?php
require '../../includes/db_connect.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $book_id = $_POST['book_id'] ?? null;
    if (empty($book_id)) {
        throw new Exception('বইয়ের আইডি পাওয়া যায়নি');
    }

    // Check if book has active borrows or orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE book_id = ?");
    $stmt->execute([$book_id]);
    if ($stmt->fetchColumn() > 0) {
        // Instead of hard delete, maybe soft delete? 
        // For now, let's just mark as inactive if it has history
        $stmt = $pdo->prepare("UPDATE books SET is_active = 0 WHERE id = ?");
        $stmt->execute([$book_id]);
        $msg = 'বইটি ইনভেন্টরিতে ইনঅ্যাক্টিভ করা হয়েছে (অর্ডার হিস্ট্রি থাকার কারণে সরাসরি রিমুভ করা সম্ভব হয়নি)';
    } else {
        // Safe to delete if no history
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $msg = 'বইটি সফলভাবে মুছে ফেলা হয়েছে।';
    }

    echo json_encode([
        'success' => true,
        'message' => $msg
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
