<?php
// membership/request.php
// Membership subscription payment page with SSLCommerz, Wallet Balance, and Manual bKash

$page_title = 'মেম্বারশিপ পেমেন্ট ও অ্যাক্টিভেশন | অন্ত্যমিল';
$path_prefix = '../';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';
include __DIR__ . '/../includes/header.php';

$plan_key = trim($_GET['plan'] ?? 'General');

$plans = [
    'General' => [
        'name' => 'সাধারণ পাঠক',
        'price' => 500,
        'discount' => '১০%',
        'desc' => 'বই পড়ার অনন্য অভিজ্ঞতা ও কミュニটি লাইব্রেরি অ্যাক্সেস। অন্ত্যমিল পরিবারের একজন গর্বিত সদস্য হন।',
        'perks' => [
            'কমিউনিটি লাইব্রেরি থেকে বই ধার নেওয়ার পূর্ণ সুযোগ',
            'সকল বই ক্রয়ে ১০% স্পেশাল মেম্বার ডিসকাউন্ট',
            'নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা',
            'ব্র্যান্ডেড টি-শার্ট উপহার'
        ]
    ],
    'BookLover' => [
        'name' => 'নিয়মিত পাঠক',
        'price' => 1000,
        'discount' => '১৫%',
        'desc' => 'যাদের নিত্যদিনের সঙ্গী প্রিয় বই। টি-শার্ট ও টোট ব্যাগ সহ আকর্ষণীয় ছাড় এবং ক্রাফট কাউন্টার অ্যাক্সেস।',
        'perks' => [
            'সাধারণ পাঠকের সব সুবিধা + অতিরিক্ত ৫% (মোট ১৫%) ছাড়',
            'কমিউনিটি লাইব্রেরি থেকে অগ্রাধিকারমূলক বই ধার',
            'ব্র্যান্ডেড টি-শার্ট ও প্রিমিয়াম টোট ব্যাগ',
            'ক্রাফট কাউন্টার ও সাহিত্যিক পোস্টকার্ড'
        ]
    ],
    'Collector' => [
        'name' => 'সাহিত্য অনুরাগী',
        'price' => 1500,
        'discount' => '২০%',
        'desc' => 'প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য প্রিমিয়াম সব সুযোগ-সুবিধা, মগ ও অগ্রাধিকারমূলক সেবা।',
        'perks' => [
            'সকল বই ক্রয়ে সর্বোচ্চ ২০% মেম্বার ডিসকাউন্ট',
            'প্রিমিয়াম বই ধার নেওয়ার অধিকার (বর্ধিত সময়)',
            'ব্র্যান্ডেড টি-শার্ট, টোট ব্যাগ ও এক্সক্লুসিভ সিরামিক মগ',
            'কাট-ফ্লাওয়ার কাউন্টার ও ভিআইপি রিডার্স লাউঞ্জ অ্যাক্সেস'
        ]
    ]
];

if (!isset($plans[$plan_key])) {
    $plan_key = 'General';
}

$current_plan = $plans[$plan_key];
$error_code = trim($_GET['error'] ?? '');
?>

