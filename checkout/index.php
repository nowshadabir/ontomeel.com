<?php
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
require_once '../includes/db_connect.php';

$user_balance = 0;
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    $user_balance = $user_data['acc_balance'] ?? 0;
}

$checkout_type = $_GET['type'] ?? 'buy'; // 'buy' or 'borrow'
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
                        <input type="tel" id="cust-phone" required placeholder="০১৭xxxxxxxx"
                            value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium tracking-wider">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">বিস্তারিত
                            ঠিকানা *</label>
                        <input type="text" id="cust-address" required placeholder="বাসা নং, রোড নং, এলাকা"
                            value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-2">শহর
                            *</label>
                        <select id="cust-city"
                            class="w-full bg-white border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:border-brand-gold transition-all font-anek text-brand-900 font-medium appearance-none">
                            <option>ঢাকা</option>
                            <option>চট্টগ্রাম</option>
                            <option>রাজশাহী</option>
                            <option>খুলনা</option>
                            <option>বরিশাল</option>
                            <option>সিলেট</option>
                            <option>রংপুর</option>
                            <option>ময়মনসিংহ</option>
                        </select>
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
                        <!-- Option 1: bKash -->
                        <div onclick="selectPayment('bkash')" id="pay-bkash"
                            class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-[#D12053]/50 transition-all flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center">
                                    <div id="dot-bkash" class="w-2.5 h-2.5 bg-[#D12053] rounded-full hidden"></div>
                                </div>
                                <span
                                    class="font-anek font-bold text-brand-900 group-hover:text-[#D12053] transition-colors">বিকাশ
                                    পেমেন্ট</span>
                            </div>
                            <img src="../assets/img/bkash_logo.png" alt="bkash"
                                class="h-8 grayscale group-hover:grayscale-0 transition-all opacity-40 group-hover:opacity-100"
                                onerror="this.style.display='none'">
                        </div>

                        <!-- Option 2: Nagad -->
                        <div onclick="selectPayment('nagad')" id="pay-nagad"
                            class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-[#EF1F23]/50 transition-all flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center">
                                    <div id="dot-nagad" class="w-2.5 h-2.5 bg-[#EF1F23] rounded-full hidden"></div>
                                </div>
                                <span
                                    class="font-anek font-bold text-brand-900 group-hover:text-[#EF1F23] transition-colors">নগদ
                                    পেমেন্ট</span>
                            </div>
                            <img src="../assets/img/nagad_logo.png" alt="nagad"
                                class="h-8 grayscale group-hover:grayscale-0 transition-all opacity-40 group-hover:opacity-100"
                                onerror="this.style.display='none'">
                        </div>

                        <!-- Option 3: COD -->
                        <div onclick="selectPayment('cod')" id="pay-cod"
                            class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-brand-900/50 transition-all flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center">
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

                        <!-- Option 4: Account Fund -->
                        <div onclick="selectPayment('fund')" id="pay-fund"
                            class="payment-card border-2 border-gray-100 p-6 rounded-[32px] cursor-pointer hover:border-brand-gold/50 transition-all flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-100 flex items-center justify-center">
                                    <div id="dot-fund" class="w-2.5 h-2.5 bg-brand-gold rounded-full hidden"></div>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="font-anek font-bold text-brand-900 group-hover:text-brand-gold transition-colors">অ্যাকাউন্ট
                                        ফান্ড</span>
                                    <span
                                        class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">৳<?php echo number_format($user_balance); ?>
                                        available</span>
                                </div>
                            </div>
                            <svg class="w-8 h-8 text-gray-200 group-hover:text-brand-gold transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </section>
            <?php else: ?>
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
            <?php endif; ?>
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
                            <span class="font-bold text-brand-900 font-anek">৳৫০</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-xl pt-4">
                        <span class="font-anek font-bold text-brand-900">সর্বমোট</span>
                        <span id="grand-total" class="font-bold text-brand-gold">৳০</span>
                    </div>
                </div>

                <button onclick="confirmOrder()"
                    class="w-full mt-10 bg-brand-900 text-white py-5 rounded-[20px] font-anek font-bold text-lg hover:bg-brand-gold hover:text-brand-900 transition-all duration-500 shadow-xl shadow-brand-900/20 flex items-center justify-center gap-3">
                    <span><?php echo ($checkout_type == 'borrow') ? 'ধার নিশ্চিত করুন' : 'অর্ডার কনফার্ম করুন'; ?></span>
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
    <div class="absolute inset-0 bg-brand-900/60 backdrop-blur-xl opacity-0 transition-opacity duration-700"
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

<script>
    const checkoutType = "<?php echo $checkout_type; ?>"; // 'buy' or 'borrow'
    const currentUserFund = <?php echo (float) $user_balance; ?>;
    let cartItems = JSON.parse(localStorage.getItem(checkoutType === 'borrow' ? 'antyam_borrow_cart' : 'antyam_cart') || '[]');
    let selectedPayMethod = checkoutType === 'borrow' ? 'borrow' : 'cod';

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
        const dots = ['bkash', 'nagad', 'cod', 'fund'];
        dots.forEach(d => {
            const dot = document.getElementById(`dot-${d}`);
            if (dot) dot.classList.add('hidden');
        });

        const targetDot = document.getElementById(`dot-${method}`);
        if (targetDot) targetDot.classList.remove('hidden');
    }

    // Initialize default selection
    if (checkoutType !== 'borrow') selectPayment('cod');

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
        container.innerHTML = '';

        cartItems.forEach(item => {
            const displayPrice = (checkoutType === 'borrow') ? 0 : item.price;
            total += displayPrice;
            container.innerHTML += `
                    <div class="flex gap-4 items-center">
                        <div class="w-12 h-16 bg-gray-50 rounded shadow-sm overflow-hidden flex-shrink-0">
                            <img src="${typeof getCorrectImagePath === 'function' ? getCorrectImagePath(item.img) : item.img}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-brand-900 font-anek text-sm truncate w-40">${item.title}</h4>
                            <p class="text-[10px] text-gray-400 font-anek">পরিমাণ: ১টি</p>
                        </div>
                        <p class="font-bold text-brand-900 font-anek">৳${convertToBengaliNumber(displayPrice)}</p>
                    </div>
                `;
        });

        const deliveryCharge = (checkoutType === 'borrow') ? 0 : 50;
        document.getElementById('sub-total').innerText = `৳${convertToBengaliNumber(total)}`;
        document.getElementById('grand-total').innerText = `৳${convertToBengaliNumber(total + deliveryCharge)}`;
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
        const name = document.getElementById('cust-name').value;
        const phone = document.getElementById('cust-phone').value;
        const addr = document.getElementById('cust-address').value;
        const city = document.getElementById('cust-city').value;

        if (!name || !phone || !addr) {
            alert('দয়া করে সব তথ্য পূরণ করুন।');
            return;
        }

        // Calculate total amount
        let total = 0;
        cartItems.forEach(item => total += (checkoutType === 'borrow' ? 0 : item.price));
        const finalAmount = total + (checkoutType === 'borrow' ? 0 : 50);

        // Check if account fund is sufficient
        if (selectedPayMethod === 'fund' && finalAmount > currentUserFund) {
            alert('আপনার অ্যাকাউন্ট ফান্ডে পর্যাপ্ত ব্যালেন্স নেই। বর্তমান ব্যালেন্স: ৳' + currentUserFund);
            return;
        }

        // Prepare data for backend
        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('address', addr);
        formData.append('city', city);
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
                    alert('অর্ডার প্রসেস করতে ত্রুটি হয়েছে: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('অর্ডার প্রসেস করতে একটি নেটওয়ার্ক ত্রুটি হয়েছে।');
            });
    }

    document.addEventListener('DOMContentLoaded', loadCheckout);
</script>
</body>

</html>