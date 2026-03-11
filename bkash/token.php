<?php
session_start();

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);

$post_token = array(
    'app_key' => $array['app_key'],
    'app_secret' => $array['app_secret']
);

$url = curl_init($array['base_url'] . "/token/grant");
$posttoken = json_encode($post_token);
$header = array(
    'Content-Type:application/json',
    "password:" . $array['password'],
    "username:" . $array['username']
);

curl_setopt($url, CURLOPT_HTTPHEADER, $header);
curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
$resultdata = curl_exec($url);
curl_close($url);

$response = json_decode($resultdata, true);

if (isset($response['id_token'])) {
    $_SESSION['id_token'] = $response['id_token'];
}

echo $resultdata;
?>
