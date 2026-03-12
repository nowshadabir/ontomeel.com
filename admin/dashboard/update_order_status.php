<?php
session_start();
include '../../includes/db_connect.php';
require_once '../../includes/notification_helper.php';

header('Content-Type: application/json');

// Admin only
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

$allowed = ['Processing', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
if (!in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Fetch current order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    $prev_status = $order['order_status'];
    $is_borrow = ($order['notes'] === 'Borrow Order');

    // Fetch items
    $itemStmt = $pdo->prepare("SELECT book_id, quantity FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll();

    // ── Status Transition Logic ──────────────────────────────────────
    //
    // WORKFLOW:
    //   User places order → stock is ALREADY deducted → order_status = 'Processing'
    //
    //   Admin marks Shipped:
    //     → order_status = 'Delivered' (visible as done to customer)
    //     → For Borrow: activate borrow records in borrows table
    //
    //   Admin marks Cancelled:
    //     → Restore inventory (stock back)
    //     → Cancel borrow records if borrow order
    //     → Refund logic:
    //         Wallet → refund acc_balance
    //         Bkash/Nagad/Card (Paid) → refund to acc_balance as credit
    //         Cash (COD) → no refund
    // ─────────────────────────────────────────────────────────────────

    if ($status === 'Shipped' && in_array($prev_status, ['Processing', 'Confirmed'])) {
        // Admin ships → mark as Shipped
        $new_db_status = 'Shipped';

        if ($is_borrow) {
            // Activate borrow records
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }

        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")
            ->execute([$new_db_status, $order_id]);

    } elseif ($status === 'Delivered') {
        // Mark as Delivered
        $pdo->prepare("UPDATE orders SET order_status = 'Delivered' WHERE id = ?")
            ->execute([$order_id]);

        // If delivered, payment is fulfilled
        $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE id = ? AND payment_status = 'Pending'")
            ->execute([$order_id]);

        if ($is_borrow) {
            // Activate borrow records if they were still processing
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }

    } elseif ($status === 'Cancelled' && in_array($prev_status, ['Processing', 'Confirmed', 'Shipped'])) {

        // Restore inventory
        foreach ($items as $item) {
            $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ?")
                ->execute([$item['quantity'], $item['book_id']]);
        }

        // Cancel borrow records
        if ($is_borrow) {
            $pdo->prepare("UPDATE borrows SET status = 'Cancelled', return_date = CURRENT_DATE
                           WHERE order_id = ? AND status IN ('Processing', 'Active')")
                ->execute([$order_id]);
        }

        // Refund logic
        $paid_amount = (float) $order['total_amount'];
        $method = $order['payment_method'];  // Wallet, Cash, Bkash, Nagad, Card

        // Rule 10a, 10c: If payment made by Bkash/Nagad or Wallet, refund to account fund
        // We handle this if status is Paid OR if it's Mobile Banking/Wallet (assuming payment was made)
        if ($paid_amount > 0) {
            $should_refund = false;
            if (in_array($method, ['Wallet', 'Bkash', 'Nagad', 'Card'])) {
                $should_refund = true;
            }

            if ($should_refund) {
                // Refund to wallet
                $pdo->prepare("UPDATE members SET acc_balance = acc_balance + ? WHERE id = ?")
                    ->execute([$paid_amount, $order['member_id']]);

                $desc = "অর্ডার বাতিল রিফান্ড #{$order['invoice_no']} ({$method})";
                $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Refund', ?, ?)")
                    ->execute([$order['member_id'], $paid_amount, $desc, $order['invoice_no']]);

                // Set order payment status to Paid if it was pending but we are refunding (meaning we acknowledge payment was made)
                $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE id = ?")
                    ->execute([$order_id]);
            }
        }

        $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ?")
            ->execute([$order_id]);

    } elseif ($status === 'Confirmed' && $prev_status === 'Processing') {
        // Just move to confirmed state, wait for Shipped
        $pdo->prepare("UPDATE orders SET order_status = 'Confirmed' WHERE id = ?")
            ->execute([$order_id]);
    } else {
        // For any other manual status change (e.g. Processing → Delivered directly)
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")
            ->execute([$status, $order_id]);

        // If it's not processing or cancelled, and it is a borrow, it should be active
        if ($is_borrow && !in_array($status, ['Processing', 'Cancelled'])) {
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }
    }

    $pdo->commit();

    // Send Notification Email
    try {
        // Fetch customer details for the notification
        $custStmt = $pdo->prepare("SELECT o.invoice_no, o.guest_name, o.guest_email, o.guest_phone, o.member_id, o.notes,
                                          m.full_name, m.email as member_email
                                   FROM orders o
                                   LEFT JOIN members m ON o.member_id = m.id
                                   WHERE o.id = ?");
        $custStmt->execute([$order_id]);
        $details = $custStmt->fetch();

        if ($details) {
            $cust_name = $details['member_id'] ? $details['full_name'] : $details['guest_name'];
            $cust_email = $details['member_id'] ? $details['member_email'] : $details['guest_email'];

            if ($cust_email) {
                $type = 'order_status_update';
                $notif_data = [
                    'name' => $cust_name,
                    'invoice_no' => $details['invoice_no'],
                    'status' => $status
                ];

                if ($status === 'Cancelled') {
                    $type = 'order_cancelled';
                } elseif ($status === 'Shipped' && $details['notes'] === 'Borrow Order') {
                    // Fetch book title for borrow
                    $bookStmt = $pdo->prepare("SELECT b.title FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ? LIMIT 1");
                    $bookStmt->execute([$order_id]);
                    $book_title = $bookStmt->fetchColumn();
                    
                    $type = 'borrow_active';
                    $notif_data['book_title'] = $book_title;
                    $notif_data['due_date'] = date('Y-m-d', strtotime('+30 days'));
                }

                send_notification($cust_email, $type, $notif_data);
            }
        }
    } catch (Exception $e) {
        error_log("Mail Error in Status Update: " . $e->getMessage());
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
