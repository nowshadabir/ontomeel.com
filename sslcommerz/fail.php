<?php
// sslcommerz/fail.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

// Step 1: Handle POST request from SSLCommerz (PRG pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tran_id   = trim($_POST['tran_id'] ?? '');
    $order_raw = trim($_POST['value_a'] ?? '');
    $inv_raw   = trim($_POST['value_b'] ?? '');
    $error     = trim($_POST['error'] ?? 'পেমেন্ট সম্পন্ন করা সম্ভব হয়নি।');

    $order = null;
    if (!empty($order_raw)) {
        if (is_numeric($order_raw)) {
            $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE id = ? OR invoice_no = ?");
            $stmt->execute([(int)$order_raw, $order_raw]);
        } else {
            $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE invoice_no = ?");
            $stmt->execute([$order_raw]);
        }
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$order && !empty($inv_raw)) {
        $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE invoice_no = ?");
        $stmt->execute([$inv_raw]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$order && !empty($tran_id)) {
        $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$tran_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $order_id = $order ? (int)$order['id'] : 0;
    if ($order && $order['payment_status'] !== 'Paid') {
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ?");
        $updateStmt->execute([$order_id]);
    }

    $redirect_url = 'fail.php?order_id=' . urlencode($order_id ?: $order_raw) . '&tran_id=' . urlencode($tran_id) . '&error=' . urlencode($error);
    header("Location: " . $redirect_url, true, 303);
    exit();
}

// Step 2: Render GET request
$order_param = trim($_GET['order_id'] ?? '');
$tran_id     = trim($_GET['tran_id'] ?? '');
$error_msg   = trim($_GET['error'] ?? 'পেমেন্ট সম্পন্ন করা সম্ভব হয়নি। গেটওয়ে থেকে ত্রুটি বার্তা পাওয়া গেছে।');

$orderData = null;
if (!empty($order_param)) {
    if (is_numeric($order_param)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? OR invoice_no = ?");
        $stmt->execute([(int)$order_param, $order_param]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
        $stmt->execute([$order_param]);
    }
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// IDOR / Access Authorization Check
$is_authorized = true;
if ($orderData && !empty($orderData['member_id'])) {
    $logged_user = $_SESSION['user_id'] ?? null;
    if ($logged_user != $orderData['member_id']) {
        if (empty($tran_id) || $orderData['trx_id'] !== $tran_id) {
            $is_authorized = false;
        }
    }
}
if (!$is_authorized) {
    $orderData = null;
}

$path_prefix = '../';
$page_title = 'পেমেন্ট ব্যর্থ | অন্ত্যমিল অনলাইন বুকশপ';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-28 pb-20 bg-brand-light min-h-screen font-anek">
    <div class="max-w-md w-full mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-[36px] p-6 sm:p-10 text-center border border-gray-100 shadow-2xl relative overflow-hidden">
            
            <!-- Red Accent Top Bar -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-red-500 to-rose-600"></div>

            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md shadow-red-500/10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-2">পেমেন্ট ব্যর্থ হয়েছে</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                <?php echo htmlspecialchars($error_msg); ?>
            </p>

            <?php if ($orderData): ?>
            <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-left text-xs text-gray-500 border border-gray-100 space-y-1.5">
                <div class="flex justify-between">
                    <span>অর্ডার নম্বর:</span>
                    <span class="font-bold text-brand-900 font-mono">#<?php echo htmlspecialchars($orderData['id']); ?></span>
                </div>
                <?php if (!empty($orderData['invoice_no'])): ?>
                <div class="flex justify-between">
                    <span>ইনভয়েস নম্বর:</span>
                    <span class="font-bold text-brand-900 font-mono"><?php echo htmlspecialchars($orderData['invoice_no']); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <span>পরিমাণ:</span>
                    <span class="font-bold text-brand-gold font-mono">৳<?php echo number_format((float)$orderData['total_amount'], 2); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="space-y-3">
                <?php if ($orderData): ?>
                <a href="initiate.php?order_id=<?php echo urlencode($orderData['id']); ?>" class="inline-flex items-center justify-center gap-2 w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    আবার পেমেন্ট চেষ্টা করুন
                </a>
                <?php endif; ?>
                <a href="../checkout/index.php" class="inline-block w-full bg-gray-100 text-gray-700 py-3.5 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    চেকআউট পেজে ফিরে যান
                </a>
                <a href="../index.php" class="inline-block w-full text-gray-400 hover:text-brand-900 py-2 text-xs font-semibold transition-all">
                    হোম পেজে যান
                </a>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
