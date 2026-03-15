<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../includes/db_connect.php';
require_once '../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function sendResponse($success, $message, $extra = [])
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    sendResponse(false, 'লগইন করা নেই। দয়া করে সাইন-ইন করুন।');
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cash';
$checkout_type = $_POST['checkout_type'] ?? 'buy';
$total_amount = (float) ($_POST['total_amount'] ?? 0);
$cart_data = $_POST['cart'] ?? '[]';
$cart = json_decode($cart_data, true);

if (empty($cart)) {
    sendResponse(false, 'কার্ট খালি।');
}

// Check if payment method is active (only for 'buy' or pre-order)
if ($checkout_type === 'buy') {
    try {
        $pay_stmt = $pdo->prepare("SELECT is_active FROM payment_methods WHERE method_key = ?");
        $pay_stmt->execute([$payment_method]);
        $is_pay_active = $pay_stmt->fetchColumn();

        if ($is_pay_active === false || (int) $is_pay_active !== 1) {
            sendResponse(false, 'দুঃখিত, এই পেমেন্ট পদ্ধতিটি বর্তমানে নিষ্ক্রিয় আছে।');
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '42S02') {
             // If table is missing, we assume failure to protect the system
             sendResponse(false, 'পেমেন্ট সিস্টেম কনফিগারেশন ত্রুটি।');
        }
        throw $e;
    }
}

