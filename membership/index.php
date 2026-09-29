<?php
$page_title = 'মেম্বারশিপ | অন্ত্যমিল';
$path_prefix = '../';
include '../includes/db_connect.php';
include '../includes/header.php';

$current_user_plan = 'None';
$plan_expire_date = null;
$student_plan_expire_date = null;
$has_pending_student_req = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT membership_plan, plan_expire_date, student_plan_expire_date FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $current_user_plan = $user_data['membership_plan'] ?? 'None';
        $plan_expire_date = $user_data['plan_expire_date'];
        $student_plan_expire_date = $user_data['student_plan_expire_date'];
    }

    $chk_stmt = $pdo->prepare("SELECT id FROM student_membership_requests WHERE member_id = ? AND status = 'Pending' LIMIT 1");
    $chk_stmt->execute([$_SESSION['user_id']]);
    if ($chk_stmt->fetch()) {
        $has_pending_student_req = true;
    }
}

$is_paid_plan_active = (in_array($current_user_plan, ['General', 'BookLover', 'Collector'], true) && !empty($plan_expire_date) && strtotime($plan_expire_date) > time());
$is_student_plan_active = (!empty($student_plan_expire_date) && strtotime($student_plan_expire_date) > time()) || ($current_user_plan === 'Student' && !empty($plan_expire_date) && strtotime($plan_expire_date) > time());
$student_effective_expire = $student_plan_expire_date ?: $plan_expire_date;
?>

