<?php
// sslcommerz/ipn.php
// Instant Payment Notification (IPN) Listener
header('Content-Type: text/plain');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $val_id   = trim($_REQUEST['val_id'] ?? '');
    $tran_id  = trim($_REQUEST['tran_id'] ?? '');
    $order_id = isset($_REQUEST['value_a']) ? (int)$_REQUEST['value_a'] : 0;
    $amount   = (float)($_REQUEST['amount'] ?? 0);

    if (empty($val_id)) {
        http_response_code(400);
        echo "IPN ERROR: Missing val_id";
        exit();
    }

    $sslConfig = getSSLCommerzConfig($pdo);

    if (empty($sslConfig['store_id']) || empty($sslConfig['store_passwd'])) {
        http_response_code(500);
        echo "IPN ERROR: Store credentials unconfigured";
        exit();
    }

    $validation_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $validation_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    $result = json_decode($response, true);

    if ($code == 200 && $result && isset($result['status']) && ($result['status'] === 'VALID' || $result['status'] === 'VALIDATED')) {
        $verified_amount = (float)($result['amount'] ?? $amount);
        $verified_currency = strtoupper($result['currency'] ?? 'BDT');

        if (empty($tran_id) && !empty($result['tran_id'])) {
            $tran_id = $result['tran_id'];
        }
        if ($order_id <= 0 && !empty($result['value_a'])) {
            $order_id = (int)$result['value_a'];
        }

        $orderData = null;
        if ($order_id > 0) {
            $checkStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $checkStmt->execute([$order_id]);
            $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        }

        $inv_candidate = trim($result['value_b'] ?? ($_REQUEST['value_b'] ?? ''));
        if (!$orderData && !empty($inv_candidate)) {
            $checkStmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ? LIMIT 1");
            $checkStmt->execute([$inv_candidate]);
            $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($orderData) {
                $order_id = (int)$orderData['id'];
            }
        }

        if (!$orderData && !empty($tran_id)) {
            $checkStmt = $pdo->prepare("SELECT * FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
            $checkStmt->execute([$tran_id]);
            $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($orderData) {
                $order_id = (int)$orderData['id'];
            }
        }

        if ($orderData && $verified_currency === 'BDT') {
            $expected_amount = (float)$orderData['total_amount'];
            if ($verified_amount >= ($expected_amount - 0.05)) {
                // Replay attack prevention: check if this val_id was already used on another order
                if (!empty($val_id)) {
                    $replayCheck = $pdo->prepare("SELECT id FROM orders WHERE payment_id = ? AND id != ? LIMIT 1");
                    $replayCheck->execute([$val_id, $order_id]);
                    if ($replayCheck->fetch()) {
                        error_log("SSLCommerz IPN REPLAY ATTACK BLOCKED: val_id {$val_id} already consumed on another order!");
                        http_response_code(400);
                        echo "IPN FAILED: REPLAY DETECTED";
                        exit();
                    }
                }

                // Transaction ID match verification
                if (!empty($orderData['trx_id']) && !empty($result['tran_id'])) {
                    if ($orderData['trx_id'] !== $result['tran_id']) {
                        error_log("SSLCommerz IPN TRANSACTION MISMATCH: Order TrxID {$orderData['trx_id']} vs Gateway TrxID {$result['tran_id']}");
                        http_response_code(400);
                        echo "IPN FAILED: TRANSACTION MISMATCH";
                        exit();
                    }
                }

                $wasAlreadyPaid = ($orderData['payment_status'] === 'Paid');

                $is_risk = (isset($result['risk_level']) && (string)$result['risk_level'] === '1');
                $new_order_status = $is_risk ? 'On Hold' : 'Processing';
                $notes = $orderData['notes'] ?? '';
                if ($is_risk && strpos($notes, 'Risk Level 1') === false) {
                    $notes = trim($notes . " | [Risk Level 1: Verify Customer]");
                }

                $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = ?, payment_id = ?, trx_id = ?, payment_method = 'SSLCommerz', notes = ? WHERE id = ?");
                $stmt->execute([$new_order_status, $val_id, $tran_id, $notes, $order_id]);

                if (!$wasAlreadyPaid && !empty($orderData['guest_email'])) {
                    try {
                        require_once __DIR__ . '/../includes/notification_helper.php';
                        $notif_data = [
                            'name' => $orderData['guest_name'],
                            'invoice_no' => $orderData['invoice_no'],
                            'amount' => $verified_amount ?: $orderData['total_amount'],
                            'address' => $orderData['shipping_address']
                        ];
                        send_notification($orderData['guest_email'], 'order_placed', $notif_data);
                    } catch (Exception $e) {
                        error_log("SSLCommerz IPN email notification error: " . $e->getMessage());
                    }
                }

                http_response_code(200);
                echo "IPN SUCCESS";
                exit();
            }
        }
    }

    http_response_code(400);
    echo "IPN FAILED";
} else {
    http_response_code(405);
    echo "INVALID METHOD";
}