<div class="pt-36 pb-24 bg-brand-light min-h-screen font-anek">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-10 text-center animate-slide-up">
            <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.3em] mb-3 block">মেম্বারশিপ অ্যাক্টিভেশন</span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-900 mb-3 tracking-tight">পেমেন্ট ও নিশ্চিতকরণ</h1>
            <p class="text-gray-600 max-w-xl mx-auto text-sm sm:text-base font-light leading-relaxed">
                আপনার নির্বাচিত মেম্বারশিপ প্ল্যানটি অ্যাক্টিভ করতে অনলাইন পেমেন্ট অথবা ওয়ালেট ব্যালেন্স ব্যবহার করুন।
            </p>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Login / Signup Prompt -->
            <div class="bg-white rounded-[36px] p-8 sm:p-12 md:p-16 shadow-2xl border border-gray-100 text-center max-w-2xl mx-auto animate-slide-up">
                <div class="w-20 h-20 bg-brand-gold/10 text-brand-gold rounded-full flex items-center justify-center mx-auto mb-6 border border-brand-gold/20 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4m0 0H7m1 0a8 8 0 018 8c0 1.56-.25 3.067-.71 4.48M11 6a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-3">লগইন বা রেজিস্ট্রেশন প্রয়োজন</h3>
                <p class="text-gray-500 text-sm sm:text-base leading-relaxed mb-8 max-w-md mx-auto">
                    মেম্বারশিপ সুবিধা উপভোগ করতে ও লাইব্রেরি থেকে বই ধার নিতে অনুগ্রহ করে আপনার অ্যাকাউন্টে লগইন করুন।
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="../login/index.php?redirect=<?php echo urlencode("../membership/request.php?plan=" . $plan_key); ?>" class="w-full sm:w-auto px-8 py-4 bg-brand-900 text-white font-bold rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10 text-base">
                        লগইন করুন
                    </a>
                    <a href="../signup/index.php?redirect=<?php echo urlencode("../membership/request.php?plan=" . $plan_key); ?>" class="w-full sm:w-auto px-8 py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition-all text-base">
                        নতুন অ্যাকাউন্ট তৈরি করুন
                    </a>
                </div>
            </div>

        <?php else: 
            $user_id = (int)$_SESSION['user_id'];
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$user_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            $current_user_plan = $member['membership_plan'] ?? 'None';
            $plan_expire_date  = $member['plan_expire_date'] ?? null;
            $wallet_balance    = (float)($member['acc_balance'] ?? 0);
            $is_active_plan    = ($current_user_plan !== 'None' && $plan_expire_date && strtotime($plan_expire_date) > time());

            // Check if there is a pending manual request
            $stmt = $pdo->prepare("SELECT * FROM membership_requests WHERE member_id = ? AND status = 'Pending' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$user_id]);
            $pending_req = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

            <!-- Error Notification Banner -->
            <?php if ($error_code === 'insufficient_balance'): ?>
                <div class="mb-8 p-5 bg-red-50 text-red-700 rounded-2xl border border-red-200 shadow-sm flex items-center gap-4 animate-slide-up">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-red-900 mb-0.5">ওয়ালেটে অপর্যাপ্ত ব্যালেন্স</h4>
                        <p class="text-xs text-red-600">আপনার বর্তমান ওয়ালেট ব্যালেন্স (৳<?php echo number_format($wallet_balance, 2); ?>) নির্বাচিত প্ল্যানের ফি পরিশোধের জন্য পর্যাপ্ত নয়। আপনি অনলাইন পেমেন্ট (SSLCommerz) ব্যবহার করে সাথে সাথেই সক্রিয় করতে পারেন।</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Renewal or Upgrade Banner -->
            <?php if ($is_active_plan): ?>
                <div class="mb-8 p-6 bg-brand-900 text-brand-gold rounded-3xl border border-brand-gold/30 shadow-xl flex items-center gap-4 animate-slide-up">
                    <div class="w-12 h-12 rounded-2xl bg-brand-gold/20 flex items-center justify-center shrink-0 border border-brand-gold/30">
                        <svg class="w-6 h-6 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-sm">
                        <?php if ($current_user_plan === $plan_key): ?>
                            <h4 class="font-bold text-base text-white mb-0.5">💡 মেম্বারশিপ রিনিউয়াল মোড</h4>
                            <p class="text-gray-300 leading-relaxed">আপনার বর্তমান মেম্বারশিপের মেয়াদ <span class="font-bold text-brand-gold"><?php echo date('d M, Y', strtotime($plan_expire_date)); ?></span> তারিখে শেষ হবে। নতুন পেমেন্ট সম্পন্ন হলে বর্তমান মেয়াদের সাথে আরও ৩০ দিন স্বয়ংক্রিয়ভাবে যুক্ত হবে!</p>
                        <?php else: ?>
                            <h4 class="font-bold text-base text-white mb-0.5">💡 আপগ্রেড / পরিবর্তন মোড</h4>
                            <p class="text-gray-300 leading-relaxed">আপনি বর্তমানে <strong><?php echo $plans[$current_user_plan]['name'] ?? $current_user_plan; ?></strong> প্ল্যানে আছেন (মেয়াদ: <?php echo date('d M, Y', strtotime($plan_expire_date)); ?>)। নতুন প্ল্যানে আপগ্রেড করলে আপনার অবশিষ্ট মেয়াদের সাথে আরও ৩০ দিন যুক্ত হয়ে নতুন প্ল্যানটি সাথে সাথেই সক্রিয় হবে!</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pending Manual Request Banner -->
            <?php if ($pending_req): ?>
                <div class="mb-8 p-5 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 flex items-start gap-4 animate-slide-up">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700 mt-0.5">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <p class="font-bold mb-1">আপনার একটি ম্যানুয়াল মেম্বারশিপ রিকোয়েস্ট (REQ-#<?php echo $pending_req['id']; ?>) ভেরিফিকেশনের অপেক্ষায় রয়েছে।</p>
                        <p class="text-amber-800">আপনি চাইলে অপেক্ষা না করে নিচের <strong>অনলাইন পেমেন্ট</strong> অপশন দিয়ে সাথে সাথেই আপনার অ্যাকাউন্ট সক্রিয় করে নিতে পারেন।</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grid Area: Left Summary / Right Options -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-start animate-slide-up">
                
                <!-- Plan Summary Card (2 Cols) -->
                <div class="lg:col-span-2 bg-brand-900 rounded-[36px] p-8 text-white shadow-2xl border border-brand-gold/30 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-44 h-44 bg-brand-gold/15 rounded-full blur-2xl group-hover:bg-brand-gold/25 transition-all duration-500 pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-brand-gold text-xs font-bold uppercase tracking-widest px-3 py-1 bg-brand-gold/15 rounded-full border border-brand-gold/30">নির্বাচিত প্ল্যান</span>
                            <span class="text-xs text-gray-400">মেয়াদ: ৩০ দিন</span>
                        </div>

                        <h3 class="text-3xl sm:text-4xl font-extrabold text-white mb-2 tracking-tight"><?php echo $current_plan['name']; ?></h3>
                        <p class="text-gray-400 text-xs sm:text-sm leading-relaxed mb-6"><?php echo $current_plan['desc']; ?></p>

                        <!-- Perks List -->
                        <div class="bg-white/5 backdrop-blur-md rounded-2xl p-5 border border-white/10 mb-6 space-y-2.5 text-xs text-gray-300">
                            <span class="text-[10px] text-brand-gold font-bold uppercase tracking-wider block mb-2">এই প্ল্যানের সুবিধাসমূহ:</span>
                            <?php foreach ($current_plan['perks'] as $perk): ?>
                            <div class="flex items-start gap-2.5">
                                <span class="text-brand-gold font-bold shrink-0 mt-0.5">✓</span>
                                <span><?php echo $perk; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-white/10 pt-6">
                            <span class="text-gray-400 text-xs uppercase tracking-wider block mb-1">সর্বমোট পরিশোধযোগ্য ফি</span>
                            <div class="text-4xl sm:text-5xl font-extrabold text-brand-gold tracking-tight font-mono">
                                ৳<?php echo number_format($current_plan['price'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Change Plan Link -->
                    <div class="mt-8 pt-4 border-t border-white/10 text-center">
                        <a href="index.php" class="text-xs text-gray-400 hover:text-brand-gold transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            অন্য প্ল্যান নির্বাচন করুন
                        </a>
                    </div>
                </div>

                <!-- Payment Methods Card (3 Cols) -->
                <div class="lg:col-span-3 bg-white rounded-[36px] p-6 sm:p-10 shadow-2xl border border-gray-100 relative space-y-6">
                    
                    <h3 class="text-xl sm:text-2xl font-bold text-brand-900 mb-2">পেমেন্ট মাধ্যম বেছে নিন</h3>
                    <p class="text-gray-500 text-xs sm:text-sm leading-relaxed mb-6">
                        আপনার সুবিধাজনক মাধ্যমটি নির্বাচন করে মেম্বারশিপ কনফার্ম করুন:
                    </p>

                    <!-- METHOD 1: Online Payment via SSLCommerz (INSTANT) -->
                    <div class="p-6 rounded-3xl border-2 border-brand-gold/60 bg-gradient-to-br from-brand-gold/5 via-white to-amber-50/20 shadow-lg relative overflow-hidden group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-brand-900 text-brand-gold flex items-center justify-center font-bold text-lg shadow-md shadow-brand-900/10 shrink-0">
                                    ⚡
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base sm:text-lg font-bold text-brand-900">অনলাইন পেমেন্ট (SSLCommerz)</h4>
                                        <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">তাৎক্ষণিক সক্রিয়</span>
                                    </div>
                                    <p class="text-xs text-gray-500">বিকাশ, নগদ, রকেট, উপায়, ভিসা ও মাস্টারকার্ড</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Icons Row -->
                        <div class="flex flex-wrap items-center gap-2 mb-6 py-2 px-3 bg-white rounded-xl border border-gray-100 text-[11px] font-bold text-gray-500">
                            <span class="text-pink-600">bKash</span>
                            <span>•</span>
                            <span class="text-orange-600">Nagad</span>
                            <span>•</span>
                            <span class="text-purple-600">Rocket</span>
                            <span>•</span>
                            <span class="text-blue-600">Visa / Master</span>
                            <span>•</span>
                            <span class="text-emerald-600">Bank Cards</span>
                        </div>

                        <!-- Direct Form Submit to initiate_sslcommerz.php -->
                        <form action="initiate_sslcommerz.php" method="POST">
                            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan_key); ?>">
                            <button type="submit" class="w-full py-4 sm:py-4.5 bg-brand-900 text-white rounded-2xl font-bold text-sm sm:text-base hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10 flex items-center justify-center gap-2 group-hover:scale-[1.01]">
                                <span>অনলাইনে পেমেন্ট করে সক্রিয় করুন (৳<?php echo number_format($current_plan['price']); ?>)</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>
                    </div>

                    <!-- METHOD 2: Pay with Wallet Balance -->
                    <?php 
                        $has_sufficient_balance = ($wallet_balance >= $current_plan['price']);
                    ?>
                    <div class="p-6 rounded-3xl border border-gray-200 hover:border-brand-gold/40 transition-all bg-gray-50/70">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-900/10 text-brand-900 flex items-center justify-center font-bold text-base shrink-0">
                                    👛
                                </div>
                                <div>
                                    <h4 class="text-sm sm:text-base font-bold text-brand-900">ওয়ালেট ব্যালেন্স থেকে পরিশোধ</h4>
                                    <p class="text-xs text-gray-500">আপনার বর্তমান ব্যালেন্স: <strong class="text-brand-900 font-mono">৳<?php echo number_format($wallet_balance, 2); ?></strong></p>
                                </div>
                            </div>
                            <?php if ($has_sufficient_balance): ?>
                                <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">পর্যাপ্ত ব্যালেন্স</span>
                            <?php else: ?>
                                <span class="px-2.5 py-0.5 bg-gray-200 text-gray-600 rounded-full text-[10px] font-bold">ব্যালেন্স কম</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($has_sufficient_balance): ?>
                            <form action="process_wallet.php" method="POST" class="mt-4">
                                <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan_key); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <button type="submit" onclick="return confirm('আপনার ওয়ালেট থেকে ৳<?php echo $current_plan['price']; ?> কর্তন করে মেম্বারশিপ সক্রিয় করতে চান?')" class="w-full py-3.5 bg-white border-2 border-brand-900 text-brand-900 hover:bg-brand-900 hover:text-white rounded-2xl font-bold text-xs sm:text-sm transition-all shadow-sm flex items-center justify-center gap-2">
                                    <span>ওয়ালেট দিয়ে কনফার্ম করুন (৳<?php echo number_format($current_plan['price']); ?>)</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="mt-3 flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-200">
                                <span>আরও প্রয়োজন: <strong class="text-brand-900 font-mono">৳<?php echo number_format($current_plan['price'] - $wallet_balance, 2); ?></strong></span>
                                <a href="../dashboard/index.php" class="text-brand-gold font-bold hover:underline">ড্যাশবোর্ড থেকে ফান্ড যোগ করুন →</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- METHOD 3: Manual bKash TrxID Verification (Collapsible / Secondary) -->
                    <div class="p-6 rounded-3xl border border-gray-200 bg-white">
                        <details class="group cursor-pointer">
                            <summary class="flex items-center justify-between list-none">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        bKash
                                    </div>
                                    <div>
                                        <h4 class="text-sm sm:text-base font-bold text-brand-900">ম্যানুয়াল বিকাশ পেমেন্ট (TrxID)</h4>
                                        <p class="text-xs text-gray-400">ব্যক্তিগতভাবে সেন্ড মানি/পেমেন্ট করে TrxID সাবমিট করুন</p>
                                    </div>
                                </div>
                                <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                            </summary>

                            <div class="mt-6 pt-5 border-t border-gray-100 cursor-default">
                                <div class="mb-5 p-4 bg-brand-gold/5 border border-brand-gold/20 rounded-2xl text-xs space-y-1">
                                    <span class="text-gray-500 block">মার্চেন্ট বিকাশ নম্বর:</span>
                                    <span class="text-base font-bold text-brand-900 font-mono select-all">01330975787</span>
                                    <p class="text-[11px] text-gray-500 pt-1">বিকাশ অ্যাপ থেকে 'Make Payment' করে TrxID নিচে দিন। এডমিন ভেরিফাই করে সক্রিয় করবে।</p>
                                </div>

                                <form action="process_request.php" method="POST" class="space-y-4">
                                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan_key); ?>">
                                    <input type="hidden" name="amount" value="<?php echo $current_plan['price']; ?>">
                                    <input type="hidden" name="payment_method" value="bkash">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                                    <div>
                                        <label for="trx_id" class="block text-xs font-bold text-brand-900 mb-1.5">ট্রানজেকশন নম্বর (TrxID) *</label>
                                        <input type="text" name="trx_id" id="trx_id" required placeholder="যেমন: 8X9D7F6E" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-900 font-mono font-bold text-sm focus:outline-none focus:border-brand-900 focus:bg-white transition-all">
                                    </div>

                                    <button type="submit" class="w-full py-3 bg-gray-100 hover:bg-brand-900 hover:text-white text-gray-700 rounded-xl font-bold text-xs transition-all">
                                        ভেরিফিকেশন রিকোয়েস্ট সাবমিট করুন
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
