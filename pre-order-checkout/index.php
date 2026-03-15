<?php
require_once '../includes/db_connect.php';
$page_title = 'প্রি-অর্ডার চেকআউট | অন্ত্যমিল অনলাইন বুকশপ';
$path_prefix = '../';
$is_checkout = true;

include '../includes/header.php';

// User data if logged in
$user_data = ['full_name' => '', 'phone' => '', 'address' => ''];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    if (!$user_data) {
        $user_data = ['full_name' => '', 'phone' => '', 'address' => '', 'email' => ''];
    }
}

// Fetch Pre-order Item Details
$pre_order_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE id = ?");
$stmt->execute([$pre_order_id]);
$pre_order = $stmt->fetch();

if (!$pre_order) {
    echo "<div class='text-center py-20 text-xl font-anek text-brand-900'>প্রি-অর্ডার আইটেম পাওয়া যায়নি।</div>";
    include '../includes/footer.php';
    exit();
}

// User fetched above

// Helper to get settings
function getSetting($pdo, $key, $default = '')
{
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }
    catch (Exception $e) {
        return $default;
    }
}

$inside_charge = (int)getSetting($pdo, 'delivery_charge_inside', 60);
$outside_charge = (int)getSetting($pdo, 'delivery_charge_outside', 120);

$price = $pre_order['discount_price'] > 0 ? (int)$pre_order['discount_price'] : (int)$pre_order['price'];
$is_free_delivery = (isset($pre_order['free_delivery']) && $pre_order['free_delivery'] == 1);
$delivery_charge = $is_free_delivery ? 0 : $inside_charge;
$total_amount = $price + $delivery_charge;
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 md:py-12">
    <div class="bg-white rounded-[30px] md:rounded-[40px] shadow-xl shadow-brand-900/5 p-5 sm:p-8 md:p-12 overflow-hidden relative">
        <h1 class="text-2xl md:text-3xl font-anek font-bold text-brand-900 mb-2 border-b-2 border-brand-gold pb-4">প্রি-বুকিং চেকআউট
        </h1>

        <!-- Pre-order Item Summary -->
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 mt-8 p-5 sm:p-6 bg-brand-light/20 rounded-3xl mb-8 border border-brand-gold/20 text-center sm:text-left">
            <div class="w-20 h-28 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 shadow-md">
                <img src="<?php echo htmlspecialchars(strpos($pre_order['cover_image'], 'http') !== false ? $pre_order['cover_image'] : '../assets/img/preorders/' . trim($pre_order['cover_image'])); ?>"
                    onerror="this.src='../assets/img/book-placeholder.jpg'"
                    alt="<?php echo htmlspecialchars($pre_order['title']); ?>" class="w-full h-full object-cover">
            </div>
            <div class="flex-1">
                <div
                    class="inline-block px-3 py-1 bg-brand-gold/20 text-brand-900 font-bold text-[10px] md:text-xs rounded-full uppercase tracking-widest mb-2">
                    Pre-Order</div>
                <h3 class="font-anek font-bold text-xl md:text-2xl text-brand-900 leading-tight">
                    <?php echo htmlspecialchars($pre_order['title']); ?>
                </h3>
                <p class="text-xs md:text-sm text-gray-500 font-anek mt-1">লেখক: <?php echo htmlspecialchars($pre_order['author']); ?>
                </p>
            </div>
            <div class="sm:text-right w-full sm:w-auto mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-brand-gold/10">
                <p class="text-xl md:text-2xl font-bold text-brand-900 font-anek">৳<?php echo number_format($price); ?></p>
                <p class="text-[10px] md:text-xs text-brand-900/60 font-anek"><?php if($is_free_delivery): ?>ফ্রি ডেলিভারি<?php else: ?>+ ৳<span id="display-delivery"><?php echo $delivery_charge; ?></span> ডেলিভারি<?php endif; ?></p>
                <div class="mt-2 text-xs md:text-sm font-bold text-brand-gold bg-brand-900 px-4 py-1.5 rounded-full inline-block">
                    মোট: ৳<span id="display-total"><?php echo number_format($total_amount); ?></span></div>
            </div>
        </div>

        <!-- Step 1: Address Details -->
        <div id="step-1" class="transition-all duration-500">
            <h2 class="text-xl font-anek font-bold text-brand-900 mb-6 flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center text-sm">১</span>
                ডেলিভারি ঠিকানা
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-3xl border border-gray-100">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">আপনার নাম</label>
                    <input type="text" id="po-name" <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?>
                        value="<?php echo htmlspecialchars($user_data['full_name']); ?>"
                        class="w-full <?php echo isset($_SESSION['user_id']) ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?> border border-gray-200 rounded-2xl px-6 py-4 text-brand-900 font-bold focus:outline-none focus:border-brand-gold transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">মোবাইল
                        নম্বর</label>
                    <input type="text" id="po-phone" <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?>
                        value="<?php echo htmlspecialchars($user_data['phone']); ?>"
                        class="w-full <?php echo isset($_SESSION['user_id']) ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?> border border-gray-200 rounded-2xl px-6 py-4 text-brand-900 font-bold tracking-wider focus:outline-none focus:border-brand-gold transition-all">
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">আপনার ইমেইল
                        (ঐচ্ছিক)</label>
                    <input type="email" id="po-email" <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?>
                        value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                        placeholder="আপনার ইমেইল এড্রেস লিখুন (যদি থাকে)"
                        class="w-full <?php echo isset($_SESSION['user_id']) ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?> border border-gray-200 rounded-2xl px-6 py-4 text-brand-900 font-bold focus:outline-none focus:border-brand-gold transition-all">
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">ডেলিভারি এরিয়া *</label>
                    <?php if ($is_free_delivery): ?>
                        <div class="p-5 bg-green-50 border border-green-200 rounded-3xl flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="font-anek font-bold text-brand-900">ফ্রি ডেলিভারি সক্রিয়</p>
                                    <p class="text-xs text-green-600 font-anek font-bold">সারা বাংলাদেশে হোম ডেলিভারি ফ্রি</p>
                                </div>
                            </div>
                            <input type="hidden" name="location" value="inside">
                        </div>
                    <?php else: ?>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="location" value="inside" checked onchange="updateDelivery(this.value)" class="hidden peer">
                            <div class="p-4 bg-white border border-gray-200 rounded-2xl text-center peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 transition-all">
                                <p class="text-sm font-anek font-bold text-brand-900">কক্সবাজার শহর</p>
                                <p class="text-xs text-gray-400 font-anek">চার্জ: ৳<?php echo $inside_charge; ?></p>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="location" value="outside" onchange="updateDelivery(this.value)" class="hidden peer">
                            <div class="p-4 bg-white border border-gray-200 rounded-2xl text-center peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 transition-all">
                                <p class="text-sm font-anek font-bold text-brand-900">আউটসাইড কক্সবাজার</p>
                                <p class="text-xs text-gray-400 font-anek">চার্জ: ৳<?php echo $outside_charge; ?></p>
                            </div>
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">ডেলিভারি ঠিকানা
                        (বিস্তারিত) *</label>
                    <textarea id="po-address" rows="3"
                        placeholder="বর্তমান বা স্থায়ী ঠিকানা, যেখানে ডেলিভারি নিতে চান..."
                        class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all text-brand-900 font-anek"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button onclick="goToStep2()"
                    class="w-full sm:w-auto bg-brand-900 text-white px-8 py-4 rounded-xl font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg flex items-center justify-center gap-2">
                    পরবর্তী ধাপ: পেমেন্ট <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Payment (bKash QR) -->
        <div id="step-2" class="hidden opacity-0 transition-opacity duration-500">
            <h2 class="text-xl font-anek font-bold text-brand-900 mb-6 flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center text-sm">২</span>
                পেমেন্ট সম্পন্ন করুন
            </h2>
            <div
                class="bg-pink-50 border-2 border-[#D12053]/20 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row gap-6 md:gap-8 items-center text-center md:text-left">
                <div class="w-40 h-40 md:w-48 md:h-48 bg-white p-4 rounded-2xl shadow-sm border border-[#D12053]/10 flex-shrink-0">
                    <!-- Custom Merchant QR Image -->
                    <img src="../assets/img/merchant.png"
                        onerror="this.src='https://ontomeel.com/assets/img/merchant.png'"
                        alt="bKash QR" class="w-full h-full object-contain mix-blend-multiply">
                </div>
                <div class="flex-1 space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="../assets/img/merchant.png" alt="bkash" class="h-8 rounded">
                        <h4 class="font-bold text-[#D12053] text-lg font-anek">বিকাশ পেমেন্ট</h4>
                    </div>
                    <p class="text-brand-900 font-anek font-medium leading-relaxed">১। আপনার বিকাশ অ্যাপ থেকে উপরের QR
                        কোডটি স্ক্যান করুন অথবা <strong class="text-[#D12053]">01330975787</strong> নম্বরে পেমেন্ট
                        করুন।<br>২। টাকার পরিমাণ: <strong
                            class="text-lg">৳<?php echo number_format($total_amount); ?></strong></p>

                    <div class="mt-6 space-y-2">
                        <label class="text-[10px] font-bold text-[#D12053] uppercase tracking-widest ml-2">আপনার
                            ট্রাঞ্জেকশন আইডিটি দিন *</label>
                        <input type="text" id="po-sender-number" placeholder="যেমন: CDDS0393DF"
                            class="w-full bg-white border border-[#D12053]/30 rounded-2xl px-6 py-4 focus:outline-none focus:border-[#D12053] transition-all text-brand-900 font-bold tracking-wider">
                    </div>
                </div>
            </div>
            <div class="mt-8 flex flex-col-reverse sm:flex-row justify-between gap-4">
                <button onclick="goToStep1()"
                    class="w-full sm:w-auto text-brand-900 px-6 py-4 font-anek font-bold hover:text-brand-gold transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg> পিছনে যান
                </button>
                <button onclick="submitPreOrder()"
                    class="w-full sm:w-auto bg-[#D12053] text-white px-8 py-4 rounded-xl font-anek font-bold hover:bg-[#b01844] transition-all shadow-lg flex items-center justify-center gap-2">
                    পেমেন্ট নিশ্চিত করুন <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Verifying State -->
        <div id="step-3" class="hidden opacity-0 transition-opacity duration-500 py-16 text-center">
            <div class="w-24 h-24 border-4 border-gray-100 border-t-[#D12053] rounded-full animate-spin mx-auto mb-8">
            </div>
            <h2 class="text-3xl font-anek font-bold text-brand-900 mb-4 animate-pulse">পেমেন্ট যাচাই করা হচ্ছে...</h2>
            <p class="text-gray-500 font-anek max-w-md mx-auto">অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন। আমরা আপনার
                পেমেন্ট নম্বরটি যাচাই করছি।</p>
        </div>

        <!-- Step 4: Success State -->
        <div id="step-4" class="hidden opacity-0 transition-opacity duration-500 py-12 text-center">
            <div
                class="w-24 h-24 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-10 h-10 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-2xl md:text-3xl font-anek font-bold text-brand-900 mb-4 px-4">প্রি-বুকিং সফল হয়েছে!</h2>
            <p class="text-gray-500 font-anek max-w-md mx-auto mb-8">আপনার পেমেন্ট রিকোয়েস্ট গ্রহণ করা হয়েছে। যাচাই শেষে
                অর্ডার কনফার্মেশন এসএমএস পাঠানো হবে।</p>

            <div
                class="bg-brand-light p-6 rounded-3xl max-w-sm mx-auto mb-8 border border-brand-gold/10 relative overflow-hidden">
                <p class="text-[10px] text-brand-gold font-bold uppercase tracking-[0.3em] mb-2">অর্ডার নাম্বার</p>
                <h3 id="final-order-id" class="text-3xl font-mono font-bold text-brand-900 tracking-wider"></h3>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mt-6 pt-6 border-t border-brand-gold/10">
                        <p class="text-xs text-red-500 font-anek font-bold mb-2">আপনি রেজিস্ট্রেশন করা সদস্য নন</p>
                        <p class="text-[10px] text-gray-500 font-anek leading-relaxed">অর্ডার ট্র্যাকিং এর জন্য উপরের আইডি
                            সহ এই স্ক্রিনটির একটি <span class="bg-yellow-100 text-brand-900 px-1">স্ক্রিনশট</span> তুলে
                            রাখুন।</p>
                    </div>
                <?php
