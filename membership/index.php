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
        <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.3em] mb-4 block">এক্সক্লুসিভ
            অ্যাক্সেস</span>
        <h1 class="text-5xl md:text-7xl font-anek font-extrabold text-white mb-6">আমাদের মেম্বারশিপ প্ল্যান</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg md:text-xl font-light leading-relaxed">
            বই পড়ার অনন্য অভিজ্ঞতা পেতে এবং আমাদের লাইব্রেরির বিশাল সংগ্রহশালা থেকে বই ধার নিতে আপনার পছন্দের
            প্ল্যানটি বেছে নিন।
        </p>

        <?php if ($current_user_plan != 'None'): ?>
            <div
                class="mt-12 bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 max-w-lg mx-auto animate-slide-up">
                <span class="text-brand-gold text-xs font-bold uppercase tracking-widest mb-1 block">আপনার বর্তমান
                    প্ল্যান</span>
                <h3 class="text-2xl font-anek font-bold text-white">
                    <?php
                    $plan_names = [
                        'General' => 'সাধারণ পাঠক (৳২০০)',
                        'BookLover' => 'বইপ্রেমী (৳৫০০)',
                        'Collector' => 'সংগ্রাহক (৳১০০০)'
                    ];
                    echo $plan_names[$current_user_plan] ?? $current_user_plan;
                    ?>
                </h3>
                <p class="text-gray-300 text-sm mt-1">সবুজ বাতির মতো আপনার মেম্বারশিপ এখন সক্রিয় আছে।</p>
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
            <div class="flex flex-col items-center text-center group reveal">
                <div
                    class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">১</span>
                </div>
                <h3 class="font-anek font-bold text-xl text-brand-900 mb-2">প্ল্যান বেছে নিন</h3>
                <p class="text-gray-500 text-sm font-light">আপনার পড়ার মাত্রা অনুযায়ী পছন্দসই প্যাকেজ সিলেক্ট করুন।
                </p>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center text-center group reveal" style="transition-delay: 100ms;">
                <div
                    class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">২</span>
                </div>
                <h3 class="font-anek font-bold text-xl text-brand-900 mb-2">তথ্য প্রদান</h3>
                <p class="text-gray-500 text-sm font-light">আপনার নাম, ঠিকানা এবং প্রয়োজনীয় তথ্য দিয়ে ফর্মটি পূরণ
                    করুন।</p>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center text-center group reveal" style="transition-delay: 200ms;">
                <div
                    class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৩</span>
                </div>
                <h3 class="font-anek font-bold text-xl text-brand-900 mb-2">পেমেন্ট নিশ্চিত</h3>
                <p class="text-gray-500 text-sm font-light">অনলাইন পেমেন্টের মাধ্যমে আপনার মেম্বারশিপ ফি প্রদান
                    করুন।</p>
            </div>
            <!-- Step 4 -->
            <div class="flex flex-col items-center text-center group reveal" style="transition-delay: 300ms;">
                <div
                    class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৪</span>
                </div>
                <h3 class="font-anek font-bold text-xl text-brand-900 mb-2">কার্ড সংগ্রহ</h3>
                <p class="text-gray-500 text-sm font-light">আপনার ডিজিটাল বা ফিজিক্যাল লাইব্রেরি কার্ডটি বুঝে নিন।
                </p>
            </div>
            <!-- Step 5 -->
            <div class="flex flex-col items-center text-center group reveal" style="transition-delay: 400ms;">
                <div
                    class="w-20 h-20 rounded-2xl bg-brand-light border border-gray-100 flex items-center justify-center mb-6 shadow-sm group-hover:bg-brand-gold group-hover:text-brand-900 transition-all duration-500">
                    <span class="text-3xl font-bold font-anek">৫</span>
                </div>
                <h3 class="font-anek font-bold text-xl text-brand-900 mb-2">বই পড়া শুরু</h3>
                <p class="text-gray-500 text-sm font-light">এখন আপনি যেকোনও বই পড়ার এবং ধার নেওয়ার জন্য তৈরি!</p>
            </div>
        </div>

        <!-- Connection Line (Hidden on mobile) -->
        <div class="hidden md:block absolute top-[230px] left-1/2 -translate-x-1/2 w-[70%] h-px bg-gray-100 -z-10">
        </div>
    </div>
