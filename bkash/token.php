<?php
session_start();

// Get bKash credentials from environment variables
$app_key = getenv('BKASH_APP_KEY') ?: '';
$app_secret = getenv('BKASH_APP_SECRET') ?: '';
$username = getenv('BKASH_USERNAME') ?: '';
$password = getenv('BKASH_PASSWORD') ?: '';
$base_url = getenv('BKASH_BASE_URL') ?: 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout';

if (empty($app_key) || empty($app_secret) || empty($username) || empty($password)) {
    echo json_encode(['error' => 'bKash credentials not configured']);
    exit();
}

$post_token = array(
    'app_key' => $app_key,
    'app_secret' => $app_secret
);

$url = curl_init($base_url . "/token/grant");
$posttoken = json_encode($post_token);
$header = array(
    'Content-Type:application/json',
    "password:" . $password,
    "username:" . $username
);

curl_setopt($url, CURLOPT_HTTPHEADER, $header);
curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
$resultdata = curl_exec($url);

// Check for curl errors
if (curl_errno($url)) {
    $curl_error = curl_error($url);
    curl_close($url);
    echo json_encode(['error' => 'cURL Error: ' . $curl_error]);
    exit();
}

curl_close($url);
$response = json_decode($resultdata, true);

if (isset($response['id_token'])) {
    $_SESSION['id_token'] = $response['id_token'];
}

echo $resultdata;
?>