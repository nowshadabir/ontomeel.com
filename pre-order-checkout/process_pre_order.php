<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $preorder_id = $_POST['preorder_id'] ?? 0;
    $address = $_POST['address'] ?? '';
    $sender_number = $_POST['sender_number'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;

    if (empty($preorder_id) || empty($address) || empty($sender_number)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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
            invoice_no, member_id, subtotal, shipping_cost, total_amount, 
            payment_status, payment_method, trx_id, order_status, shipping_address
        ) VALUES (?, ?, ?, ?, ?, 'Pending', 'Bkash', ?, 'Processing', ?)
    ");
    
    // Notice subtotal assumes $total_amount - shipping ($50)
    $shipping_cost = 50;
    $subtotal = $total_amount - $shipping_cost;

    $stmt->execute([
        $invoice_no, $user_id, $subtotal, $shipping_cost, $total_amount,
        $sender_number, $address
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert Order Item (pre-order specific)
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, preorder_id, quantity, unit_price, total_price) 
        VALUES (?, NULL, ?, 1, ?, ?)
    ");
    $stmt->execute([$order_id, $preorder_id, $subtotal, $subtotal]);

    // Ensure member address is updated if not fully set
    $stmt = $pdo->prepare("UPDATE members SET address = ? WHERE id = ? AND (address IS NULL OR address = '')");
    $stmt->execute([$address, $user_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $invoice_no]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
