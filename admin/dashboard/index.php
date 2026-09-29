<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'overview';
$page_title = 'ড্যাশবোর্ড ওভারভিউ';

// Optimized aggregation queries
// 1. Total & Today's Revenue
$total_revenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
$today_revenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid' AND DATE(order_date) = CURDATE()")->fetchColumn();

// 2. Orders summary
$total_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$processing_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Processing'")->fetchColumn();

// 3. Books & Inventory
$total_books = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$out_of_stock = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE stock_qty <= 0")->fetchColumn();

// 4. Members & Verification
$total_members = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$pending_student_verifications = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'pending'")->fetchColumn();
$pending_membership_requests = (int)$pdo->query("SELECT COUNT(*) FROM membership_requests WHERE status = 'Pending'")->fetchColumn();

// 5. Active Borrows
$active_borrows_count = (int)$pdo->query("SELECT COUNT(*) FROM borrows WHERE status = 'Active'")->fetchColumn();
$overdue_borrows_count = (int)$pdo->query("SELECT COUNT(*) FROM borrows WHERE status = 'Overdue'")->fetchColumn();

// 6. Recent 8 Orders
$recent_orders_stmt = $pdo->query("
    SELECT o.id, o.invoice_no, 
           COALESCE(m.full_name, o.guest_name, 'গ্রাহক') as customer_name,
           COALESCE(m.phone, o.guest_phone, '-') as customer_phone,
           o.total_amount, o.payment_status, o.payment_method, o.order_status, o.order_date 
    FROM orders o 
    LEFT JOIN members m ON o.member_id = m.id 
    ORDER BY o.order_date DESC 
    LIMIT 8
");
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. Last 6 Months Sales Aggregate for visual bar summary
$monthly_sales_stmt = $pdo->query("
    SELECT DATE_FORMAT(order_date, '%b') as month_name, SUM(total_amount) as monthly_total, COUNT(*) as count 
    FROM orders 
    WHERE payment_status = 'Paid' AND order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(order_date), MONTH(order_date), DATE_FORMAT(order_date, '%b')
    ORDER BY YEAR(order_date) ASC, MONTH(order_date) ASC
");
$monthly_sales = $monthly_sales_stmt->fetchAll(PDO::FETCH_ASSOC);
$max_monthly = 1;
foreach ($monthly_sales as $m) {
    if ((float)$m['monthly_total'] > $max_monthly) {
        $max_monthly = (float)$m['monthly_total'];
    }
}
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
                    <p class="text-xs text-stone-500 font-normal mt-1">অন্ত্যমিল বুকশপ ও লাইব্রেরি সিস্টেমের সার্বিক পরিসংখ্যান ও সাম্প্রতিক কার্যক্রম।</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f3f0e8] text-stone-700 border border-[#d8d3c7] hover:border-stone-400 rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>রিফ্রেশ</span>
                    </button>
                    <a href="/admin/orders" class="inline-flex items-center gap-2 px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                        <span>সকল অর্ডার দেখুন</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Pending Action Alerts (if any) -->
            <?php if ($pending_orders > 0 || $pending_student_verifications > 0 || $pending_membership_requests > 0 || $overdue_borrows_count > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php if ($pending_orders > 0): ?>
                        <a href="/admin/orders" class="p-3.5 bg-amber-50/80 hover:bg-amber-100/70 border border-amber-200 rounded-xl flex items-center justify-between transition-all group shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-xs text-amber-950 font-medium"><?php echo bn_num($pending_orders); ?> টি নতুন অর্ডার পেন্ডিং</span>
                            </div>
                            <span class="text-xs font-mono text-amber-700 group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($pending_student_verifications > 0): ?>
                        <a href="/admin/student-memberships" class="p-3.5 bg-blue-50/80 hover:bg-blue-100/70 border border-blue-200 rounded-xl flex items-center justify-between transition-all group shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                                <span class="text-xs text-blue-950 font-medium"><?php echo bn_num($pending_student_verifications); ?> টি স্টুডেন্ট আবেদন</span>
                            </div>
                            <span class="text-xs font-mono text-blue-700 group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($pending_membership_requests > 0): ?>
                        <a href="/admin/membership" class="p-3.5 bg-purple-50/80 hover:bg-purple-100/70 border border-purple-200 rounded-xl flex items-center justify-between transition-all group shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
                                <span class="text-xs text-purple-950 font-medium"><?php echo bn_num($pending_membership_requests); ?> টি মেম্বারশিপ আবেদন</span>
                            </div>
                            <span class="text-xs font-mono text-purple-700 group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($overdue_borrows_count > 0): ?>
                        <a href="/admin/borrows" class="p-3.5 bg-rose-50/80 hover:bg-rose-100/70 border border-rose-200 rounded-xl flex items-center justify-between transition-all group shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-xs text-rose-950 font-medium"><?php echo bn_num($overdue_borrows_count); ?> টি বই ফেরত মেয়াদোত্তীর্ণ</span>
                            </div>
                            <span class="text-xs font-mono text-rose-700 group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Primary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Revenue -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মোট বিক্রয় (Paid)</span>
                        <span class="text-[10px] font-mono font-bold text-stone-500 bg-[#f4f1ea] px-1.5 py-0.5 rounded">BDT</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        ৳<?php echo bn_num(number_format($total_revenue)); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">
                        আজকের বিক্রয়: ৳<?php echo bn_num(number_format($today_revenue)); ?>
                    </span>
                </div>

                <!-- Card 2: Orders Total -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মোট অর্ডার</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($total_orders); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">
                        পেন্ডিং: <?php echo bn_num($pending_orders); ?> • প্রসেসিং: <?php echo bn_num($processing_orders); ?>
                    </span>
                </div>

                <!-- Card 3: Books in Library -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">ইনভেন্টরি বই সংখ্যা</span>
                        <span class="w-2 h-2 rounded-full <?php echo $out_of_stock > 0 ? 'bg-amber-500' : 'bg-emerald-500'; ?>"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($total_books); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">
                        <?php echo $out_of_stock > 0 ? bn_num($out_of_stock) . " টি বই স্টক আউট" : "সকল বই স্টকে আছে"; ?>
                    </span>
                </div>

                <!-- Card 4: Members & Borrows -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">নিবন্ধিত পাঠক ও মেম্বার</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($total_members); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">
                        বর্তমানে ধার: <?php echo bn_num($active_borrows_count); ?> টি বই
                    </span>
                </div>
            </div>

            <!-- Two Column Section: Monthly Trends & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sales Trend (Last 6 Months) -->
                <div class="lg:col-span-2 bg-white border border-[#e7e3da] rounded-xl p-6 space-y-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-[#f0ece3] pb-4">
                        <div>
                            <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-700">মাসিক বিক্রয় পরিসংখ্যান</h2>
                            <p class="text-xs text-stone-500 mt-0.5">বিগত ৬ মাসের অনুমোদিত পেমেন্ট হিসাব</p>
                        </div>
                        <span class="text-xs font-mono text-stone-500 bg-[#f4f1ea] px-2 py-0.5 rounded">সর্বমোট ৬ মাস</span>
                    </div>

                    <div class="space-y-4 pt-1">
                        <?php if (empty($monthly_sales)): ?>
                            <div class="py-12 text-center text-xs text-stone-500 font-mono">পর্যাপ্ত বিক্রয় তথ্য নেই</div>
                        <?php else: ?>
                            <?php foreach ($monthly_sales as $month):
                                $percent = $max_monthly > 0 ? min(100, round(((float)$month['monthly_total'] / $max_monthly) * 100)) : 0;
                                ?>
                                <div>
                                    <div class="flex items-center justify-between text-xs font-mono mb-1.5">
                                        <span class="text-stone-800 font-semibold"><?php echo htmlspecialchars($month['month_name']); ?></span>
                                        <span class="text-stone-900 font-bold">৳<?php echo bn_num(number_format((float)$month['monthly_total'])); ?> <span class="text-stone-500 font-normal text-[10px]">(<?php echo bn_num($month['count']); ?> টি অর্ডার)</span></span>
                                    </div>
                                    <div class="w-full h-2.5 bg-[#f0ece3] rounded-full overflow-hidden">
                                        <div class="bg-stone-800 h-full rounded-full transition-all duration-500" style="width: <?php echo max(4, $percent); ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Navigation Cards -->
                <div class="lg:col-span-1 bg-white border border-[#e7e3da] rounded-xl p-6 flex flex-col justify-between space-y-4 shadow-xs">
                    <div>
                        <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-700 mb-3 border-b border-[#f0ece3] pb-3">কুইক অ্যাকশন</h2>
                        <div class="space-y-2">
                            <a href="/admin/orders" class="block p-3 rounded-lg bg-[#faf8f5] border border-[#e7e3da] hover:border-stone-400 hover:bg-white transition-all shadow-xs">
                                <span class="text-xs font-bold text-stone-900 block">📦 অর্ডার ম্যানেজমেন্ট</span>
                                <span class="text-[11px] text-stone-500 block mt-0.5">স্ট্যাটাস আপডেট ও ইনভয়েস প্রিন্ট</span>
                            </a>
                            <a href="/admin/inventory" class="block p-3 rounded-lg bg-[#faf8f5] border border-[#e7e3da] hover:border-stone-400 hover:bg-white transition-all shadow-xs">
                                <span class="text-xs font-bold text-stone-900 block">📚 নতুন বই যুক্ত করুন</span>
                                <span class="text-[11px] text-stone-500 block mt-0.5">একক বই বা CSV বাল্ক ইমপোর্ট</span>
                            </a>
                            <a href="/admin/student-memberships" class="block p-3 rounded-lg bg-[#faf8f5] border border-[#e7e3da] hover:border-stone-400 hover:bg-white transition-all shadow-xs">
                                <span class="text-xs font-bold text-stone-900 block">🎓 স্টুডেন্ট মেম্বারশিপ আবেদন</span>
                                <span class="text-[11px] text-stone-500 block mt-0.5">আইডি কার্ড যাচাই ও অনুমোদন</span>
                            </a>
                            <a href="/admin/payments" class="block p-3 rounded-lg bg-[#faf8f5] border border-[#e7e3da] hover:border-stone-400 hover:bg-white transition-all shadow-xs">
                                <span class="text-xs font-bold text-stone-900 block">⚙️ পেমেন্ট ও ডেলিভারি চার্জ</span>
                                <span class="text-[11px] text-stone-500 block mt-0.5">bKash, SSLCommerz ও রেট সেটআপ</span>
                            </a>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#e7e3da] text-[11px] text-stone-500 font-mono">
                        সিস্টেম ভার্সন: Ontomeel Admin v2.0
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-700">সাম্প্রতিক অর্ডারসমূহ</h2>
                        <p class="text-xs text-stone-500 mt-0.5">সর্বশেষ প্রাপ্ত ৮টি গ্রাহক অর্ডার</p>
                    </div>
                    <a href="/admin/orders" class="text-xs font-mono font-semibold text-stone-700 hover:text-stone-950 transition-colors">
                        সকল অর্ডার →
                    </a>
                </div>

                <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-3.5 px-5 font-bold">অর্ডার কোড</th>
                                    <th class="py-3.5 px-5 font-bold">গ্রাহকের নাম ও ফোন</th>
                                    <th class="py-3.5 px-5 font-bold">তারিখ ও সময়</th>
                                    <th class="py-3.5 px-5 font-bold">মোট মূল্য</th>
                                    <th class="py-3.5 px-5 font-bold">পেমেন্ট</th>
                                    <th class="py-3.5 px-5 font-bold">স্ট্যাটাস</th>
                                    <th class="py-3.5 px-5 font-bold text-right">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="7" class="py-12 text-center text-stone-500 font-mono">কোনো অর্ডার পাওয়া যায়নি।</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $ord): ?>
                                        <tr class="hover:bg-[#faf8f5] transition-colors">
                                            <td class="py-3.5 px-5 font-mono">
                                                <a href="/admin/orders" class="font-bold text-stone-900 hover:underline">
                                                    #<?php echo htmlspecialchars($ord['invoice_no'] ?: ('ORD-' . $ord['id'])); ?>
                                                </a>
                                            </td>
                                            <td class="py-3.5 px-5">
                                                <div class="font-semibold text-stone-900"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                                                <div class="text-[11px] text-stone-500 font-mono"><?php echo htmlspecialchars($ord['customer_phone']); ?></div>
                                            </td>
                                            <td class="py-3.5 px-5 font-mono text-xs text-stone-600">
                                                <?php echo date('d M Y, h:i A', strtotime($ord['order_date'])); ?>
                                            </td>
                                            <td class="py-3.5 px-5 font-mono font-bold text-stone-900">
                                                ৳<?php echo bn_num(number_format((float)$ord['total_amount'])); ?>
                                            </td>
                                            <td class="py-3.5 px-5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold <?php echo $ord['payment_status'] === 'Paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200'; ?>">
                                                    <?php echo htmlspecialchars($ord['payment_status'] ?: 'Pending'); ?>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo $ord['order_status'] === 'Delivered' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($ord['order_status'] === 'Processing' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-stone-100 text-stone-800 border border-stone-200'); ?>">
                                                    <?php echo htmlspecialchars($ord['order_status'] ?: 'Pending'); ?>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-5 text-right font-mono">
                                                <a href="/admin/orders" class="px-3 py-1.5 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-colors shadow-xs">
                                                    বিস্তারিত
                                                </a>
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
</body>
</html>