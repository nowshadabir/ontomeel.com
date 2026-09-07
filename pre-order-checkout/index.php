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

// Read Geo Data from JSON files safely on server
$bd_divisions_json = @file_get_contents(__DIR__ . '/../bd-divisions.json') ?: '{"divisions":[]}';
$bd_districts_json = @file_get_contents(__DIR__ . '/../bd-districts.json') ?: '{"districts":[]}';
$bd_upazilas_json  = @file_get_contents(__DIR__ . '/../bd-upazilas.json') ?: '{"upazilas":[]}';
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 md:py-12 font-anek">
    <div class="bg-white rounded-[32px] md:rounded-[40px] shadow-2xl shadow-slate-900/10 p-6 sm:p-8 md:p-12 overflow-hidden relative border border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-6 mb-8">
            <div>
                <span class="text-xs font-black text-amber-700 uppercase tracking-[0.25em] block mb-1">Pre-Order Checkout</span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">প্রি-বুকিং চেকআউট</h1>
            </div>
            <a href="../pre-booking/index.php" class="text-xs font-bold text-slate-700 hover:text-slate-950 transition-colors flex items-center gap-1.5 self-start sm:self-auto bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-full border border-slate-200 shadow-sm">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                প্রি-বুকিং পেজে ফিরে যান
            </a>
        </div>

        <!-- Pre-order Item Summary -->
        <div class="flex flex-col sm:flex-row items-center gap-5 sm:gap-6 p-5 sm:p-6 bg-amber-50/70 rounded-3xl mb-10 border border-amber-200/90 text-center sm:text-left shadow-sm">
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="w-20 h-28 bg-slate-100 rounded-2xl overflow-hidden shadow-md border-2 border-white">
                    <img src="<?php echo htmlspecialchars(strpos($pre_order['cover_image'], 'http') !== false ? $pre_order['cover_image'] : '../assets/img/preorders/' . trim($pre_order['cover_image'])); ?>"
                        onerror="this.src='../assets/img/book-placeholder.jpg'"
                        alt="<?php echo htmlspecialchars($pre_order['title']); ?>" class="w-full h-full object-cover">
                </div>
                <?php if (!empty($pre_order['second_cover_image'])): ?>
                    <div class="w-16 h-24 bg-slate-100 rounded-xl overflow-hidden shadow-md border-2 border-white -ml-6 z-10">
                        <img src="<?php echo htmlspecialchars(strpos($pre_order['second_cover_image'], 'http') !== false ? $pre_order['second_cover_image'] : '../assets/img/preorders/' . trim($pre_order['second_cover_image'])); ?>"
                            onerror="this.src='../assets/img/book-placeholder.jpg'"
                            alt="Combo Book" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 border border-amber-300/90 text-amber-950 font-bold text-[11px] rounded-full uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                    <?php echo !empty($pre_order['second_title']) ? 'কম্বো প্রি-অর্ডার' : 'প্রি-অর্ডার অফার'; ?>
                </div>
                <h3 class="font-extrabold text-xl md:text-2xl text-slate-900 leading-tight">
                    <?php echo htmlspecialchars($pre_order['title']); ?>
                    <?php if (!empty($pre_order['second_title'])): ?>
                        <span class="text-base text-slate-700 font-semibold">এবং <?php echo htmlspecialchars($pre_order['second_title']); ?></span>
                    <?php endif; ?>
                </h3>
                <p class="text-xs text-slate-700 font-medium mt-1.5">লেখক: <strong class="text-slate-900 font-bold"><?php echo htmlspecialchars($pre_order['author']); ?></strong></p>
            </div>
            <div class="sm:text-right w-full sm:w-auto pt-4 sm:pt-0 border-t sm:border-t-0 border-amber-200/60 flex flex-col items-center sm:items-end">
                <p class="text-2xl md:text-3xl font-black text-slate-900 font-mono">৳<?php echo number_format($price); ?></p>
                <p class="text-xs text-slate-700 font-medium mt-0.5">
                    <?php if ($is_free_delivery): ?>
                        <span class="text-emerald-700 font-extrabold">ফ্রি হোম ডেলিভারি</span>
                    <?php else: ?>
                        + ৳<span id="display-delivery" class="font-bold text-slate-900"><?php echo $delivery_charge; ?></span> ডেলিভারি
                    <?php endif; ?>
                </p>
                <div class="mt-2 text-xs md:text-sm font-extrabold text-amber-300 bg-slate-950 px-4 py-1.5 rounded-full inline-block whitespace-nowrap shadow-md border border-slate-800">
                    সর্বমোট: ৳<span id="display-total"><?php echo number_format($total_amount); ?></span>
                </div>
            </div>
        </div>

        <!-- Form: Delivery Details -->
        <div class="space-y-8">
            <div>
                <h2 class="text-lg md:text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    ডেলিভারি ঠিকানা ও গ্রাহকের তথ্য
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-slate-50 p-6 sm:p-8 rounded-[28px] border border-slate-200 shadow-sm">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-800 tracking-wide ml-1 block">আপনার পুরো নাম *</label>
                        <input type="text" id="po-name" required
                            value="<?php echo htmlspecialchars($user_data['full_name']); ?>"
                            placeholder="Your Name"
                            class="w-full bg-white border-2 border-slate-200 rounded-2xl px-5 py-3.5 text-slate-900 font-semibold placeholder:text-slate-400 focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm shadow-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-800 tracking-wide ml-1 block">মোবাইল নম্বর *</label>
                        <input type="tel" id="po-phone" required
                            value="<?php echo htmlspecialchars($user_data['phone']); ?>"
                            placeholder="017XXXXXXXX"
                            class="w-full bg-white border-2 border-slate-200 rounded-2xl px-5 py-3.5 text-slate-900 font-semibold font-mono tracking-wider placeholder:text-slate-400 focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm shadow-sm">
                    </div>
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-xs font-bold text-slate-800 tracking-wide ml-1 block">ইমেইল এড্রেস (পেমেন্ট রসিদ ও আপডেটের জন্য) *</label>
                        <input type="email" id="po-email" required
                            value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                            placeholder="name@example.com"
                            class="w-full bg-white border-2 border-slate-200 rounded-2xl px-5 py-3.5 text-slate-900 font-semibold placeholder:text-slate-400 focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm shadow-sm">
                    </div>

                    <!-- Geographic Dropdowns: Division, District, Upazila -->
                    <div class="md:col-span-2 space-y-3 pt-2">
                        <label class="text-xs font-bold text-slate-800 tracking-wide ml-1 block">ডেলিভারি এলাকা নির্বাচন করুন *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <!-- Division Select -->
                            <div class="space-y-1">
                                <span class="text-xs text-slate-700 font-bold ml-1 block">বিভাগ (Division)</span>
                                <div class="relative">
                                    <select id="po-division" onchange="onDivisionChange()"
                                        class="w-full bg-white border-2 border-slate-200 rounded-2xl px-4 py-3 text-slate-900 font-semibold focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm appearance-none cursor-pointer pr-10 shadow-sm">
                                        <option value="">বিভাগ নির্বাচন করুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- District Select -->
                            <div class="space-y-1">
                                <span class="text-xs text-slate-700 font-bold ml-1 block">জেলা (District)</span>
                                <div class="relative">
                                    <select id="po-district" onchange="onDistrictChange()" disabled
                                        class="w-full bg-slate-100 border-2 border-slate-200 rounded-2xl px-4 py-3 text-slate-900 font-semibold focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm appearance-none cursor-pointer pr-10 shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">প্রথমে বিভাগ বাছুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Upazila Select -->
                            <div class="space-y-1">
                                <span class="text-xs text-slate-700 font-bold ml-1 block">উপজেলা / থানা (Upazila)</span>
                                <div class="relative">
                                    <select id="po-upazila" onchange="onUpazilaChange()" disabled
                                        class="w-full bg-slate-100 border-2 border-slate-200 rounded-2xl px-4 py-3 text-slate-900 font-semibold focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-sm appearance-none cursor-pointer pr-10 shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">প্রথমে জেলা বাছুন</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Charge Status Badge -->
                        <div id="delivery-badge" class="pt-1">
                            <?php if ($is_free_delivery): ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-full text-xs font-bold shadow-sm">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    সারা বাংলাদেশে ফ্রি ডেলিভারি অফার সক্রিয়
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-100 border border-amber-300 text-amber-950 rounded-full text-xs font-bold shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                                    কক্সবাজার সদর: ৳<?php echo $inside_charge; ?> | অন্যান্য এলাকা: ৳<?php echo $outside_charge; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-xs font-bold text-slate-800 tracking-wide ml-1 block">সম্পূর্ণ ঠিকানা (রোড/মহল্লা/বাড়ি/গ্রাম) *</label>
                        <textarea id="po-address" rows="3" required
                            placeholder="বাসা/হোল্ডিং নং, রোড, এলাকা বা গ্রামের নাম লিখুন..."
                            class="w-full bg-white border-2 border-slate-200 rounded-2xl px-5 py-3.5 focus:outline-none focus:border-amber-600 focus:ring-4 focus:ring-amber-500/15 transition-all text-slate-900 font-semibold placeholder:text-slate-400 text-sm leading-relaxed shadow-sm"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Terms and Consent -->
            <div class="pt-2 flex items-start gap-3">
                <input type="checkbox" id="po-terms-agree" class="mt-1 w-4 h-4 text-slate-900 rounded border-slate-300 focus:ring-amber-500 cursor-pointer" required>
                <label for="po-terms-agree" class="text-xs text-slate-800 font-medium leading-relaxed cursor-pointer select-none">
                    আমি অন্ত্যমিলের <a href="../terms.php" target="_blank" class="text-slate-950 font-extrabold underline hover:text-amber-700">শর্তাবলী</a>, <a href="../privacy.php" target="_blank" class="text-slate-950 font-extrabold underline hover:text-amber-700">প্রাইভেসি পলিসি</a> এবং <a href="../refund.php" target="_blank" class="text-slate-950 font-extrabold underline hover:text-amber-700">রিটার্ন ও রিফান্ড নীতি</a> পড়েছি এবং সম্মত আছি।
                </label>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
                <button type="button" onclick="submitPreOrder()" id="submit-btn"
                    class="w-full bg-slate-900 text-white py-5 px-8 rounded-2xl font-bold text-base md:text-lg hover:bg-slate-800 hover:text-amber-300 transition-all duration-300 shadow-xl shadow-slate-900/20 flex items-center justify-center gap-3 group border border-slate-800">
                    <span id="btn-text">চেকআউট সম্পন্ন করুন — ৳<span id="btn-total"><?php echo number_format($total_amount); ?></span></span>
                    <svg id="btn-icon" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
                <p class="text-center text-xs font-semibold text-slate-600 mt-3 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    নিরাপদ ও সুরক্ষিত অনলাইন পেমেন্ট
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

    // Embedded Geo Data from server JSON
    const geoData = {
        divisions: <?php echo $bd_divisions_json; ?>.divisions || [],
        districts: <?php echo $bd_districts_json; ?>.districts || [],
        upazilas: <?php echo $bd_upazilas_json; ?>.upazilas || []
    };

    function formatGeoName(item) {
        const hasValidEn = item.name && !item.name.includes('{') && !item.name.includes('}');
        if (hasValidEn && item.name !== item.bn_name) {
            return `${item.bn_name} (${item.name})`;
        }
        return item.bn_name;
    }

    // Populate Divisions on load
    function initGeoDropdowns() {
        const divSelect = document.getElementById('po-division');
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
        const divSelect = document.getElementById('po-division');
        const distSelect = document.getElementById('po-district');
        const upzSelect = document.getElementById('po-upazila');
        const selectedDivId = divSelect.value;

        distSelect.innerHTML = '<option value="">জেলা নির্বাচন করুন</option>';
        upzSelect.innerHTML = '<option value="">প্রথমে জেলা বাছুন</option>';
        upzSelect.disabled = true;
        upzSelect.classList.add('bg-slate-100');
        upzSelect.classList.remove('bg-white');

        if (!selectedDivId) {
            distSelect.disabled = true;
            distSelect.classList.add('bg-slate-100');
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
        distSelect.classList.remove('bg-slate-100');
        distSelect.classList.add('bg-white');
        recalculateDelivery();
    }

    function onDistrictChange() {
        const distSelect = document.getElementById('po-district');
        const upzSelect = document.getElementById('po-upazila');
        const selectedDistId = distSelect.value;

        upzSelect.innerHTML = '<option value="">উপজেলা / থানা নির্বাচন করুন</option>';

        if (!selectedDistId) {
            upzSelect.disabled = true;
            upzSelect.classList.add('bg-slate-100');
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
        upzSelect.classList.remove('bg-slate-100');
        upzSelect.classList.add('bg-white');
        recalculateDelivery();
    }

    function onUpazilaChange() {
        recalculateDelivery();
    }

    function recalculateDelivery() {
        if (isFreeDelivery) {
            currentDeliveryCharge = 0;
            updateTotals(0);
            return;
        }

        const distSelect = document.getElementById('po-district');
        const upzSelect = document.getElementById('po-upazila');
        const selectedDistId = distSelect ? distSelect.value : '';
        const selectedUpzOpt = upzSelect && upzSelect.selectedIndex > 0 ? upzSelect.options[upzSelect.selectedIndex] : null;
        const selectedUpzText = selectedUpzOpt ? selectedUpzOpt.textContent : '';

        // District 45 is Cox's Bazar in bd-districts.json
        // If Cox's Bazar Sadar is selected (or contains সদর / Sadar): inside charge
        let isInside = false;
        if (selectedDistId === '45') {
            if (selectedUpzText.includes('সদর') || selectedUpzText.toLowerCase().includes('sadar')) {
                isInside = true;
            } else if (!upzSelect.value) {
                // Default to inside while district is Cox's Bazar
                isInside = true;
            }
        }

        currentDeliveryCharge = isInside ? insideCharge : outsideCharge;
        updateTotals(currentDeliveryCharge, isInside);
    }

    function updateTotals(charge, isInside = false) {
        totalAmount = basePrice + charge;

        const dispDelivery = document.getElementById('display-delivery');
        if (dispDelivery) dispDelivery.innerText = charge;

        const dispTotal = document.getElementById('display-total');
        if (dispTotal) dispTotal.innerText = totalAmount.toLocaleString();

        const btnTotal = document.getElementById('btn-total');
        if (btnTotal) btnTotal.innerText = totalAmount.toLocaleString();

        const badge = document.getElementById('delivery-badge');
        if (badge && !isFreeDelivery) {
            if (isInside) {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-100 border border-amber-300 text-amber-950 rounded-full text-xs font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                    কক্সবাজার শহর ডেলিভারি চার্জ: ৳${insideCharge}
                </span>`;
            } else {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-100 border border-blue-300 text-blue-950 rounded-full text-xs font-bold shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    আউটসাইড কক্সবাজার (সারাদেশ) ডেলিভারি চার্জ: ৳${outsideCharge}
                </span>`;
            }
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
        }, 4000);
    }

    let isPreOrderSubmitting = false;

    async function submitPreOrder() {
        if (isPreOrderSubmitting) return;

        const name = document.getElementById('po-name').value.trim();
        const phone = document.getElementById('po-phone').value.trim();
        const email = document.getElementById('po-email').value.trim();
        const divSelect = document.getElementById('po-division');
        const distSelect = document.getElementById('po-district');
        const upzSelect = document.getElementById('po-upazila');
        const addr = document.getElementById('po-address').value.trim();

        if (!name || !phone || !email) {
            showError('অনুগ্রহ করে আপনার নাম, মোবাইল নম্বর এবং ইমেইল দিন।');
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

        if (!divSelect.value) {
            showError('অনুগ্রহ করে আপনার বিভাগ নির্বাচন করুন।');
            divSelect.focus();
            return;
        }

        if (!distSelect.value) {
            showError('অনুগ্রহ করে আপনার জেলা নির্বাচন করুন।');
            distSelect.focus();
            return;
        }

        if (!upzSelect.value) {
            showError('অনুগ্রহ করে আপনার উপজেলা / থানা নির্বাচন করুন।');
            upzSelect.focus();
            return;
        }

        if (!addr) {
            showError('অনুগ্রহ করে আপনার সম্পূর্ণ ঠিকানা (রোড/মহল্লা/বাড়ি নং) লিখুন।');
            document.getElementById('po-address').focus();
            return;
        }

        const agreeCheckbox = document.getElementById('po-terms-agree');
        if (!agreeCheckbox || !agreeCheckbox.checked) {
            showError('পেমেন্ট এগিয়ে নিতে অন্ত্যমিলের শর্তাবলী ও পলিসিতে সম্মতি দিন।');
            return;
        }

        const divisionText = divSelect.options[divSelect.selectedIndex].textContent;
        const districtText = distSelect.options[distSelect.selectedIndex].textContent;
        const upazilaText = upzSelect.options[upzSelect.selectedIndex].textContent;

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

        const formData = new FormData();
        formData.append('preorder_id', preOrderId);
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('division', divisionText);
        formData.append('district', districtText);
        formData.append('upazila', upazilaText);
        formData.append('address', addr);
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
                btnText.innerHTML = `চেকআউট সম্পন্ন করুন — ৳<span id="btn-total">${totalAmount.toLocaleString()}</span>`;
                if (btnIcon) btnIcon.classList.remove('hidden');
                showError(data.message || 'অর্ডার শুরু করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
            }
        } catch (err) {
            isPreOrderSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-not-allowed');
            btnText.innerHTML = `চেকআউট সম্পন্ন করুন — ৳<span id="btn-total">${totalAmount.toLocaleString()}</span>`;
            if (btnIcon) btnIcon.classList.remove('hidden');
            showError('নেটওয়ার্ক সমস্যা হয়েছে। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করুন।');
        }
    }

    document.addEventListener('DOMContentLoaded', initGeoDropdowns);
</script>

<?php include '../includes/footer.php'; ?>