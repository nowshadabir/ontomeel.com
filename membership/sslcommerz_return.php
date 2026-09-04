<?php
// membership/sslcommerz_return.php
// Handles SSLCommerz returns for membership subscriptions with PRG pattern

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../sslcommerz/config.php';
require_once __DIR__ . '/../includes/notification_helper.php';

$plans = [
    'General' => [
        'name' => 'সাধারণ পাঠক',
        'price' => 500,
        'discount' => '১০%',
        'color' => 'from-brand-900 to-brand-800'
    ],
    'BookLover' => [
        'name' => 'নিয়মিত পাঠক',
        'price' => 1000,
        'discount' => '১৫%',
        'color' => 'from-brand-900 via-brand-800 to-brand-gold/30'
    ],
    'Collector' => [
        'name' => 'সাহিত্য অনুরাগী',
        'price' => 1500,
        'discount' => '২০%',
        'color' => 'from-brand-900 via-brand-gold/40 to-brand-900'
    ]
];

// --- STEP 1: Handle POST callback from SSLCommerz (PRG Pattern) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_post = trim($_POST['status'] ?? '');
    $val_id      = trim($_POST['val_id'] ?? '');
    $tran_id     = trim($_POST['tran_id'] ?? '');
    $amount_post = (float)($_POST['amount'] ?? 0);
    $user_id_raw = (int)($_POST['value_a'] ?? 0);
    $plan_key    = trim($_POST['value_b'] ?? 'General');
    $req_id_raw  = (int)($_POST['value_c'] ?? 0);
    $card_type   = trim($_POST['card_type'] ?? 'SSLCommerz');

    if (!isset($plans[$plan_key])) {
        $plan_key = 'General';
    }

    $is_success = false;
    $error_msg = 'পেমেন্ট সম্পন্ন করা সম্ভব হয়নি।';

    // Check gateway status
    if ($status_post === 'FAILED') {
        $error_msg = 'গেটওয়ে থেকে পেমেন্ট ব্যর্থ হয়েছে।';
        if ($req_id_raw > 0) {
            $pdo->prepare("UPDATE membership_requests SET status = 'Cancelled' WHERE id = ? AND status = 'Pending'")->execute([$req_id_raw]);
        }
    } elseif ($status_post === 'CANCELLED') {
        $error_msg = 'পেমেন্ট প্রক্রিয়াটি বাতিল করা হয়েছে।';
        if ($req_id_raw > 0) {
            $pdo->prepare("UPDATE membership_requests SET status = 'Cancelled' WHERE id = ? AND status = 'Pending'")->execute([$req_id_raw]);
        }
    } elseif (!empty($val_id)) {
        // Server-to-Server Validation Call
        $sslConfig = getSSLCommerzConfig($pdo);
        $val_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $val_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($handle);
        $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $result = json_decode($response, true);

        if ($http_code == 200 && $result && isset($result['status']) && ($result['status'] === 'VALID' || $result['status'] === 'VALIDATED')) {
            $verified_amount = (float)($result['amount'] ?? $amount_post);
            $verified_currency = strtoupper($result['currency'] ?? 'BDT');
            $expected_amount = (float)$plans[$plan_key]['price'];

            if ($user_id_raw <= 0 && !empty($result['value_a'])) {
                $user_id_raw = (int)$result['value_a'];
            }
            if (!empty($result['value_b']) && isset($plans[$result['value_b']])) {
                $plan_key = trim($result['value_b']);
            }
            if ($req_id_raw <= 0 && !empty($result['value_c'])) {
                $req_id_raw = (int)$result['value_c'];
            }

            if ($verified_currency === 'BDT' && $verified_amount >= ($expected_amount - 0.05)) {
                // Replay attack prevention: check if this val_id was already used
                $replayCheck = $pdo->prepare("SELECT id FROM transactions WHERE reference_id = ? LIMIT 1");
                $replayCheck->execute([$val_id]);
                if ($replayCheck->fetch()) {
                    error_log("MEMBERSHIP REPLAY ATTACK BLOCKED: val_id {$val_id} already consumed!");
                    $error_msg = 'এই ট্রানজেকশনটি ইতোমধ্যে প্রসেস করা হয়েছে।';
                } else {
                    try {
                        $pdo->beginTransaction();

                        // 1. Fetch current member details
                        $mStmt = $pdo->prepare("SELECT id, full_name, email, membership_id, membership_plan, plan_expire_date FROM members WHERE id = ? FOR UPDATE");
                        $mStmt->execute([$user_id_raw]);
                        $member = $mStmt->fetch(PDO::FETCH_ASSOC);

                        if ($member) {
                            // 2. Smart Expiry Date Extension
                            $new_expire_sql = "NOW() + INTERVAL 30 DAY";
                            if (!empty($member['plan_expire_date']) && strtotime($member['plan_expire_date']) > time()) {
                                $new_expire_sql = "plan_expire_date + INTERVAL 30 DAY";
                            }

                            // 3. Update Member Record
                            $updMember = $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = {$new_expire_sql} WHERE id = ?");
                            $updMember->execute([$plan_key, $user_id_raw]);

                            // 4. Update or Insert Membership Request
                            if ($req_id_raw > 0) {
                                $updReq = $pdo->prepare("UPDATE membership_requests SET status = 'Confirmed', payment_method = 'SSLCommerz', trx_id = ?, amount = ? WHERE id = ?");
                                $updReq->execute([$tran_id, $verified_amount, $req_id_raw]);
                            } else {
                                $insReq = $pdo->prepare("INSERT INTO membership_requests (member_id, plan, payment_method, trx_id, amount, status, created_at) VALUES (?, ?, 'SSLCommerz', ?, ?, 'Confirmed', NOW())");
                                $insReq->execute([$user_id_raw, $plan_key, $tran_id, $verified_amount]);
                                $req_id_raw = (int)$pdo->lastInsertId();
                            }

                            // 5. Record in Transactions table
                            $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, ?, 'Purchase', ?, ?, NOW())");
                            $insTrx->execute([
                                $user_id_raw,
                                $verified_amount,
                                "Membership Subscription: " . $plans[$plan_key]['name'],
                                $val_id ?: $tran_id
                            ]);

                            $pdo->commit();
                            $is_success = true;

                            // Update active session plan
                            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id_raw) {
                                $_SESSION['membership_plan'] = $plan_key;
                            }

                            // Retrieve updated expiration date for notification
                            $dateStmt = $pdo->prepare("SELECT plan_expire_date FROM members WHERE id = ?");
                            $dateStmt->execute([$user_id_raw]);
                            $updDate = $dateStmt->fetchColumn();

                            // 6. Send Celebratory Notification Email
                            if (!empty($member['email'])) {
                                try {
                                    $notif_data = [
                                        'name' => $member['full_name'],
                                        'membership_id' => $member['membership_id'],
                                        'plan_name' => $plans[$plan_key]['name'],
                                        'expire_date' => date('d M, Y', strtotime($updDate)),
                                        'amount' => $verified_amount,
                                        'payment_method' => $card_type ?: 'SSLCommerz'
                                    ];
                                    send_notification_instantly($member['email'], 'membership_activated', $notif_data);
                                } catch (Exception $e) {
                                    error_log("Membership Email Error: " . $e->getMessage());
                                }
                            }
                        } else {
                            $pdo->rollBack();
                            $error_msg = 'মেম্বার অ্যাকাউন্ট খুঁজে পাওয়া যায়নি।';
                        }
                    } catch (Exception $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        error_log("Membership Activation DB Error: " . $e->getMessage());
                        $error_msg = 'ডাটাবেজ আপডেটে ত্রুটি হয়েছে।';
                    }
                }
            } else {
                $error_msg = 'পেমেন্ট পরিমাণ বা মুদ্রায় অসঙ্গতি রয়েছে।';
            }
        } else {
            $error_msg = 'গেটওয়ে ভ্যালিডেশন ব্যর্থ হয়েছে।';
        }
    }

    // PRG Redirect (preserves user session!)
    $status_param = $is_success ? 'success' : 'failed';
    $redirect_url = 'sslcommerz_return.php?status=' . $status_param . '&plan=' . urlencode($plan_key) . '&tran_id=' . urlencode($tran_id) . '&req_id=' . urlencode($req_id_raw) . '&error=' . urlencode($error_msg);
    header("Location: " . $redirect_url, true, 303);
    exit();
}