// Membership check
if ($checkout_type === 'borrow') {
    $stmt = $pdo->prepare("SELECT membership_plan, plan_expire_date FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $m_user = $stmt->fetch();

    $is_expired = false;
    if (!empty($m_user['plan_expire_date'])) {
        $is_expired = strtotime($m_user['plan_expire_date']) < time();
    }

    if (!$m_user || $m_user['membership_plan'] === 'None' || $is_expired) {
        sendResponse(false, 'বই ধার নিতে একটি সক্রিয় মেম্বারশিপ প্রয়োজন।');
    }
}

// Payment method mapping for older PHP versions
$db_payment_method = 'Cash';
switch ($payment_method) {
    case 'fund':
        $db_payment_method = 'Wallet';
        break;
    case 'bkash':
        $db_payment_method = 'Bkash';
        break;
    case 'nagad':
        $db_payment_method = 'Nagad';
        break;
    case 'card':
        $db_payment_method = 'Card';
        break;
    default:
        $db_payment_method = 'Cash';
        break;
}

try {
    $pdo->beginTransaction();

    // 1. Stock Check
    foreach ($cart as $item) {
        $itemId = $item['id'];
        $isPreorder = (strpos($itemId, 'pre_') === 0);

        if ($isPreorder) {
            $realPoId = substr($itemId, 4);
            $poStmt = $pdo->prepare("SELECT status, title FROM pre_orders WHERE id = ?");
            $poStmt->execute([$realPoId]);
            $po = $poStmt->fetch();

            if (!$po || $po['status'] !== 'Open') {
                $pdo->rollBack();
                $title = $po['title'] ?? 'বইটি';
                sendResponse(false, "$title প্রি-অর্ডার এখন আর নেওয়া হচ্ছে না।");
            }
        } else {
            $stockStmt = $pdo->prepare("SELECT stock_qty, title FROM books WHERE id = ? FOR UPDATE");
            $stockStmt->execute([$itemId]);
            $book = $stockStmt->fetch();

            if (!$book || $book['stock_qty'] < 1) {
                $pdo->rollBack();
                $title = $book['title'] ?? 'বইটি';
                sendResponse(false, "$title স্টকে নেই।");
            }
        }
    }

    // 2. Wallet logic
    if ($payment_method === 'fund' && $checkout_type === 'buy' && $total_amount > 0) {
        $stmt = $pdo->prepare("SELECT acc_balance FROM members WHERE id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $current_balance = (float) $stmt->fetchColumn();

        if ($current_balance < $total_amount) {
            $pdo->rollBack();
            sendResponse(false, 'আপনার অ্যাকাউন্ট ফান্ডে পর্যাপ্ত ব্যালেন্স নেই।');
        }

        $pdo->prepare("UPDATE members SET acc_balance = acc_balance - ? WHERE id = ?")
            ->execute([$total_amount, $user_id]);

        $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description) VALUES (?, ?, 'Purchase', ?)")
            ->execute([$user_id, $total_amount, 'Book purchase via Wallet']);
    }

    // Fetch shipping charges from settings
    function getSetting($pdo, $key, $default = '') {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    $c_location = $_POST['location'] ?? 'inside';
    $inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
    $outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);
    $selected_charge = ($c_location === 'inside') ? $inside_charge : $outside_charge;

    // 3. Create Main Order
    $invoice_no = 'OM-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $payment_status = ($payment_method === 'fund') ? 'Paid' : 'Pending';
    $shipping_cost = ($checkout_type === 'borrow' || $total_amount <= 0) ? 0 : $selected_charge;
    $subtotal = max(0, $total_amount - $shipping_cost);

    // Determine order notes based on contents
    $hasPreorder = false;
    foreach ($cart as $item) {
        if (strpos($item['id'], 'pre_') === 0) {
            $hasPreorder = true;
            break;
        }
    }

    $notes = $hasPreorder ? 'Pre-order Booking' : (($checkout_type === 'borrow') ? 'Borrow Order' : 'Purchase Order');
    $shipping_addr = trim($address . ($city ? ', ' . $city : ''));

    $orderStmt = $pdo->prepare("INSERT INTO orders (invoice_no, member_id, subtotal, shipping_cost, total_amount, payment_status, payment_method, order_status, shipping_address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'Processing', ?, ?)");
    $orderStmt->execute([
        $invoice_no,
        $user_id,
        $subtotal,
        $shipping_cost,
        $total_amount,
        $payment_status,
        $db_payment_method,
        $shipping_addr,
        $notes
    ]);
    $order_id = $pdo->lastInsertId();

    // 4. Items & Inventory
    foreach ($cart as $item) {
        $itemId = $item['id'];
        $isPreorder = (strpos($itemId, 'pre_') === 0);
        $unit_price = ($checkout_type === 'borrow') ? 0 : (float) ($item['price'] ?? 0);

        if ($isPreorder) {
            $realPoId = substr($itemId, 4);
            $pdo->prepare("INSERT INTO order_items (order_id, preorder_id, quantity, unit_price, total_price) VALUES (?, ?, 1, ?, ?)")
                ->execute([$order_id, $realPoId, $unit_price, $unit_price]);
        } else {
            $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price, total_price) VALUES (?, ?, 1, ?, ?)")
                ->execute([$order_id, $itemId, $unit_price, $unit_price]);

            $pdo->prepare("UPDATE books SET stock_qty = GREATEST(stock_qty - 1, 0) WHERE id = ?")
                ->execute([$itemId]);

            if ($checkout_type === 'borrow') {
                $due_date = date('Y-m-d', strtotime('+30 days'));
                $pdo->prepare("INSERT INTO borrows (member_id, order_id, book_id, due_date, status) VALUES (?, ?, ?, ?, 'Processing')")
                    ->execute([$user_id, $order_id, $itemId, $due_date]);
            }
        }
    }

    $pdo->commit();

    // Send Notification Email
    try {
        $emailStmt = $pdo->prepare("SELECT email FROM members WHERE id = ?");
        $emailStmt->execute([$user_id]);
        $user_email = $emailStmt->fetchColumn();

        // Breadcrumb Log
        $log_id = $invoice_no ?? 'UNKNOWN';
        file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT: Order #$log_id | User ID: $user_id | Email: " . ($user_email ?: 'EMPTY') . "\n", FILE_APPEND);

        if ($user_email) {
            $notif_data = [
                'name' => $name,
                'invoice_no' => $invoice_no,
                'amount' => $total_amount,
                'address' => $shipping_addr
            ];
            
            // ... (rest of book detail logic) ...
            if (!empty($cart)) {
                $firstItem = $cart[0];
                $itemId = $firstItem['id'];
                $isPreorder = (strpos($itemId, 'pre_') === 0);
                
                if ($isPreorder) {
                    $realId = substr($itemId, 4);
                    $stmt = $pdo->prepare("SELECT title_en, author_en FROM pre_orders WHERE id = ?");
                    $stmt->execute([$realId]);
                    $info = $stmt->fetch();
                    $notif_data['is_preorder'] = true;
                } else {
                    $stmt = $pdo->prepare("SELECT title_en, author_en FROM books WHERE id = ?");
                    $stmt->execute([$itemId]);
                    $info = $stmt->fetch();
                }

                if ($info) {
                    $notif_data['book_title_en'] = $info['title_en'];
                    $notif_data['book_author_en'] = $info['author_en'];
                }
            }

            $result = send_notification($user_email, 'order_placed', $notif_data);
            file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT: send_notification triggered. Result: " . ($result['success'] ? 'OK' : 'FAIL - ' . ($result['message'] ?? '')) . "\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        error_log("Mail Error in Checkout: " . $e->getMessage());
    }

    sendResponse(true, 'অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।', ['order_id' => $invoice_no]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    sendResponse(false, 'অর্ডার প্রসেস করতে ত্রুটি: ' . $e->getMessage());
} catch (Error $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    sendResponse(false, 'সিস্টেম ত্রুটি: ' . $e->getMessage());
}
