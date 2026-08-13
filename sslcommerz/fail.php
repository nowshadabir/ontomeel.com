<?php
// sslcommerz/fail.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

$tran_id = $_POST['tran_id'] ?? '';
$order_id = isset($_POST['value_a']) ? (int)$_POST['value_a'] : 0;
$error = $_POST['error'] ?? 'পেমেন্ট সম্পন্ন করা সম্ভব হয়নি।';

if ($order_id > 0) {
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ?");
    $stmt->execute([$order_id]);
}

$page_title = 'পেমেন্ট ব্যর্থ | অন্ত্যমিল';
include __DIR__ . '/../includes/header.php';
?>

<div class="pt-32 pb-20 bg-brand-light min-h-screen font-anek flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-6">
        <div class="bg-white rounded-[40px] p-8 text-center border border-gray-100 shadow-xl">
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট ব্যর্থ হয়েছে</h1>
            <p class="text-gray-500 text-sm mb-6"><?php echo htmlspecialchars($error); ?></p>

            <a href="../checkout/index.php" class="inline-block w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all">
                আবার চেষ্টা করুন
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
