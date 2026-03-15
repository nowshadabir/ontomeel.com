<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/notification_helper.php';

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $preorder_id = $_POST['preorder_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $sender_number = $_POST['sender_number'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;

    if (empty($preorder_id) || empty($address) || empty($sender_number) || empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'প্রয়োজনীয় তথ্য প্রদান করুন (নাম, মোবাইল, ঠিকানা এবং ট্রাঞ্জেকশন আইডি)']);
        exit();
    }

    // Email is required for order updates
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'অনুগ্রহ করে আপনার ইমেইল এড্রেস দিন']);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'সঠিক ইমেইল এড্রেস দিন']);
        exit();
    }

    // Check if Transaction ID already exists
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE trx_id = ?");
    $stmt->execute([$sender_number]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'এই ট্রাঞ্জেকশন আইডিটি ইতিপূর্বেই ব্যবহার করা হয়েছে। অনুগ্রহ করে সঠিক আইডিটি দিন অথবা আমাদের সাথে যোগাযোগ করুন।']);
        exit();
    }

    // Generate Invoice Number
    $invoice_prefix = 'PRE-';
    $year = date('y');
    $stmt = $pdo->query("SELECT invoice_no FROM orders WHERE invoice_no LIKE '{$invoice_prefix}{$year}-%' ORDER BY id DESC LIMIT 1");
    $last_invoice = $stmt->fetchColumn();

    if ($last_invoice) {
        $last_num = (int)substr($last_invoice, -4);
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    }
    else {
        $new_num = '0001';
    }
    $invoice_no = "{$invoice_prefix}{$year}-{$new_num}";

    $pdo->beginTransaction();


    // Insert Order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            invoice_no, member_id, guest_name, guest_phone, guest_email, subtotal, shipping_cost, total_amount, 
            payment_status, payment_method, trx_id, order_status, shipping_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Bkash', ?, 'Processing', ?)
    ");

    // Fetch free_delivery status for this pre-order
    $po_stmt = $pdo->prepare("SELECT free_delivery FROM pre_orders WHERE id = ?");
    $po_stmt->execute([$preorder_id]);
    $is_free_delivery = (int)($po_stmt->fetchColumn() ?: 0);

    // Fetch shipping charges from settings
    function getSetting($pdo, $key, $default = '')
    {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        }
        catch (Exception $e) {
            return $default;
        }
    }

    $c_location = $_POST['location'] ?? 'inside';
    $inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
    $outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);
    $selected_charge = ($c_location === 'inside') ? $inside_charge : $outside_charge;

    $shipping_cost = ($is_free_delivery === 1) ? 0 : $selected_charge;
    $subtotal = $total_amount - $shipping_cost;

    $stmt->execute([
        $invoice_no, $user_id, $name, $phone, $email, $subtotal, $shipping_cost, $total_amount,
        $sender_number, $address
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert Order Item (pre-order specific)
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, preorder_id, quantity, unit_price, total_price) 
        VALUES (?, NULL, ?, 1, ?, ?)
    ");
    $stmt->execute([$order_id, $preorder_id, $subtotal, $subtotal]);

    // Ensure member address is updated ONLY IF LOGGED IN
    if ($user_id) {
        $stmt = $pdo->prepare("UPDATE members SET address = ? WHERE id = ? AND (address IS NULL OR address = '')");
        $stmt->execute([$address, $user_id]);
    }

    $pdo->commit();

    // Send Notification Email
    try {
        // Get pre-order book details - Database Safe (no _en columns)
        $poStmt = $pdo->prepare("SELECT title, author FROM pre_orders WHERE id = ?");
        $poStmt->execute([$preorder_id]);
        $po_info = $poStmt->fetch();

        $notif_data = [
            'name' => $name,
            'invoice_no' => $invoice_no,
            'amount' => $total_amount,
            'address' => $address,
            'guest' => !$user_id,
            'is_preorder' => true
        ];

        if ($po_info) {
            $notif_data['book_title'] = $po_info['title'];
            $notif_data['book_title_en'] = $po_info['title']; // Fallback
            $notif_data['book_author'] = $po_info['author'];
            $notif_data['book_author_en'] = $po_info['author']; // Fallback
        }

        $result = send_notification($email, 'order_placed', $notif_data);
        
        // Log the trigger for debugging
        file_put_contents(__DIR__ . '/../mail_debug.log', "[" . date('Y-m-d H:i:s') . "] PRE-ORDER: Invoice #$invoice_no | Email: $email | Result: " . ($result['success'] ? 'OK' : 'FAIL - ' . ($result['message'] ?? '')) . "\n", FILE_APPEND);
    }
    catch (Exception $e) {
        // Log mail error but don't fail the order
        error_log("Mail Error in Pre-order: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'order_id' => $invoice_no]);

}
catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
