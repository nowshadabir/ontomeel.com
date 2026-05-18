<?php
$page_title = 'মেম্বারশিপ | অন্ত্যমিল';
$path_prefix = '../';
include '../includes/db_connect.php';
include '../includes/header.php';

$current_user_plan = 'None';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT membership_plan FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $current_user_plan = $user_data['membership_plan'];
    }
}
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
            <p class="text-brand-gold font-anek font-bold tracking-wide text-sm md:text-base">আমাদের মেম্বারশিপ প্রোগ্রাম এখন উন্মুক্ত! আজই যুক্ত হোন অন্ত্যমিল পরিবারে。</p>
        </div>

        <?php if (isset($_GET['request']) && $_GET['request'] == 'success'): ?>
            <div class="mt-6 bg-green-500/20 border border-green-500/40 backdrop-blur-md rounded-2xl p-6 max-w-2xl mx-auto shadow-2xl">
                <p class="text-green-300 font-anek font-bold text-lg">আপনার মেম্বারশিপ রিকোয়েস্ট সফলভাবে সাবমিট হয়েছে। আমাদের এডমিন প্যানেল থেকে ভেরিফাই করে খুব শীঘ্রই আপনার মেম্বারশিপ অ্যাক্টিভ করা হবে।</p>
            </div>
        <?php endif; ?>

        <?php if ($current_user_plan != 'None'): ?>
            <div class="mt-12 bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-8 max-w-lg mx-auto shadow-2xl">
                <span class="text-brand-gold text-xs font-bold uppercase tracking-widest mb-1 block font-anek">আপনার বর্তমান প্ল্যান</span>
                <h3 class="text-3xl font-anek font-extrabold text-white mb-2">
                    <?php
                    $plan_names = [
                        'General' => 'সাধারণ পাঠক (৳৫০০)',
                        'BookLover' => 'নিয়মিত পাঠক (৳১০০০)',
                        'Collector' => 'সাহিত্য অনুরাগী (৳১৫০০)'
                    ];
                    echo $plan_names[$current_user_plan] ?? $current_user_plan;
                    ?>
                </h3>
                <div class="inline-flex items-center gap-2 bg-green-500/20 border border-green-500/30 px-4 py-1.5 rounded-full mt-2">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <p class="text-green-300 text-xs font-anek font-bold tracking-wide">আপনার মেম্বারশিপ এখন সক্রিয় আছে</p>
                </div>
            </div>
        <?php endif; ?>
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
<section class="py-24 bg-brand-light overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <!-- General Reader Plan -->
            <div class="bg-white p-10 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সাধারণ পাঠক</h3>
                    <p class="text-gray-500 text-sm mb-8 font-anek">বই ও কফির আড্ডায় যারা মেতে উঠতে ভালোবাসেন।</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-brand-900 font-anek">৳৫০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-600 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>ব্র্যান্ডেড টি-শার্ট</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে ১০% ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>কミュニটি লাইব্রেরি থেকে বই ধার নেওয়ার সুযোগ</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=General"
                    class="block text-center w-full py-4 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Regular Reader Plan (Featured) -->
            <div class="bg-brand-900 p-12 rounded-3xl shadow-2xl relative transform lg:scale-105 border border-brand-gold/30 hover:border-brand-gold hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full" style="transition-delay: 100ms;">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold text-brand-900 px-6 py-2 rounded-full text-xs font-bold uppercase tracking-widest font-anek shadow-xl">
                    সর্বাধিক জনপ্রিয়
                </div>
                <div>
                    <h3 class="text-2xl font-anek font-bold text-white mb-2">নিয়মিত পাঠক</h3>
                    <p class="text-gray-400 text-sm mb-8 font-anek">যাদের নিত্যদিনের সঙ্গী প্রিয় বই。</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-white font-anek text-gradient-gold">৳১০০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-300 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>ব্র্যান্ডেড টি-শার্ট ও টোট ব্যাগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>সাধারণ পাঠকের সব সুবিধা + অতিরিক্ত ৫% (মোট ১৫%) ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>ক্রাফট কাউন্টার থেকে কেনাকাটার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>চমকপ্রদ বুকমার্ক/সাহিত্যিক পোস্টকার্ড</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=BookLover"
                    class="block text-center w-full py-4 rounded-xl bg-brand-gold text-brand-900 font-anek font-bold hover:bg-white transition-all shadow-xl shadow-brand-gold/20 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Literature Enthusiast Plan -->
            <div class="bg-white p-10 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full" style="transition-delay: 200ms;">
                <div>
                    <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সাহিত্য অনুরাগী</h3>
                    <p class="text-gray-500 text-sm mb-8 font-anek">প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য。</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-brand-900 font-anek">৳১৫০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-600 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>ব্র্যান্ডেড টি-শার্ট, টোট ব্যাগ ও মগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নিয়মিত পাঠকের সব সুবিধা + অতিরিক্ত ৫% (মোট ২০%) ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>কাট-ফ্লাওয়ার কাউন্টার থেকে কেনাকাটার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>প্রিমিয়াম ধার নেওয়ার অধিকার (বর্ধিত সময়, অগ্রাধিকারমূলক শিরোনাম)</span>
                        </li>
                    </ul>
                </div>
                <a href="request.php?plan=Collector"
                    class="block text-center w-full py-4 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>

</html>