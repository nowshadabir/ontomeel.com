<?php
// admin/dashboard/orders.php
// Dedicated Management Page for All Store Orders (White & Cream UI)

require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'orders';
$page_title = 'অর্ডারসমূহ';

// Fetch all orders with member details if available
$orders_stmt = $pdo->query("SELECT o.*, 
                                   COALESCE(o.invoice_no, CONCAT('ORD-', o.id)) as order_code,
                                   COALESCE(m.full_name, o.guest_name, 'গ্রাহক') as customer_name, 
                                   COALESCE(m.phone, o.guest_phone, '-') as customer_phone,
                                   COALESCE(m.email, o.guest_email, '') as customer_email,
                                   o.shipping_cost as delivery_charge,
                                   m.membership_id
                            FROM orders o 
                            LEFT JOIN members m ON o.member_id = m.id 
                            ORDER BY o.order_date DESC");
$admin_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$order_stats = [
    'total_count' => count($admin_orders),
    'total_sales' => 0,
    'processing_count' => 0,
    'ssl_count' => 0,
    'ssl_amount' => 0,
    'delivered_count' => 0,
];
foreach ($admin_orders as $ord) {
    if ($ord['payment_status'] === 'Paid') {
        $order_stats['total_sales'] += (float)$ord['total_amount'];
    }
    if ($ord['order_status'] === 'Processing' || $ord['order_status'] === 'On Hold') {
        $order_stats['processing_count']++;
    }
    if ($ord['order_status'] === 'Delivered') {
        $order_stats['delivered_count']++;
    }
    if (stripos($ord['payment_method'], 'sslcommerz') !== false) {
        $order_stats['ssl_count']++;
        if ($ord['payment_status'] === 'Paid') {
            $order_stats['ssl_amount'] += (float)$ord['total_amount'];
        }
    }
}

// Pre-fetch all order items
$all_order_items_stmt = $pdo->query("SELECT oi.*, 
                                      COALESCE(oi.unit_price, 0) as price,
                                      COALESCE(b.title, po.title, 'বই') as title, 
                                      COALESCE(b.cover_image, po.cover_image) as cover_image 
                               FROM order_items oi 
                               LEFT JOIN books b ON oi.book_id = b.id 
                               LEFT JOIN pre_orders po ON oi.preorder_id = po.id");
$all_order_items = $all_order_items_stmt->fetchAll(PDO::FETCH_ASSOC);

$order_items_by_order = [];
foreach ($all_order_items as $item) {
    $order_items_by_order[$item['order_id']][] = $item;
}

function getOrderItems($order_id, $order_items_by_order) {
    return $order_items_by_order[$order_id] ?? [];
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
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                            মোট <?php echo bn_num($order_stats['total_count']); ?> টি অর্ডার
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">সকল অর্ডারের স্ট্যাটাস, পেমেন্ট ও ইনভয়েস বিবরণ পরিচালনা করুন।</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f3f0e8] text-stone-700 border border-[#d8d3c7] hover:border-stone-400 rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>রিফ্রেশ</span>
                    </button>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>

            <!-- Order Summary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Orders -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মোট অর্ডার</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($order_stats['total_count']); ?>টি
                    </div>
                    <p class="text-[11px] text-stone-500 mt-0.5 font-mono">
                        ৳<?php echo bn_num(number_format($order_stats['total_sales'])); ?> সংগৃহীত
                    </p>
                </div>

                <!-- Processing / Needs Action -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">প্রসেসিং / অপেক্ষমাণ</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-amber-700 mt-2">
                        <?php echo bn_num($order_stats['processing_count']); ?>টি
                    </div>
                    <p class="text-[11px] text-stone-500 mt-0.5">
                        ডেলিভারি পদক্ষেপ প্রয়োজন
                    </p>
                </div>

                <!-- SSLCommerz Gateway Volume -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">এসএসএলকমার্জ অনলাইন</span>
                        <span class="text-xs font-mono font-bold text-stone-600 bg-[#f4f1ea] px-1.5 py-0.5 rounded">SSL</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($order_stats['ssl_count']); ?>টি
                    </div>
                    <p class="text-[11px] text-stone-500 mt-0.5 font-mono">
                        ৳<?php echo bn_num(number_format($order_stats['ssl_amount'])); ?> গেটওয়ে আদায়
                    </p>
                </div>

                <!-- Delivered -->
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">সফল ডেলিভারি</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($order_stats['delivered_count']); ?>টি
                    </div>
                    <p class="text-[11px] text-stone-500 mt-0.5">
                        সম্পন্ন ও পৌঁছানো হয়েছে
                    </p>
                </div>
            </div>

            <!-- Filters & Search Controls Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl p-4 space-y-3 shadow-xs">
                <!-- Status Filter Pills -->
                <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-[#f0ece3]">
                    <div class="flex items-center gap-1.5 overflow-x-auto py-1 max-w-full">
                        <button onclick="setOrderStatusFilter('all', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-stone-900 text-white shadow-xs" data-status="all">
                            সব অর্ডার (<?php echo bn_num(count($admin_orders)); ?>)
                        </button>
                        <button onclick="setOrderStatusFilter('Processing', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Processing">
                            প্রসেসিং
                        </button>
                        <button onclick="setOrderStatusFilter('On Hold', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="On Hold">
                            অন হোল্ড
                        </button>
                        <button onclick="setOrderStatusFilter('Confirmed', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Confirmed">
                            কনফার্মড
                        </button>
                        <button onclick="setOrderStatusFilter('Shipped', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Shipped">
                            শিপড
                        </button>
                        <button onclick="setOrderStatusFilter('Delivered', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Delivered">
                            ডেলিভারড
                        </button>
                        <button onclick="setOrderStatusFilter('Cancelled', this)" class="order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Cancelled">
                            বাতিল
                        </button>
                    </div>

                    <div class="text-xs text-stone-500 font-mono">
                        প্রদর্শিত: <span id="orders-visible-count" class="font-bold text-stone-900"><?php echo bn_num(count($admin_orders)); ?></span>টি
                    </div>
                </div>

                <!-- Search + Gateway + Status Filters -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="md:col-span-6 relative">
                        <input type="text" id="orderSearchInput" onkeyup="filterOrders()"
                            placeholder="অর্ডার #সিরিয়াল, ইনভয়েস, নাম, ফোন, বা TrxID খুঁজুন..."
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Payment Gateway Filter -->
                    <div class="md:col-span-3">
                        <select id="orderGatewayFilter" onchange="filterOrders()"
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-800 focus:outline-none focus:border-stone-800 cursor-pointer">
                            <option value="all">সব পেমেন্ট মাধ্যম</option>
                            <option value="sslcommerz">SSLCommerz (গেটওয়ে)</option>
                            <option value="bkash">bKash (বিকাশ)</option>
                            <option value="nagad">Nagad (নগদ)</option>
                            <option value="cash">Cash / COD (ক্যাশ অন ডেলিভারি)</option>
                            <option value="card">Card / POS</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="md:col-span-3">
                        <select id="orderPayStatusFilter" onchange="filterOrders()"
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-800 focus:outline-none focus:border-stone-800 cursor-pointer">
                            <option value="all">সব পেমেন্ট স্ট্যাটাস</option>
                            <option value="paid">পেইড (Paid)</option>
                            <option value="pending">পেন্ডিং (Pending)</option>
                            <option value="failed">ব্যর্থ / বাতিল (Failed)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Orders Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 font-bold">অর্ডার তথ্য</th>
                                <th class="py-3.5 px-5 font-bold">গ্রাহক</th>
                                <th class="py-3.5 px-5 font-bold">পেমেন্ট</th>
                                <th class="py-3.5 px-5 font-bold">অর্ডার স্ট্যাটাস</th>
                                <th class="py-3.5 px-5 font-bold">মোট মূল্য</th>
                                <th class="py-3.5 px-5 font-bold text-right">পদক্ষেপ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans" id="ordersTableBody">
                            <?php if (empty($admin_orders)): ?>
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-stone-500 font-mono">
                                        কোনো অর্ডার পাওয়া যায়নি।
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admin_orders as $order):
                                    $items = getOrderItems($order['id'], $order_items_by_order);
                                    $items_list = implode(', ', array_column($items, 'title'));
                                    $item_count = count($items);
                                    
                                    $status_val = $order['order_status'];
                                    $pay_status_val = $order['payment_status'];
                                    $pay_method_lower = strtolower($order['payment_method'] ?? '');
                                    $is_ssl = (strpos($pay_method_lower, 'sslcommerz') !== false);
                                    $is_bkash = (strpos($pay_method_lower, 'bkash') !== false);
                                    $is_nagad = (strpos($pay_method_lower, 'nagad') !== false);
                                    $is_cod = (strpos($pay_method_lower, 'cash') !== false || strpos($pay_method_lower, 'cod') !== false);

                                    $search_tokens = strtolower(
                                        $order['order_code'] . ' ' .
                                        $order['customer_name'] . ' ' .
                                        $order['customer_phone'] . ' ' .
                                        ($order['trx_id'] ?? '') . ' ' .
                                        ($order['payment_method'] ?? '') . ' ' .
                                        $items_list
                                    );
                                ?>
                                <tr class="order-row hover:bg-[#faf8f5] transition-colors"
                                    id="order-row-<?php echo $order['id']; ?>"
                                    data-search="<?php echo htmlspecialchars($search_tokens); ?>"
                                    data-status="<?php echo htmlspecialchars($status_val); ?>"
                                    data-gateway="<?php echo htmlspecialchars($pay_method_lower); ?>"
                                    data-paystatus="<?php echo htmlspecialchars(strtolower($pay_status_val)); ?>">
                                    
                                    <!-- Order Info -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-stone-900 text-sm">
                                                #<?php echo htmlspecialchars($order['order_code']); ?>
                                            </span>
                                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($order['order_code']); ?>', 'অর্ডার কোড')"
                                                class="text-stone-400 hover:text-stone-700 p-0.5" title="কোড কপি">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="text-[11px] font-mono text-stone-500 mt-0.5">
                                            <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                                        </div>
                                        <div class="text-[11px] text-stone-500 mt-1 truncate max-w-[200px]" title="<?php echo htmlspecialchars($items_list); ?>">
                                            📦 <?php echo bn_num($item_count); ?>টি আইটেম: <?php echo htmlspecialchars($items_list); ?>
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-stone-900 text-sm">
                                            <?php echo htmlspecialchars($order['customer_name']); ?>
                                        </div>
                                        <div class="text-xs font-mono text-stone-500 mt-0.5">
                                            <?php echo htmlspecialchars($order['customer_phone']); ?>
                                        </div>
                                        <?php if (!empty($order['member_id'])): ?>
                                            <span class="inline-block px-1.5 py-0.5 bg-[#f4f1ea] text-stone-700 rounded text-[10px] font-mono mt-1 border border-[#ded8cc]">
                                                মেম্বার #<?php echo htmlspecialchars($order['membership_id'] ?? $order['member_id']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Payment -->
                                    <td class="py-4 px-5">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 bg-[#f4f1ea] text-stone-800 border border-[#ded8cc] rounded text-[10px] font-mono font-bold uppercase">
                                                    <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
                                                </span>
                                            </div>

                                            <div>
                                                <?php if ($pay_status_val === 'Paid'): ?>
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                        <span>●</span> পেইড
                                                    </span>
                                                <?php elseif ($pay_status_val === 'Failed'): ?>
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                                        <span>●</span> ব্যর্থ
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                        <span>●</span> পেন্ডিং
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($order['trx_id'])): ?>
                                                <div class="text-[10px] font-mono text-stone-500 flex items-center gap-1">
                                                    <span>Trx:</span>
                                                    <span class="truncate max-w-[100px] text-stone-700" title="<?php echo htmlspecialchars($order['trx_id']); ?>">
                                                        <?php echo htmlspecialchars($order['trx_id']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Order Status -->
                                    <td class="py-4 px-5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono font-medium <?php 
                                            echo $status_val === 'Delivered' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 
                                                ($status_val === 'Processing' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 
                                                ($status_val === 'Shipped' ? 'bg-purple-50 text-purple-800 border border-purple-200' : 
                                                ($status_val === 'Cancelled' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-[#f4f1ea] text-stone-800 border border-[#ded8cc]'))); 
                                        ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?php 
                                                echo $status_val === 'Delivered' ? 'bg-emerald-500' : 
                                                    ($status_val === 'Processing' ? 'bg-blue-500' : 
                                                    ($status_val === 'Shipped' ? 'bg-purple-500' : 
                                                    ($status_val === 'Cancelled' ? 'bg-rose-500' : 'bg-stone-500'))); 
                                            ?>"></span>
                                            <?php echo htmlspecialchars($status_val); ?>
                                        </span>
                                    </td>

                                    <!-- Total Amount -->
                                    <td class="py-4 px-5 font-mono">
                                        <div class="text-sm font-bold text-stone-900">
                                            ৳<?php echo bn_num(number_format((float)$order['total_amount'])); ?>
                                        </div>
                                        <?php if ((float)($order['delivery_charge'] ?? 0) > 0): ?>
                                            <div class="text-[10px] text-stone-500">
                                                (ডেলিভারি: ৳<?php echo bn_num(number_format((float)$order['delivery_charge'])); ?>)
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-5 text-right font-mono">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- View Details Button -->
                                            <button onclick='viewOrderModal(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($items), ENT_QUOTES); ?>)'
                                                class="px-2.5 py-1.5 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-colors shadow-xs" title="বিস্তারিত দেখুন">
                                                ভিউ
                                            </button>

                                            <!-- Status Change Trigger -->
                                            <button onclick="openStatusChangeModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($status_val); ?>', '<?php echo htmlspecialchars($pay_status_val); ?>')"
                                                class="px-2.5 py-1.5 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg text-xs transition-colors shadow-xs" title="স্ট্যাটাস পরিবর্তন">
                                                আপডেট
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
        </main>
    </div>

    <!-- Order Details Modal (White & Cream Theme) -->
    <div id="orderDetailsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-2xl max-h-[90vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5] shrink-0">
                <div>
                    <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                        <span>অর্ডার বিবরণ</span>
                        <span id="modalOrderCode" class="font-mono text-stone-500 text-xs">#—</span>
                    </h3>
                    <p id="modalOrderDate" class="text-xs text-stone-500 font-mono mt-0.5">—</p>
                </div>
                <button onclick="closeOrderModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-xs font-sans">
                <!-- Customer & Delivery Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-[#faf8f5] p-4 rounded-xl border border-[#e7e3da] space-y-2">
                        <span class="text-stone-500 font-mono text-[11px] font-bold uppercase block">গ্রাহকের বিবরণ</span>
                        <div id="modalCustomerName" class="font-bold text-stone-900 text-sm">—</div>
                        <div id="modalCustomerPhone" class="font-mono text-stone-700">—</div>
                        <div id="modalCustomerEmail" class="font-mono text-stone-500 text-[11px]">—</div>
                    </div>

                    <div class="bg-[#faf8f5] p-4 rounded-xl border border-[#e7e3da] space-y-2">
                        <span class="text-stone-500 font-mono text-[11px] font-bold uppercase block">ডেলিভারি ঠিকানা</span>
                        <div id="modalShippingAddress" class="text-stone-800 leading-relaxed">—</div>
                        <div id="modalShippingDistrict" class="font-mono text-stone-600 text-[11px]">—</div>
                    </div>
                </div>

                <!-- Order Items Table -->
                <div>
                    <span class="text-stone-700 font-mono text-[11px] font-bold uppercase tracking-wider block mb-2">অর্ডারের আইটেমসমূহ</span>
                    <div class="bg-white rounded-xl border border-[#e7e3da] overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-[#f4f1ea] text-stone-700 font-mono text-[11px] border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-2.5 px-4 font-bold">বই</th>
                                    <th class="py-2.5 px-4 font-bold text-center">পরিমাণ</th>
                                    <th class="py-2.5 px-4 font-bold text-right">একক মূল্য</th>
                                    <th class="py-2.5 px-4 font-bold text-right">মোট</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody" class="divide-y divide-[#e7e3da] text-stone-800 font-mono">
                                <!-- Injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment & Summary -->
                <div class="bg-[#faf8f5] p-4 rounded-xl border border-[#e7e3da] space-y-2 font-mono">
                    <div class="flex justify-between text-stone-600">
                        <span>সাব-টোটাল:</span>
                        <span id="modalSubtotal" class="font-bold text-stone-900">৳০</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>ডেলিভারি চার্জ:</span>
                        <span id="modalDelivery" class="font-bold text-stone-900">৳০</span>
                    </div>
                    <div class="flex justify-between text-stone-900 font-bold text-sm pt-2 border-t border-[#ded8cc]">
                        <span>সর্বমোট মূল্য:</span>
                        <span id="modalGrandTotal">৳০</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-stone-500 pt-1">
                        <span>পেমেন্ট মাধ্যম ও ট্রানজ্যাকশন:</span>
                        <span id="modalPaymentInfo" class="text-stone-800 font-bold">—</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-[#e7e3da] flex justify-between items-center bg-[#faf8f5] shrink-0">
                <button onclick="printOrderInvoice()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-mono font-semibold transition-colors flex items-center gap-2 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>ইনভয়েস প্রিন্ট</span>
                </button>
                <button onclick="closeOrderModal()" class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold shadow-xs">
                    বন্ধ করুন
                </button>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div id="statusChangeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5]">
                <h3 class="text-sm font-bold text-stone-900 font-mono uppercase">অর্ডার স্ট্যাটাস আপডেট</h3>
                <button onclick="closeStatusChangeModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="statusChangeForm" onsubmit="handleStatusChange(event)" class="p-6 space-y-4">
                <input type="hidden" id="statusOrderId" name="order_id">

                <div>
                    <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">অর্ডার স্ট্যাটাস</label>
                    <select id="statusOrderSelect" name="order_status" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                        <option value="Processing">Processing (প্রসেসিং)</option>
                        <option value="On Hold">On Hold (অন হোল্ড)</option>
                        <option value="Confirmed">Confirmed (কনফার্মড)</option>
                        <option value="Shipped">Shipped (কুরিয়ারে পাঠানো হয়েছে)</option>
                        <option value="Delivered">Delivered (পৌঁছে দেওয়া হয়েছে)</option>
                        <option value="Cancelled">Cancelled (বাতিল)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-mono text-stone-600 font-bold uppercase mb-1.5">পেমেন্ট স্ট্যাটাস</label>
                    <select id="statusPaymentSelect" name="payment_status" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 focus:outline-none focus:border-stone-800">
                        <option value="Pending">Pending (পেন্ডিং)</option>
                        <option value="Paid">Paid (পরিশোধিত)</option>
                        <option value="Failed">Failed (ব্যর্থ)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-[#e7e3da] flex justify-end gap-3">
                    <button type="button" onclick="closeStatusChangeModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-semibold">
                        বাতিল
                    </button>
                    <button type="submit" id="statusSaveBtn" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold shadow-xs">
                        আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStatusFilter = 'all';
        let activeOrderData = null;
        let activeOrderItems = [];

        function showToast(msg) {
            const container = document.getElementById('toastContainer');
            const el = document.createElement('div');
            el.className = 'bg-stone-900 text-white px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 text-xs font-mono transition-all duration-300';
            el.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span>${msg}</span>`;
            container.appendChild(el);
            setTimeout(() => {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 3000);
        }

        function copyToClipboard(text, label) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                showToast((label || 'তথ্য') + ' কপি করা হয়েছে!');
            }).catch(() => {});
        }

        function setOrderStatusFilter(status, btn) {
            currentStatusFilter = status;
            document.querySelectorAll('.order-status-filter-btn').forEach(b => {
                b.className = 'order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-[#f4f1ea] text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]';
            });
            if (btn) {
                btn.className = 'order-status-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono transition-all bg-stone-900 text-white shadow-xs';
            }
            filterOrders();
        }

        function filterOrders() {
            const search = (document.getElementById('orderSearchInput')?.value || '').toLowerCase().trim();
            const gateway = (document.getElementById('orderGatewayFilter')?.value || 'all').toLowerCase();
            const payStatus = (document.getElementById('orderPayStatusFilter')?.value || 'all').toLowerCase();

            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowSearch = row.getAttribute('data-search') || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const rowGateway = row.getAttribute('data-gateway') || '';
                const rowPayStatus = row.getAttribute('data-paystatus') || '';

                const matchStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
                const matchSearch = !search || rowSearch.includes(search);
                const matchGateway = (gateway === 'all') || rowGateway.includes(gateway);
                const matchPayStatus = (payStatus === 'all') || (rowPayStatus === payStatus);

                if (matchStatus && matchSearch && matchGateway && matchPayStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countEl = document.getElementById('orders-visible-count');
            if (countEl) countEl.textContent = visibleCount;
        }

        function viewOrderModal(order, items) {
            activeOrderData = order;
            activeOrderItems = items;

            document.getElementById('modalOrderCode').innerText = '#' + order.order_code;
            document.getElementById('modalOrderDate').innerText = new Date(order.order_date).toLocaleString('bn-BD');
            document.getElementById('modalCustomerName').innerText = order.customer_name || 'N/A';
            document.getElementById('modalCustomerPhone').innerText = order.customer_phone || 'N/A';
            document.getElementById('modalCustomerEmail').innerText = order.customer_email || 'No email';
            document.getElementById('modalShippingAddress').innerText = order.shipping_address || 'N/A';
            document.getElementById('modalShippingDistrict').innerText = (order.district || '') + ' ' + (order.division ? '(' + order.division + ')' : '');

            const itemsBody = document.getElementById('modalItemsBody');
            let subtotal = 0;
            let html = '';

            items.forEach(item => {
                const itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1);
                subtotal += itemTotal;
                html += `
                    <tr>
                        <td class="py-2.5 px-4 font-sans text-xs text-stone-900 font-medium">${item.title || 'বই'}</td>
                        <td class="py-2.5 px-4 text-center text-stone-600">${item.quantity || 1}</td>
                        <td class="py-2.5 px-4 text-right text-stone-600">৳${parseFloat(item.price || 0).toLocaleString()}</td>
                        <td class="py-2.5 px-4 text-right font-bold text-stone-900">৳${itemTotal.toLocaleString()}</td>
                    </tr>
                `;
            });

            itemsBody.innerHTML = html;
            document.getElementById('modalSubtotal').innerText = '৳' + subtotal.toLocaleString();
            document.getElementById('modalDelivery').innerText = '৳' + (parseFloat(order.delivery_charge) || 0).toLocaleString();
            document.getElementById('modalGrandTotal').innerText = '৳' + (parseFloat(order.total_amount) || 0).toLocaleString();
            document.getElementById('modalPaymentInfo').innerText = (order.payment_method || 'COD') + ' • ' + (order.payment_status || 'Pending') + (order.trx_id ? ' (Trx: ' + order.trx_id + ')' : '');

            document.getElementById('orderDetailsModal').classList.remove('hidden');
            document.getElementById('orderDetailsModal').classList.add('flex');
        }

        function closeOrderModal() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
            document.getElementById('orderDetailsModal').classList.remove('flex');
        }

        function openStatusChangeModal(orderId, orderStatus, payStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('statusOrderSelect').value = orderStatus;
            document.getElementById('statusPaymentSelect').value = payStatus;

            document.getElementById('statusChangeModal').classList.remove('hidden');
            document.getElementById('statusChangeModal').classList.add('flex');
        }

        function closeStatusChangeModal() {
            document.getElementById('statusChangeModal').classList.add('hidden');
            document.getElementById('statusChangeModal').classList.remove('flex');
        }

        function handleStatusChange(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const btn = document.getElementById('statusSaveBtn');

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            fetch('/admin/dashboard/update_order_status.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('অর্ডার স্ট্যাটাস সফলভাবে আপডেট হয়েছে!');
                    closeStatusChangeModal();
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'আপডেট করা সম্ভব হয়নি।'));
                }
            })
            .catch(() => alert('সার্ভার সংযোগে সমস্যা।'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'আপডেট করুন';
            });
        }

        function printOrderInvoice() {
            if (!activeOrderData) return;
            window.open('print_invoice.php?order_id=' + activeOrderData.id, '_blank');
        }
    </script>
</body>
</html>
