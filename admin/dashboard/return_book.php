<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$borrow_id = $_POST['borrow_id'] ?? null;

if (!$borrow_id) {
    echo json_encode(['success' => false, 'message' => 'Missing borrow ID']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Fetch borrow record to get book_id
    $stmt = $pdo->prepare("SELECT book_id, status FROM borrows WHERE id = ?");
    $stmt->execute([$borrow_id]);
    $borrow = $stmt->fetch();

    if (!$borrow) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Borrow record not found']);
        exit();
    }

    if ($borrow['status'] === 'Returned') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Book already returned']);
        exit();
    }

    // Mark borrow as returned
    $pdo->prepare("UPDATE borrows SET status = 'Returned', return_date = CURRENT_DATE WHERE id = ?")
        ->execute([$borrow_id]);

    // Restore inventory stock
    $pdo->prepare("UPDATE books SET stock_qty = stock_qty + 1 WHERE id = ?")
        ->execute([$borrow['book_id']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Book returned and inventory updated']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
