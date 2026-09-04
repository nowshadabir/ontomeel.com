<?php
// sslcommerz/cancel.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

// Step 1: Handle POST request from SSLCommerz (PRG pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tran_id   = trim($_POST['tran_id'] ?? '');
    $order_raw = trim($_POST['value_a'] ?? '');
    $inv_raw   = trim($_POST['value_b'] ?? '');

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
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'Cancelled' WHERE id = ?");
        $updateStmt->execute([$order_id]);
    }

    $redirect_url = 'cancel.php?order_id=' . urlencode($order_id ?: $order_raw) . '&tran_id=' . urlencode($tran_id);
    header("Location: " . $redirect_url, true, 303);
    exit();
}

// Step 2: Render GET request
$order_param = trim($_GET['order_id'] ?? '');
$tran_id     = trim($_GET['tran_id'] ?? '');

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
$page_title = 'পেমেন্ট বাতিল | অন্ত্যমিল অনলাইন বুকশপ';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-28 pb-20 bg-brand-light min-h-screen font-anek">
    <div class="max-w-md w-full mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-[36px] p-6 sm:p-10 text-center border border-gray-100 shadow-2xl relative overflow-hidden">
            
            <!-- Amber Accent Top Bar -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-gray-400 to-gray-600"></div>

            <div class="w-20 h-20 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-2">পেমেন্ট বাতিল করা হয়েছে</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                আপনি পেমেন্ট প্রক্রিয়াটি বাতিল করেছেন। আপনার অ্যাকাউন্ট থেকে কোনো টাকা কর্তন করা হয়নি।
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    পুনরায় পেমেন্ট সম্পন্ন করুন
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
