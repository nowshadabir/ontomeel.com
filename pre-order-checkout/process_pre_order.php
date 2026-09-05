<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!function_exists('getSetting')) {
    function getSetting($pdo, $key, $default = '')
    {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $preorder_id = (int)($_POST['preorder_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $division = trim($_POST['division'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $upazila = trim($_POST['upazila'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($preorder_id) || empty($address) || empty($name) || empty($phone) || empty($division) || empty($district) || empty($upazila)) {
        echo json_encode(['success' => false, 'message' => 'প্রয়োজনীয় সব তথ্য প্রদান করুন (নাম, মোবাইল নম্বর, বিভাগ, জেলা, উপজেলা এবং ঠিকানা)।']);
        exit();
    }

    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($clean_phone) < 11) {
        echo json_encode(['success' => false, 'message' => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর প্রদান করুন।']);
        exit();
    }

    // Email is required for payment receipts and order status
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'অনুগ্রহ করে আপনার ইমেইল এড্রেস দিন।']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'সঠিক ইমেইল এড্রেস প্রদান করুন।']);
        exit();
    }

    // Verify pre-order item existence
    $po_stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE id = ?");
    $po_stmt->execute([$preorder_id]);
    $pre_order = $po_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pre_order) {
        echo json_encode(['success' => false, 'message' => 'প্রি-অর্ডার বইটি খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    if ($pre_order['status'] !== 'Open') {
        echo json_encode(['success' => false, 'message' => 'এই বইটির প্রি-বুকিং বর্তমানে বন্ধ রয়েছে।']);
        exit();
    }

    // Calculate accurate pricing from database
    $item_price = ($pre_order['discount_price'] > 0) ? (float)$pre_order['discount_price'] : (float)$pre_order['price'];
    $is_free_delivery = (isset($pre_order['free_delivery']) && (int)$pre_order['free_delivery'] === 1);

    $inside_charge = (float)getSetting($pdo, 'delivery_charge_inside', 60);
    $outside_charge = (float)getSetting($pdo, 'delivery_charge_outside', 120);

    // Determine if delivery is inside Cox's Bazar Sadar
    $is_inside = false;
    if (stripos($district, 'Cox') !== false || mb_strpos($district, 'কক্সবাজার') !== false) {
        if (empty($upazila) || stripos($upazila, 'Sadar') !== false || mb_strpos($upazila, 'সদর') !== false) {
            $is_inside = true;
        }
    }
    $shipping_charge = $is_free_delivery ? 0.00 : ($is_inside ? $inside_charge : $outside_charge);

    $subtotal = $item_price;
    $total_amount = $subtotal + $shipping_charge;

    // Full combined shipping address
    $full_shipping_address = implode(', ', array_filter([$address, $upazila, $district, $division]));

    // Generate Invoice Number
    $invoice_prefix = 'PRE-';
    $year = date('y');
    $stmt = $pdo->query("SELECT invoice_no FROM orders WHERE invoice_no LIKE '{$invoice_prefix}{$year}-%' ORDER BY id DESC LIMIT 1");
    $last_invoice = $stmt->fetchColumn();

    if ($last_invoice) {
        $last_num = (int)substr($last_invoice, -4);
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }
    $invoice_no = "{$invoice_prefix}{$year}-{$new_num}";

    $pdo->beginTransaction();

    // Insert Order with SSLCommerz method, division, district, upazila and Pending payment status
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            invoice_no, member_id, guest_name, guest_phone, guest_email, subtotal, shipping_cost, total_amount, 
            payment_status, payment_method, trx_id, order_status, shipping_address, division, district, upazila, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'SSLCommerz', NULL, 'Processing', ?, ?, ?, ?, 'Pre-order Booking')
    ");

    $stmt->execute([
        $invoice_no,
        $user_id,
        $name,
        $phone,
        $email,
        $subtotal,
        $shipping_charge,
        $total_amount,
        $full_shipping_address,
        $division,
        $district,
        $upazila
    ]);

    $order_id = (int)$pdo->lastInsertId();

    // Insert Order Item (linked with preorder_id)
    $item_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, preorder_id, quantity, unit_price, total_price) 
        VALUES (?, NULL, ?, 1, ?, ?)
    ");
    $item_stmt->execute([$order_id, $preorder_id, $subtotal, $subtotal]);

    // Update member profile address if member is logged in and had no saved address
    if ($user_id) {
        $updateMember = $pdo->prepare("UPDATE members SET address = ? WHERE id = ? AND (address IS NULL OR address = '')");
        $updateMember->execute([$full_shipping_address, $user_id]);
    }

    $pdo->commit();

    // Return order ID and redirect URL to SSLCommerz initiation
    echo json_encode([
        'success'      => true,
        'order_id'     => $order_id,
        'invoice_no'   => $invoice_no,
        'redirect_url' => '../sslcommerz/initiate.php?order_id=' . $order_id
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'ত্রুটি: ' . $e->getMessage()]);
}
