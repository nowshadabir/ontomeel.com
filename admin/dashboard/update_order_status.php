<?php
// admin/dashboard/update_order_status.php
// Order Status and Payment Status Management with Automatic Email & SMS Notifications

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/notification_helper.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login as admin.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
// Support both 'order_status' and 'status' POST parameter names
$status = trim($_POST['order_status'] ?? ($_POST['status'] ?? ''));
$payment_status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : null;

if (!$order_id || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'অর্ডারের তথ্য অথবা স্ট্যাটাস নির্বাচন করা হয়নি।']);
    exit();
}

$allowed_statuses = ['Processing', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled', 'On Hold'];
if (!in_array($status, $allowed_statuses, true)) {
    echo json_encode(['success' => false, 'message' => 'অকার্যকর অর্ডার স্ট্যাটাস নির্বাচন করা হয়েছে।']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Fetch current order record
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'অর্ডারটি খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    $prev_status = $order['order_status'];
    $is_borrow = ($order['notes'] === 'Borrow Order');

    // Fetch items belonging to this order
    $itemStmt = $pdo->prepare("SELECT book_id, quantity FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll();

    // ── Status Transition & Stock Logic ──────────────────────────────
    if ($status === 'Shipped') {
        $pdo->prepare("UPDATE orders SET order_status = 'Shipped' WHERE id = ?")->execute([$order_id]);

        if ($is_borrow) {
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }

    } elseif ($status === 'Delivered') {
        $pdo->prepare("UPDATE orders SET order_status = 'Delivered' WHERE id = ?")->execute([$order_id]);

        // When delivered, auto-mark payment as Paid unless explicitly changed to Pending/Failed
        if (empty($payment_status)) {
            $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE id = ? AND payment_status = 'Pending'")
                ->execute([$order_id]);
        }

        if ($is_borrow) {
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }

    } elseif ($status === 'Cancelled' && $prev_status !== 'Cancelled') {
        // Order cancelled from active state: restore inventory
        foreach ($items as $item) {
            if (!empty($item['book_id'])) {
                $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ?")
                    ->execute([$item['quantity'], $item['book_id']]);
            }
        }

        // Cancel borrow records if borrow order
        if ($is_borrow) {
            $pdo->prepare("UPDATE borrows SET status = 'Cancelled', return_date = CURRENT_DATE
                           WHERE order_id = ? AND status IN ('Processing', 'Active')")
                ->execute([$order_id]);
        }

        // Electronic refund logic for registered members
        $paid_amount = (float)$order['total_amount'];
        $method = $order['payment_method'] ?? '';

        if ($paid_amount > 0 && !empty($order['member_id'])) {
            $should_refund = false;
            if (in_array($method, ['Wallet', 'Bkash', 'Nagad', 'Card', 'SSLCommerz']) || $order['payment_status'] === 'Paid') {
                $should_refund = true;
            }

            if ($should_refund) {
                $pdo->prepare("UPDATE members SET acc_balance = acc_balance + ? WHERE id = ?")
                    ->execute([$paid_amount, $order['member_id']]);

                $desc = "অর্ডার বাতিল রিফান্ড #{$order['invoice_no']} ({$method})";
                $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Refund', ?, ?)")
                    ->execute([$order['member_id'], $paid_amount, $desc, $order['invoice_no']]);
            }
        }

        $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ?")->execute([$order_id]);

    } elseif ($prev_status === 'Cancelled' && $status !== 'Cancelled') {
        // Re-activating a previously cancelled order: re-deduct book stock
        foreach ($items as $item) {
            if (!empty($item['book_id'])) {
                $pdo->prepare("UPDATE books SET stock_qty = GREATEST(0, stock_qty - ?) WHERE id = ?")
                    ->execute([$item['quantity'], $item['book_id']]);
            }
        }

        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $order_id]);

    } else {
        // Confirmed, Processing, On Hold, etc.
        $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $order_id]);

        if ($is_borrow && !in_array($status, ['Processing', 'Cancelled'])) {
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP
                           WHERE order_id = ? AND status = 'Processing'")
                ->execute([$order_id]);
        }
    }

    // Apply explicit payment_status update if supplied
    if (!empty($payment_status)) {
        $allowed_pay = ['Pending', 'Paid', 'Failed'];
        if (in_array($payment_status, $allowed_pay, true)) {
            $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$payment_status, $order_id]);
        }
    }

    $pdo->commit();

    // ── Notifications (Email & SMS) ──────────────────────────────────
    $email_sent = false;
    $sms_sent = false;
    $email_msg = '';
    $sms_msg = '';

    try {
        // Fetch fresh customer contact details
        $custStmt = $pdo->prepare("SELECT o.invoice_no, o.guest_name, o.guest_email, o.guest_phone, o.member_id, o.notes, o.total_amount, o.shipping_address,
                                          m.full_name, m.email as member_email, m.phone as member_phone
                                   FROM orders o
                                   LEFT JOIN members m ON o.member_id = m.id
                                   WHERE o.id = ?");
        $custStmt->execute([$order_id]);
        $details = $custStmt->fetch();

        if ($details) {
            $cust_name = !empty($details['full_name']) ? $details['full_name'] : (!empty($details['guest_name']) ? $details['guest_name'] : 'গ্রাহক');
            $cust_email = !empty($details['member_email']) ? $details['member_email'] : (!empty($details['guest_email']) ? $details['guest_email'] : '');
            $cust_phone = !empty($details['member_phone']) ? $details['member_phone'] : (!empty($details['guest_phone']) ? $details['guest_phone'] : '');

            // 1. Send Email Notification
            if (!empty($cust_email)) {
                $type = ($status === 'Cancelled') ? 'order_cancelled' : 'order_status_update';
                $notif_data = [
                    'name'        => $cust_name,
                    'invoice_no'  => $details['invoice_no'],
                    'status'      => $status,
                    'amount'      => (float)$details['total_amount'],
                    'address'     => $details['shipping_address']
                ];

                file_put_contents(__DIR__ . '/../../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] STATUS_UPDATE_EMAIL: Invoice #" . $details['invoice_no'] . " | Status: {$status} | Target: {$cust_email}\n", FILE_APPEND);
                $mail_res = send_notification($cust_email, $type, $notif_data);
                $email_sent = !empty($mail_res['success']);
                $email_msg = $mail_res['message'] ?? ($email_sent ? 'OK' : 'Failed');
                file_put_contents(__DIR__ . '/../../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] STATUS_UPDATE_EMAIL_RESULT: " . ($email_sent ? 'SUCCESS' : 'FAILED - ' . $email_msg) . "\n", FILE_APPEND);
            }

            // 2. Send SMS Notification if phone number is present
            if (!empty($cust_phone)) {
                $inv = $details['invoice_no'];
                $clean_first_name = explode(' ', trim($cust_name))[0];
                if (empty($clean_first_name) || $clean_first_name === 'গ্রাহক') {
                    $clean_first_name = 'Customer';
                }

                switch ($status) {
                    case 'Confirmed':
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} has been confirmed. Thank you!";
                        break;
                    case 'Shipped':
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} is shipped and on the way to your delivery address.";
                        break;
                    case 'Delivered':
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} has been successfully delivered. Thank you for choosing Ontomeel!";
                        break;
                    case 'Cancelled':
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} has been cancelled. Please contact support for any query.";
                        break;
                    case 'On Hold':
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} is currently on hold. We will contact you soon.";
                        break;
                    default:
                        $sms_body = "Dear {$clean_first_name}, your Ontomeel order #{$inv} status is now {$status}.";
                        break;
                }

                file_put_contents(__DIR__ . '/../../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] STATUS_UPDATE_SMS_TRIGGER: Invoice #{$inv} | Phone: {$cust_phone} | Msg: {$sms_body}\n", FILE_APPEND);
                $sms_res = send_sms_instantly($cust_phone, $sms_body);
                $sms_sent = !empty($sms_res['success']);
                $sms_msg = $sms_res['message'] ?? ($sms_sent ? 'OK' : 'Failed');
            }
        }
    } catch (Throwable $notifEx) {
        file_put_contents(__DIR__ . '/../../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] STATUS_NOTIF_EXCEPTION: " . $notifEx->getMessage() . "\n", FILE_APPEND);
    }

    echo json_encode([
        'success'    => true,
        'message'    => 'অর্ডার স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।',
        'email_sent' => $email_sent,
        'sms_sent'   => $sms_sent,
        'email_msg'  => $email_msg,
        'sms_msg'    => $sms_msg
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'সার্ভার ত্রুটি: ' . $e->getMessage()]);
}