<!-- Membership Hero -->
<div class="pt-40 pb-20 bg-brand-900 relative overflow-hidden">
    <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.3em] mb-4 block font-anek">এক্সক্লুসিভ অ্যাক্সেস</span>
        <h1 class="text-5xl md:text-7xl font-anek font-extrabold text-white mb-6">আমাদের মেম্বারশিপ প্ল্যান</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg md:text-xl font-light leading-relaxed font-anek">
            বই পড়ার অনন্য অভিজ্ঞতা পেতে এবং আমাদের লাইব্রেরির বিশাল সংগ্রহশালা থেকে বই ধার নিতে আপনার পছন্দের প্ল্যানটি বেছে নিন।
        </p>

        <!-- Active Notice -->
        <div class="mt-10 inline-flex items-center gap-4 px-8 py-4 bg-white/10 backdrop-blur-md border border-brand-gold/30 rounded-full shadow-2xl">
            <p class="text-brand-gold font-anek font-bold tracking-wide text-sm md:text-base">আমাদের মেম্বারশিপ প্রোগ্রাম এখন উন্মুক্ত! আজই যুক্ত হোন অন্ত্যমিল পরিবারে।</p>
        </div>

        <?php if (isset($_GET['request']) && $_GET['request'] == 'success'): ?>
            <div class="mt-6 bg-green-500/20 border border-green-500/40 backdrop-blur-md rounded-2xl p-6 max-w-2xl mx-auto shadow-2xl">
                <p class="text-green-300 font-anek font-bold text-lg">আপনার মেম্বারশিপ রিকোয়েস্ট সফলভাবে সাবমিট হয়েছে। আমাদের এডমিন প্যানেল থেকে ভেরিফাই করে খুব শীঘ্রই আপনার মেম্বারশিপ অ্যাক্টিভ করা হবে।</p>
            </div>
        <?php endif; ?>

        <?php if ($has_pending_student_req): ?>
            <div class="mt-6 bg-amber-500/20 border border-amber-500/40 backdrop-blur-md rounded-2xl p-6 max-w-2xl mx-auto shadow-2xl">
                <p class="text-amber-300 font-anek font-bold text-base md:text-lg">
                    ⏳ আপনার ‘বইয়ের আনন্দ-পাঠ’ স্টুডেন্ট মেম্বারশিপ রিকোয়েস্ট অপেক্ষমান (Pending) রয়েছে। এডমিন ভেরিফিকেশনের পর দ্রুত সক্রিয় করা হবে।
                </p>
                <a href="student-apply.php" class="inline-block mt-3 text-xs md:text-sm text-brand-gold font-bold underline">আবেদনের বিবরণ দেখুন →</a>
            </div>
        <?php endif; ?>

        <!-- Active Membership Cards (Supports Both Subscriptions) -->
        <?php if ($is_paid_plan_active || $is_student_plan_active): ?>
            <div class="mt-12 flex flex-wrap justify-center gap-6 max-w-4xl mx-auto">
                
                <?php if ($is_paid_plan_active): 
                    $plan_names = [
                        'General' => 'সাধারণ পাঠক (৳৫০০)',
                        'BookLover' => 'নিয়মিত পাঠক (৳৭৫০)',
                        'Collector' => 'সাহিত্য অনুরাগী (৳১০০০)'
                    ];
                ?>
                    <div class="bg-white/10 backdrop-blur-md border border-brand-gold/30 rounded-3xl p-6 sm:p-8 flex-1 min-w-[280px] max-w-md shadow-2xl text-center">
                        <span class="text-brand-gold text-xs font-bold uppercase tracking-widest mb-1 block font-anek">আপনার সক্রিয় মেম্বারশিপ</span>
                        <h3 class="text-2xl sm:text-3xl font-anek font-extrabold text-white mb-2">
                            <?php echo $plan_names[$current_user_plan] ?? $current_user_plan; ?>
                        </h3>
                        <p class="text-xs text-brand-gold font-mono font-bold mb-3">মেয়াদ শেষ: <?php echo date('d M, Y', strtotime($plan_expire_date)); ?></p>
                        <div class="inline-flex items-center gap-2 bg-green-500/20 border border-green-500/30 px-4 py-1.5 rounded-full">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            <p class="text-green-300 text-xs font-anek font-bold tracking-wide">পেইড মেম্বারশিপ সক্রিয়</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($is_student_plan_active): ?>
                    <div class="bg-white/10 backdrop-blur-md border border-emerald-400/40 rounded-3xl p-6 sm:p-8 flex-1 min-w-[280px] max-w-md shadow-2xl text-center">
                        <span class="text-emerald-400 text-xs font-bold uppercase tracking-widest mb-1 block font-anek">স্টুডেন্ট মেম্বারশিপ</span>
                        <h3 class="text-2xl sm:text-3xl font-anek font-extrabold text-white mb-2">
                            বইয়ের আনন্দ-পাঠ
                        </h3>
                        <p class="text-xs text-emerald-300 font-mono font-bold mb-3">মেয়াদ শেষ: <?php echo date('d M, Y', strtotime($student_effective_expire)); ?></p>
                        <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-500/30 px-4 py-1.5 rounded-full">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                            <p class="text-emerald-300 text-xs font-anek font-bold tracking-wide">স্টুডেন্ট প্ল্যান সক্রিয়</p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<!-- How it Works (Steps) -->
