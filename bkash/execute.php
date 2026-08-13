<?php
session_start();

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);

$id_token = $_SESSION['id_token'];
$paymentID = $_GET['paymentID'];

$post_token = array(
    'paymentID' => $paymentID
);

$url = curl_init($array['base_url'] . "/execute");
$posttoken = json_encode($post_token);
$header = array(
    'Content-Type:application/json',
    "Authorization:" . $id_token,
    "X-APP-Key:" . $array['app_key']
);

curl_setopt($url, CURLOPT_HTTPHEADER, $header);
curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
$resultdata = curl_exec($url);
curl_close($url);

$response = json_decode($resultdata, true);

if (isset($response['paymentID']) && ($response['transactionStatus'] == 'Completed' || $response['transactionStatus'] == 'Successful')) {
    require_once '../includes/db_connect.php';
    $invoice = $response['merchantInvoiceNumber'];
    $paymentID = $response['paymentID'];
    $trxID = $response['trxID'];
    
    $checkStmt = $pdo->prepare("SELECT payment_status, guest_name, guest_email, shipping_address, total_amount FROM orders WHERE invoice_no = ?");
    $checkStmt->execute([$invoice]);
    $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $wasAlreadyPaid = ($orderData && $orderData['payment_status'] === 'Paid');

    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', trx_id = ?, payment_id = ? WHERE invoice_no = ?");
    $stmt->execute([$trxID, $paymentID, $invoice]);

    if (!$wasAlreadyPaid && $orderData && !empty($orderData['guest_email'])) {
        try {
            require_once '../includes/notification_helper.php';
            $notif_data = [
                'name' => $orderData['guest_name'],
                'invoice_no' => $invoice,
                'amount' => $orderData['total_amount'],
                'address' => $orderData['shipping_address']
            ];
            send_notification($orderData['guest_email'], 'order_placed', $notif_data);
        } catch (Exception $e) {
            error_log("bKash execute email notification error: " . $e->getMessage());
        }
    }
}

echo $resultdata;
?>
