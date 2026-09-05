<?php
// sslcommerz/initiate.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

$order_param = trim($_GET['order_id'] ?? ($_POST['order_id'] ?? ''));

if (empty($order_param)) {
    header("Location: ../checkout/index.php");
    exit();
}

// Fetch order details from database by ID or invoice_no
if (is_numeric($order_param)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? OR invoice_no = ?");
    $stmt->execute([(int)$order_param, $order_param]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
    $stmt->execute([$order_param]);
}
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("অর্ডারটি খুঁজে পাওয়া যায়নি। (Order not found)");
}

$order_id = (int)$order['id'];

// Don't initiate payment if already paid
if ($order['payment_status'] === 'Paid') {
    header("Location: ../dashboard/index.php");
    exit();
}

$sslConfig = getSSLCommerzConfig($pdo);

if (empty($sslConfig['store_id']) || empty($sslConfig['store_passwd'])) {
    die("SSLCommerz is not fully configured in admin panel. Store ID or Store Password missing.");
}

// Determine canonical base URL
$base_url = getSSLCommerzBaseUrl();

// Build callback URLs
$success_url = $base_url . '/sslcommerz/success.php';
$fail_url    = $base_url . '/sslcommerz/fail.php';
$cancel_url  = $base_url . '/sslcommerz/cancel.php';
$ipn_url     = $base_url . '/sslcommerz/ipn.php';

$tran_id = 'ONT-' . $order['id'] . '-' . time();

// Update order with transaction ID
$updateStmt = $pdo->prepare("UPDATE orders SET trx_id = ?, payment_method = 'SSLCommerz' WHERE id = ?");
$updateStmt->execute([$tran_id, $order['id']]);

// Customer Information Sanitization
$cus_name = trim($order['guest_name'] ?? '');
if (empty($cus_name)) {
    $cus_name = 'Customer';
}

$cus_email = trim($order['guest_email'] ?? '');
if (empty($cus_email) || !filter_var($cus_email, FILTER_VALIDATE_EMAIL)) {
    $cus_email = 'info@ontomeel.com';
}

$cus_phone = preg_replace('/[^0-9]/', '', $order['guest_phone'] ?? '');
if (empty($cus_phone)) {
    $cus_phone = '01700000000';
}

$cus_address = trim($order['shipping_address'] ?? '');
if (empty($cus_address)) {
    $cus_address = 'Bangladesh';
}

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
$post_data['cus_name'] = $cus_name;
$post_data['cus_email'] = $cus_email;
$post_data['cus_add1'] = mb_substr($cus_address, 0, 100);
$post_data['cus_city'] = !empty($order['district']) ? mb_substr($order['district'], 0, 50) : 'Dhaka';
$post_data['cus_state'] = !empty($order['division']) ? mb_substr($order['division'], 0, 50) : 'Bangladesh';
$post_data['cus_postcode'] = '1000';
$post_data['cus_country'] = 'Bangladesh';
$post_data['cus_phone'] = $cus_phone;

# SHIPMENT INFORMATION
$post_data['shipping_method'] = 'NO';
$post_data['num_of_item'] = '1';
$post_data['product_name'] = 'Books & Publications';
$post_data['product_category'] = 'Bookstore';
$post_data['product_profile'] = 'physical-goods';

# CUSTOM PARAMETERS
$post_data['value_a'] = (string)$order['id'];
$post_data['value_b'] = (string)($order['invoice_no'] ?? '');

# Call SSLCommerz API
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $sslConfig['init_url']);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
// Enforce strict SSL verification for gateway requests
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

$content = curl_exec($handle);
$curl_error = curl_error($handle);
$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);

if ($code == 200 && empty($curl_error)) {
    $sslcommerzResponse = json_decode($content, true);

    if (isset($sslcommerzResponse['status']) && $sslcommerzResponse['status'] == 'SUCCESS') {
        if (!empty($sslcommerzResponse['GatewayPageURL'])) {
            header("Location: " . $sslcommerzResponse['GatewayPageURL']);
            exit();
        }
    }

    $errorMessage = isset($sslcommerzResponse['failedreason']) ? $sslcommerzResponse['failedreason'] : 'গেটওয়ে সংযোগে সমস্যা হয়েছে।';
} else {
    $errorMessage = !empty($curl_error) ? $curl_error : 'Failed to connect with SSLCommerz API (HTTP ' . $code . ').';
}

$path_prefix = '../';
$page_title = 'পেমেন্ট ত্রুটি | অন্ত্যমিল';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-32 pb-20 bg-brand-light min-h-screen font-anek flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-6">
        <div class="bg-white rounded-[40px] p-8 text-center border border-gray-100 shadow-xl">
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট গেটওয়ে সংযোগ ব্যর্থ</h1>
            <p class="text-gray-500 text-sm mb-6"><?php echo htmlspecialchars($errorMessage); ?></p>

            <div class="space-y-3">
                <a href="initiate.php?order_id=<?php echo $order_id; ?>" class="inline-block w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all">
                    পুনরায় চেষ্টা করুন
                </a>
                <a href="../checkout/index.php" class="inline-block w-full bg-gray-100 text-gray-700 py-3 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    চেকআউট পেজে ফিরে যান
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