</section>

<!-- Pricing Area -->
<section class="py-24 bg-brand-light overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <!-- Basic Plan -->
            <div
                class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 hover:shadow-2xl transition-all duration-500 reveal">
                <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সাধারণ পাঠক</h3>
                <p class="text-gray-500 text-sm mb-8">মাঝে মাঝে পড়তে যারা ভালোবাসেন।</p>
                <div class="flex items-baseline gap-1 mb-8">
                    <span class="text-5xl font-bold text-brand-900 font-anek">৳২০০</span>
                    <span class="text-gray-400 text-sm font-anek">/মাস</span>
                </div>
                <ul class="space-y-4 mb-10 text-gray-600 font-anek">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        একসাথে ২টি বই ধার
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        বই কেনায় ৫% ছাড়
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        ডিজিটাল অ্যাক্সেস
                    </li>
                </ul>
                <button onclick="openModal('সাধারণ পাঠক')"
                    class="w-full py-4 rounded-xl border-2 border-brand-900 text-brand-900 font-bold hover:bg-brand-900 hover:text-white transition-all duration-300">শুরু
                    করুন</button>
            </div>

            <!-- Pro Plan -->
            <div class="bg-brand-900 p-12 rounded-3xl shadow-2xl relative transform scale-105 border border-white/10 reveal"
                style="transition-delay: 100ms;">
                <div
                    class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold text-brand-900 px-6 py-2 rounded-full text-xs font-bold uppercase tracking-widest">
                    সর্বাধিক জনপ্রিয়</div>
                <h3 class="text-2xl font-anek font-bold text-white mb-2">বইপ্রেমী</h3>
                <p class="text-gray-400 text-sm mb-8">যাদের দিন শুরু হয় বই দিয়ে।</p>
                <div class="flex items-baseline gap-1 mb-8">
                    <span class="text-5xl font-bold text-white font-anek text-gradient-gold">৳৫০০</span>
                    <span class="text-gray-400 text-sm font-anek">/মাস</span>
                </div>
                <ul class="space-y-4 mb-10 text-gray-300 font-anek">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        একসাথে ৫টি বই ধার
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        বই কেনায় ১৫% ছাড়
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        ফ্রি হোম ডেলিভারি
                    </li>
                    <li class="flex items-center gap-3 text-brand-gold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        অডিওবুক অ্যাক্সেস
                    </li>
                </ul>
                <button onclick="openModal('বইপ্রেমী')"
                    class="w-full py-4 rounded-xl bg-brand-gold text-brand-900 font-bold hover:bg-white transition-all duration-300 shadow-lg shadow-brand-gold/20">সাবস্ক্রাইব
                    করুন</button>
            </div>

            <!-- Collector Plan -->
            <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 hover:shadow-2xl transition-all duration-500 reveal"
                style="transition-delay: 200ms;">
                <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সংগ্রাহক</h3>
                <p class="text-gray-500 text-sm mb-8">প্রকৃত বই সংগ্রাহকদের জন্য।</p>
                <div class="flex items-baseline gap-1 mb-8">
                    <span class="text-5xl font-bold text-brand-900 font-anek">৳১০০০</span>
                    <span class="text-gray-400 text-sm font-anek">/মাস</span>
                </div>
                <ul class="space-y-4 mb-10 text-gray-600 font-anek">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        আনলিমিটেড বই ধার
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        বই কেনায় ২৫% ছাড়
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        ভিআইপি লাউঞ্জ অ্যাক্সেস
                    </li>
                </ul>
                <button onclick="openModal('সংগ্রাহক')"
                    class="w-full py-4 rounded-xl border-2 border-brand-900 text-brand-900 font-bold hover:bg-brand-900 hover:text-white transition-all duration-300">শুরু
                    করুন</button>
            </div>
        </div>
    </div>
