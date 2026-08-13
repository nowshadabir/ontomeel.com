<?php
// sslcommerz/success.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

$val_id = $_POST['val_id'] ?? '';
$tran_id = $_POST['tran_id'] ?? '';
$amount = $_POST['amount'] ?? 0;
$order_id = isset($_POST['value_a']) ? (int)$_POST['value_a'] : 0;

$sslConfig = getSSLCommerzConfig($pdo);

$is_valid = false;
$verified_amount = 0;

if (!empty($val_id) && !empty($sslConfig['store_id']) && !empty($sslConfig['store_passwd'])) {
    $validation_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $validation_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($handle);
    curl_close($handle);

    $result = json_decode($response, true);

    if ($result && isset($result['status']) && ($result['status'] == 'VALID' || $result['status'] == 'VALIDATED')) {
        $is_valid = true;
        $verified_amount = $result['amount'] ?? $amount;
        if (empty($tran_id)) {
            $tran_id = $result['tran_id'] ?? '';
        }
        if ($order_id <= 0 && !empty($result['value_a'])) {
            $order_id = (int)$result['value_a'];
        }
    }
}

if ($is_valid && $order_id > 0) {
    // Fetch order information first
    $checkStmt = $pdo->prepare("SELECT payment_status, invoice_no, guest_name, guest_email, shipping_address, total_amount FROM orders WHERE id = ?");
    $checkStmt->execute([$order_id]);
    $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $wasAlreadyPaid = ($orderData && $orderData['payment_status'] === 'Paid');

    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = 'Pending', payment_id = ?, trx_id = ? WHERE id = ?");
    $stmt->execute([$val_id, $tran_id, $order_id]);

    // Send email notification ONLY after payment is confirmed paid (and hasn't been sent yet)
    if (!$wasAlreadyPaid && $orderData && !empty($orderData['guest_email'])) {
        try {
            require_once __DIR__ . '/../includes/notification_helper.php';
            $notif_data = [
                'name' => $orderData['guest_name'],
                'invoice_no' => $orderData['invoice_no'],
                'amount' => $orderData['total_amount'],
                'address' => $orderData['shipping_address']
            ];
            send_notification($orderData['guest_email'], 'order_placed', $notif_data);
        } catch (Exception $e) {
            error_log("SSLCommerz success email notification error: " . $e->getMessage());
        }
    }
}

$page_title = 'পেমেন্ট সফল | অন্ত্যমিল';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-32 pb-20 bg-brand-light min-h-screen font-anek flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-6">
        <div class="bg-white rounded-[40px] p-8 text-center border border-gray-100 shadow-xl">
            <?php if ($is_valid): ?>
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট সফল হয়েছে!</h1>
                <p class="text-gray-500 text-sm mb-6">আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে। অর্ডারটি শীঘ্রই প্রক্রিয়াকরণ করা হবে।</p>
                <div class="bg-gray-50 p-4 rounded-2xl mb-6 text-left space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-400">অর্ডার নম্বর:</span>
                        <span class="font-bold text-brand-900">#<?php echo htmlspecialchars($order_id); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">ট্রানজেকশন নম্বর:</span>
                        <span class="font-mono text-brand-900"><?php echo htmlspecialchars($tran_id); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">পরিমাণ:</span>
                        <span class="font-bold text-brand-gold">৳<?php echo number_format($verified_amount, 2); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="w-20 h-20 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট যাচাই পেন্ডিং</h1>
                <p class="text-gray-500 text-sm mb-6">আপনার পেমেন্ট রেকর্ড করা হয়েছে, খুব শীঘ্রই আমাদের টিম এটি নিশ্চিত করবে।</p>
            <?php endif; ?>

            <a href="../index.php" class="inline-block w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all">
                হোম পেজে ফিরে যান
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
