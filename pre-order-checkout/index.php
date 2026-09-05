<?php
require_once '../includes/db_connect.php';
$page_title = 'প্রি-অর্ডার চেকআউট | অন্ত্যমিল অনলাইন বুকশপ';
$path_prefix = '../';
$is_checkout = true;

include '../includes/header.php';

// User data if logged in
$user_data = ['full_name' => '', 'phone' => '', 'address' => '', 'email' => ''];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();
    if ($member) {
        $user_data = $member;
    }
}

// Fetch Pre-order Item Details
$pre_order_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE id = ?");
$stmt->execute([$pre_order_id]);
$pre_order = $stmt->fetch();

if (!$pre_order) {
    echo "<div class='text-center py-28 text-xl font-anek font-bold text-brand-900'>প্রি-অর্ডার বইটি খুঁজে পাওয়া যায়নি। <br><a href='../pre-booking/index.php' class='mt-4 inline-block text-sm text-brand-gold hover:underline'>প্রি-বুকিং সংগ্রহে ফিরে যান</a></div>";
    include '../includes/footer.php';
    exit();
}

if ($pre_order['status'] !== 'Open') {
    echo "<div class='text-center py-28 text-xl font-anek font-bold text-brand-900'>এই বইটির প্রি-বুকিং বর্তমানে বন্ধ রয়েছে। <br><a href='../pre-booking/index.php' class='mt-4 inline-block text-sm text-brand-gold hover:underline'>অন্যান্য বই দেখুন</a></div>";
    include '../includes/footer.php';
    exit();
}