<section class="py-24 bg-white relative">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-anek font-bold text-brand-900 mb-4">কিভাবে মেম্বার হবেন?</h2>
            <div class="w-16 h-1 bg-brand-gold mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 relative">
            <!-- Step 1 -->
            <div class="flex flex-col items-center text-center group reveal font-anek">
                <div class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">১</span>
                </div>
                <h3 class="font-bold text-xl text-brand-900 mb-2">প্ল্যান বেছে নিন</h3>
                <p class="text-gray-500 text-sm font-light">আপনার পড়ার মাত্রা অনুযায়ী পছন্দসই প্যাকেজ সিলেক্ট করুন।</p>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center text-center group reveal font-anek" style="transition-delay: 100ms;">
                <div class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">২</span>
                </div>
                <h3 class="font-bold text-xl text-brand-900 mb-2">তথ্য প্রদান</h3>
                <p class="text-gray-500 text-sm font-light">আপনার নাম, ঠিকানা এবং প্রয়োজনীয় তথ্য দিয়ে ফর্মটি পূরণ করুন।</p>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center text-center group reveal font-anek" style="transition-delay: 200ms;">
                <div class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৩</span>
                </div>
                <h3 class="font-bold text-xl text-brand-900 mb-2">পেমেন্ট নিশ্চিত</h3>
                <p class="text-gray-500 text-sm font-light">অনলাইন পেমেন্টের মাধ্যমে আপনার মেম্বারশিপ ফি প্রদান করুন।</p>
            </div>
            <!-- Step 4 -->
            <div class="flex flex-col items-center text-center group reveal font-anek" style="transition-delay: 300ms;">
                <div class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৪</span>
                </div>
                <h3 class="font-bold text-xl text-brand-900 mb-2">কার্ড সংগ্রহ</h3>
                <p class="text-gray-500 text-sm font-light">আপনার ডিজিটাল বা ফিজিক্যাল লাইব্রেরি কার্ডটি বুঝে নিন।</p>
            </div>
            <!-- Step 5 -->
            <div class="flex flex-col items-center text-center group reveal font-anek" style="transition-delay: 400ms;">
                <div class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৫</span>
                </div>
                <h3 class="font-bold text-xl text-brand-900 mb-2">বই পড়া শুরু</h3>
                <p class="text-gray-500 text-sm font-light">এখন আপনি যেকোনও বই পড়ার এবং ধার নেওয়ার জন্য তৈরি!</p>
            </div>
        </div>

        <!-- Connection Line (Hidden on mobile) -->
        <div class="hidden md:block absolute top-[230px] left-1/2 -translate-x-1/2 w-[70%] h-px bg-gray-100 -z-10"></div>
    </div>
</section>

