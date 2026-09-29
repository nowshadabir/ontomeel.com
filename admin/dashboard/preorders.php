<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'preorders';
$page_title = 'প্রি-অর্ডার ম্যানেজমেন্ট';

// Fetch Pre-order Campaigns
$admin_preorders = $pdo->query("SELECT * FROM pre_orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pre-order Customer Bookings
$admin_preorder_bookings = $pdo->query("
    SELECT oi.id as booking_id, oi.order_id, oi.quantity, oi.unit_price, oi.total_price,
           o.invoice_no, o.order_date, o.payment_status, o.payment_method, o.order_status,
           o.shipping_address, o.division, o.district, o.upazila, o.notes,
           COALESCE(m.full_name, o.guest_name, 'গ্রাহক') as customer_name,
           COALESCE(m.phone, o.guest_phone, '-') as customer_phone,
           COALESCE(m.email, o.guest_email, '-') as customer_email,
           po.id as preorder_id, po.title as po_title, po.release_date as po_release, po.cover_image as po_cover, po.is_hot_deal 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN pre_orders po ON oi.preorder_id = po.id
    LEFT JOIN members m ON o.member_id = m.id
    ORDER BY o.order_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

$total_campaigns = count($admin_preorders);
$total_bookings = count($admin_preorder_bookings);
$active_campaigns = count(array_filter($admin_preorders, fn($p) => $p['status'] === 'Open'));
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
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                            <?php echo bn_num($total_campaigns); ?> টি ক্যাম্পেইন • <?php echo bn_num($total_bookings); ?> টি বুকিং
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">আসন্ন বইগুলোর প্রি-বুকিং তথ্য, ক্যাম্পেইন ও গ্রাহকদের বুকিং অর্ডার পরিচালনা করুন।</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="openPreorderModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>নতুন প্রি-অর্ডার ক্যাম্পেইন</span>
                    </button>
                </div>
            </div>

            <!-- Sub-Tabs Navigation -->
            <div class="flex items-center gap-2 border-b border-[#e7e3da] pb-px">
                <button onclick="switchPreorderTab('manage')" id="tab-btn-manage"
                        class="px-4 py-2.5 text-xs font-bold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2">
                    <span>ক্যাম্পেইন ম্যানেজমেন্ট</span>
                    <span class="px-2 py-0.5 bg-stone-900 text-white rounded-full text-[10px]"><?php echo $total_campaigns; ?></span>
                </button>
                <button onclick="switchPreorderTab('bookings')" id="tab-btn-bookings"
                        class="px-4 py-2.5 text-xs font-medium font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2">
                    <span>বুকিং অর্ডারসমূহ</span>
                    <span class="px-2 py-0.5 bg-[#f4f1ea] text-stone-700 rounded-full text-[10px]"><?php echo $total_bookings; ?></span>
                </button>
            </div>

            <!-- Subtab 1: Campaigns Management -->
            <div id="subcontent-manage" class="space-y-6">
                <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-3.5 px-5 font-bold">বই ও লেখক</th>
                                    <th class="py-3.5 px-5 font-bold">মূল্য ও ছাড়</th>
                                    <th class="py-3.5 px-5 font-bold">রিলিজ ডেট</th>
                                    <th class="py-3.5 px-5 font-bold">ক্যাম্পেইন স্ট্যাটাস</th>
                                    <th class="py-3.5 px-5 font-bold">ফিচারসমূহ</th>
                                    <th class="py-3.5 px-5 font-bold text-right">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                                <?php if (empty($admin_preorders)): ?>
                                    <tr>
                                        <td colspan="6" class="py-16 text-center text-stone-500">
                                            <p class="text-sm text-stone-600 font-medium">কোনো প্রি-অর্ডার ক্যাম্পেইন ডাটা পাওয়া যায়নি।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admin_preorders as $po): 
                                        $po_cover_path = strpos($po['cover_image'], 'http') === 0 ? $po['cover_image'] : '/assets/img/preorders/' . trim($po['cover_image']);
                                    ?>
                                        <tr class="hover:bg-[#faf8f5] transition-colors">
                                            <td class="py-4 px-5">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="w-10 h-14 rounded bg-[#f4f1ea] border border-[#d8d3c7] overflow-hidden flex-shrink-0 shadow-xs">
                                                        <img src="<?php echo htmlspecialchars($po_cover_path); ?>"
                                                             class="w-full h-full object-cover"
                                                             onerror="this.src='/assets/img/og-image-for-prebooking.jpg';">
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-stone-900 text-sm">
                                                            <?php
                                                            $combo_title = htmlspecialchars($po['title']);
                                                            if (!empty($po['second_title'])) {
                                                                $combo_title .= ' + ' . htmlspecialchars($po['second_title']) . ' (কম্বো)';
                                                            }
                                                            echo $combo_title;
                                                            ?>
                                                        </div>
                                                        <div class="text-xs text-stone-500 mt-0.5">
                                                            <?php echo htmlspecialchars($po['author']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-5 font-mono">
                                                <span class="font-bold text-stone-900">৳<?php echo bn_num((int)$po['discount_price']); ?></span>
                                                <?php if (!empty($po['price']) && $po['price'] > $po['discount_price']): ?>
                                                    <span class="text-xs text-stone-400 line-through ml-1.5 font-normal">৳<?php echo bn_num((int)$po['price']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs text-stone-600">
                                                <?php echo !empty($po['release_date']) ? date('d M, Y', strtotime($po['release_date'])) : '—'; ?>
                                            </td>
                                            <td class="py-4 px-5">
                                                <?php if ($po['status'] === 'Open'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        চলছে (Open)
                                                    </span>
                                                <?php elseif ($po['status'] === 'Upcoming'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        আসন্ন (Upcoming)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                                                        বন্ধ (Closed)
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-5">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <?php if (!empty($po['is_hot_deal'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-mono font-bold">🔥 হট ডিল</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($po['free_delivery'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-mono font-medium">ফ্রি ডেলিভারি</span>
                                                    <?php endif; ?>
                                                    <?php if (empty($po['is_hot_deal']) && empty($po['free_delivery'])): ?>
                                                        <span class="text-stone-400 font-mono text-[11px]">সাধারণ</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-5 text-right font-mono">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="/pre-booking/<?php echo !empty($po['slug']) ? 'book/' . htmlspecialchars($po['slug']) : 'book-details.php?id=' . $po['id']; ?>"
                                                       target="_blank" class="p-1.5 text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] rounded transition-colors" title="প্রিভিউ দেখুন">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    <button onclick="editPreorder(<?php echo htmlspecialchars(json_encode($po), ENT_QUOTES); ?>)"
                                                            class="p-1.5 text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] rounded transition-colors" title="সম্পাদনা">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deletePreorder(<?php echo (int)$po['id']; ?>)"
                                                            class="p-1.5 text-stone-600 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="ডিলিট">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subtab 2: Bookings List -->
            <div id="subcontent-bookings" class="space-y-6 hidden">
                <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-3.5 px-5 font-bold">ইনভয়েস ও তারিখ</th>
                                    <th class="py-3.5 px-5 font-bold">গ্রাহক</th>
                                    <th class="py-3.5 px-5 font-bold">বই ও পরিমাণ</th>
                                    <th class="py-3.5 px-5 font-bold">রিলিজ ডেট</th>
                                    <th class="py-3.5 px-5 font-bold">স্ট্যাটাস ও পেমেন্ট</th>
                                    <th class="py-3.5 px-5 font-bold text-right">বিস্তারিত</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                                <?php if (empty($admin_preorder_bookings)): ?>
                                    <tr>
                                        <td colspan="6" class="py-16 text-center text-stone-500">
                                            <p class="text-sm text-stone-600 font-medium">আপাতত কোনো প্রি-অর্ডার বুকিং অর্ডার নেই।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admin_preorder_bookings as $booking): ?>
                                        <tr class="hover:bg-[#faf8f5] transition-colors">
                                            <td class="py-4 px-5 font-mono">
                                                <div class="font-bold text-stone-900">#<?php echo htmlspecialchars($booking['invoice_no']); ?></div>
                                                <div class="text-[11px] text-stone-500 mt-0.5"><?php echo date('d M, Y', strtotime($booking['order_date'])); ?></div>
                                            </td>
                                            <td class="py-4 px-5">
                                                <div class="font-bold text-stone-900"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                                <div class="text-xs text-stone-500 font-mono mt-0.5"><?php echo htmlspecialchars($booking['customer_phone']); ?></div>
                                            </td>
                                            <td class="py-4 px-5">
                                                <div class="font-medium text-stone-900"><?php echo htmlspecialchars($booking['po_title']); ?></div>
                                                <div class="text-xs text-stone-500 font-mono mt-0.5">পরিমাণ: <?php echo bn_num((int)$booking['quantity']); ?> টি</div>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs text-stone-600">
                                                <?php echo !empty($booking['po_release']) ? date('d M, Y', strtotime($booking['po_release'])) : '—'; ?>
                                            </td>
                                            <td class="py-4 px-5">
                                                <div class="flex flex-col gap-1 items-start">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-medium <?php echo $booking['order_status'] === 'Delivered' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($booking['order_status'] === 'Processing' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]'); ?>">
                                                        <?php echo htmlspecialchars($booking['order_status']); ?>
                                                    </span>
                                                    <?php if (($booking['payment_status'] ?? '') === 'Paid'): ?>
                                                        <span class="text-[10px] text-emerald-700 font-mono font-semibold">✓ Paid (<?php echo htmlspecialchars($booking['payment_method'] ?? 'SSL'); ?>)</span>
                                                    <?php else: ?>
                                                        <span class="text-[10px] text-amber-700 font-mono font-semibold">⏳ Unpaid (পেন্ডিং)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-5 text-right font-mono">
                                                <button onclick="viewPreOrderDetails(<?php echo htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8'); ?>)"
                                                        class="px-3 py-1.5 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-colors shadow-xs">
                                                    ভিউ
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Pre-order Create/Edit Modal -->
    <div id="preorder-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-xs" onclick="closePreorderModal()"></div>
        <div class="bg-white border border-[#e7e3da] w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl relative z-10 overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5] shrink-0">
                <div>
                    <h3 id="po-modal-title" class="text-lg font-bold text-stone-900">নতুন প্রি-অর্ডার</h3>
                    <p class="text-xs text-stone-500 mt-0.5">ক্যাম্পেইনের তথ্য ও বইয়ের বিস্তারিত ইনপুট করুন।</p>
                </div>
                <button onclick="closePreorderModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="preorder-form" onsubmit="handlePreorder(event)" class="flex-1 overflow-y-auto p-6 space-y-6">
                <input type="hidden" name="po_id" id="po_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">১ম বইয়ের নাম (বা প্রি-অর্ডার বই) *</label>
                            <input type="text" name="title" required placeholder="বইয়ের নাম"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">২য় বইয়ের নাম (কম্বো হলে)</label>
                            <input type="text" name="second_title" placeholder="২য় বইয়ের নাম (ঐচ্ছিক)"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">লেখক *</label>
                            <input type="text" name="author" required placeholder="লেখকের নাম"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">সাব-টাইটেল</label>
                            <input type="text" name="sub_title" placeholder="যেমন: কম্বো প্যাকে বিশেষ ছাড়!"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">বইয়ের বর্ণনা</label>
                            <textarea name="description" rows="3" placeholder="বইয়ের বিস্তারিত বর্ণনা বা কম্বো অফারের বিবরণ"
                                      class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">মূল মূল্য *</label>
                                <input type="number" name="price" required placeholder="৳ ০০"
                                       class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">ছাড়ের মূল্য</label>
                                <input type="number" name="discount_price" placeholder="৳ ০০"
                                       class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">রিলিজ ডেট *</label>
                                <input type="date" name="release_date" required
                                       class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">স্ট্যাটাস</label>
                                <select name="status" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2.5 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                                    <option value="Upcoming">Upcoming (আসন্ন)</option>
                                    <option value="Open">Open (চলছে)</option>
                                    <option value="Closed">Closed (বন্ধ)</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-3 bg-[#faf8f5] border border-[#e7e3da] rounded-lg flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-stone-900 block">হট ডিল হিসেবে দেখান</span>
                                <span class="text-[11px] text-stone-500 block">হোমপেজের বিশেষ সেকশনে প্রদর্শিত হবে</span>
                            </div>
                            <input type="checkbox" name="is_hot_deal" class="w-4 h-4 rounded text-stone-900 focus:ring-0">
                        </div>

                        <div class="p-3 bg-[#faf8f5] border border-[#e7e3da] rounded-lg flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-stone-900 block">ফ্রি ডেলিভারি সুবিধা</span>
                                <span class="text-[11px] text-stone-500 block">চেকআউটে ডেলিভারি চার্জ মওকুফ থাকবে</span>
                            </div>
                            <input type="checkbox" name="free_delivery" class="w-4 h-4 rounded text-stone-900 focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">১ম কাভার ইমেজ</label>
                            <input type="file" name="cover_image" accept="image/*"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs text-stone-800 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">২য় কাভার ইমেজ (ঐচ্ছিক)</label>
                            <input type="file" name="second_cover_image" accept="image/*"
                                   class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs text-stone-800 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#e7e3da] flex justify-end gap-3">
                    <button type="button" onclick="closePreorderModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 rounded-lg text-xs font-medium transition-colors">
                        বাতিল
                    </button>
                    <button type="submit" id="po-submit-btn" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-xs">
                        সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="booking-details-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-xs" onclick="closeBookingModal()"></div>
        <div class="bg-white border border-[#e7e3da] w-full max-w-xl rounded-2xl shadow-2xl relative z-10 overflow-hidden">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5]">
                <div>
                    <h3 class="text-base font-bold text-stone-900">প্রি-অর্ডার বুকিং বিবরণ</h3>
                    <p id="modal-invoice-no" class="text-xs font-mono text-stone-500 mt-0.5">#—</p>
                </div>
                <button onclick="closeBookingModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="booking-details-body" class="p-6 space-y-4 text-xs font-sans text-stone-800">
                <!-- Injected via JS -->
            </div>
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

        function switchPreorderTab(tab) {
            const btnManage = document.getElementById('tab-btn-manage');
            const btnBookings = document.getElementById('tab-btn-bookings');
            const contentManage = document.getElementById('subcontent-manage');
            const contentBookings = document.getElementById('subcontent-bookings');

            if (tab === 'manage') {
                contentManage.classList.remove('hidden');
                contentBookings.classList.add('hidden');
                btnManage.className = "px-4 py-2.5 text-xs font-bold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2";
                btnBookings.className = "px-4 py-2.5 text-xs font-medium font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2";
            } else {
                contentManage.classList.add('hidden');
                contentBookings.classList.remove('hidden');
                btnBookings.className = "px-4 py-2.5 text-xs font-bold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2";
                btnManage.className = "px-4 py-2.5 text-xs font-medium font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2";
            }
        }

        function openPreorderModal() {
            document.getElementById('po-modal-title').innerText = "নতুন প্রি-অর্ডার";
            document.getElementById('preorder-form').reset();
            document.getElementById('po_id').value = "";
            document.getElementById('preorder-modal').classList.remove('hidden');
            document.getElementById('preorder-modal').classList.add('flex');
        }

        function closePreorderModal() {
            document.getElementById('preorder-modal').classList.add('hidden');
            document.getElementById('preorder-modal').classList.remove('flex');
        }

        function editPreorder(po) {
            openPreorderModal();
            document.getElementById('po-modal-title').innerText = "প্রি-অর্ডার সম্পাদনা";
            document.getElementById('po_id').value = po.id;
            const form = document.getElementById('preorder-form');
            form.title.value = po.title || '';
            form.second_title.value = po.second_title || '';
            form.author.value = po.author || '';
            form.sub_title.value = po.sub_title || '';
            form.description.value = po.description || '';
            form.price.value = po.price || '';
            form.discount_price.value = po.discount_price || '';
            form.release_date.value = po.release_date || '';
            form.status.value = po.status || 'Upcoming';
            form.is_hot_deal.checked = parseInt(po.is_hot_deal) === 1;
            form.free_delivery.checked = parseInt(po.free_delivery) === 1;
        }

        function handlePreorder(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const btn = document.getElementById('po-submit-btn');

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            fetch('/admin/dashboard/process_preorder.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'সফলভাবে সংরক্ষিত হয়েছে।');
                    closePreorderModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('ত্রুটি: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('সংরক্ষণ করতে সমস্যা হয়েছে।');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'সংরক্ষণ করুন';
            });
        }

        function deletePreorder(id) {
            if (!confirm('আপনি কি নিশ্চিত যে এই প্রি-অর্ডার ক্যাম্পেইনটি ডিলিট করতে চান?')) return;

            fetch('/admin/dashboard/delete_preorder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'মুছে ফেলা হয়েছে।');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('ত্রুটি: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('মুছে ফেলতে সমস্যা হয়েছে।');
            });
        }

        function viewPreOrderDetails(booking) {
            document.getElementById('modal-invoice-no').innerText = '#' + (booking.invoice_no || '');
            const body = document.getElementById('booking-details-body');
            body.innerHTML = `
                <div class="bg-[#faf8f5] p-4 rounded-lg border border-[#e7e3da] space-y-3">
                    <div class="flex justify-between">
                        <span class="text-stone-500">গ্রাহকের নাম:</span>
                        <span class="font-bold text-stone-900">${booking.customer_name || '—'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">মোবাইল:</span>
                        <span class="font-mono text-stone-800">${booking.customer_phone || '—'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">ইমেইল:</span>
                        <span class="font-mono text-stone-600">${booking.customer_email || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">ঠিকানা:</span>
                        <span class="text-stone-800 text-right max-w-[250px]">${booking.shipping_address || 'N/A'}</span>
                    </div>
                </div>

                <div class="bg-[#faf8f5] p-4 rounded-lg border border-[#e7e3da] space-y-3">
                    <div class="flex justify-between">
                        <span class="text-stone-500">বইয়ের শিরোনাম:</span>
                        <span class="font-bold text-stone-900">${booking.po_title || '—'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">পরিমাণ:</span>
                        <span class="font-mono text-stone-900 font-bold">${booking.quantity || 1} টি</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">পেমেন্ট মেথড:</span>
                        <span class="font-mono text-stone-800 font-bold">${booking.payment_method || 'SSLCommerz'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">পেমেন্ট স্ট্যাটাস:</span>
                        <span class="font-mono ${booking.payment_status === 'Paid' ? 'text-emerald-700 font-bold' : 'text-amber-700 font-bold'}">${booking.payment_status || 'Pending'}</span>
                    </div>
                </div>
            `;
            document.getElementById('booking-details-modal').classList.remove('hidden');
            document.getElementById('booking-details-modal').classList.add('flex');
        }

        function closeBookingModal() {
            document.getElementById('booking-details-modal').classList.add('hidden');
            document.getElementById('booking-details-modal').classList.remove('flex');
        }
    </script>
</body>
</html>
