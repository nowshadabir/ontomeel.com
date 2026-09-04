<?php
// sslcommerz/success.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

// --- Step 1: Handle POST request from SSLCommerz (PRG: Post-Redirect-Get Pattern) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $val_id    = trim($_POST['val_id'] ?? '');
    $tran_id   = trim($_POST['tran_id'] ?? '');
    $amount    = (float)($_POST['amount'] ?? 0);
    $order_raw = trim($_POST['value_a'] ?? '');
    $inv_raw   = trim($_POST['value_b'] ?? '');
    $card_type = trim($_POST['card_type'] ?? '');

    $sslConfig = getSSLCommerzConfig($pdo);

    $is_valid = false;
    $orderData = null;
    $order_id = 0;
    $is_risk = false;

    if (!empty($val_id) && !empty($sslConfig['store_id']) && !empty($sslConfig['store_passwd'])) {
        $validation_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $validation_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $result = json_decode($response, true);

        if ($code == 200 && $result && isset($result['status']) && ($result['status'] === 'VALID' || $result['status'] === 'VALIDATED')) {
            $verified_amount = (float)($result['amount'] ?? $amount);
            $verified_currency = strtoupper($result['currency'] ?? 'BDT');

            if (empty($tran_id) && !empty($result['tran_id'])) {
                $tran_id = $result['tran_id'];
            }
            if (empty($order_raw) && !empty($result['value_a'])) {
                $order_raw = trim($result['value_a']);
            }
            if (empty($inv_raw) && !empty($result['value_b'])) {
                $inv_raw = trim($result['value_b']);
            }
            if (empty($card_type) && !empty($result['card_type'])) {
                $card_type = $result['card_type'];
            }

            if (isset($result['risk_level']) && (string)$result['risk_level'] === '1') {
                $is_risk = true;
            }

            // Order lookup: by ID
            if (!empty($order_raw) && is_numeric($order_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([(int)$order_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by invoice number
            if (!$orderData && !empty($inv_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
                $stmt->execute([$inv_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by value_a as invoice
            if (!$orderData && !empty($order_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
                $stmt->execute([$order_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by transaction ID
            if (!$orderData && !empty($tran_id)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$tran_id]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Amount validation
            if ($orderData && $verified_currency === 'BDT') {
                $expected_amount = (float)$orderData['total_amount'];
                if ($verified_amount >= ($expected_amount - 0.05)) {
                    $is_valid = true;
                    $order_id = (int)$orderData['id'];

                    // Replay attack prevention: check if this val_id was already used on another order
                    if (!empty($val_id)) {
                        $replayCheck = $pdo->prepare("SELECT id FROM orders WHERE payment_id = ? AND id != ? LIMIT 1");
                        $replayCheck->execute([$val_id, $order_id]);
                        if ($replayCheck->fetch()) {
                            error_log("SSLCommerz REPLAY ATTACK BLOCKED: val_id {$val_id} already consumed on another order!");
                            $is_valid = false;
                        }
                    }

                    // Transaction ID match verification
                    if ($is_valid && !empty($orderData['trx_id']) && !empty($result['tran_id'])) {
                        if ($orderData['trx_id'] !== $result['tran_id']) {
                            error_log("SSLCommerz TRANSACTION MISMATCH: Order TrxID {$orderData['trx_id']} vs Gateway TrxID {$result['tran_id']}");
                            $is_valid = false;
                        }
                    }
                } else {
                    error_log("SSLCommerz amount mismatch for Order #{$orderData['id']}: Paid {$verified_amount}, Expected {$expected_amount}");
                }
            }
        }
    }

    if ($is_valid && $orderData && $order_id > 0) {
        $wasAlreadyPaid = ($orderData['payment_status'] === 'Paid');
        $new_order_status = $is_risk ? 'On Hold' : 'Processing';
        $notes = $orderData['notes'] ?? '';
        if ($is_risk && strpos($notes, 'Risk Level 1') === false) {
            $notes = trim($notes . " | [Risk Level 1: Verify Customer]");
        }

        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = ?, payment_id = ?, trx_id = ?, payment_method = 'SSLCommerz', notes = ? WHERE id = ?");
        $updateStmt->execute([$new_order_status, $val_id, $tran_id, $notes, $order_id]);

        if (!$wasAlreadyPaid && !empty($orderData['guest_email'])) {
            try {
                require_once __DIR__ . '/../includes/notification_helper.php';
                $notif_data = [
                    'name' => $orderData['guest_name'],
                    'invoice_no' => $orderData['invoice_no'],
                    'amount' => $verified_amount ?: $orderData['total_amount'],
                    'address' => $orderData['shipping_address']
                ];
                send_notification($orderData['guest_email'], 'order_placed', $notif_data);
            } catch (Exception $e) {
                error_log("SSLCommerz success email notification error: " . $e->getMessage());
            }
        }
    }

    // PRG Redirect to GET request on same-site URL (preserves customer session cookie!)
    $redirect_url = 'success.php?order_id=' . urlencode($order_id ?: $order_raw) . '&tran_id=' . urlencode($tran_id) . '&val_id=' . urlencode($val_id) . '&card_type=' . urlencode($card_type) . ($is_valid ? '&status=success' : '&status=pending');
    header("Location: " . $redirect_url, true, 303);
    exit();
}

// --- Step 2: Render GET request with full styles and preserved session ---
$order_param = trim($_GET['order_id'] ?? '');
$tran_id     = trim($_GET['tran_id'] ?? '');
$val_id      = trim($_GET['val_id'] ?? '');
$card_type   = trim($_GET['card_type'] ?? '');
$status_req  = trim($_GET['status'] ?? '');

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

if (!$orderData && !empty($tran_id)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$tran_id]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch order items if order was found
$order_items = [];
if ($orderData) {
    $stmt = $pdo->prepare("SELECT oi.*, b.title as book_title, b.author as book_author, b.cover_image FROM order_items oi LEFT JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?");
    $stmt->execute([$orderData['id']]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$is_paid = ($orderData && $orderData['payment_status'] === 'Paid');
$is_risk = ($orderData && strpos($orderData['notes'] ?? '', 'Risk Level 1') !== false);

// IDOR / Access Authorization Check
$is_authorized = true;
if ($orderData) {
    if (!empty($orderData['member_id'])) {
        $logged_user = $_SESSION['user_id'] ?? null;
        if ($logged_user != $orderData['member_id']) {
            if (empty($tran_id) || $orderData['trx_id'] !== $tran_id) {
                $is_authorized = false;
            }
        }
    }
}

// Correct path prefix for assets, tailwind-config, style.css, script.js
$path_prefix = '../';
$page_title = $is_paid ? 'পেমেন্ট সফল | অন্ত্যমিল অনলাইন বুকশপ' : 'পেমেন্ট স্ট্যাটাস | অন্ত্যমিল';

include __DIR__ . '/../includes/header.php';
?>

<!-- Confetti Canvas Container -->
<?php if ($is_paid && $is_authorized): ?>
<div id="confetti-container" class="fixed inset-0 pointer-events-none z-50 overflow-hidden"></div>
<?php endif; ?>

<div class="pt-28 pb-20 bg-brand-light min-h-screen font-anek">
    <div class="max-w-xl mx-auto px-4 sm:px-6">
        
        <?php if (!$is_authorized): ?>
        <!-- Unauthorized Access Card -->
        <div class="bg-white rounded-[36px] p-8 text-center border border-gray-100 shadow-xl">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3.5a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-brand-900 mb-2">অননুমোদিত অনুরোধ</h2>
            <p class="text-gray-500 text-sm mb-6">এই অর্ডারের বিস্তারিত তথ্য দেখার জন্য অনুগ্রহ করে আপনার অ্যাকাউন্টে লগইন করুন।</p>
            <div class="space-y-3">
                <a href="../login/index.php" class="inline-block w-full bg-brand-900 text-white py-3.5 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all">লগইন পেজে যান</a>
                <a href="../index.php" class="inline-block w-full bg-gray-100 text-gray-700 py-3 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">হোম পেজ</a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Main Card -->
        <div class="bg-white rounded-[36px] p-6 sm:p-10 text-center border border-gray-100 shadow-2xl relative overflow-hidden">
            
            <!-- Top Gradient Accent Bar -->
            <div class="absolute top-0 left-0 right-0 h-2 <?php echo $is_paid ? 'bg-gradient-to-r from-emerald-400 via-green-500 to-emerald-600' : 'bg-gradient-to-r from-amber-400 to-yellow-500'; ?>"></div>

            <?php if ($is_paid): ?>
                <!-- Animated Success Badge -->
                <div class="relative w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                    <div class="absolute inset-0 bg-green-100 rounded-full animate-ping opacity-25"></div>
                    <div class="w-20 h-20 bg-gradient-to-tr from-green-500 to-emerald-400 text-white rounded-full flex items-center justify-center shadow-lg shadow-green-500/30">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-2">পেমেন্ট সফল হয়েছে!</h1>
                <p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-md mx-auto">
                    <?php if ($is_risk): ?>
                        আপনার পেমেন্ট রেকর্ড করা হয়েছে। সুরক্ষাজনিত চূড়ান্ত যাচাইয়ের পর খুব দ্রুত আপনার অর্ডারটি ডেলিভারির জন্য প্রস্তুত করা হবে।
                    <?php else: ?>
                        ধন্যবাদ! আপনার পেমেন্টটি সফলভাবে সম্পন্ন হয়েছে। শীঘ্রই আমাদের টিম আপনার বইগুলো পাঠানো শুরু করবে।
                    <?php endif; ?>
                </p>

                <!-- Order Receipt Box -->
                <div class="bg-gray-50 rounded-3xl p-5 sm:p-6 mb-8 text-left border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200/80">
                        <div>
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">ইনভয়েস নম্বর</span>
                            <span class="font-bold text-brand-900 font-mono text-base"><?php echo htmlspecialchars($orderData['invoice_no'] ?? 'N/A'); ?></span>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">পরিশোধিত</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div>
                            <span class="text-gray-400 block mb-0.5">অর্ডার আইডি:</span>
                            <span class="font-bold text-brand-900 font-mono">#<?php echo htmlspecialchars($orderData['id'] ?? ''); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-0.5">পেমেন্ট মাধ্যম:</span>
                            <span class="font-bold text-brand-900"><?php echo !empty($card_type) ? htmlspecialchars($card_type) : 'SSLCommerz'; ?></span>
                        </div>
                    </div>

                    <?php if (!empty($orderData['trx_id']) || !empty($tran_id)): ?>
                    <div class="bg-white p-3 rounded-2xl border border-gray-200/60 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-2">
                            <span class="text-gray-400 text-[10px] block">ট্রানজেকশন নম্বর (TrxID)</span>
                            <span class="font-mono text-brand-900 font-semibold truncate block select-all text-[11px]">
                                <?php echo htmlspecialchars($orderData['trx_id'] ?: $tran_id); ?>
                            </span>
                        </div>
                        <button onclick="copyTrxId(this, '<?php echo htmlspecialchars($orderData['trx_id'] ?: $tran_id); ?>')" class="px-2.5 py-1 bg-gray-100 hover:bg-brand-gold hover:text-brand-900 text-gray-600 rounded-lg text-[10px] font-bold transition-all shrink-0">
                            কপি
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Ordered Items List -->
                    <?php if (!empty($order_items)): ?>
                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">অর্ডারকৃত বইসমূহ:</span>
                        <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                            <?php foreach ($order_items as $item): ?>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-gray-100 last:border-0">
                                <span class="text-brand-900 font-medium truncate max-w-[220px]">
                                    <?php echo htmlspecialchars($item['book_title'] ?? 'বই'); ?> (×<?php echo $item['quantity']; ?>)
                                </span>
                                <span class="font-bold text-brand-900 font-mono">৳<?php echo number_format((float)$item['total_price'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Total Amount Paid -->
                    <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                        <span class="font-bold text-gray-700 text-sm">সর্বমোট পরিশোধ:</span>
                        <span class="text-xl font-bold text-brand-gold font-mono">
                            ৳<?php echo number_format((float)($orderData['total_amount'] ?? 0), 2); ?>
                        </span>
                    </div>
                </div>

            <?php else: ?>
                <!-- Pending/Verifying Badge -->
                <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-brand-900 mb-2">পেমেন্ট যাচাই পেন্ডিং</h1>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    আপনার পেমেন্টটি গ্রহণ করা হয়েছে এবং গেটওয়ে থেকে চূড়ান্ত নিশ্চিতকরণ প্রক্রিয়াধীন রয়েছে। খুব শীঘ্রই আপনার ড্যাশবোর্ডে স্ট্যাটাস আপডেট হবে।
                </p>

                <?php if ($orderData): ?>
                <div class="bg-gray-50 p-4 rounded-2xl mb-6 text-xs text-gray-500 text-left">
                    <div class="flex justify-between mb-1">
                        <span>অর্ডার নম্বর:</span>
                        <strong class="font-mono text-brand-900">#<?php echo htmlspecialchars($orderData['id']); ?></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>ইনভয়েস নম্বর:</span>
                        <strong class="font-mono text-brand-900"><?php echo htmlspecialchars($orderData['invoice_no']); ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="../dashboard/index.php" class="inline-flex items-center justify-center gap-2 w-full bg-brand-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    আমার অর্ডার ও ড্যাশবোর্ড দেখুন
                </a>

                <div class="flex gap-3">
                    <button onclick="window.print()" class="flex-1 py-3.5 bg-gray-100 text-gray-700 rounded-2xl font-bold text-xs hover:bg-gray-200 transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        রশিদ প্রিন্ট করুন
                    </button>
                    <a href="../index.php" class="flex-1 py-3.5 bg-gray-100 text-gray-700 rounded-2xl font-bold text-xs hover:bg-gray-200 transition-all flex items-center justify-center">
                        হোম পেজে ফিরে যান
                    </a>
                </div>
            </div>

        </div>
        <?php endif; ?>

    </div>
</div>

<script>
    // Ensure Cart is cleared upon arriving at payment success
    try {
        localStorage.removeItem('antyam_cart');
        localStorage.removeItem('antyam_borrow_cart');
    } catch(e) {}

    // Confetti Animation on Success
    <?php if ($is_paid): ?>
    function triggerConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        const colors = ['#cda873', '#0a0a0a', '#10B981', '#34D399', '#FBBF24'];
        for (let i = 0; i < 40; i++) {
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
    <?php endif; ?>

    function copyTrxId(btn, text) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerText;
            btn.innerText = 'কপি হয়েছে!';
            btn.classList.add('bg-green-100', 'text-green-700');
            setTimeout(() => {
                btn.innerText = orig;
                btn.classList.remove('bg-green-100', 'text-green-700');
            }, 2000);
        });
    }
</script>

<style>
@keyframes fall {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
}
@media print {
    header, footer, #confetti-container, button, a { display: none !important; }
    body, .pt-28 { padding-top: 0 !important; background: white !important; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