<!-- Pricing Area -->
<section class="py-20 bg-brand-light overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <!-- Student Membership Plan Featured Section: বইয়ের আনন্দ-পাঠ -->
        <div class="mb-16 bg-white rounded-3xl shadow-xl border border-brand-gold/30 overflow-hidden hover:shadow-2xl transition-all duration-500 reveal">
            <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                <!-- Cover Image Column -->
                <div class="lg:col-span-5 h-64 lg:h-full relative min-h-[300px] overflow-hidden bg-brand-900">
                    <img src="../assets/img/boi-er-ananda.jpeg" alt="বইয়ের আনন্দ-পাঠ" class="w-full h-full object-cover transform hover:scale-105 transition-all duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-black/70 via-black/30 to-transparent flex flex-col justify-end p-6 lg:p-8">
                    </div>
                </div>

                <!-- Plan Details Column -->
                <div class="lg:col-span-7 p-8 lg:p-12 flex flex-col justify-between h-full font-anek">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <h2 class="text-3xl lg:text-4xl font-extrabold text-brand-900 font-anek">
                                বইয়ের আনন্দ-পাঠ
                            </h2>
                            <div class="inline-flex items-baseline gap-1.5 px-4 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full text-emerald-800 font-bold">
                                <span class="text-xl font-extrabold">সম্পূর্ণ ফ্রি</span>
                                <span class="text-xs text-emerald-600">(৳০)</span>
                            </div>
                        </div>

                        <p class="text-brand-gold font-bold text-base mb-2">
                            বইয়ের ভেতর নিজের পথ
                        </p>
                        <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6 font-light">
                            বইয়ের সঙ্গে একান্ত ও স্বতন্ত্র সম্পর্ক গড়ে তোলার পাঠ-উদ্যোগ।
                        </p>
                    </div>

                    <!-- Action Button & Status -->
                    <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <a href="student-apply.php" class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white font-bold rounded-xl text-sm transition-all">
                                <span>আরও জানতে দেখুন</span>
                            </a>
                        </div>
                        <div>
                            <?php if ($is_student_plan_active): ?>
                                <a href="student-apply.php" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-emerald-600 text-white font-bold rounded-xl text-sm shadow-md">
                                    <span>✓ মেম্বারশিপ সক্রিয় আছে</span>
                                </a>
                            <?php elseif ($has_pending_student_req): ?>
                                <a href="student-apply.php" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-amber-500 text-brand-900 font-bold rounded-xl text-sm shadow-md animate-pulse">
                                    <span>⏳ রিকোয়েস্ট পেন্ডিং রয়েছে</span>
                                </a>
                            <?php else: ?>
                                <a href="student-apply.php" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-brand-900 hover:bg-brand-gold hover:text-brand-900 text-white font-bold rounded-xl transition-all text-sm shadow-lg">
                                    <span>পাঠযাত্রায় যুক্ত হন &rarr;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-10">
            <span class="text-brand-gold text-xs font-bold uppercase tracking-widest block mb-1.5 font-anek">অন্যান্য মেম্বারশিপ</span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-900 font-anek">সাধারণ ও নিয়মিত পাঠকদের জন্য প্ল্যান</h2>
        </div>

        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 items-stretch">
            <!-- General Reader Plan -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-md border border-gray-100 hover:shadow-xl transition-all duration-300 reveal flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-xl font-anek font-bold text-brand-900 mb-1">সাধারণ পাঠক</h3>
                    <p class="text-gray-500 text-xs sm:text-sm mb-4 font-anek">বই ও সাহিত্যের সান্নিধ্যে যারা থাকতে ভালোবাসেন।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-900 font-anek">৳৫০০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-600 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৫% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=General"
                    class="block text-center w-full py-3 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-md text-sm sm:text-base">
                    <?php 
                        if ($is_paid_plan_active && $current_user_plan === 'General') echo 'রিনিউ করুন (মেয়াদ বৃদ্ধি)';
                        elseif ($is_paid_plan_active) echo 'এই প্ল্যানে পরিবর্তন';
                        else echo 'প্ল্যানটি বেছে নিন';
                    ?>
                </a>
            </div>

            <!-- Regular Reader Plan (Featured) -->
            <div class="bg-brand-900 p-6 sm:p-7 rounded-2xl shadow-xl relative border border-brand-gold/40 hover:border-brand-gold transition-all duration-300 reveal flex flex-col justify-between h-full" style="transition-delay: 100ms;">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold text-brand-900 px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider font-anek shadow-md">
                    সর্বাধিক জনপ্রিয়
                </div>
                <div>
                    <h3 class="text-xl font-anek font-bold text-white mb-1">নিয়মিত পাঠক</h3>
                    <p class="text-gray-400 text-xs sm:text-sm mb-4 font-anek">যাদের নিত্যদিনের সঙ্গী প্রিয় বই।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-gold font-anek">৳৭৫০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-300 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ ও ব্র্যান্ডেড টি-শার্ট</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৮% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=BookLover"
                    class="block text-center w-full py-3 rounded-xl bg-brand-gold text-brand-900 font-anek font-bold hover:bg-white transition-all shadow-md text-sm sm:text-base">
                    <?php 
                        if ($is_paid_plan_active && $current_user_plan === 'BookLover') echo 'রিনিউ করুন (মেয়াদ বৃদ্ধি)';
                        elseif ($is_paid_plan_active) echo 'এই প্ল্যানে আপগ্রেড করুন';
                        else echo 'প্ল্যানটি বেছে নিন';
                    ?>
                </a>
            </div>

            <!-- Literature Enthusiast Plan -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-md border border-gray-100 hover:shadow-xl transition-all duration-300 reveal flex flex-col justify-between h-full" style="transition-delay: 200ms;">
                <div>
                    <h3 class="text-xl font-anek font-bold text-brand-900 mb-1">সাহিত্য অনুরাগী</h3>
                    <p class="text-gray-500 text-xs sm:text-sm mb-4 font-anek">প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-900 font-anek">৳১০০০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-600 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ, ব্র্যান্ডেড টি-শার্ট, বুকমার্ক ও কীরিং</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ১০% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=Collector"
                    class="block text-center w-full py-3 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-md text-sm sm:text-base">
                    <?php 
                        if ($is_paid_plan_active && $current_user_plan === 'Collector') echo 'রিনিউ করুন (মেয়াদ বৃদ্ধি)';
                        elseif ($is_paid_plan_active) echo 'এই প্ল্যানে আপগ্রেড করুন';
                        else echo 'প্ল্যানটি বেছে নিন';
                    ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>

</html>