// Helper to get settings
if (!function_exists('getSetting')) {
    function getSetting($pdo, $key, $default = '')
    {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

$inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
$outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);

$price = $pre_order['discount_price'] > 0 ? (int)$pre_order['discount_price'] : (int)$pre_order['price'];
$is_free_delivery = (isset($pre_order['free_delivery']) && (int)$pre_order['free_delivery'] === 1);
$delivery_charge = $is_free_delivery ? 0 : $inside_charge;
$total_amount = $price + $delivery_charge;
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 md:py-12 font-anek">
    <div class="bg-white rounded-[32px] md:rounded-[40px] shadow-xl shadow-brand-900/5 p-6 sm:p-8 md:p-12 overflow-hidden relative border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-6 mb-8">
            <div>
                <span class="text-[10px] font-bold text-brand-gold uppercase tracking-[0.25em] block mb-1">Pre-Order Checkout</span>
                <h1 class="text-2xl md:text-3xl font-bold text-brand-900">প্রি-বুকিং চেকআউট</h1>
            </div>
            <a href="../pre-booking/index.php" class="text-xs font-bold text-gray-400 hover:text-brand-900 transition-colors flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                প্রি-বুকিং পেজে ফিরে যান
            </a>
        </div>

        <!-- Pre-order Item Summary -->
        <div class="flex flex-col sm:flex-row items-center gap-5 sm:gap-6 p-5 sm:p-6 bg-brand-light/30 rounded-3xl mb-10 border border-brand-gold/15 text-center sm:text-left">
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="w-20 h-28 bg-gray-100 rounded-2xl overflow-hidden shadow-md border border-white">
                    <img src="<?php echo htmlspecialchars(strpos($pre_order['cover_image'], 'http') !== false ? $pre_order['cover_image'] : '../assets/img/preorders/' . trim($pre_order['cover_image'])); ?>"
                        onerror="this.src='../assets/img/book-placeholder.jpg'"
                        alt="<?php echo htmlspecialchars($pre_order['title']); ?>" class="w-full h-full object-cover">
                </div>
                <?php if (!empty($pre_order['second_cover_image'])): ?>
                    <div class="w-16 h-24 bg-gray-100 rounded-xl overflow-hidden shadow-md border-2 border-white -ml-6 z-10">
                        <img src="<?php echo htmlspecialchars(strpos($pre_order['second_cover_image'], 'http') !== false ? $pre_order['second_cover_image'] : '../assets/img/preorders/' . trim($pre_order['second_cover_image'])); ?>"
                            onerror="this.src='../assets/img/book-placeholder.jpg'"
                            alt="Combo Book" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 bg-brand-gold/20 text-brand-900 font-bold text-[10px] rounded-full uppercase tracking-wider mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-gold animate-pulse"></span>
                    <?php echo !empty($pre_order['second_title']) ? 'কম্বো প্রি-অর্ডার' : 'প্রি-অর্ডার অফার'; ?>
                </div>
                <h3 class="font-bold text-xl md:text-2xl text-brand-900 leading-tight">
                    <?php echo htmlspecialchars($pre_order['title']); ?>
                    <?php if (!empty($pre_order['second_title'])): ?>
                        <span class="text-base text-gray-500 font-normal">এবং <?php echo htmlspecialchars($pre_order['second_title']); ?></span>
                    <?php endif; ?>
                </h3>
                <p class="text-xs text-gray-500 mt-1">লেখক: <?php echo htmlspecialchars($pre_order['author']); ?></p>
            </div>
            <div class="sm:text-right w-full sm:w-auto pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-100 flex flex-col items-center sm:items-end">
                <p class="text-xl md:text-2xl font-extrabold text-brand-900 font-mono">৳<?php echo number_format($price); ?></p>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    <?php if ($is_free_delivery): ?>
                        <span class="text-green-600 font-bold">ফ্রি হোম ডেলিভারি</span>
                    <?php else: ?>
                        + ৳<span id="display-delivery"><?php echo $delivery_charge; ?></span> ডেলিভারি
                    <?php endif; ?>
                </p>
                <div class="mt-2 text-xs md:text-sm font-bold text-brand-gold bg-brand-900 px-4 py-1.5 rounded-full inline-block whitespace-nowrap shadow-sm">
                    সর্বমোট: ৳<span id="display-total"><?php echo number_format($total_amount); ?></span>
                </div>
            </div>
        </div>

        <!-- Form: Delivery Details -->
        <div class="space-y-8">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-brand-900 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center text-xs font-bold font-mono">১</span>
                    ডেলিভারি ঠিকানা ও গ্রাহকের তথ্য
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50/70 p-6 sm:p-8 rounded-[28px] border border-gray-100">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-2">আপনার পুরো নাম *</label>
                        <input type="text" id="po-name" required
                            value="<?php echo htmlspecialchars($user_data['full_name']); ?>"
                            placeholder="আপনার নাম লিখুন"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-5 py-3.5 text-brand-900 font-semibold focus:outline-none focus:border-brand-gold focus:ring-2 focus:ring-brand-gold/20 transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-2">মোবাইল নম্বর *</label>
                        <input type="tel" id="po-phone" required
                            value="<?php echo htmlspecialchars($user_data['phone']); ?>"
                            placeholder="০১৭১xxxxxxx"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-5 py-3.5 text-brand-900 font-semibold font-mono tracking-wider focus:outline-none focus:border-brand-gold focus:ring-2 focus:ring-brand-gold/20 transition-all text-sm">
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-2">ইমেইল এড্রেস (পেমেন্ট রসিদ ও আপডেটের জন্য) *</label>
                        <input type="email" id="po-email" required
                            value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                            placeholder="yourname@gmail.com"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-5 py-3.5 text-brand-900 font-semibold focus:outline-none focus:border-brand-gold focus:ring-2 focus:ring-brand-gold/20 transition-all text-sm">
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-2">ডেলিভারি এরিয়া *</label>
                        <?php if ($is_free_delivery): ?>
                            <div class="p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-green-500 text-white rounded-full flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-brand-900 text-sm">ফ্রি ডেলিভারি অফার সক্রিয়</p>
                                        <p class="text-xs text-green-700">সারা বাংলাদেশে কোনো অতিরিক্ত ডেলিভারি চার্জ নেই।</p>
                                    </div>
                                </div>
                                <input type="hidden" name="location" value="inside">
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="location" value="inside" checked onchange="updateDelivery(this.value)" class="hidden peer">
                                    <div class="p-4 bg-white border border-gray-200 rounded-2xl text-center peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 peer-checked:ring-2 peer-checked:ring-brand-gold/20 transition-all h-full flex flex-col justify-center">
                                        <p class="text-sm font-bold text-brand-900">কক্সবাজার শহর</p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">চার্জ: ৳<?php echo $inside_charge; ?></p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="location" value="outside" onchange="updateDelivery(this.value)" class="hidden peer">
                                    <div class="p-4 bg-white border border-gray-200 rounded-2xl text-center peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 peer-checked:ring-2 peer-checked:ring-brand-gold/20 transition-all h-full flex flex-col justify-center">
                                        <p class="text-sm font-bold text-brand-900">আউটসাইড কক্সবাজার (সারাদেশ)</p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">চার্জ: ৳<?php echo $outside_charge; ?></p>
                                    </div>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-2">সম্পূর্ণ ডেলিভারি ঠিকানা *</label>
                        <textarea id="po-address" rows="3" required
                            placeholder="বাসা/হোল্ডিং নং, রোড, এলাকা, থানা ও জেলা..."
                            class="w-full bg-white border border-gray-200 rounded-2xl px-5 py-3.5 focus:outline-none focus:border-brand-gold focus:ring-2 focus:ring-brand-gold/20 transition-all text-brand-900 text-sm leading-relaxed"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: Payment Gateway Card (SSLCommerz) -->
            <div>
                <h2 class="text-lg md:text-xl font-bold text-brand-900 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center text-xs font-bold font-mono">২</span>
                    পেমেন্ট মেথড
                </h2>

                <div class="bg-gradient-to-br from-amber-50/50 via-white to-orange-50/40 rounded-[28px] border-2 border-brand-gold/40 p-6 sm:p-8 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-brand-gold/15">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wider">Automated & Instant</span>
                                <span class="text-xs text-gray-400">• SSL 256-bit Secure</span>
                            </div>
                            <h3 class="text-xl font-bold text-brand-900">নিরাপদ অনলাইন পেমেন্ট</h3>
                            <p class="text-xs text-gray-600 leading-relaxed max-w-lg">
                                SSLCommerz গেটওয়ের মাধ্যমে আপনার পছন্দমতো <strong>বিকাশ, নগদ, রকেট, ডেবিট/ক্রেডিট কার্ড</strong> অথবা <strong>ইন্টারনেট ব্যাংকিং</strong> দিয়ে তাৎক্ষণিকভাবে পেমেন্ট সম্পন্ন করতে পারবেন। কোনো ম্যানুয়াল স্ক্রিনশট বা TrxID দেওয়ার প্রয়োজন নেই।
                            </p>
                        </div>
                        <div class="flex items-center gap-3 self-center md:self-auto bg-white px-4 py-2 rounded-2xl border border-gray-200/80 shadow-xs shrink-0">
                            <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">সুরক্ষিত গেটওয়ে</span>
                                <span class="text-xs font-bold text-brand-900">SSLCommerz Live</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Partners Badges -->
                    <div class="pt-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">সমর্থিত পেমেন্ট মাধ্যমসমূহ:</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="px-3.5 py-2 bg-white rounded-xl border border-gray-200/80 flex items-center gap-2 shadow-xs">
                                <img src="../assets/img/bkash-logo.jpg" alt="bKash" class="h-5 w-auto object-contain rounded" onerror="this.src='https://raw.githubusercontent.com/bikashpoudel/bkash-logo/master/bkash_logo.webp'">
                                <span class="text-xs font-bold text-[#D12053]">বিকাশ</span>
                            </div>
                            <div class="px-3.5 py-2 bg-white rounded-xl border border-gray-200/80 flex items-center gap-2 shadow-xs">
                                <img src="../assets/img/nagad-logo.jpg" alt="Nagad" class="h-5 w-auto object-contain rounded" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'">
                                <span class="text-xs font-bold text-[#EF1F23]">নগদ</span>
                            </div>
                            <div class="px-3.5 py-2 bg-white rounded-xl border border-gray-200/80 flex items-center gap-2 shadow-xs">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                                <span class="text-xs font-bold text-gray-700">ভিসা / মাস্টারকার্ড</span>
                            </div>
                            <div class="px-3.5 py-2 bg-white rounded-xl border border-gray-200/80 flex items-center gap-2 shadow-xs">
                                <span class="text-xs font-bold text-purple-700">রকেট / অন্যান্য ব্যাংক</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Consent -->
            <div class="pt-2 flex items-start gap-3">
                <input type="checkbox" id="po-terms-agree" class="mt-1 w-4 h-4 text-brand-gold rounded border-gray-300 focus:ring-brand-gold cursor-pointer" required>
                <label for="po-terms-agree" class="text-xs text-gray-600 leading-relaxed cursor-pointer select-none">
                    আমি অন্ত্যমিলের <a href="../terms.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">শর্তাবলী</a>, <a href="../privacy.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">প্রাইভেসি পলিসি</a> এবং <a href="../refund.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">রিটার্ন ও রিফান্ড নীতি</a> পড়েছি এবং সম্মত আছি।
                </label>
            </div>

            <!-- Action Button -->
            <div class="pt-4">
                <button type="button" onclick="submitPreOrder()" id="submit-btn"
                    class="w-full bg-brand-900 text-white py-5 px-8 rounded-2xl font-bold text-base md:text-lg hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/15 flex items-center justify-center gap-3 group">
                    <span id="btn-text">SSLCommerz দিয়ে পেমেন্ট করুন — ৳<span id="btn-total"><?php echo number_format($total_amount); ?></span></span>
                    <svg id="btn-icon" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
                <p class="text-center text-[11px] text-gray-400 mt-3 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    ক্লিক করলে আপনাকে SSLCommerz নিরাপদ পেমেন্ট পেজে নিয়ে যাওয়া হবে
                </p>
            </div>
        </div>
    </div>
</main>

<!-- Toast Notification -->
<div id="po-toast"
    class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[200] flex items-center gap-4 bg-red-600 text-white px-7 py-3.5 rounded-2xl shadow-2xl transition-all duration-500 translate-y-20 opacity-0 invisible font-anek">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <span id="po-toast-msg" class="font-bold text-sm"></span>
</div>

<script>
    const preOrderId = <?php echo $pre_order_id; ?>;
    const basePrice = <?php echo $price; ?>;
    const isFreeDelivery = <?php echo $is_free_delivery ? 'true' : 'false'; ?>;
    const insideCharge = isFreeDelivery ? 0 : <?php echo $inside_charge; ?>;
    const outsideCharge = isFreeDelivery ? 0 : <?php echo $outside_charge; ?>;
    let currentDeliveryCharge = insideCharge;
    let totalAmount = <?php echo $total_amount; ?>;

    function updateDelivery(loc) {
        currentDeliveryCharge = loc === 'inside' ? insideCharge : outsideCharge;
        totalAmount = basePrice + currentDeliveryCharge;
        
        const dispDelivery = document.getElementById('display-delivery');
        if (dispDelivery) dispDelivery.innerText = currentDeliveryCharge;
        
        const dispTotal = document.getElementById('display-total');
        if (dispTotal) dispTotal.innerText = totalAmount.toLocaleString();
        
        const btnTotal = document.getElementById('btn-total');
        if (btnTotal) btnTotal.innerText = totalAmount.toLocaleString();
    }

    function showError(msg) {
        const toast = document.getElementById('po-toast');
        document.getElementById('po-toast-msg').innerText = msg;
        toast.classList.remove('translate-y-20', 'opacity-0', 'invisible');
        toast.classList.add('translate-y-0', 'opacity-100', 'visible');
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100', 'visible');
            toast.classList.add('translate-y-20', 'opacity-0', 'invisible');
        }, 4000);
    }

    let isPreOrderSubmitting = false;

    async function submitPreOrder() {
        if (isPreOrderSubmitting) return;

        const name = document.getElementById('po-name').value.trim();
        const phone = document.getElementById('po-phone').value.trim();
        const email = document.getElementById('po-email').value.trim();
        const addr = document.getElementById('po-address').value.trim();

        if (!name || !phone || !email || !addr) {
            showError('অনুগ্রহ করে আপনার নাম, মোবাইল নম্বর, ইমেইল এবং সম্পূর্ণ ঠিকানা দিন।');
            return;
        }

        if (phone.length < 11) {
            showError('সঠিক ১১ ডিজিটের মোবাইল নম্বর প্রদান করুন।');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('সঠিক ইমেইল এড্রেস প্রদান করুন।');
            return;
        }

        const agreeCheckbox = document.getElementById('po-terms-agree');
        if (!agreeCheckbox || !agreeCheckbox.checked) {
            showError('পেমেন্ট এগিয়ে নিতে অন্ত্যমিলের শর্তাবলী ও পলিসিতে সম্মতি দিন।');
            return;
        }

        const btn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnIcon = document.getElementById('btn-icon');

        isPreOrderSubmitting = true;
        btn.disabled = true;
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        btnText.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            গেটওয়েতে রিডাইরেক্ট করা হচ্ছে...
        `;
        if (btnIcon) btnIcon.classList.add('hidden');

        const locationInput = document.querySelector('input[name="location"]:checked') || document.querySelector('input[name="location"][type="hidden"]');
        const location = locationInput ? locationInput.value : 'inside';

        const formData = new FormData();
        formData.append('preorder_id', preOrderId);
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('address', addr);
        formData.append('location', location);
        formData.append('total_amount', totalAmount);

        try {
            const response = await fetch('process_pre_order.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                isPreOrderSubmitting = false;
                btn.disabled = false;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
                btnText.innerHTML = `SSLCommerz দিয়ে পেমেন্ট করুন — ৳<span id="btn-total">${totalAmount.toLocaleString()}</span>`;
                if (btnIcon) btnIcon.classList.remove('hidden');
                showError(data.message || 'অর্ডার শুরু করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
            }
        } catch (err) {
            isPreOrderSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-not-allowed');
            btnText.innerHTML = `SSLCommerz দিয়ে পেমেন্ট করুন — ৳<span id="btn-total">${totalAmount.toLocaleString()}</span>`;
            if (btnIcon) btnIcon.classList.remove('hidden');
            showError('নেটওয়ার্ক সমস্যা হয়েছে। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করুন।');
        }
    }
</script>

<?php include '../includes/footer.php'; ?>