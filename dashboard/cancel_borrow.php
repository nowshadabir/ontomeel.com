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
$borrow_id = $_POST['borrow_id'] ?? null;

if (!$borrow_id) {
    echo json_encode(['success' => false, 'message' => 'Borrow ID is required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if the record belongs to the user and is still processing
    $stmt = $pdo->prepare("SELECT status, book_id, order_id FROM borrows WHERE id = ? AND member_id = ? FOR UPDATE");
    $stmt->execute([$borrow_id, $user_id]);
    $borrow = $stmt->fetch();

    if (!$borrow) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'অর্ডার পাওয়া যায়নি']);
        exit;
    }

    if ($borrow['status'] !== 'Processing') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'শুধুমাত্র প্রসেসিং অবস্থায় থাকা অর্ডার বাতিল করা সম্ভব']);
        exit;
    }

    // Update status to Cancelled
    $stmt = $pdo->prepare("UPDATE borrows SET status = 'Cancelled', return_date = CURRENT_DATE WHERE id = ?");
    $stmt->execute([$borrow_id]);

    // Restore inventory
    $pdo->prepare("UPDATE books SET stock_qty = stock_qty + 1 WHERE id = ?")
        ->execute([$borrow['book_id']]);

    // If there's an associated order, check if all items in that order are cancelled
    // and then cancel the order itself if needed, or just cancel the order anyway if it's a 1:1 match.
    if ($borrow['order_id']) {
        $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ?")
            ->execute([$borrow['order_id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'ধার অর্ডারটি বাতিল করা হয়েছে।']);

} catch (PDOException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>