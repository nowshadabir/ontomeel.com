<?php
// sslcommerz/ipn.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $val_id = $_POST['val_id'] ?? '';
    $tran_id = $_POST['tran_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $order_id = isset($_POST['value_a']) ? (int)$_POST['value_a'] : 0;

    $sslConfig = getSSLCommerzConfig($pdo);

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
            if ($order_id > 0) {
                $checkStmt = $pdo->prepare("SELECT payment_status, invoice_no, guest_name, guest_email, shipping_address, total_amount FROM orders WHERE id = ?");
                $checkStmt->execute([$order_id]);
                $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);

                $wasAlreadyPaid = ($orderData && $orderData['payment_status'] === 'Paid');

                $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = 'Pending', payment_id = ?, trx_id = ? WHERE id = ?");
                $stmt->execute([$val_id, $tran_id, $order_id]);

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
                        error_log("SSLCommerz IPN email notification error: " . $e->getMessage());
                    }
                }
                echo "IPN SUCCESS";
                exit();
            }
        }
    }
    echo "IPN FAILED";
} else {
    echo "INVALID METHOD";
}
?>