endif; ?>
            </div>

            <a href="<?php echo isset($_SESSION['user_id']) ? '../dashboard/index.php' : '../index.php'; ?>"
                class="inline-block bg-brand-900 text-white px-8 py-4 rounded-xl font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all">
                <?php echo isset($_SESSION['user_id']) ? 'আমার ড্যাশবোর্ডে যান' : 'হোম পেজে ফিরে যান'; ?>
            </a>
        </div>
    </div>
</main>

<!-- Toast for errors -->
<div id="po-toast"
    class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[200] flex items-center gap-4 bg-red-600 text-white px-8 py-4 rounded-2xl shadow-2xl transition-all duration-500 translate-y-20 opacity-0 invisible">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <span id="po-toast-msg" class="font-anek font-bold text-sm"></span>
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
        
        document.getElementById('display-delivery').innerText = currentDeliveryCharge;
        document.getElementById('display-total').innerText = totalAmount.toLocaleString();
        
        // Update QR code fallback amount
        const qrImg = document.querySelector('#step-2 img');
        if (qrImg) {
            qrImg.onerror = () => {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=bkash://pay?amount=${totalAmount}`;
            };
        }
    }

    function showError(msg) {
        const toast = document.getElementById('po-toast');
        document.getElementById('po-toast-msg').innerText = msg;
        toast.classList.remove('translate-y-20', 'opacity-0', 'invisible');
        toast.classList.add('translate-y-0', 'opacity-100', 'visible');
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100', 'visible');
            toast.classList.add('translate-y-20', 'opacity-0', 'invisible');
        }, 3000);
    }

    function switchStep(hideId, showId) {
        const hideEl = document.getElementById(hideId);
        const showEl = document.getElementById(showId);

        hideEl.classList.add('opacity-0');
        setTimeout(() => {
            hideEl.classList.add('hidden');
            showEl.classList.remove('hidden');
            // Small trigger delay for transition
            setTimeout(() => {
                showEl.classList.remove('opacity-0');
            }, 50);
        }, 500); // Wait for fade out
    }

    function goToStep2() {
        const name = document.getElementById('po-name').value.trim();
        const phone = document.getElementById('po-phone').value.trim();
        const addr = document.getElementById('po-address').value.trim();

        if (!name || !phone || !addr) {
            showError('অনুগ্রহ করে আপনার নাম, মোবাইল নম্বর এবং ডেলিভারি ঠিকানা দিন');
            return;
        }

        if (phone.length < 11) {
            showError('সঠিক মোবাইল নম্বর দিন');
            return;
        }

        switchStep('step-1', 'step-2');
    }

    function goToStep1() {
        switchStep('step-2', 'step-1');
    }

    async function submitPreOrder() {
        const addr = document.getElementById('po-address').value.trim();
        const senderNum = document.getElementById('po-sender-number').value.trim();

        if (!senderNum) {
            showError('দয়া করে পেমেন্ট করার নম্বরটি লিখুন');
            return;
        }

        // Switch to Step 3 (Verifying Animation)
        switchStep('step-2', 'step-3');

        // Prepare data to send to server
        const locationInput = document.querySelector('input[name="location"]:checked') || document.querySelector('input[name="location"][type="hidden"]');
        const location = locationInput ? locationInput.value : 'inside';
        const formData = new FormData();
        formData.append('preorder_id', preOrderId);
        formData.append('name', document.getElementById('po-name').value.trim());
        formData.append('phone', document.getElementById('po-phone').value.trim());
        formData.append('email', document.getElementById('po-email').value.trim());
        formData.append('address', addr);
        formData.append('location', location);
        formData.append('sender_number', senderNum);
        formData.append('total_amount', totalAmount);

        // Record start time to ensure minimum animation duration
        const startTime = Date.now();
        const minAnimDuration = 2000; // 2 seconds

        try {
            const response = await fetch('process_pre_order.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            // Calculate remaining time for animation
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minAnimDuration - elapsedTime);

            setTimeout(() => {
                if (data.success) {
                    document.getElementById('final-order-id').innerText = '#' + data.order_id;
                    switchStep('step-3', 'step-4');
                } else {
                    showError(data.message || 'একটি সমস্যা হয়েছে।');
                    switchStep('step-3', 'step-2');
                }
            }, remainingTime);

        } catch (err) {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minAnimDuration - elapsedTime);
            
            setTimeout(() => {
                showError('নেটওয়ার্ক সমস্যা। আবার চেষ্টা করুন।');
                switchStep('step-3', 'step-2');
            }, remainingTime);
        }
    }
</script>

<?php include '../includes/footer.php'; ?>