// --- STEP 2: Render GET request with full aesthetics ---
$status_req = trim($_GET['status'] ?? 'failed');
$plan_key   = trim($_GET['plan'] ?? 'General');
$tran_id    = trim($_GET['tran_id'] ?? '');
$req_id     = (int)($_GET['req_id'] ?? 0);
$error_msg  = trim($_GET['error'] ?? 'পেমেন্ট সম্পন্ন করা সম্ভব হয়নি।');

if (!isset($plans[$plan_key])) {
    $plan_key = 'General';
}
$plan = $plans[$plan_key];

$member = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
}

$is_success = ($status_req === 'success');

$path_prefix = '../';
$page_title = $is_success ? 'মেম্বারশিপ সক্রিয় হয়েছে! | অন্ত্যমিল' : 'পেমেন্ট ব্যর্থ | অন্ত্যমিল';
include __DIR__ . '/../includes/header.php';
?>

<!-- Confetti Container -->
<?php if ($is_success): ?>
<div id="confetti-container" class="fixed inset-0 pointer-events-none z-50 overflow-hidden"></div>
<?php endif; ?>

<div class="pt-32 pb-24 bg-brand-light min-h-screen font-anek">
    <div class="max-w-xl mx-auto px-4 sm:px-6">

        <?php if ($is_success): ?>
        <!-- SUCCESS CARD -->
        <div class="bg-white rounded-[36px] p-6 sm:p-10 text-center border border-gray-100 shadow-2xl relative overflow-hidden animate-slide-up">
            <!-- Accent Top Bar -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-400 via-brand-gold to-yellow-500"></div>

            <!-- Pulsing Badge -->
            <div class="relative w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                <div class="absolute inset-0 bg-brand-gold/20 rounded-full animate-ping"></div>
                <div class="w-20 h-20 bg-gradient-to-tr from-brand-900 to-brand-800 text-brand-gold rounded-full flex items-center justify-center shadow-xl border-2 border-brand-gold">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.25em] mb-2 block">অভিনন্দন!</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-900 mb-3 tracking-tight">মেম্বারশিপ সফলভাবে সক্রিয় হয়েছে!</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-md mx-auto">
                অন্ত্যমিল পরিবারে আপনাকে স্বাগতম। আপনার মেম্বারশিপ অ্যাকাউন্টটি সক্রিয় করা হয়েছে এবং সকল বিশেষ সুবিধাসমূহ আনলক হয়েছে।
            </p>

            <!-- Digital Membership Card Preview -->
            <div class="bg-gradient-to-br from-brand-900 via-gray-900 to-brand-900 rounded-3xl p-6 sm:p-7 text-white text-left shadow-2xl relative overflow-hidden mb-8 border border-brand-gold/30">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-brand-gold/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-brand-gold/20 flex items-center justify-center border border-brand-gold/40">
                            <span class="text-brand-gold font-bold text-xs">অ</span>
                        </div>
                        <span class="font-bold text-sm tracking-wider text-brand-gold">অন্ত্যমিল মেম্বারশিপ কার্ড</span>
                    </div>
                    <span class="px-3 py-1 bg-brand-gold text-brand-900 rounded-full text-[11px] font-bold uppercase tracking-wider">
                        <?php echo htmlspecialchars($plan['name']); ?>
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">সদস্যের নাম</span>
                        <p class="text-lg font-bold text-white tracking-wide"><?php echo htmlspecialchars($member['full_name'] ?? 'Member'); ?></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-white/10 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">মেম্বার আইডি</span>
                            <span class="font-mono font-bold text-brand-gold text-sm">#<?php echo htmlspecialchars($member['membership_id'] ?? 'N/A'); ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">মেয়াদ উত্তীর্ণের তারিখ</span>
                            <span class="font-bold text-white text-xs">
                                <?php echo !empty($member['plan_expire_date']) ? date('d M, Y', strtotime($member['plan_expire_date'])) : '৩০ দিন'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt & Perks Box -->
            <div class="bg-gray-50 rounded-2xl p-5 mb-8 text-left border border-gray-100 text-xs space-y-3">
                <div class="flex justify-between items-center pb-2.5 border-b border-gray-200">
                    <span class="text-gray-500 font-medium">পরিশোধিত ফি:</span>
                    <span class="font-bold text-brand-900 font-mono text-sm">৳<?php echo number_format($plan['price'], 2); ?></span>
                </div>
                <?php if (!empty($tran_id)): ?>
                <div class="flex justify-between items-center pb-2.5 border-b border-gray-200">
                    <span class="text-gray-500 font-medium">ট্রানজেকশন নম্বর (TrxID):</span>
                    <span class="font-mono font-bold text-brand-900 select-all"><?php echo htmlspecialchars($tran_id); ?></span>
                </div>
                <?php endif; ?>
                <div class="pt-1">
                    <span class="text-gray-500 font-bold block mb-1.5 uppercase tracking-wider text-[10px]">আনলক হওয়া সুবিধাসমূহ:</span>
                    <ul class="space-y-1.5 text-gray-700">
                        <li class="flex items-center gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>কমিউনিটি লাইব্রেরি থেকে বিনামূল্যে বই ধার নেওয়ার অধিকার</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>বই ক্রয়ে <strong><?php echo $plan['discount']; ?></strong> অতিরিক্ত মেম্বার ডিসকাউন্ট</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>এক্সক্লুসিভ রিডার্স লাউঞ্জ ও সেবা সুবিধা</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="../dashboard/index.php" class="inline-flex items-center justify-center gap-2 w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    আমার ড্যাশবোর্ডে যান
                </a>
                <a href="../books.php" class="inline-flex items-center justify-center gap-2 w-full bg-gray-100 text-gray-700 py-3.5 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    লাইব্রেরির বইসমূহ ব্রাউজ করুন
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- FAILED CARD -->
        <div class="bg-white rounded-[36px] p-6 sm:p-10 text-center border border-gray-100 shadow-2xl relative overflow-hidden animate-slide-up">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-red-500 to-rose-600"></div>

            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-2">মেম্বারশিপ পেমেন্ট ব্যর্থ</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-md mx-auto">
                <?php echo htmlspecialchars($error_msg); ?>
            </p>

            <div class="space-y-3">
                <a href="request.php?plan=<?php echo urlencode($plan_key); ?>" class="inline-block w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10">
                    পুনরায় পেমেন্ট চেষ্টা করুন
                </a>
                <a href="index.php" class="inline-block w-full bg-gray-100 text-gray-700 py-3.5 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    মেম্বারশিপ প্ল্যানে ফিরে যান
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if ($is_success): ?>
<script>
    // Trigger Confetti Animation
    function triggerConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        const colors = ['#cda873', '#0a0a0a', '#10B981', '#34D399', '#FBBF24'];
        for (let i = 0; i < 45; i++) {
            const el = document.createElement('div');
            el.className = 'confetti-piece';
            el.style.position = 'absolute';
            el.style.left = Math.random() * 100 + 'vw';
            el.style.top = '-20px';
            el.style.width = Math.random() * 8 + 6 + 'px';
            el.style.height = Math.random() * 10 + 6 + 'px';
            el.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            el.style.borderRadius = '2px';
            el.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
            el.style.opacity = Math.random() * 0.7 + 0.3;
            el.style.animation = 'fall ' + (Math.random() * 2 + 2) + 's ease-in-out forwards';
            el.style.animationDelay = (Math.random() * 1.5) + 's';
            container.appendChild(el);
        }
    }
    document.addEventListener('DOMContentLoaded', triggerConfetti);
</script>

<style>
@keyframes fall {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
}
</style>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
