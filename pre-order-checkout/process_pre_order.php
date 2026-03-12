<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    // One-time check/migration for guest columns if they don't exist
    // This is a safe way to ensure the DB is ready without separate access
    $pdo->exec("ALTER TABLE orders MODIFY member_id INT(11) NULL");
    
    // Check if guest_name exists, if not add it
    $check = $pdo->query("SHOW COLUMNS FROM orders LIKE 'guest_name'");
    if (!$check->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN guest_name VARCHAR(150) DEFAULT NULL");
        $pdo->exec("ALTER TABLE orders ADD COLUMN guest_phone VARCHAR(20) DEFAULT NULL");
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $preorder_id = $_POST['preorder_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $sender_number = $_POST['sender_number'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;

    if (empty($preorder_id) || empty($address) || empty($sender_number) || empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'প্রয়োজনীয় তথ্য প্রদান করুন (নাম, মোবাইল, ঠিকানা এবং ট্রাঞ্জেকশন আইডি)']);
        exit();
    }

    // Generate Invoice Number
    $invoice_prefix = 'PRE-';
    $year = date('y');
    $stmt = $pdo->query("SELECT invoice_no FROM orders WHERE invoice_no LIKE '{$invoice_prefix}{$year}-%' ORDER BY id DESC LIMIT 1");
    $last_invoice = $stmt->fetchColumn();

    if ($last_invoice) {
        $last_num = (int) substr($last_invoice, -4);
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }
    $invoice_no = "{$invoice_prefix}{$year}-{$new_num}";

    $pdo->beginTransaction();


    // Insert Order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            invoice_no, member_id, guest_name, guest_phone, subtotal, shipping_cost, total_amount, 
            payment_status, payment_method, trx_id, order_status, shipping_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Bkash', ?, 'Processing', ?)
    ");
    
    // Notice subtotal assumes $total_amount - shipping ($50)
    $shipping_cost = 50;
    $subtotal = $total_amount - $shipping_cost;

    $stmt->execute([
        $invoice_no, $user_id, $name, $phone, $subtotal, $shipping_cost, $total_amount,
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

    echo json_encode(['success' => true, 'order_id' => $invoice_no]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
