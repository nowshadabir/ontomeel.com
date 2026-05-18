<?php
$page_title = 'রিকোয়েস্ট সাবমিট হচ্ছে... | অন্ত্যমিল';
$path_prefix = '../';
require_once '../includes/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $member_id = $_SESSION['user_id'];
    $plan = trim($_POST['plan'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $trx_id = trim($_POST['trx_id'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);

    if (empty($plan) || empty($payment_method) || empty($trx_id) || $amount <= 0) {
        die("সব তথ্য সঠিকভাবে প্রদান করা আবশ্যক।");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO membership_requests (member_id, plan, payment_method, trx_id, amount, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$member_id, $plan, $payment_method, $trx_id, $amount]);

        include '../includes/header.php';
        ?>
        <div class="min-h-screen bg-brand-light flex items-center justify-center px-4 font-anek fixed inset-0 z-50">
            <div class="bg-white rounded-3xl p-10 md:p-16 shadow-2xl border border-gray-100 text-center max-w-lg mx-auto animate-slide-up flex flex-col items-center w-full">
                <!-- Animated Checkmark / Ring -->
                <div class="relative w-32 h-32 mb-8 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-brand-gold/20 animate-ping"></div>
                    <div class="absolute inset-2 rounded-full bg-brand-gold/40 animate-pulse"></div>
                    <div class="relative w-24 h-24 bg-brand-900 text-brand-gold rounded-full flex items-center justify-center shadow-xl border-2 border-brand-gold">
                        <svg class="w-12 h-12 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h2 class="text-3xl font-extrabold text-brand-900 mb-3">রিকোয়েস্ট সাবমিট হয়েছে!</h2>
                <p class="text-gray-500 text-base leading-relaxed mb-8">আপনার পেমেন্ট তথ্য ভেরিফাই করা হচ্ছে। অনুগ্রহ করে অপেক্ষা করুন...</p>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden mb-2">
                    <div class="bg-brand-gold h-full rounded-full animate-[progress_2s_ease-in-out_forwards]" style="width: 0%;"></div>
                </div>
            </div>
        </div>
        <style>
            @keyframes progress {
                0% { width: 0%; }
                100% { width: 100%; }
            }
        </style>
        <script>
            setTimeout(() => {
                window.location.href = 'index.php?request=success';
            }, 2000);
        </script>
        <?php
        include '../includes/footer.php';
        exit();
    } catch (PDOException $e) {
        error_log("Membership Request Error: " . $e->getMessage());
        die("একটি ত্রুটি ঘটেছে। অনুগ্রহ করে আবার চেষ্টা করুন।");
    }
} else {
    header("Location: index.php");
    exit();
}
?>
