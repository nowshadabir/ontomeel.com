<?php
// membership/initiate_sslcommerz.php
// Online payment initiation for membership subscriptions via SSLCommerz

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../sslcommerz/config.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $plan_param = trim($_GET['plan'] ?? ($_POST['plan'] ?? 'General'));
    header("Location: ../login/index.php?redirect=" . urlencode("../membership/request.php?plan=" . $plan_param));
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Plans Definition
$plans = [
    'General' => [
        'name' => 'সাধারণ পাঠক',
        'price' => 500,
        'days' => 30
    ],
    'BookLover' => [
        'name' => 'নিয়মিত পাঠক',
        'price' => 700,
        'days' => 30
    ],
    'Collector' => [
        'name' => 'সাহিত্য অনুরাগী',
        'price' => 1000,
        'days' => 30
    ]
];

$plan_key = trim($_POST['plan'] ?? ($_GET['plan'] ?? 'General'));
if (!isset($plans[$plan_key])) {
    $plan_key = 'General';
}

$selected_plan = $plans[$plan_key];
$amount = (float)$selected_plan['price'];

// Fetch Member Details
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die("মেম্বার তথ্য পাওয়া যায়নি। অনুগ্রহ করে আবার লগইন করুন।");
}

$sslConfig = getSSLCommerzConfig($pdo);

if (empty($sslConfig['store_id']) || empty($sslConfig['store_passwd'])) {
    die("SSLCommerz গেটওয়ে কনফিগার করা হয়নি। দয়া করে এডমিন প্যানেল চেক করুন।");
}

$base_url = getSSLCommerzBaseUrl();

// Unique Transaction ID for Membership
$tran_id = 'MEM-' . $user_id . '-' . time();

// Insert pending membership request
$insertStmt = $pdo->prepare("INSERT INTO membership_requests (member_id, plan, payment_method, trx_id, amount, status, created_at) VALUES (?, ?, 'SSLCommerz', ?, ?, 'Pending', NOW())");
$insertStmt->execute([$user_id, $plan_key, $tran_id, $amount]);
$request_id = (int)$pdo->lastInsertId();

// Sanitized Customer Data
$cus_name = trim($member['full_name'] ?? 'Member');
$cus_email = trim($member['email'] ?? '');
if (empty($cus_email) || !filter_var($cus_email, FILTER_VALIDATE_EMAIL)) {
    $cus_email = 'member@ontomeel.com';
}
$cus_phone = preg_replace('/[^0-9]/', '', $member['phone'] ?? '');
if (empty($cus_phone)) {
    $cus_phone = '01700000000';
}
$cus_address = trim($member['address'] ?? 'Dhaka, Bangladesh');
if (empty($cus_address)) {
    $cus_address = 'Bangladesh';
}

// Prepare SSLCommerz Payload
$post_data = array();
$post_data['store_id'] = $sslConfig['store_id'];
$post_data['store_passwd'] = $sslConfig['store_passwd'];
$post_data['total_amount'] = number_format($amount, 2, '.', '');
$post_data['currency'] = "BDT";
$post_data['tran_id'] = $tran_id;

$post_data['success_url'] = $base_url . '/membership/sslcommerz_return.php';
$post_data['fail_url']    = $base_url . '/membership/sslcommerz_return.php';
$post_data['cancel_url']  = $base_url . '/membership/sslcommerz_return.php';
$post_data['ipn_url']     = $base_url . '/membership/sslcommerz_ipn.php';

# Customer Information
$post_data['cus_name']     = $cus_name;
$post_data['cus_email']    = $cus_email;
$post_data['cus_add1']     = mb_substr($cus_address, 0, 100);
$post_data['cus_city']     = 'Dhaka';
$post_data['cus_postcode'] = '1000';
$post_data['cus_country']  = 'Bangladesh';
$post_data['cus_phone']    = $cus_phone;

# Shipment Information
$post_data['shipping_method']  = 'NO';
$post_data['num_of_item']      = '1';
$post_data['product_name']     = 'Ontomeel Membership - ' . $selected_plan['name'];
$post_data['product_category'] = 'Subscription';
$post_data['product_profile']  = 'non-physical-goods';

# Custom Tracking Parameters
$post_data['value_a'] = (string)$user_id;
$post_data['value_b'] = (string)$plan_key;
$post_data['value_c'] = (string)$request_id;
$post_data['value_d'] = 'membership';

// Initiate Gateway Request
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $sslConfig['init_url']);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
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

    $errorMessage = $sslcommerzResponse['failedreason'] ?? 'পেমেন্ট গেটওয়ে সংযোগে সমস্যা হয়েছে।';
} else {
    $errorMessage = !empty($curl_error) ? $curl_error : 'Failed to connect with SSLCommerz API (HTTP ' . $code . ').';
}

$path_prefix = '../';
$page_title = 'মেম্বারশিপ পেমেন্ট ত্রুটি | অন্ত্যমিল';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-36 pb-24 bg-brand-light min-h-screen font-anek flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-6">
        <div class="bg-white rounded-[36px] p-8 text-center border border-gray-100 shadow-2xl">
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট গেটওয়ে সংযোগ ব্যর্থ</h1>
            <p class="text-gray-500 text-sm mb-6"><?php echo htmlspecialchars($errorMessage); ?></p>

            <div class="space-y-3">
                <a href="request.php?plan=<?php echo urlencode($plan_key); ?>" class="inline-block w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10">
                    পুনরায় চেষ্টা করুন
                </a>
                <a href="index.php" class="inline-block w-full bg-gray-100 text-gray-700 py-3 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    মেম্বারশিপ প্ল্যানসমূহ
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
