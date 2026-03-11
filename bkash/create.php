<?php
session_start();

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);

$id_token = $_SESSION['id_token'];

$amount = $_GET['amount'] ?? '1';
$invoice = $_GET['invoice'] ?? 'INV' . uniqid(); 
$callbackURL = 'http://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['PHP_SELF']) == '/' ? '' : dirname($_SERVER['PHP_SELF'])) . '/callback.php';

$post_token = array(
    'mode' => '0011',
    'payerReference' => $invoice,
    'callbackURL' => $callbackURL,
    'amount' => $amount,
    'currency' => 'BDT',
    'intent' => 'sale',
    'merchantInvoiceNumber' => $invoice
);

$url = curl_init($array['base_url'] . "/create");
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

if (isset($response['paymentID'])) {
    require_once '../includes/db_connect.php';
    $stmt = $pdo->prepare("UPDATE orders SET payment_id = ? WHERE invoice_no = ?");
    $stmt->execute([$response['paymentID'], $invoice]);
}

echo $resultdata;
?>
