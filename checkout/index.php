<?php
require_once '../includes/db_connect.php';
$page_title = 'চেকআউট | অন্ত্যমিল অনলাইন বুকশপ';
$path_prefix = '../';
$is_checkout = true;
$additional_head = '
    <style>
        .payment-card.active {
            border-color: #cda873;
            background-color: #fef9f1;
        }

        @keyframes confetti {
            0% {
                transform: translateY(0) rotate(0);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #cda873;
            animation: confetti 3s ease-out forwards;
        }
    </style>';
include '../includes/header.php';

$user_balance = 0;
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    $user_balance = $user_data['acc_balance'] ?? 0;
}

$checkout_type = $_GET['type'] ?? 'buy'; // 'buy' or 'borrow'

// Fetch Active Payment Methods
$payments_stmt = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY id ASC");
$active_payment_methods = $payments_stmt->fetchAll();

// Helper to get settings
function getSetting($pdo, $key, $defaultValue = '')
{
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $defaultValue;
    }
    catch (Exception $e) {
        return $defaultValue;
    }
}

$inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
$outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);

// Load Bangladesh Geographic Data (Divisions, Districts, Upazilas) from JSON files
$divisions_data = file_exists(__DIR__ . '/../bd-divisions.json') ? file_get_contents(__DIR__ . '/../bd-divisions.json') : '{"divisions":[]}';
$districts_data = file_exists(__DIR__ . '/../bd-districts.json') ? file_get_contents(__DIR__ . '/../bd-districts.json') : '{"districts":[]}';
$upazilas_data = file_exists(__DIR__ . '/../bd-upazilas.json') ? file_get_contents(__DIR__ . '/../bd-upazilas.json') : '{"upazilas":[]}';
?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">

        <!-- Left: Form -->
        <div class="flex-1 space-y-12">
            <section>
                <h2 class="text-2xl font-anek font-bold text-brand-900 mb-8 flex items-center gap-4">
                    <span
                        class="w-10 h-10 bg-brand-900 text-white rounded-full flex items-center justify-center text-sm">১</span>
                    ডেলিভারি ঠিকানা
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">আপনার নাম
                            *</label>
                        <input type="text" id="cust-name" required placeholder="পুরো নাম লিখুন"
                            value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">মোবাইল
                            নম্বর *</label>
                        <input type="tel" id="cust-phone" required placeholder="017XXXXXXXX"
                            value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium tracking-wider">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">আপনার ইমেইল *</label>
                        <input type="email" id="cust-email" required placeholder="email@example.com"
                            value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium tracking-wide">
                    </div>

                    <!-- Geographic Dropdowns: Division, District, Upazila -->
                    <div class="md:col-span-2 space-y-3 pt-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2 block">ডেলিভারি এলাকা নির্বাচন করুন *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Division Select -->
                            <div class="space-y-1">
                                <span class="text-[11px] text-gray-500 font-bold ml-1">বিভাগ (Division)</span>
                                <div class="relative">
                                    <select id="cust-division" onchange="onDivisionChange()"
                                        class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3.5 text-brand-900 font-semibold focus:outline-none focus:border-brand-gold transition-all font-anek text-sm appearance-none cursor-pointer pr-9">
                                        <option value="">বিভাগ নির্বাচন করুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- District Select -->
                            <div class="space-y-1">
                                <span class="text-[11px] text-gray-500 font-bold ml-1">জেলা (District)</span>
                                <div class="relative">
                                    <select id="cust-district" onchange="onDistrictChange()" disabled
                                        class="w-full bg-gray-100 border border-gray-200 rounded-2xl px-4 py-3.5 text-brand-900 font-semibold focus:outline-none focus:border-brand-gold transition-all font-anek text-sm appearance-none cursor-pointer pr-9 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">প্রথমে বিভাগ বাছুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Upazila Select -->
                            <div class="space-y-1">
                                <span class="text-[11px] text-gray-500 font-bold ml-1">উপজেলা / থানা (Upazila)</span>
                                <div class="relative">
                                    <select id="cust-upazila" onchange="onUpazilaChange()" disabled
                                        class="w-full bg-gray-100 border border-gray-200 rounded-2xl px-4 py-3.5 text-brand-900 font-semibold focus:outline-none focus:border-brand-gold transition-all font-anek text-sm appearance-none cursor-pointer pr-9 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">প্রথমে জেলা বাছুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Charge Status Badge -->
                        <div id="delivery-badge" class="pt-1">
                            <?php if ($checkout_type == 'borrow'): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 border border-green-200 text-green-700 rounded-full text-xs font-bold font-anek">
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    বই ধার নেওয়ায় ডেলিভারি চার্জ প্রযোজ্য নয়
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-xs font-bold font-anek">
                                    <span class="w-2 h-2 rounded-full bg-brand-gold"></span>
                                    কক্সবাজার সদর: ৳<?php echo $inside_charge; ?> | অন্যান্য এলাকা: ৳<?php echo $outside_charge; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">সম্পূর্ণ ঠিকানা (বাসা/রোড/এলাকা/গ্রাম) *</label>
                        <input type="text" id="cust-address" required placeholder="বাসা/হোল্ডিং নং, রোড, এলাকা বা গ্রামের নাম লিখুন..."
                            value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium">
                    </div>
                </div>
            </section>

            <?php if ($checkout_type == 'buy'): ?>
                <section>
                    <h2 class="text-2xl font-anek font-bold text-brand-900 mb-8 flex items-center gap-4">
                        <span
                            class="w-10 h-10 bg-brand-900 text-white rounded-full flex items-center justify-center text-sm">২</span>
                        পেমেন্ট পদ্ধতি
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($active_payment_methods as $method): ?>
                            <?php if ($method['method_key'] == 'bkash'): ?>
                                <!-- Option: bKash -->
                                <div onclick="selectPayment('bkash')" id="pay-bkash"
                                    class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-[#D12053]/50 transition-all flex items-center justify-between group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center shrink-0">
                                            <div id="dot-bkash" class="w-2.5 h-2.5 bg-[#D12053] rounded-full hidden"></div>
                                        </div>
                                        <span
                                            class="font-anek font-bold text-brand-900 group-hover:text-[#D12053] transition-colors">বিকাশ
                                            পেমেন্ট</span>
                                    </div>
                                    <img src="../assets/img/bkash-logo.jpg" alt="bkash"
                                        class="h-8 grayscale group-hover:grayscale-0 transition-all opacity-40 group-hover:opacity-100"
                                        onerror="this.src='https://raw.githubusercontent.com/bikashpoudel/bkash-logo/master/bkash_logo.webp'">
                                </div>
                            <?php
        elseif ($method['method_key'] == 'nagad'): ?>
                                <!-- Option: Nagad -->
                                <div onclick="selectPayment('nagad')" id="pay-nagad"
                                    class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-[#EF1F23]/50 transition-all flex items-center justify-between group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center shrink-0">
                                            <div id="dot-nagad" class="w-2.5 h-2.5 bg-[#EF1F23] rounded-full hidden"></div>
                                        </div>
                                        <span
                                            class="font-anek font-bold text-brand-900 group-hover:text-[#EF1F23] transition-colors">নগদ
                                            পেমেন্ট</span>
                                    </div>
                                    <img src="../assets/img/nagad-logo.jpg" alt="nagad"
                                        class="h-8 grayscale group-hover:grayscale-0 transition-all opacity-40 group-hover:opacity-100"
                                        onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'">
                                </div>
                            <?php
        elseif ($method['method_key'] == 'cod'): ?>
                                <!-- Option: COD -->
                                <div onclick="selectPayment('cod')" id="pay-cod"
                                    class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-brand-900/50 transition-all flex items-center justify-between group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center shrink-0">
                                            <div id="dot-cod" class="w-2.5 h-2.5 bg-brand-900 rounded-full hidden"></div>
                                        </div>
                                        <span class="font-anek font-bold text-brand-900">ক্যাশ অন ডেলিভারি</span>
                                    </div>
                                    <svg class="w-8 h-8 text-gray-200 group-hover:text-brand-900 transition-colors" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                            <?php
        elseif ($method['method_key'] == 'sslcommerz'): ?>
                                <!-- Option: SSLCommerz -->
                                <div onclick="selectPayment('sslcommerz')" id="pay-sslcommerz"
                                    class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-brand-gold/50 transition-all flex items-center justify-between group">
                                    <div class="flex items-center gap-3 min-w-0 pr-2">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center shrink-0">
                                            <div id="dot-sslcommerz" class="w-2.5 h-2.5 bg-brand-gold rounded-full hidden"></div>
                                        </div>
                                        <span
                                            class="font-anek font-bold text-brand-900 group-hover:text-brand-gold transition-colors text-sm truncate">কার্ড / মোবাইল ব্যাংকিং</span>
                                    </div>
                                    <svg class="w-8 h-8 text-gray-200 group-hover:text-brand-gold transition-colors shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                </div>
                            <?php
        endif; ?>
                        <?php
    endforeach; ?>

                        <?php if (empty($active_payment_methods)): ?>
                            <div class="p-6 bg-red-50 border border-red-100 rounded-3xl col-span-2">
                                <p class="text-sm text-red-600 font-anek text-center">আপাতত পেমেন্ট গেটওয়ে বন্ধ আছে। অনুগ্রহ করে
                                    পরে চেষ্টা করুন।</p>
                            </div>
                        <?php
    endif; ?>
                    </div>
                </section>
            <?php
else: ?>
                <input type="hidden" id="borrow-mode" value="true">
                <section class="bg-brand-900 p-8 rounded-[32px] text-white">
                    <h2 class="text-xl font-anek font-bold mb-4 flex items-center gap-3 text-brand-gold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        মেম্বারশিপ সুবিধা: ফ্রী ধার
                    </h2>
                    <p class="text-sm text-gray-300 font-anek leading-relaxed">
                        আপনি একজন <span
                            class="text-brand-gold font-bold"><?php echo htmlspecialchars($user_data['membership_plan'] ?? 'General'); ?></span>
                        মেম্বার হিসেবে এই বইগুলো বিনামূল্যে ধার নিতে পারছেন। ৩০ দিন পর বইগুলো ফেরত দিতে হবে।
                    </p>
                </section>
            <?php
endif; ?>
        </div>

        <!-- Right: Order Summary -->
        <div class="w-full lg:w-[400px]">
            <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-xl shadow-brand-900/5 sticky top-32">
                <h3 class="text-xl font-anek font-bold text-brand-900 mb-8 pb-4 border-b border-gray-50">অর্ডার
                    সামারি</h3>

                <div id="checkout-items" class="space-y-6 mb-10 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <!-- Items will be injected here -->
                </div>

                <div class="space-y-4 pt-6 border-t border-gray-50">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400 font-anek">উপ-মোট</span>
                        <span id="sub-total" class="font-bold text-brand-900">৳০</span>
                    </div>
                    <?php if ($checkout_type == 'buy'): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 font-anek">ডেলিভারি চার্জ</span>
                            <span id="display-delivery" class="font-bold text-brand-900 font-anek">৳<?php echo $inside_charge; ?></span>
                        </div>
                    <?php
endif; ?>
                    <div class="flex justify-between text-xl pt-4">
                        <span class="font-anek font-bold text-brand-900">সর্বমোট</span>
                        <span id="grand-total" class="font-bold text-brand-gold">৳০</span>
                    </div>
                </div>

                <div class="mt-6 mb-4 flex items-start gap-3">
                    <input type="checkbox" id="terms-agree" class="mt-1 w-4 h-4 text-brand-gold rounded border-gray-300 focus:ring-brand-gold cursor-pointer" required>
                    <label for="terms-agree" class="text-xs text-gray-600 font-anek leading-relaxed cursor-pointer">
                        আমি অন্ত্যমিলের <a href="<?php echo $path_prefix ?? ''; ?>terms.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">শর্তাবলী</a>, <a href="<?php echo $path_prefix ?? ''; ?>privacy.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">প্রাইভেসি পলিসি</a> এবং <a href="<?php echo $path_prefix ?? ''; ?>refund.php" target="_blank" class="text-brand-900 font-bold underline hover:text-brand-gold">রিটার্ন ও রিফান্ড নীতি</a> পড়েছি এবং সম্মত আছি।
                    </label>
                </div>

                <button onclick="confirmOrder()"
                    class="w-full mt-2 bg-brand-900 text-white py-5 rounded-[20px] font-anek font-bold text-lg hover:bg-brand-gold hover:text-brand-900 transition-all duration-500 shadow-xl shadow-brand-900/20 flex items-center justify-center gap-3">
                    <span><?php echo($checkout_type == 'borrow') ? 'ধার নিশ্চিত করুন' : 'অর্ডার কনফার্ম করুন'; ?></span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>

                <p class="text-[10px] text-center text-gray-400 uppercase tracking-widest mt-6">৭ দিনের মধ্যে
                    রিটার্ন পলিসি প্রযোজ্য</p>
            </div>
        </div>
    </div>
</main>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6">
    <div class="absolute inset-0 bg-brand-900/60"
        id="modal-overlay"></div>

    <div class="bg-white w-full max-w-lg rounded-[60px] p-12 text-center relative z-10 scale-90 opacity-0 transition-all duration-700"
        id="modal-content">
        <div
            class="w-24 h-24 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h2 class="text-3xl font-anek font-bold text-brand-900 mb-4">অর্ডার সফল হয়েছে!</h2>
        <p class="text-gray-500 font-anek mb-8">আমাদের সাথে থাকার জন্য ধন্যবাদ। আপনার অর্ডারটি শীঘ্রই ডেলিভারি দেওয়া
            হবে।</p>

        <div class="bg-brand-light p-6 rounded-3xl mb-10 border border-brand-gold/10">
            <p class="text-[10px] text-brand-gold font-bold uppercase tracking-[0.3em] mb-2">অর্ডার নাম্বার</p>
            <h3 id="order-id-display" class="text-3xl font-mono font-bold text-brand-900 tracking-wider">
                #ORD-26-XXXX</h3>
        </div>

        <a href="../index.php"
            class="inline-block w-full bg-brand-900 text-white py-5 rounded-2xl font-anek font-bold text-lg hover:bg-brand-gold hover:text-brand-900 transition-all">হোম
            পেজে ফিরে যান</a>
    </div>
</div>

<!-- Confetti Container -->
<div id="confetti-container" class="fixed inset-0 pointer-events-none z-[110]"></div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- bKash Script -->
<script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>
<script src="../bkash/bkash-helper.js"></script>

<!-- Toast Notification -->
<div id="toast"
    class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[200] flex items-center gap-4 bg-brand-900 text-white px-8 py-4 rounded-2xl shadow-2xl transition-all duration-500 translate-y-20 opacity-0 invisible">
    <div class="w-2 h-2 rounded-full bg-brand-gold shadow-[0_0_10px_#cda873]"></div>
    <span id="toast-message" class="font-anek font-bold text-sm tracking-wide"></span>
</div>

<script>
    let isOrderSubmitting = false;
    const checkoutType = "<?php echo $checkout_type; ?>";
    const cartItems = JSON.parse(localStorage.getItem(checkoutType === 'borrow' ? 'antyam_borrow_cart' : 'antyam_cart') || '[]');
    let selectedPayMethod = checkoutType === 'borrow' ? 'borrow' : 'cod';

    const insideCharge = <?php echo $inside_charge; ?>;
    const outsideCharge = <?php echo $outside_charge; ?>;
    let currentDeliveryCharge = insideCharge;
    let subTotal = 0;

    // Bangladesh Geographic Data
    const geoData = {
        divisions: <?php echo $divisions_data; ?>.divisions || [],
        districts: <?php echo $districts_data; ?>.districts || [],
        upazilas: <?php echo $upazilas_data; ?>.upazilas || []
    };

    function formatGeoName(item) {
        if (!item) return '';
        const bn = item.bn_name || '';
        const en = (item.name && !item.name.includes('{{')) ? item.name : '';
        if (bn && en) return `${bn} (${en})`;
        return bn || en || '';
    }

    function initGeoDropdowns() {
        const divSelect = document.getElementById('cust-division');
        if (!divSelect) return;
        divSelect.innerHTML = '<option value="">বিভাগ নির্বাচন করুন</option>';
        geoData.divisions.forEach(div => {
            const opt = document.createElement('option');
            opt.value = div.id;
            opt.textContent = formatGeoName(div);
            opt.dataset.name = div.name;
            opt.dataset.bn = div.bn_name;
            divSelect.appendChild(opt);
        });
    }

    function onDivisionChange() {
        const divSelect = document.getElementById('cust-division');
        const distSelect = document.getElementById('cust-district');
        const upzSelect = document.getElementById('cust-upazila');
        const selectedDivId = divSelect.value;

        distSelect.innerHTML = '<option value="">জেলা নির্বাচন করুন</option>';
        upzSelect.innerHTML = '<option value="">প্রথমে জেলা বাছুন</option>';
        upzSelect.disabled = true;
        upzSelect.classList.add('bg-gray-100');
        upzSelect.classList.remove('bg-white');

        if (!selectedDivId) {
            distSelect.disabled = true;
            distSelect.classList.add('bg-gray-100');
            distSelect.classList.remove('bg-white');
            recalculateDelivery();
            return;
        }

        const filteredDistricts = geoData.districts.filter(d => String(d.division_id) === String(selectedDivId));
        filteredDistricts.forEach(dist => {
            const opt = document.createElement('option');
            opt.value = dist.id;
            opt.textContent = formatGeoName(dist);
            opt.dataset.name = dist.name;
            opt.dataset.bn = dist.bn_name;
            distSelect.appendChild(opt);
        });

        distSelect.disabled = false;
        distSelect.classList.remove('bg-gray-100');
        distSelect.classList.add('bg-white');
        recalculateDelivery();
    }

    function onDistrictChange() {
        const distSelect = document.getElementById('cust-district');
        const upzSelect = document.getElementById('cust-upazila');
        const selectedDistId = distSelect.value;

        upzSelect.innerHTML = '<option value="">উপজেলা / থানা নির্বাচন করুন</option>';

        if (!selectedDistId) {
            upzSelect.disabled = true;
            upzSelect.classList.add('bg-gray-100');
            upzSelect.classList.remove('bg-white');
            recalculateDelivery();
            return;
        }

        const filteredUpazilas = geoData.upazilas.filter(u => String(u.district_id) === String(selectedDistId));
        filteredUpazilas.forEach(upz => {
            const opt = document.createElement('option');
            opt.value = upz.id;
            opt.textContent = formatGeoName(upz);
            opt.dataset.name = upz.name;
            opt.dataset.bn = upz.bn_name;
            upzSelect.appendChild(opt);
        });

        upzSelect.disabled = false;
        upzSelect.classList.remove('bg-gray-100');
        upzSelect.classList.add('bg-white');
        recalculateDelivery();
    }

    function onUpazilaChange() {
        recalculateDelivery();
    }

    function recalculateDelivery() {
        if (checkoutType === 'borrow') {
            currentDeliveryCharge = 0;
            updateDeliveryTotals(0);
            return;
        }

        const distSelect = document.getElementById('cust-district');
        const upzSelect = document.getElementById('cust-upazila');
        const selectedDistId = distSelect ? distSelect.value : '';
        const selectedUpzOpt = upzSelect && upzSelect.selectedIndex > 0 ? upzSelect.options[upzSelect.selectedIndex] : null;
        const selectedUpzText = selectedUpzOpt ? selectedUpzOpt.textContent : '';

        // District 45 is Cox's Bazar in bd-districts.json
        let isInside = false;
        if (selectedDistId === '45') {
            if (selectedUpzText.includes('সদর') || selectedUpzText.toLowerCase().includes('sadar')) {
                isInside = true;
            } else if (!upzSelect.value) {
                // Default to inside while Cox's Bazar is selected
                isInside = true;
            }
        }

        currentDeliveryCharge = isInside ? insideCharge : outsideCharge;
        updateDeliveryTotals(currentDeliveryCharge, isInside);
    }

    function updateDeliveryTotals(charge, isInside = false) {
        const total = subTotal + (checkoutType === 'borrow' ? 0 : charge);
        
        const dispDelivery = document.getElementById('display-delivery');
        if (dispDelivery) {
            dispDelivery.innerText = `৳${convertToBengaliNumber(charge)}`;
        }
        
        const grandTotal = document.getElementById('grand-total');
        if (grandTotal) {
            grandTotal.innerText = `৳${convertToBengaliNumber(total)}`;
        }

        const badge = document.getElementById('delivery-badge');
        if (badge && checkoutType !== 'borrow') {
            if (isInside) {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-xs font-bold font-anek">
                    <span class="w-2 h-2 rounded-full bg-brand-gold"></span>
                    কক্সবাজার শহর ডেলিভারি চার্জ: ৳${insideCharge}
                </span>`;
            } else {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-800 rounded-full text-xs font-bold font-anek">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    আউটসাইড কক্সবাজার (সারাদেশ) ডেলিভারি চার্জ: ৳${outsideCharge}
                </span>`;
            }
        }
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-message') || document.getElementById('toast-msg');
        if (toastMsg) toastMsg.innerText = message;
        if (toast) {
            toast.classList.remove('translate-y-20', 'opacity-0', 'invisible');
            toast.classList.add('translate-y-0', 'opacity-100', 'visible');

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'invisible');
                toast.classList.remove('translate-y-0', 'opacity-100', 'visible');
            }, 5000);
        }
    }


    function selectPayment(method) {
        selectedPayMethod = method;
        document.querySelectorAll('.payment-card').forEach(el => {
            el.classList.remove('active', 'border-brand-gold', 'border-[#D12053]', 'border-[#EF1F23]', 'border-brand-900');
            el.style.backgroundColor = 'white';
        });

        const targetCard = document.getElementById(`pay-${method}`);
        if (targetCard) {
            targetCard.classList.add('active');
            if (method === 'bkash') targetCard.classList.add('border-[#D12053]');
            else if (method === 'nagad') targetCard.classList.add('border-[#EF1F23]');
            else if (method === 'cod') targetCard.classList.add('border-brand-900');
            else targetCard.classList.add('border-brand-gold');
        }

        // Handle dots
        const dots = ['bkash', 'nagad', 'cod', 'sslcommerz'];
        dots.forEach(d => {
            const dot = document.getElementById(`dot-${d}`);
            if (dot) dot.classList.add('hidden');
        });

        const targetDot = document.getElementById(`dot-${method}`);
        if (targetDot) targetDot.classList.remove('hidden');
    }

    // Initialize default selection
    if (checkoutType !== 'borrow') {
        const firstActive = "<?php echo !empty($active_payment_methods) ? $active_payment_methods[0]['method_key'] : 'cod'; ?>";
        selectPayment(firstActive);
    }

    function convertToBengaliNumber(n) {
        const bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return n.toString().replace(/\d/g, d => bengaliDigits[d]);
    }

    function loadCheckout() {
        const container = document.getElementById('checkout-items');
        if (cartItems.length === 0) {
            window.location.href = '../index.php';
            return;
        }

        let total = 0;
        let anyOutOfStock = false;
        container.innerHTML = '';

        cartItems.forEach(item => {
            const displayPrice = (checkoutType === 'borrow') ? 0 : item.price;
            const isItemOutOfStock = item.isOutOfStock || false;
            if (isItemOutOfStock) anyOutOfStock = true;
            
            total += (isItemOutOfStock ? 0 : displayPrice);
            
            container.innerHTML += `
                    <div class="flex gap-4 items-center ${isItemOutOfStock ? 'opacity-60 grayscale' : ''}">
                        <div class="w-12 h-16 bg-gray-50 rounded shadow-sm overflow-hidden flex-shrink-0 relative">
                            <img src="${typeof getCorrectImagePath === 'function' ? getCorrectImagePath(item.img) : item.img}" class="w-full h-full object-cover">
                            ${isItemOutOfStock ? '<div class="absolute inset-0 bg-red-600/40 flex items-center justify-center"><p class="text-[8px] text-white font-bold">X</p></div>' : ''}
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-brand-900 font-anek text-sm truncate w-40">${item.title}</h4>
                            ${isItemOutOfStock ? '<p class="text-[9px] text-red-600 font-bold uppercase tracking-wider">সদ্য স্টক শেষ!</p>' : '<p class="text-[10px] text-gray-400 font-anek">পরিমাণ: ১টি</p>'}
                        </div>
                        <p class="font-bold text-brand-900 font-anek">৳${isItemOutOfStock ? '০' : convertToBengaliNumber(displayPrice)}</p>
                    </div>
                `;
        });

        const deliveryCharge = (checkoutType === 'borrow') ? 0 : currentDeliveryCharge;
        subTotal = total;
        document.getElementById('sub-total').innerText = `৳${convertToBengaliNumber(total)}`;
        document.getElementById('grand-total').innerText = `৳${convertToBengaliNumber(total + deliveryCharge)}`;
        
        // Disable order button if any item is out of stock
        const orderBtn = document.querySelector('button[onclick="confirmOrder()"]');
        if (orderBtn) {
            if (anyOutOfStock) {
                orderBtn.disabled = true;
                orderBtn.classList.replace('bg-brand-900', 'bg-gray-400');
                orderBtn.classList.add('cursor-not-allowed');
                orderBtn.innerHTML = '<span>স্টক শেষ (অর্ডার করা যাবে না)</span>';
                showToast('আপনার কার্টে থাকা কিছু বই স্টকে ফুরিয়ে গেছে। দয়া করে সেগুলো বাদ দিন।');
            } else {
                orderBtn.disabled = false;
                orderBtn.classList.replace('bg-gray-400', 'bg-brand-900');
                orderBtn.classList.remove('cursor-not-allowed');
                orderBtn.innerHTML = `<span>${checkoutType == 'borrow' ? 'ধার নিশ্চিত করুন' : 'অর্ডার কনফার্ম করুন'}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>`;
            }
        }
    }

    function createConfetti() {
        const container = document.getElementById('confetti-container');
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.backgroundColor = ['#cda873', '#0a0a0a', '#f5f5f5'][Math.floor(Math.random() * 3)];
            confetti.style.width = Math.random() * 10 + 5 + 'px';
            confetti.style.height = Math.random() * 10 + 5 + 'px';
            confetti.style.animationDelay = Math.random() * 2 + 's';
            container.appendChild(confetti);
        }
    }

    function confirmOrder() {
        if (isOrderSubmitting) return;

        const name = document.getElementById('cust-name').value.trim();
        const phone = document.getElementById('cust-phone').value.trim();
        const email = document.getElementById('cust-email').value.trim();
        const divSelect = document.getElementById('cust-division');
        const distSelect = document.getElementById('cust-district');
        const upzSelect = document.getElementById('cust-upazila');
        const addr = document.getElementById('cust-address').value.trim();

        if (!name || !phone || !email) {
            showToast('দয়া করে আপনার নাম, মোবাইল নম্বর এবং ইমেইল প্রদান করুন।');
            return;
        }

        const cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.length < 11) {
            showToast('সঠিক ১১ ডিজিটের মোবাইল নম্বর প্রদান করুন।');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('সঠিক ইমেইল এড্রেস প্রদান করুন।');
            return;
        }

        if (!divSelect.value) {
            showToast('অনুগ্রহ করে বিভাগ নির্বাচন করুন।');
            divSelect.focus();
            return;
        }

        if (!distSelect.value) {
            showToast('অনুগ্রহ করে জেলা নির্বাচন করুন।');
            distSelect.focus();
            return;
        }

        if (!upzSelect.value) {
            showToast('অনুগ্রহ করে উপজেলা / থানা নির্বাচন করুন।');
            upzSelect.focus();
            return;
        }

        if (!addr) {
            showToast('অনুগ্রহ করে বিস্তারিত ঠিকানা (বাসা/রোড/এলাকা) লিখুন।');
            document.getElementById('cust-address').focus();
            return;
        }

        const agreeCheckbox = document.getElementById('terms-agree');
        if (!agreeCheckbox || !agreeCheckbox.checked) {
            showToast('অর্ডার সম্পন্ন করতে শর্তাবলী, প্রাইভেসি পলিসি এবং রিটার্ন পলিসিতে সম্মতি দিন।');
            return;
        }
        
        // Calculate total amount
        let total = 0;
        cartItems.forEach(item => total += (checkoutType === 'borrow' ? 0 : item.price));
        const finalAmount = total + (checkoutType === 'borrow' ? 0 : currentDeliveryCharge);

        const divisionText = divSelect.options[divSelect.selectedIndex].textContent;
        const districtText = distSelect.options[distSelect.selectedIndex].textContent;
        const upazilaText = upzSelect.options[upzSelect.selectedIndex].textContent;

        const orderBtn = document.querySelector('button[onclick="confirmOrder()"]');
        const originalBtnHtml = orderBtn ? orderBtn.innerHTML : '';

        // Helper to reset button state on failure
        const resetOrderButton = () => {
            isOrderSubmitting = false;
            if (orderBtn) {
                orderBtn.disabled = false;
                orderBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                orderBtn.innerHTML = originalBtnHtml;
            }
        };

        // Disable button & show spinner
        isOrderSubmitting = true;
        if (orderBtn) {
            orderBtn.disabled = true;
            orderBtn.classList.add('opacity-75', 'cursor-not-allowed');
            orderBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>প্রসেসিং হচ্ছে...</span>
            `;
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('division', divisionText);
        formData.append('district', districtText);
        formData.append('upazila', upazilaText);
        formData.append('address', addr);
        formData.append('payment_method', selectedPayMethod);
        formData.append('checkout_type', checkoutType);
        formData.append('total_amount', finalAmount);
        formData.append('cart', JSON.stringify(cartItems));

        // Submit via AJAX
        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (selectedPayMethod === 'bkash') {
                        // Start bKash flow
                        initiateBkash(data.order_id, finalAmount);
                        return;
                    } else if (selectedPayMethod === 'sslcommerz') {
                        // Clear Cart & Redirect to SSLCommerz initiation
                        if (checkoutType === 'borrow') {
                            localStorage.removeItem('antyam_borrow_cart');
                        } else {
                            localStorage.removeItem('antyam_cart');
                        }
                        window.location.href = '../sslcommerz/initiate.php?order_id=' + data.order_id;
                        return;
                    }

                    // Clear Cart
                    if (checkoutType === 'borrow') {
                        localStorage.removeItem('antyam_borrow_cart');
                    } else {
                        localStorage.removeItem('antyam_cart');
                    }
                    // Update Success UI
                    document.getElementById('order-id-display').innerText = '#' + data.order_id;

                    const modal = document.getElementById('success-modal');
                    const overlay = document.getElementById('modal-overlay');
                    const content = document.getElementById('modal-content');

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    setTimeout(() => {
                        overlay.classList.add('opacity-100');
                        content.classList.remove('scale-90', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                        createConfetti();
                    }, 100);
                } else {
                    resetOrderButton();
                    showToast('অর্ডার প্রসেস করতে ত্রুটি হয়েছে: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resetOrderButton();
                showToast('অর্ডার প্রসেস করতে একটি নেটওয়ার্ক ত্রুটি হয়েছে।');
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadCheckout();
        initGeoDropdowns();
    });
</script>
</body>

</html>