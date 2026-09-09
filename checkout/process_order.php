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
$email = trim($_POST['email'] ?? '');
$division = trim($_POST['division'] ?? '');
$district = trim($_POST['district'] ?? '');
$upazila = trim($_POST['upazila'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cod';
$checkout_type = $_POST['checkout_type'] ?? 'buy'; // 'buy' or 'borrow'
$total_amount = (float) ($_POST['total_amount'] ?? 0);
$cart_raw = $_POST['cart'] ?? '[]';
$cart = json_decode($cart_raw, true);

if (empty($name) || empty($phone) || empty($email) || empty($address) || empty($division) || empty($district) || empty($upazila) || empty($cart)) {
    sendResponse(false, 'সব তথ্য প্রদান করা আবশ্যক (নাম, ফোন, ইমেইল, বিভাগ, জেলা, উপজেলা এবং সম্পূর্ণ ঠিকানা)।');
}

$clean_phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($clean_phone) < 11) {
    sendResponse(false, 'সঠিক ১১ ডিজিটের মোবাইল নম্বর প্রদান করুন।');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'সঠিক ইমেইল এড্রেস প্রদান করুন।');
}

if ($checkout_type === 'borrow' && !$user_id) {
    sendResponse(false, 'বই ধার নিতে হলে আপনাকে অবশ্যই লগইন করতে হবে।');
}

// Check if payment method is active (only for 'buy' or pre-order)
if ($checkout_type === 'buy') {
    if ($payment_method === 'fund') {
        sendResponse(false, 'ওয়ালেট পেমেন্ট সিস্টেমটি বর্তমানে বন্ধ রয়েছে। অনুগ্রহ করে অন্য কোনো পেমেন্ট মাধ্যম নির্বাচন করুন।');
    }
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
    case 'bkash':
        $db_payment_method = 'Bkash';
        break;
    case 'nagad':
        $db_payment_method = 'Nagad';
        break;
    case 'sslcommerz':
        $db_payment_method = 'SSLCommerz';
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

    // Determine inside or outside Cox's Bazar Sadar
    $is_inside = false;
    if (stripos($district, 'Cox') !== false || mb_strpos($district, 'কক্সবাজার') !== false) {
        if (empty($upazila) || stripos($upazila, 'Sadar') !== false || mb_strpos($upazila, 'সদর') !== false) {
            $is_inside = true;
        }
    }

    $inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
    $outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);
    $selected_charge = $is_inside ? $inside_charge : $outside_charge;

    // 3. Create Main Order
    $invoice_no = 'OM-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
    
    // Calculate raw subtotal from cart
    $raw_subtotal = 0;
    if ($checkout_type === 'buy') {
        foreach ($cart as $cItem) {
            $raw_subtotal += (float)($cItem['price'] ?? 0);
        }
    }

    // Apply active member discount if eligible
    $discount_percent = 0;
    if ($checkout_type === 'buy' && $user_id) {
        $mStmt = $pdo->prepare("SELECT membership_plan, plan_expire_date FROM members WHERE id = ?");
        $mStmt->execute([$user_id]);
        $mUser = $mStmt->fetch();
        if ($mUser && !empty($mUser['membership_plan']) && $mUser['membership_plan'] !== 'None') {
            if (!empty($mUser['plan_expire_date']) && strtotime($mUser['plan_expire_date']) >= time()) {
                if ($mUser['membership_plan'] === 'General') $discount_percent = 5;
                elseif ($mUser['membership_plan'] === 'BookLover') $discount_percent = 8;
                elseif ($mUser['membership_plan'] === 'Collector') $discount_percent = 10;
            }
        }
    }

    $discount_amount = ($discount_percent > 0) ? round(($raw_subtotal * $discount_percent) / 100, 2) : 0;
    $subtotal = max(0, $raw_subtotal - $discount_amount);
    $shipping_cost = ($checkout_type === 'borrow' || $total_amount <= 0) ? 0 : $selected_charge;
    $total_amount = ($checkout_type === 'borrow') ? 0 : ($subtotal + $shipping_cost);

    $payment_status = ($checkout_type === 'borrow') ? 'Paid' : 'Pending';
    if ($checkout_type === 'borrow') {
        $db_payment_method = 'Membership';
    }

    // Determine order notes based on contents
    $hasPreorder = false;
    foreach ($cart as $item) {
        if (strpos($item['id'], 'pre_') === 0) {
            $hasPreorder = true;
            break;
        }
    }

    $notes = $hasPreorder ? 'Pre-order Booking' : (($checkout_type === 'borrow') ? 'Borrow Order' : ($discount_percent > 0 ? "Purchase Order (Member Discount {$discount_percent}%)" : 'Purchase Order'));
    $shipping_addr = implode(', ', array_filter([$address, $upazila, $district, $division]));

    $orderStmt = $pdo->prepare("INSERT INTO orders (invoice_no, member_id, guest_name, guest_phone, guest_email, subtotal, shipping_cost, total_amount, payment_status, payment_method, order_status, shipping_address, division, district, upazila, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', ?, ?, ?, ?, ?)");
    $orderStmt->execute([
        $invoice_no,
        $user_id,
        $name,
        $phone,
        $email,
        $subtotal,
        $shipping_cost,
        $total_amount,
        $payment_status,
        $db_payment_method,
        $shipping_addr,
        $division,
        $district,
        $upazila,
        $notes
    ]);
    $order_id = $pdo->lastInsertId();

    // Update member address if empty
    if ($user_id) {
        $pdo->prepare("UPDATE members SET address = ? WHERE id = ? AND (address IS NULL OR address = '')")
            ->execute([$shipping_addr, $user_id]);
    }

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

    // Send Notification Email ONLY if payment is already done (e.g. Wallet/Fund) or COD
    if ($payment_status === 'Paid' || $payment_method === 'cod') {
        try {
            // We use the email captured from the form ($email variable)
            $target_email = $email;

            // Breadcrumb Log
            $log_id = $invoice_no ?? 'UNKNOWN';
            file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT: Order #$log_id | User ID: " . ($user_id ?: 'GUEST') . " | Email: $target_email\n", FILE_APPEND);

            if ($target_email) {
                $notif_data = [
                    'name' => $name,
                    'invoice_no' => $invoice_no,
                    'amount' => $total_amount,
                    'address' => $shipping_addr
                ];
                
                if (!empty($cart)) {
                    $firstItem = $cart[0];
                    $itemId = $firstItem['id'];
                    $isPreorder = (strpos($itemId, 'pre_') === 0);
                    
                    if ($isPreorder) {
                        $realId = substr($itemId, 4);
                        $stmt = $pdo->prepare("SELECT title, author FROM pre_orders WHERE id = ?");
                        $stmt->execute([$realId]);
                        $info = $stmt->fetch();
                        $notif_data['is_preorder'] = true;
                    } else {
                        $stmt = $pdo->prepare("SELECT title, author FROM books WHERE id = ?");
                        $stmt->execute([$itemId]);
                        $info = $stmt->fetch();
                    }

                    if ($info) {
                        $notif_data['book_title_en'] = $info['title']; // Using title as fallback
                        $notif_data['book_author_en'] = $info['author'];
                    }
                }

                $result = send_notification($target_email, 'order_placed', $notif_data);
                file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT: send_notification triggered. Result: " . ($result['success'] ? 'OK' : 'FAIL - ' . ($result['message'] ?? '')) . "\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] CHECKOUT ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log("Mail Error in Checkout: " . $e->getMessage());
        }
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
