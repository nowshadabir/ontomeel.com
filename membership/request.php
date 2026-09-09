<?php
// membership/request.php
// Minimal, distraction-free Membership Checkout & Payment

$page_title = 'মেম্বারশিপ পেমেন্ট | অন্ত্যমিল';
$path_prefix = '../';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';

// Flexible plan key extraction
$plan_key = trim($_GET['plan'] ?? '');
if (empty($plan_key)) {
    foreach ($_GET as $k => $v) {
        if (in_array($k, ['General', 'BookLover', 'Collector'], true)) {
            $plan_key = $k;
            break;
        }
        if (in_array($v, ['General', 'BookLover', 'Collector'], true)) {
            $plan_key = $v;
            break;
        }
    }
}
if (!in_array($plan_key, ['General', 'BookLover', 'Collector'], true)) {
    $plan_key = 'General';
}

$plans = [
    'General' => [
        'name' => 'সাধারণ পাঠক',
        'price' => 500,
        'discount' => '৫%',
        'summary' => 'টোট ব্যাগ, সর্বোচ্চ ৫% ছাড়, লাইব্রেরি ও নেসক্যাফে বুথ সুবিধা'
    ],
    'BookLover' => [
        'name' => 'নিয়মিত পাঠক',
        'price' => 700,
        'discount' => '৮%',
        'summary' => 'টোট ব্যাগ ও টি-শার্ট, সর্বোচ্চ ৮% ছাড়, লাইব্রেরি ও নেসক্যাফে বুথ সুবিধা'
    ],
    'Collector' => [
        'name' => 'সাহিত্য অনুরাগী',
        'price' => 1000,
        'discount' => '১০%',
        'summary' => 'টোট ব্যাগ, টি-শার্ট, বুকমার্ক/পোস্টকার্ড ও কীরিং, সর্বোচ্চ ১০% ছাড়'
    ]
];

