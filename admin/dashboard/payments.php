<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'payments';
$page_title = 'পেমেন্ট সেটিংস';

// Fetch Payment Methods
$payment_methods = $pdo->query("SELECT * FROM payment_methods ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Delivery Charges
$inside_charge = getSetting($pdo, 'delivery_inside_cox', '50');
$outside_charge = getSetting($pdo, 'delivery_outside_cox', '100');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title><?php echo $page_title; ?> - অন্ত্যমিল অ্যাডমিন</title>
</head>
<body class="bg-[#faf8f5] text-stone-900 font-anek antialiased min-h-screen flex selection:bg-stone-200 selection:text-stone-900">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#faf8f5] lg:pl-64">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <main class="flex-1 p-6 lg:p-10 space-y-8 max-w-7xl w-full mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e7e3da] pb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                    <p class="text-xs text-stone-500 font-normal mt-1">পেমেন্ট গেটওয়ে, ম্যানুয়াল একাউন্ট এবং ডেলিভারি চার্জ কনফিগার করুন।</p>
                </div>
            </div>

            <!-- Payment Methods Grid -->
            <div>
                <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-700 mb-4">পেমেন্ট মেথডসমূহ</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($payment_methods as $method):
                        $config = json_decode($method['config_json'] ?? '{}', true) ?: [];
                        $is_active = (bool)$method['is_active'];
                        ?>
                        <div class="bg-white border border-[#e7e3da] rounded-xl p-6 flex flex-col justify-between space-y-6 shadow-xs hover:border-stone-400 transition-colors">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-[#f4f1ea] border border-[#d8d3c7] flex items-center justify-center font-mono font-bold text-xs text-stone-800 shadow-xs">
                                            <?php echo strtoupper(substr($method['method_key'], 0, 3)); ?>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-stone-900 text-sm">
                                                <?php echo htmlspecialchars($method['method_name']); ?>
                                            </h3>
                                            <span class="text-[11px] font-mono text-stone-500 uppercase">
                                                <?php echo htmlspecialchars($method['method_key']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               onchange="togglePaymentMethod('<?php echo $method['method_key']; ?>', this.checked)"
                                               class="sr-only peer" <?php echo $is_active ? 'checked' : ''; ?>>
                                        <div class="w-9 h-5 bg-[#e7e2d6] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-stone-900">
                                        </div>
                                    </label>
                                </div>
                                <p class="text-xs text-stone-600 font-normal leading-relaxed">
                                    <?php if ($method['method_key'] === 'cod'): ?>
                                        ক্যাশ অন ডেলিভারি (পণ্য হাতে পেয়ে মূল্য পরিশোধ)।
                                    <?php elseif ($method['method_key'] === 'sslcommerz'): ?>
                                        অটোমেটেড পেমেন্ট গেটওয়ে (কার্ড, ইন্টারনেট ব্যাংকিং ও মোবাইল ওয়ালেট)।
                                    <?php else: ?>
                                        ডিরেক্ট মোবাইল ব্যাংকিং পেমেন্ট মেথড।
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div>
                                <?php if (in_array($method['method_key'], ['bkash', 'nagad', 'sslcommerz'])): ?>
                                    <button onclick="openPaymentConfigModal('<?php echo $method['method_key']; ?>', <?php echo htmlspecialchars(json_encode($config), ENT_QUOTES); ?>)"
                                            class="w-full py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg font-mono text-xs font-semibold transition-colors shadow-xs">
                                        কনফিগারেশন
                                    </button>
                                <?php else: ?>
                                    <div class="py-2 text-center text-[11px] text-stone-400 font-mono">
                                        অতিরিক্ত কনফিগারেশন নেই
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Delivery Charges Section -->
            <div class="pt-6 border-t border-[#e7e3da]">
                <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-700 mb-4">ডেলিভারি চার্জ সেটিংস</h2>
                <div class="bg-white border border-[#e7e3da] rounded-xl p-6 max-w-xl shadow-xs">
                    <form onsubmit="updateDeliveryCharges(event)" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1.5">কক্সবাজার শহর (Inside)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-mono text-xs">৳</span>
                                    <input type="number" id="charge_inside" value="<?php echo htmlspecialchars($inside_charge); ?>" required
                                           class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-7 pr-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1.5">আউটসাইড কক্সবাজার (Outside)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-mono text-xs">৳</span>
                                    <input type="number" id="charge_outside" value="<?php echo htmlspecialchars($outside_charge); ?>" required
                                           class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-7 pr-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="delivery-submit-btn"
                                class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-xs">
                            চার্জ সংরক্ষণ করুন
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Payment API Config Modal -->
    <div id="payment-config-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-xs" onclick="closePaymentConfigModal()"></div>
        <div class="bg-white border border-[#e7e3da] w-full max-w-lg rounded-2xl shadow-2xl relative z-10 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5]">
                <div>
                    <h3 id="payment-modal-title" class="text-base font-bold text-stone-900 uppercase font-mono">পেমেন্ট কনফিগারেশন</h3>
                    <p class="text-xs text-stone-500 mt-0.5">API ক্রেডেনশিয়াল ও সিক্রেট তথ্য সেট করুন।</p>
                </div>
                <button onclick="closePaymentConfigModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form onsubmit="savePaymentConfig(event)" class="p-6 space-y-4">
                <input type="hidden" name="method_key" id="config_method_key">
                <div id="config-fields" class="space-y-4 text-xs font-sans">
                    <!-- Fields injected via JS -->
                </div>
                <div class="pt-4 border-t border-[#e7e3da] flex justify-end gap-3">
                    <button type="button" onclick="closePaymentConfigModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 rounded-lg text-xs font-medium">
                        বাতিল
                    </button>
                    <button type="submit" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold shadow-xs">
                        সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-stone-900 text-white px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 text-xs font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="toast-message">নোটিফিকেশন বার্তা</span>
        </div>
    </div>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        function togglePaymentMethod(methodKey, isActive) {
            fetch('/admin/dashboard/update_payment_method.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `method_key=${methodKey}&is_active=${isActive ? 1 : 0}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`${methodKey.toUpperCase()} স্ট্যাটাস আপডেট হয়েছে।`);
                } else {
                    alert('ত্রুটি: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('আপডেট করতে সমস্যা হয়েছে।');
            });
        }

        function openPaymentConfigModal(methodKey, config) {
            document.getElementById('config_method_key').value = methodKey;
            document.getElementById('payment-modal-title').innerText = `${methodKey.toUpperCase()} কনফিগারেশন`;
            const container = document.getElementById('config-fields');

            let html = '';
            if (methodKey === 'bkash') {
                html = `
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">App Key</label>
                        <input type="text" name="app_key" value="${config.app_key || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">App Secret</label>
                        <input type="password" name="app_secret" value="${config.app_secret || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Username</label>
                        <input type="text" name="username" value="${config.username || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Password</label>
                        <input type="password" name="password" value="${config.password || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                `;
            } else if (methodKey === 'nagad') {
                html = `
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Merchant ID</label>
                        <input type="text" name="merchant_id" value="${config.merchant_id || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Merchant Phone</label>
                        <input type="text" name="merchant_phone" value="${config.merchant_phone || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                `;
            } else if (methodKey === 'sslcommerz') {
                html = `
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Store ID</label>
                        <input type="text" name="store_id" value="${config.store_id || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Store Password</label>
                        <input type="password" name="store_passwd" value="${config.store_passwd || ''}" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-stone-700 font-bold uppercase mb-1">Environment Mode</label>
                        <select name="is_sandbox" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                            <option value="1" ${config.is_sandbox == '1' ? 'selected' : ''}>Sandbox (Testing)</option>
                            <option value="0" ${config.is_sandbox == '0' ? 'selected' : ''}>Live (Production)</option>
                        </select>
                    </div>
                `;
            }

            container.innerHTML = html;
            document.getElementById('payment-config-modal').classList.remove('hidden');
            document.getElementById('payment-config-modal').classList.add('flex');
        }

        function closePaymentConfigModal() {
            document.getElementById('payment-config-modal').classList.add('hidden');
            document.getElementById('payment-config-modal').classList.remove('flex');
        }

        function savePaymentConfig(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch('/admin/dashboard/update_payment_method.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("কনফিগারেশন সংরক্ষিত হয়েছে।");
                    closePaymentConfigModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('ত্রুটি: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('সংরক্ষণ করতে সমস্যা হয়েছে।');
            });
        }

        function updateDeliveryCharges(e) {
            e.preventDefault();
            const inside = document.getElementById('charge_inside').value;
            const outside = document.getElementById('charge_outside').value;
            const btn = document.getElementById('delivery-submit-btn');

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            const formData = new FormData();
            formData.append('action', 'update_delivery_charges');
            formData.append('inside_charge', inside);
            formData.append('outside_charge', outside);

            fetch('/admin/dashboard/process_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('ডেলিভারি চার্জ আপডেট হয়েছে।');
                } else {
                    alert('ত্রুটি: ' + (data.message || 'আপডেট করা সম্ভব হয়নি।'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('সার্ভার সংযোগে সমস্যা হয়েছে।');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'চার্জ সংরক্ষণ করুন';
            });
        }
    </script>
</body>
</html>
