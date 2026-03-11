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
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', trx_id = ?, payment_id = ? WHERE invoice_no = ?");
    $stmt->execute([$trxID, $paymentID, $invoice]);
}

echo $resultdata;
?>