$current_plan = $plans[$plan_key];
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$member = null;
$is_active_plan = false;
$current_user_plan = 'None';
$plan_expire_date = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $current_user_plan = $member['membership_plan'] ?? 'None';
        $plan_expire_date = $member['plan_expire_date'] ?? null;
        $is_active_plan = ($current_user_plan !== 'None' && !empty($plan_expire_date) && strtotime($plan_expire_date) > time());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="pt-32 sm:pt-36 pb-20 bg-[#faf9f6] min-h-screen font-anek flex items-center justify-center">
    <div class="w-full max-w-lg mx-auto px-4 sm:px-6">
        
        <!-- Back link -->
        <div class="mb-4">
            <a href="index.php" class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-brand-900 transition-colors font-medium">
                <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>অন্য প্ল্যান নির্বাচন করুন</span>
            </a>
        </div>

        <?php if (!$user_id): ?>
            <!-- Logged-out Login Card -->
            <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-gray-100 text-center">
                <div class="w-14 h-14 rounded-2xl bg-brand-gold/15 text-brand-gold flex items-center justify-center mx-auto mb-4 text-2xl">
                    🔐
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-brand-900 mb-2">লগইন প্রয়োজন</h2>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                    <strong><?php echo $current_plan['name']; ?> (৳<?php echo number_format($current_plan['price']); ?>)</strong> মেম্বারশিপ সক্রিয় করতে অনুগ্রহ করে লগইন করুন।
                </p>
                <div class="space-y-3">
                    <a href="../login/index.php?redirect=<?php echo urlencode("../membership/request.php?plan=" . $plan_key); ?>" 
                       class="block w-full py-3.5 bg-brand-900 text-white font-bold rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all text-sm text-center shadow-md">
                        লগইন করুন
                    </a>
                    <a href="../signup/index.php?redirect=<?php echo urlencode("../membership/request.php?plan=" . $plan_key); ?>" 
                       class="block w-full py-3.5 bg-gray-100 text-brand-900 font-bold rounded-xl hover:bg-gray-200 transition-all text-sm text-center">
                        নতুন অ্যাকাউন্ট তৈরি
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Minimal Checkout Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-gray-100">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-5 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-brand-gold">মেম্বারশিপ চেকআউট</span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-brand-900 leading-tight">পেমেন্ট নিশ্চিতকরণ</h1>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl sm:text-3xl font-extrabold text-brand-900 font-mono">৳<?php echo number_format($current_plan['price']); ?></span>
                        <span class="text-[11px] text-gray-400 block font-anek">মেয়াদ: ১ বছর</span>
                    </div>
                </div>

                <!-- Plan Summary Box -->
                <div class="my-5 p-4 rounded-2xl bg-[#f8f7f4] border border-gray-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-bold text-brand-900"><?php echo $current_plan['name']; ?></span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">বইয়ে <?php echo $current_plan['discount']; ?> ছাড়</span>
                    </div>
                    <p class="text-xs text-gray-500 font-light leading-relaxed">
                        <?php echo $current_plan['summary']; ?>
                    </p>
                </div>

                <!-- Renewal / Extension Note if Active -->
                <?php if ($is_active_plan): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-[11px] text-amber-900 flex items-start gap-2">
                        <span class="text-sm">💡</span>
                        <p class="leading-relaxed">
                            আপনার বর্তমান মেম্বারশিপের সাথে আরও <strong>১ বছর</strong> মেয়াদ স্বয়ংক্রিয়ভাবে যুক্ত হয়ে যাবে।
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Member Info Row -->
                <div class="pb-5 border-b border-gray-100 text-xs text-gray-500 flex items-center justify-between">
                    <span>গ্রাহক: <strong class="text-brand-900"><?php echo htmlspecialchars($member['full_name'] ?? ''); ?></strong></span>
                    <span class="font-mono"><?php echo htmlspecialchars($member['phone'] ?? $member['email'] ?? ''); ?></span>
                </div>

                <!-- Checkout Form -->
                <form action="initiate_sslcommerz.php" method="POST" class="pt-5 space-y-5" onsubmit="handlePayClick(event)">
                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan_key); ?>">

                    <!-- Terms & Conditions Checkbox -->
                    <label class="flex items-start gap-3 cursor-pointer select-none group">
                        <input 
                            type="checkbox" 
                            id="terms_agree" 
                            name="terms_agree" 
                            required 
                            checked
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-gold focus:ring-brand-gold accent-brand-gold cursor-pointer">
                        <span class="text-xs text-gray-600 leading-relaxed">
                            আমি অন্ত্যমিল <a href="../terms/" target="_blank" class="text-brand-gold font-bold hover:underline">মেম্বারশিপ পলিসি</a> ও ব্যবহারের শর্তাবলীতে সম্মতি দিচ্ছি।
                        </span>
                    </label>

                    <!-- Pay Button -->
                    <button 
                        type="submit" 
                        id="pay-btn"
                        class="w-full py-4 bg-brand-900 hover:bg-brand-gold hover:text-brand-900 text-white rounded-2xl font-bold text-base transition-all duration-200 shadow-lg shadow-brand-900/10 flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <span id="pay-btn-text">৳<?php echo number_format($current_plan['price']); ?> পেমেন্ট করুন</span>
                        <svg id="pay-btn-arrow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                        <svg id="pay-btn-spinner" class="w-4 h-4 animate-spin hidden text-current" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <p class="text-[11px] text-gray-400 text-center mt-4">
                    🔒 পেমেন্ট সম্পন্ন হওয়ামাত্র আপনার মেম্বারশিপ স্বয়ংক্রিয়ভাবে সক্রিয় হবে
                </p>

            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function handlePayClick(e) {
        const terms = document.getElementById('terms_agree');
        if (terms && !terms.checked) {
            e.preventDefault();
            alert('অনুগ্রহ করে মেম্বারশিপ শর্তাবলীতে সম্মতি দিন।');
            return;
        }

        const btn = document.getElementById('pay-btn');
        const text = document.getElementById('pay-btn-text');
        const arrow = document.getElementById('pay-btn-arrow');
        const spinner = document.getElementById('pay-btn-spinner');

        if (btn && text && arrow && spinner) {
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            arrow.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'প্রসেসিং হচ্ছে...';
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
