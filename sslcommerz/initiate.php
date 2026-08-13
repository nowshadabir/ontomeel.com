<?php
// sslcommerz/initiate.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0);

if ($order_id <= 0) {
    die("Invalid Order ID.");
}

// Fetch order details from database
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

$sslConfig = getSSLCommerzConfig($pdo);

if (empty($sslConfig['store_id']) || empty($sslConfig['store_passwd'])) {
    die("SSLCommerz is not fully configured in admin panel. Store ID or Store Password missing.");
}

// Determine protocol & host for callback URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host;

// Build callback URLs
$success_url = $base_url . '/sslcommerz/success.php';
$fail_url    = $base_url . '/sslcommerz/fail.php';
$cancel_url  = $base_url . '/sslcommerz/cancel.php';
$ipn_url     = $base_url . '/sslcommerz/ipn.php';

$tran_id = 'ONT-' . $order['id'] . '-' . time();

// Update order with transaction ID
$updateStmt = $pdo->prepare("UPDATE orders SET trx_id = ?, payment_method = 'sslcommerz' WHERE id = ?");
$updateStmt->execute([$tran_id, $order['id']]);

$post_data = array();
$post_data['store_id'] = $sslConfig['store_id'];
$post_data['store_passwd'] = $sslConfig['store_passwd'];
$post_data['total_amount'] = number_format((float)$order['total_amount'], 2, '.', '');
$post_data['currency'] = "BDT";
$post_data['tran_id'] = $tran_id;

$post_data['success_url'] = $success_url;
$post_data['fail_url'] = $fail_url;
$post_data['cancel_url'] = $cancel_url;
$post_data['ipn_url'] = $ipn_url;

# CUSTOMER INFORMATION
$post_data['cus_name'] = !empty($order['guest_name']) ? $order['guest_name'] : 'Customer';
$post_data['cus_email'] = !empty($order['guest_email']) ? $order['guest_email'] : 'info@ontomeel.com';
$post_data['cus_add1'] = !empty($order['shipping_address']) ? $order['shipping_address'] : 'Bangladesh';
$post_data['cus_city'] = 'Dhaka';
$post_data['cus_postcode'] = '1000';
$post_data['cus_country'] = 'Bangladesh';
$post_data['cus_phone'] = !empty($order['guest_phone']) ? $order['guest_phone'] : '01700000000';

# SHIPMENT INFORMATION
$post_data['shipping_method'] = 'NO';
$post_data['num_of_item'] = '1';
$post_data['product_name'] = 'Books';
$post_data['product_category'] = 'Bookstore';
$post_data['product_profile'] = 'physical-goods';

# OPTIONAL PARAMETERS
$post_data['value_a'] = (string)$order['id'];

# Call SSLCommerz API
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $sslConfig['init_url']);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); // Keep false in dev/local

$content = curl_exec($handle);
$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if ($code == 200 && !(curl_errno($handle))) {
    curl_close($handle);
    $sslcommerzResponse = json_decode($content, true);
    
    if (isset($sslcommerzResponse['status']) && $sslcommerzResponse['status'] == 'SUCCESS') {
        if (!empty($sslcommerzResponse['GatewayPageURL'])) {
            header("Location: " . $sslcommerzResponse['GatewayPageURL']);
            exit();
        }
    }
    
    $errorMessage = isset($sslcommerzResponse['failedreason']) ? $sslcommerzResponse['failedreason'] : 'Failed to connect with SSLCommerz.';
    die("SSLCommerz Error: " . htmlspecialchars($errorMessage));
} else {
    curl_close($handle);
    die("Failed to connect with SSLCommerz API.");
}
?>