</section>

<!-- Membership Registration Modal -->
<div id="registration-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-brand-900/80 backdrop-blur-md transition-opacity" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl relative z-10 overflow-hidden animate-slide-up">
        <div class="bg-brand-gold p-6 flex justify-between items-center">
            <h3 class="text-2xl font-anek font-bold text-brand-900">
                <?php echo isset($_SESSION['user_id']) ? 'মেম্বারশিপ সাবস্ক্রিপশন' : 'মেম্বারশিপ রেজিস্ট্রেশন'; ?>
            </h3>
            <button onclick="closeModal()" class="text-brand-900/50 hover:text-brand-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <?php if (isset($_GET['subscription']) && $_GET['subscription'] == 'success'): ?>
            <div class="p-8 text-center">
                <div
                    class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h4 class="text-2xl font-anek font-bold text-brand-900 mb-2">অভিনন্দন!</h4>
                <p class="text-gray-500 mb-8">আপনার মেম্বারশিপ প্ল্যান সফলভাবে আপডেট করা হয়েছে। পরবর্তী ৩০ দিন আপনি এই
                    সুযোগ-সুবিধাগুলো উপভোগ করবেন।</p>
                <a href="../dashboard/"
                    class="block w-full py-4 bg-brand-900 text-white font-anek font-bold rounded-xl">ড্যাশবোর্ডে ফিরে
                    যান</a>
            </div>
        <?php else: ?>
            <form
                action="<?php echo isset($_SESSION['user_id']) ? 'process_subscription.php' : '../signup/process_signup.php'; ?>"
                method="POST" id="membership-form" class="p-8 space-y-4">

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সম্পূর্ণ
                            নাম</label>
                        <input type="text" name="full_name" required placeholder="Ex: Sayeam Ahmed"
                            class="w-full bg-brand-light border border-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-gold font-anek">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">মোবাইল</label>
                            <input type="tel" name="phone" required placeholder="০১XXXXXXXXX"
                                class="w-full bg-brand-light border border-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-gold font-anek">
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ইমেইল</label>
                            <input type="email" name="email" required placeholder="name@mail.com"
                                class="w-full bg-brand-light border border-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-gold font-anek">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full bg-brand-light border border-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-gold font-anek">
                    </div>
                <?php endif; ?>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">Selected
                        Plan</label>
                    <input type="text" id="selected-plan-display" readonly
                        class="w-full bg-gray-100 border border-transparent rounded-xl px-4 py-3 text-brand-900 font-bold font-anek focus:outline-none cursor-default">
                    <input type="hidden" name="plan" id="selected-plan-value">
                </div>
                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-4 bg-brand-900 text-white font-anek font-bold text-lg rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10">নিবন্ধন
                        সম্পন্ন করুন</button>
                </div>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <p class="text-[10px] text-gray-400 text-center uppercase tracking-wider">তথ্যগুলো আপনার লগইন এবং মেম্বারশিপ
                        আইডিতে ব্যবহৃত হবে</p>
                <?php else: ?>
                    <p class="text-[10px] text-gray-400 text-center uppercase tracking-wider">এই প্ল্যানটি ৩০ দিনের জন্য কার্যকর
                        হবে</p>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    const modal = document.getElementById('registration-modal');
    const planDisplay = document.getElementById('selected-plan-display');
    const planValue = document.getElementById('selected-plan-value');

    const planMap = {
        'সাধারণ পাঠক': 'General',
        'বইপ্রেমী': 'BookLover',
        'সংগ্রাহক': 'Collector'
    };

    function openModal(planName) {
        planDisplay.value = planName;
        planValue.value = planMap[planName] || 'General';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
</script>

</body>

</html>