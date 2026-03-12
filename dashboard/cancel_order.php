<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/notification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'লগইন করা নেই']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'অর্ডার আইডি প্রয়োজন']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch the order and verify ownership
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND member_id = ? FOR UPDATE");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'অর্ডার পাওয়া যায়নি বা আপনি এটি বাতিল করতে পারবেন না']);
        exit;
    }

    if ($order['order_status'] !== 'Processing') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'শুধুমাত্র প্রসেসিং অবস্থায় থাকা অর্ডার বাতিল করা সম্ভব']);
        exit;
    }

    $order_tz = new DateTimeZone('Asia/Dhaka');
    $order_dt = new DateTime($order['order_date'], $order_tz);
    $now_dt = new DateTime('now', $order_tz);
    
    if (($now_dt->getTimestamp() - $order_dt->getTimestamp()) > 180) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'অর্ডার করার ৩ মিনিট পর আর বাতিল করা সম্ভব নয়']);
        exit;
    }

    // 2. Fetch items for this order
    $itemStmt = $pdo->prepare("SELECT book_id, quantity FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll();

    // 3. Restore inventory
    foreach ($items as $item) {
        $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ?")
            ->execute([$item['quantity'], $item['book_id']]);
    }

    // 4. Update associated borrow records if any
    $pdo->prepare("UPDATE borrows SET status = 'Cancelled' WHERE order_id = ?")
        ->execute([$order_id]);

    // Alternatively, if it's an old borrow without order_id link (unlikely with new code)
    // we could search by member_id and book_id and Processing status, but order_id is safer.

    // 5. Refund logic (Same as admin cancellation)
    $paid_amount = (float) $order['total_amount'];
    $method = $order['payment_method'];

    if ($paid_amount > 0) {
        $should_refund = false;
        if (in_array($method, ['Wallet', 'Bkash', 'Nagad', 'Card'])) {
            $should_refund = true;
        }

        if ($should_refund) {
            // Refund to user's balance
            $pdo->prepare("UPDATE members SET acc_balance = acc_balance + ? WHERE id = ?")
                ->execute([$paid_amount, $user_id]);

            $desc = "অর্ডার বাতিল রিফান্ড (ইউজার দ্বারা) #{$order['invoice_no']} ({$method})";
            $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Refund', ?, ?)")
                ->execute([$user_id, $paid_amount, $desc, $order['invoice_no']]);

            // Mark as Paid if it wasn't already (since we acknowledge receipt for refund)
            $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE id = ?")
                ->execute([$order_id]);
        }
    }

    // 6. Final Status Update
    $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ?")
        ->execute([$order_id]);

    $pdo->commit();

    // Send Notification Email
    try {
        $userStmt = $pdo->prepare("SELECT full_name, email FROM members WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user_meta = $userStmt->fetch();

        if ($user_meta && !empty($user_meta['email'])) {
            $notif_data = [
                'name' => $user_meta['full_name'],
                'invoice_no' => $order['invoice_no']
            ];
            send_notification($user_meta['email'], 'order_cancelled', $notif_data);
        }
    } catch (Exception $e) {
        error_log("Mail Error in Customer Cancel: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'অর্ডারটি সফলভাবে বাতিল করা হয়েছে।']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'ত্রুটি: ' . $e->getMessage()]);
}
