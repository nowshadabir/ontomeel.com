<?php
session_start();
include '../../includes/db_connect.php';
require_once '../../includes/notification_helper.php';

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

    // Send Notification Email
    try {
        $dataStmt = $pdo->prepare("SELECT b.member_id, b.order_id, m.full_name, m.email, bk.title, bk.title_en, bk.author, bk.author_en
                                   FROM borrows b 
                                   JOIN members m ON b.member_id = m.id 
                                   JOIN books bk ON b.book_id = bk.id 
                                   WHERE b.id = ?");
        $dataStmt->execute([$borrow_id]);
        $details = $dataStmt->fetch();

        if ($details && !empty($details['email'])) {
            $inv_no = 'N/A';
            if ($details['order_id']) {
                $inv_stmt = $pdo->prepare("SELECT invoice_no FROM orders WHERE id = ?");
                $inv_stmt->execute([$details['order_id']]);
                $inv_no = $inv_stmt->fetchColumn() ?: 'N/A';
            }

            $notif_data = [
                'name' => $details['full_name'],
                'invoice_no' => $inv_no,
                'book_title' => $details['title'],
                'book_title_en' => $details['title_en'],
                'book_author' => $details['author'],
                'book_author_en' => $details['author_en']
            ];
            send_notification($details['email'], 'borrow_returned', $notif_data);
        }
    } catch (Exception $e) {
        error_log("Mail Error in Return Book: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Book returned and inventory updated']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
