<?php
// membership/sslcommerz_ipn.php
// Instant Payment Notification (IPN) listener for membership subscriptions
header('Content-Type: text/plain');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../sslcommerz/config.php';
require_once __DIR__ . '/../includes/notification_helper.php';

$plans = [
    'General' => ['name' => 'সাধারণ পাঠক', 'price' => 500],
    'BookLover' => ['name' => 'নিয়মিত পাঠক', 'price' => 700],
    'Collector' => ['name' => 'সাহিত্য অনুরাগী', 'price' => 1000]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $val_id      = trim($_REQUEST['val_id'] ?? '');
    $tran_id     = trim($_REQUEST['tran_id'] ?? '');
    $user_id_raw = (int)($_REQUEST['value_a'] ?? 0);
    $plan_key    = trim($_REQUEST['value_b'] ?? 'General');
    $req_id_raw  = (int)($_REQUEST['value_c'] ?? 0);
    $card_type   = trim($_REQUEST['card_type'] ?? 'SSLCommerz');

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

    $val_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $val_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($handle);
    $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    $result = json_decode($response, true);

    if ($http_code == 200 && $result && isset($result['status']) && ($result['status'] === 'VALID' || $result['status'] === 'VALIDATED')) {
        $verified_amount = (float)($result['amount'] ?? ($_REQUEST['amount'] ?? 0));
        $verified_currency = strtoupper($result['currency'] ?? 'BDT');

        if ($user_id_raw <= 0 && !empty($result['value_a'])) {
            $user_id_raw = (int)$result['value_a'];
        }
        if (!empty($result['value_b']) && isset($plans[$result['value_b']])) {
            $plan_key = trim($result['value_b']);
        }
        if ($req_id_raw <= 0 && !empty($result['value_c'])) {
            $req_id_raw = (int)$result['value_c'];
        }

        if (!isset($plans[$plan_key])) {
            $plan_key = 'General';
        }
        $expected_amount = (float)$plans[$plan_key]['price'];

        if ($verified_currency === 'BDT' && $verified_amount >= ($expected_amount - 0.05)) {
            // Check if already processed (Idempotency)
            $existingTrx = $pdo->prepare("SELECT id FROM transactions WHERE reference_id = ? LIMIT 1");
            $existingTrx->execute([$val_id]);
            if ($existingTrx->fetch()) {
                http_response_code(200);
                echo "IPN SUCCESS: ALREADY PROCESSED";
                exit();
            }

            try {
                $pdo->beginTransaction();

                $mStmt = $pdo->prepare("SELECT id, full_name, email, membership_id, membership_plan, plan_expire_date FROM members WHERE id = ? FOR UPDATE");
                $mStmt->execute([$user_id_raw]);
                $member = $mStmt->fetch(PDO::FETCH_ASSOC);

                if ($member) {
                    $new_expire_sql = "NOW() + INTERVAL 1 YEAR";
                    if (!empty($member['plan_expire_date']) && strtotime($member['plan_expire_date']) > time()) {
                        $new_expire_sql = "plan_expire_date + INTERVAL 1 YEAR";
                    }

                    $updMember = $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = {$new_expire_sql} WHERE id = ?");
                    $updMember->execute([$plan_key, $user_id_raw]);

                    if ($req_id_raw > 0) {
                        $updReq = $pdo->prepare("UPDATE membership_requests SET status = 'Confirmed', payment_method = 'SSLCommerz', trx_id = ?, amount = ? WHERE id = ?");
                        $updReq->execute([$tran_id, $verified_amount, $req_id_raw]);
                    } else {
                        $insReq = $pdo->prepare("INSERT INTO membership_requests (member_id, plan, payment_method, trx_id, amount, status, created_at) VALUES (?, ?, 'SSLCommerz', ?, ?, 'Confirmed', NOW())");
                        $insReq->execute([$user_id_raw, $plan_key, $tran_id, $verified_amount]);
                    }

                    $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, ?, 'Purchase', ?, ?, NOW())");
                    $insTrx->execute([
                        $user_id_raw,
                        $verified_amount,
                        "Membership Subscription: " . $plans[$plan_key]['name'],
                        $val_id ?: $tran_id
                    ]);

                    $pdo->commit();

                    // Send email
                    if (!empty($member['email'])) {
                        $dateStmt = $pdo->prepare("SELECT plan_expire_date FROM members WHERE id = ?");
                        $dateStmt->execute([$user_id_raw]);
                        $updDate = $dateStmt->fetchColumn();

                        try {
                            $notif_data = [
                                'name' => $member['full_name'],
                                'membership_id' => $member['membership_id'],
                                'plan_name' => $plans[$plan_key]['name'],
                                'expire_date' => date('d M, Y', strtotime($updDate)),
                                'amount' => $verified_amount,
                                'payment_method' => $card_type ?: 'SSLCommerz'
                            ];
                            send_notification_instantly($member['email'], 'membership_activated', $notif_data);
                        } catch (Exception $e) {
                            error_log("IPN Membership Email Error: " . $e->getMessage());
                        }
                    }

                    http_response_code(200);
                    echo "IPN SUCCESS";
                    exit();
                } else {
                    $pdo->rollBack();
                    http_response_code(400);
                    echo "IPN FAILED: Member not found";
                    exit();
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                http_response_code(500);
                echo "IPN FAILED: DB Error";
                exit();
            }
        }
    }

    http_response_code(400);
    echo "IPN FAILED: Validation failed";
    exit();
}

http_response_code(405);
echo "Method Not Allowed";
