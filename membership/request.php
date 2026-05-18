<?php
$page_title = 'মেম্বারশিপ পেমেন্ট | অন্ত্যমিল';
$path_prefix = '../';
require_once '../includes/db_connect.php';
include '../includes/header.php';

$plan_key = $_GET['plan'] ?? 'General';

$plans = [
    'General' => [
        'name' => 'সাধারণ পাঠক',
        'price' => 500,
        'desc' => 'বই পড়ার অনন্য অভিজ্ঞতা ও কミュニটি লাইব্রেরি অ্যাক্সেস। অন্ত্যমিল পরিবারের একজন গর্বিত সদস্য হন।'
    ],
    'BookLover' => [
        'name' => 'নিয়মিত পাঠক',
        'price' => 1000,
        'desc' => 'যাদের নিত্যদিনের সঙ্গী প্রিয় বই। টি-শার্ট ও টোট ব্যাগ সহ আকর্ষণীয় ছাড় এবং ক্রাফট কাউন্টার অ্যাক্সেস।'
    ],
    'Collector' => [
        'name' => 'সাহিত্য অনুরাগী',
        'price' => 1500,
        'desc' => 'প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য প্রিমিয়াম সব সুযোগ-সুবিধা, মগ ও অগ্রাধিকারমূলক সেবা।'
    ]
];

if (!isset($plans[$plan_key])) {
    $plan_key = 'General';
}

$current_plan = $plans[$plan_key];
?>

<div class="pt-36 pb-24 bg-brand-light min-h-screen font-anek">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-12 text-center animate-slide-up">
            <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.3em] mb-3 block">মেম্বারশিপ ভেরিফিকেশন</span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-900 mb-4 tracking-tight">পেমেন্ট ও নিশ্চিতকরণ</h1>
            <p class="text-gray-600 max-w-xl mx-auto text-sm sm:text-base md:text-lg font-light leading-relaxed">
                আপনার নির্বাচিত মেম্বারশিপ প্ল্যানটি অ্যাক্টিভ করতে নিচে দেওয়া নির্দেশনা অনুযায়ী পেমেন্ট সম্পন্ন করুন।
            </p>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Login / Signup Prompt -->
            <div class="bg-white rounded-3xl p-8 sm:p-12 md:p-16 shadow-2xl border border-gray-100 text-center max-w-2xl mx-auto animate-slide-up">
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-brand-gold/10 text-brand-gold rounded-full flex items-center justify-center mx-auto mb-8 border border-brand-gold/20 shadow-inner">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4m0 0H7m1 0a8 8 0 018 8c0 1.56-.25 3.067-.71 4.48M11 6a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-4">লগইন বা রেজিস্ট্রেশন প্রয়োজন</h3>
                <?php else:
            $pending_req = null;
            $current_user_plan = 'None';
            $plan_expire_date = null;
            
            $stmt = $pdo->prepare("SELECT * FROM membership_requests WHERE member_id = ? AND status = 'Pending' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $pending_req = $stmt->fetch();

            $stmt = $pdo->prepare("SELECT membership_plan, plan_expire_date FROM members WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch();
            if ($user_data) {
                $current_user_plan = $user_data['membership_plan'];
                $plan_expire_date = $user_data['plan_expire_date'];
            }
        ?>
            <?php if ($pending_req): ?>
                <!-- Pending Request Notice -->
                <div class="bg-white rounded-3xl p-8 sm:p-12 md:p-16 shadow-2xl border border-yellow-200 text-center max-w-2xl mx-auto animate-slide-up">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-8 border border-yellow-200 shadow-inner">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-brand-900 mb-4">রিকোয়েস্ট ভেরিফিকেশন চলছে</h3>
                    <p class="text-gray-600 text-base sm:text-lg leading-relaxed mb-8">
                        আপনার একটি মেম্বারশিপ রিকোয়েস্ট (<span class="font-bold text-brand-900 font-mono">REQ-<?php echo $pending_req['id']; ?></span>) বর্তমানে এডমিন প্যানেলে ভেরিফিকেশনের অপেক্ষায় রয়েছে। অনুগ্রহ করে এডমিন কনফার্মেশন পর্যন্ত অপেক্ষা করুন।
                    </p>
                    <a href="../dashboard/" class="inline-flex items-center gap-2 py-4 px-8 bg-brand-900 text-white font-bold rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10 text-base sm:text-lg">
                        <span>ড্যাশবোর্ডে ফিরে যান</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                </div>
            <?php else: ?>
                <?php if ($current_user_plan !== 'None' && $plan_expire_date && strtotime($plan_expire_date) > time()): ?>
                    <div class="mb-8 p-6 bg-brand-900 text-brand-gold rounded-2xl border border-brand-gold/30 shadow-xl flex items-center gap-4 animate-slide-up">
                        <div class="w-12 h-12 rounded-xl bg-brand-gold/10 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <?php if ($current_user_plan === $plan_key): ?>
                                <h4 class="font-bold text-lg text-white mb-1">💡 মেম্বারশিপ রিনিউয়াল মোড</h4>
                                <p class="text-sm text-gray-300 leading-relaxed">আপনার বর্তমান মেম্বারশিপের মেয়াদ <span class="font-bold text-brand-gold"><?php echo date('d M, Y', strtotime($plan_expire_date)); ?></span> তারিখে শেষ হবে। নতুন পেমেন্ট সম্পন্ন হলে আপনার বর্তমান মেয়াদের সাথে আরও ৩০ দিন যুক্ত হবে!</p>
                            <?php else: ?>
                                <h4 class="font-bold text-lg text-white mb-1">💡 আপগ্রেড / পরিবর্তন মোড</h4>
                                <p class="text-sm text-gray-300 leading-relaxed">আপনি বর্তমানে '<?php echo $plans[$current_user_plan]['name'] ?? $current_user_plan; ?>' প্ল্যানে আছেন (মেয়াদ: <span class="font-bold text-brand-gold"><?php echo date('d M, Y', strtotime($plan_expire_date)); ?></span>)। নতুন প্ল্যানে আপগ্রেড করলে আপনার অবশিষ্ট মেয়াদের সাথে আরও ৩০ দিন যুক্ত হয়ে নতুন প্ল্যানটি সাথে সাথেই অ্যাক্টিভ হবে!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <!-- Payment & Request Form -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-start animate-slide-up">
                <!-- Plan Summary Card -->
                <div class="lg:col-span-2 bg-brand-900 rounded-3xl p-8 sm:p-10 text-white shadow-2xl border border-brand-gold/30 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand-gold/10 rounded-full blur-2xl group-hover:bg-brand-gold/20 transition-all duration-500 pointer-events-none"></div>
                    
                    <div class="relative z-10 mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-brand-gold text-xs font-bold uppercase tracking-widest px-3 py-1 bg-brand-gold/10 rounded-full border border-brand-gold/20">নির্বাচিত প্যাকেজ</span>
                            <span class="text-xs text-gray-400">মেয়াদ: ৩০ দিন</span>
                        </div>
                        <h3 class="text-3xl sm:text-4xl font-extrabold text-white mb-3 tracking-tight"><?php echo $current_plan['name']; ?></h3>
                        <p class="text-gray-400 text-sm sm:text-base leading-relaxed mb-8"><?php echo $current_plan['desc']; ?></p>
                        
                        <div class="border-t border-white/10 pt-6 mb-4">
                            <span class="text-gray-400 text-xs uppercase tracking-wider block mb-1">সর্বমোট পরিশোধযোগ্য ফি</span>
                            <div class="text-4xl sm:text-5xl font-extrabold text-brand-gold tracking-tight">৳<?php echo $current_plan['price']; ?></div>
                        </div>
                    </div>

                    <div class="relative z-10 bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-inner">
                        <h4 class="text-sm font-bold text-brand-gold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            পেমেন্ট নির্দেশনা
                        </h4>
                        <div class="text-xs sm:text-sm text-gray-300 space-y-2 leading-relaxed">
                            <p class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">১</span>
                                <span>নিচে দেওয়া আমাদের বিকাশ নম্বরে নির্দিষ্ট ফি পেমেন্ট (পেমেন্ট) করুন।</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">২</span>
                                <span>পেমেন্ট সম্পন্ন হলে আপনার ট্রানজেকশন আইডি (TrxID) নিচের ফর্মে প্রদান করে সাবমিট করুন।</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Form Card -->
                <div class="lg:col-span-3 bg-white rounded-3xl p-6 sm:p-10 md:p-12 shadow-2xl border border-gray-100 relative">
                    <!-- Merchant Numbers -->
                    <div class="mb-10 p-6 sm:p-8 bg-brand-gold/5 border border-brand-gold/20 rounded-2xl shadow-sm">
                        <h4 class="text-base sm:text-lg font-bold text-brand-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            মার্চেন্ট পেমেন্ট নম্বর:
                        </h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="bg-white p-5 rounded-2xl border border-brand-gold/20 shadow-sm flex items-center gap-4 hover:border-brand-gold transition-all group">
                                <div class="w-12 h-12 bg-pink-50 text-pink-600 rounded-2xl flex items-center justify-center font-bold text-sm shadow-inner group-hover:scale-105 transition-transform">bKash</div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">বিকাশ (পেমেন্ট)</span>
                                    <span class="font-bold text-brand-900 text-lg sm:text-xl font-mono tracking-wide">01330975787</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="process_request.php" method="POST" class="space-y-8">
                        <input type="hidden" name="plan" value="<?php echo $plan_key; ?>">
                        <input type="hidden" name="amount" value="<?php echo $current_plan['price']; ?>">

                        <div>
                            <label class="block text-sm sm:text-base font-bold text-brand-900 mb-4">পেমেন্ট মাধ্যম নির্বাচন করুন *</label>
                            <div class="grid grid-cols-1 gap-4">
                                <label class="relative flex items-center justify-between p-5 border-2 border-brand-900 bg-brand-gold/5 rounded-2xl cursor-pointer transition-all shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <input type="radio" name="payment_method" value="bkash" required checked class="text-brand-900 focus:ring-brand-900 w-5 h-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-brand-900 text-base">বিকাশ (bKash)</span>
                                            <span class="text-xs text-gray-400">পেমেন্ট সম্পন্ন করেছেন</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="trx_id" class="block text-sm sm:text-base font-bold text-brand-900 mb-3">ট্রানজেকশন আইডি (TrxID) *</label>
                            <div class="relative">
                                <input type="text" name="trx_id" id="trx_id" required placeholder="e.g. 8X9D7F6E"
                                    class="w-full bg-gray-50 border-2 border-gray-200 rounded-2xl px-6 py-5 text-brand-900 font-mono font-bold text-lg sm:text-xl focus:outline-none focus:border-brand-900 focus:bg-white transition-all shadow-inner">
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.62.5176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-500 mt-3 leading-relaxed">বিকাশ অ্যাপ থেকে পেমেন্ট করার পর প্রাপ্ত ট্রানজেকশন আইডিটি (TrxID) এখানে হুবহু প্রদান করুন।</p>
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full py-5 sm:py-6 bg-brand-900 text-white font-bold text-lg sm:text-xl rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/20 flex items-center justify-center gap-3 group">
                                <span>ভেরিফিকেশন রিকোয়েস্ট সাবমিট করুন</span>
                                <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
