<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch User Profile
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "বড়সড় ভুল হয়েছে! ব্যবহারকারীর তথ্য পাওয়া যায়নি। দয়া করে আবার লগইন করুন।";
    exit();
}

$wallet_balance = $user['acc_balance'] ?? 0;

// 2. Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE member_id = ? AND status IN ('Active', 'Processing')");
$stmt->execute([$user_id]);
$borrow_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT o.id) FROM orders o WHERE o.member_id = ? AND o.order_status = 'Delivered' AND o.notes = 'Purchase Order'");
$stmt->execute([$user_id]);
$purchase_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE member_id = ? AND status = 'Returned'");
$stmt->execute([$user_id]);
$completed_count = $stmt->fetchColumn();

// 3. Borrowed Books (Active/Processing)
$stmt = $pdo->prepare("
    SELECT br.*, bk.title, bk.author, bk.cover_image, o.order_status
    FROM borrows br
    JOIN books bk ON br.book_id = bk.id
    JOIN orders o ON br.order_id = o.id
    WHERE br.member_id = ? AND br.status IN ('Active', 'Processing')
");
$stmt->execute([$user_id]);
$borrowed_books = $stmt->fetchAll();

// 4. Purchased Books (from Delivered purchase orders)
$stmt = $pdo->prepare("
    SELECT oi.*, 
           COALESCE(bk.title, po.title) as title, 
           COALESCE(bk.author, po.author) as author, 
           COALESCE(bk.cover_image, po.cover_image) as cover_image
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN books bk ON oi.book_id = bk.id
    LEFT JOIN pre_orders po ON oi.preorder_id = po.id
    WHERE o.member_id = ? 
      AND o.order_status = 'Delivered' 
      AND o.notes IN ('Purchase Order', 'Pre-order Booking')
");
$stmt->execute([$user_id]);
$purchased_books = $stmt->fetchAll();

// 5. All Orders grouped (newest first) with items - Fixed N+1 query
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        oi.id as item_id,
        oi.book_id,
        oi.preorder_id,
        oi.quantity,
        oi.unit_price,
        oi.total_price,
        COALESCE(bk.title, po.title) as item_title,
        COALESCE(bk.author, po.author) as item_author,
        COALESCE(bk.cover_image, po.cover_image) as item_cover_image,
        CASE WHEN oi.preorder_id IS NOT NULL THEN 'Pre-order' ELSE 'Book' END as item_type,
        po.release_date
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN books bk ON oi.book_id = bk.id
    LEFT JOIN pre_orders po ON oi.preorder_id = po.id
    WHERE o.member_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$user_id]);
$order_items_raw = $stmt->fetchAll();

// Group items by order
$all_orders = [];
foreach ($order_items_raw as $row) {
    $order_id = $row['id'];
    if (!isset($all_orders[$order_id])) {
        $all_orders[$order_id] = [
            'id' => $row['id'],
            'invoice_no' => $row['invoice_no'],
            'member_id' => $row['member_id'],
            'order_date' => $row['order_date'],
            'subtotal' => $row['subtotal'],
            'discount' => $row['discount'],
            'shipping_cost' => $row['shipping_cost'],
            'total_amount' => $row['total_amount'],
            'payment_status' => $row['payment_status'],
            'payment_method' => $row['payment_method'],
            'order_status' => $row['order_status'],
            'shipping_address' => $row['shipping_address'],
            'notes' => $row['notes'],
            'items' => []
        ];
    }
    if ($row['item_id']) {
        $all_orders[$order_id]['items'][] = [
            'id' => $row['item_id'],
            'book_id' => $row['book_id'],
            'preorder_id' => $row['preorder_id'],
            'quantity' => $row['quantity'],
            'unit_price' => $row['unit_price'],
            'total_price' => $row['total_price'],
            'title' => $row['item_title'],
            'author' => $row['item_author'],
            'cover_image' => $row['item_cover_image'],
            'item_type' => $row['item_type'],
            'release_date' => $row['release_date']
        ];
    }
}
$all_orders = array_values($all_orders);

// Keep legacy $order_history for existing HTML compatibility
$order_history = $all_orders;

// 6. Recent Activity
$stmt = $pdo->prepare("
    (SELECT br.return_date as action_date, bk.title, 'বই ফেরত' as type, br.status
     FROM borrows br JOIN books bk ON br.book_id = bk.id
     WHERE br.member_id = ? AND br.status = 'Returned')
    UNION ALL
    (SELECT o.order_date as action_date,
     GROUP_CONCAT(bk.title SEPARATOR ', ') as title,
     IF(o.notes='Borrow Order','ধার নেওয়া','ক্রয় অর্ডার') as type,
     o.order_status as status
     FROM orders o
     JOIN order_items oi ON o.id = oi.order_id
     JOIN books bk ON oi.book_id = bk.id
     WHERE o.member_id = ?
     GROUP BY o.id)
    ORDER BY action_date DESC LIMIT 8
");
$stmt->execute([$user_id, $user_id]);
$recent_activities = $stmt->fetchAll();

// Helpers
function formatBanglaDate($date)
{
    if (!$date)
        return "N/A";
    return date('d M, Y', strtotime($date));
}

function getStatusClass($status)
{
    $s = strtolower($status);
    if (in_array($s, ['active', 'paid', 'delivered', 'returned']))
        return 'bg-green-100 text-green-700';
    if (in_array($s, ['processing', 'shipped']))
        return 'bg-blue-100 text-blue-700';
    if (in_array($s, ['confirmed']))
        return 'bg-amber-100 text-amber-600';
    if (in_array($s, ['cancelled', 'overdue', 'failed']))
        return 'bg-red-100 text-red-600';
    return 'bg-gray-100 text-gray-500';
}

function getStatusLabel($status)
{
    switch (strtolower($status)) {
        case 'processing':
            return 'প্রসেসিং';
        case 'confirmed':
            return 'কনফার্মড';
        case 'shipped':
            return 'শিপড';
        case 'delivered':
            return 'ডেলিভার্ড ✓';
        case 'cancelled':
            return 'বাতিল';
        case 'active':
            return 'সক্রিয়';
        case 'returned':
            return 'ফেরত';
        case 'overdue':
            return 'মেয়াদ পার';
        default:
            return $status;
    }
}

function getDaysRemaining($due_date)
{
    return round((strtotime($due_date) - time()) / 86400);
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../assets/js/tailwind-config.js"></script>

    <!-- Custom JS -->
    <script src="../assets/js/script.js" defer></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <script>
        const user_id = <?php echo (int)$user['id']; ?>;
        const membership_plan = '<?php echo htmlspecialchars($user['membership_plan'], ENT_QUOTES, 'UTF-8'); ?>';
        localStorage.setItem('membership_plan', membership_plan);
    </script>

    <style>
        .sidebar-link.active {
            background: #cda873;
            color: #0a0a0a;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-[#f8f9fa] min-h-screen">

    <!-- Sidebar Navigation -->
    <aside id="dashboard-sidebar"
        class="fixed inset-y-0 left-0 w-72 bg-brand-900 z-50 transform -translate-x-full transition-transform lg:translate-x-0 overflow-y-auto">
        <div class="p-8 border-b border-white/5">
            <a href="../index.php" class="flex items-center gap-2 group">
                <img src="../assets/img/logo.webp" alt="logo" class="w-10 h-auto">
                <span class="font-serif text-2xl font-bold tracking-wide text-white mt-1">অন্ত্যমিল<span
                        class="text-brand-gold">.</span></span>
            </a>
        </div>

        <div class="px-6 py-10 space-y-2">
            <button onclick="switchTab('home')" id="nav-home"
                class="sidebar-link active w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                হোম
            </button>
            <button onclick="switchTab('my-books')" id="nav-my-books"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                আমার বইসমূহ
            </button>
            <button onclick="switchTab('my-orders')" id="nav-my-orders"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                অর্ডার হিস্টোরি
            </button>
            <button onclick="switchTab('profile')" id="nav-profile"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                প্রোফাইল
            </button>
            <a href="logout.php"
                class="sidebar-link text-red-400 hover:text-white hover:bg-red-500/20 flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300 mt-20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                লগআউট
            </a>
        </div>
    </aside>
    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-brand-900/40 z-40 hidden lg:hidden transition-all duration-300"></div>

    <!-- Main Content -->
    <main class="lg:ml-72 min-h-screen">

        <!-- Top Navbar -->
        <header
            class="bg-white border-b border-gray-100 px-8 py-5 flex items-center justify-between sticky top-0 z-40 bg-white/80">
            <button onclick="toggleSidebar()" class="lg:hidden text-brand-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex items-center gap-4 ml-auto">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-brand-900 font-anek leading-none">
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1 leading-none">
                        স্বাগতম ড্যাশবোর্ডে
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-full bg-brand-gold/10 flex items-center justify-center text-brand-gold font-bold font-anek border border-brand-gold/20">
                    <?php echo mb_substr($user['full_name'], 0, 2); ?>
                </div>
            </div>
        </header>

        <!-- Tab Content: Home -->
        <div id="tab-home" class="p-8 lg:p-12 tab-content">
            <div class="mb-12">
                <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">স্বাগতম,
                    <?php echo explode(' ', $user['full_name'])[0]; ?>!
                </h1>
                <p class="text-gray-500 font-light">আপনার আজকের পাঠ পরিকল্পনা কী?</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-6 mb-12">
                <!-- Membership Plan Stats -->
                <div
                    class="dashboard-card bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-3 bg-red-500/10 rounded-2xl text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">মেম্বারশিপ
                            প্ল্যান</span>
                    </div>
                    <div class="space-y-4">
                        <p class="text-2xl font-anek font-bold text-brand-900 leading-tight">
                            <?php echo htmlspecialchars($user['membership_plan']); ?> মেম্বার
                        </p>
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1 leading-none">
                                মেম্বারশিপ ID</p>
                            <p class="text-xs font-bold text-brand-900 font-anek">
                                #<?php echo htmlspecialchars($user['membership_id']); ?></p>
                        </div>
                        <?php if ($user['membership_plan'] != 'None' && $user['plan_expire_date']): ?>
                            <p class="text-[10px] text-red-500 font-bold uppercase tracking-widest leading-none">
                                মেয়াদ শেষ: <?php echo date('d M, Y', strtotime($user['plan_expire_date'])); ?>
                            </p>
                        <?php
endif; ?>
                        <a href="../membership/"
                            class="text-[10px] text-brand-gold font-bold uppercase tracking-widest mt-2 hover:underline block leading-none">প্ল্যান
                            পরিবর্তন করুন →</a>
                    </div>
                </div>

                <!-- Account Fund Stats -->
                <div
                    class="dashboard-card bg-brand-900 p-8 rounded-[32px] shadow-xl shadow-brand-900/10 border border-brand-900/5 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-3 bg-brand-gold/20 rounded-2xl text-brand-gold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">অ্যাকাউন্ট
                            ফান্ড</span>
                    </div>
                    <div class="space-y-4">
                        <p class="text-3xl font-anek font-bold text-brand-gold">৳
                            <?php echo number_format($wallet_balance); ?>
                        </p>
                        <!-- <button onclick="openAddFundModal()"
                            class="w-full py-2 bg-brand-gold text-brand-900 rounded-xl font-anek font-bold transition-all hover:bg-white text-xs shadow-lg shadow-brand-gold/20 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            তহবিল যোগ করুন
                        </button> -->
                        <div class="py-2 text-[10px] text-brand-gold/50 font-anek font-bold border border-brand-gold/20 rounded-xl text-center uppercase tracking-widest">তহবিল যোগ করা সাময়িকভাবে বন্ধ আছে</div>
                        <a href="#"
                            class="text-[10px] text-white/50 font-bold uppercase tracking-widest mt-2 hover:text-brand-gold transition-colors block text-center leading-none">লেনদেন
                            দেখুন →</a>
                    </div>
                </div>

                <!-- Borrowed Stats -->
                <div
                    class="dashboard-card bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-3 bg-brand-gold/10 rounded-2xl text-brand-gold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">ধার
                            নেওয়া</span>
                    </div>
                    <p class="text-3xl font-anek font-bold text-brand-900">
                        <?php echo sprintf("%02d", $borrow_count); ?>টি
                    </p>
                    <p class="text-[10px] text-gray-500 mt-2 leading-none">অ্যাক্টিভ বই</p>
                </div>

                <!-- Purchased Stats -->
                <div
                    class="dashboard-card bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">কেনা
                            বই</span>
                    </div>
                    <p class="text-3xl font-anek font-bold text-brand-900">
                        <?php echo sprintf("%02d", $purchase_count); ?>টি
                    </p>
                    <p class="text-[10px] text-gray-500 mt-2 leading-none">সংগ্রহে আছে</p>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-2xl font-anek font-bold text-brand-900">সাম্প্রতিক কার্যক্রম</h3>
                    <a href="#" class="text-xs font-bold text-brand-gold uppercase tracking-widest hover:underline">সব
                        দেখুন</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বইয়ের নাম</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    ধরণ</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    তারিখ</th>
                                <th
                                    class="px-10 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td class="px-10 py-6">
                                            <span class="font-bold text-brand-900">
                                                <?php echo htmlspecialchars($activity['title']); ?>
                                            </span>
                                        </td>
                                        <td class="px-10 py-6 text-sm text-gray-500">
                                            <?php echo $activity['type']; ?>
                                        </td>
                                        <td class="px-10 py-6 text-sm text-gray-500">
                                            <?php echo formatBanglaDate($activity['action_date']); ?>
                                        </td>
                                        <td class="px-10 py-6 text-right">
                                            <span
                                                class="px-3 py-1 <?php echo getStatusClass($activity['status']); ?> rounded-full text-[10px] font-bold uppercase tracking-widest">
                                                <?php echo getStatusLabel($activity['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            <?php
else: ?>
                                <tr>
                                    <td colspan="4" class="px-10 py-10 text-center text-gray-400 font-anek italic">কোন
                                        কার্যক্রম খুঁজে পাওয়া যায়নি</td>
                                </tr>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: My Books -->
        <div id="tab-my-books" class="p-8 lg:p-12 tab-content hidden">
            <div class="mb-12">
                <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">আমার বইসমূহ</h1>
                <p class="text-gray-500 font-light">আপনার সংগ্রহে থাকা এবং বর্তমানে ধার নেওয়া বইগুলো।</p>
            </div>

            <!-- Separated Books Display -->
            <div class="space-y-16">
                <!-- Borrowed Books Section -->
                <div>
                    <h2
                        class="text-xl font-anek font-bold text-brand-900 mb-8 border-l-4 border-brand-gold pl-4 flex items-center justify-between">
                        ধার নেওয়া বইসমূহ
                        <span
                            class="text-xs text-gray-400 font-bold uppercase tracking-widest ml-4 bg-gray-100 px-3 py-1 rounded-full"><?php echo count($borrowed_books); ?>টি
                            বই</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php if (!empty($borrowed_books)): ?>
                            <?php foreach ($borrowed_books as $book): ?>
                                <div
                                    class="bg-white p-6 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 group">
                                    <div class="relative aspect-[3/4] mb-6 overflow-hidden rounded-2xl bg-gray-50">
                                        <img src="<?php echo !empty($book['cover_image']) ? '../admin/assets/book-images/' . $book['cover_image'] : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=400'; ?>"
                                            alt="book" class="w-full h-full object-cover">
                                        <div
                                            class="absolute top-4 right-4 bg-brand-gold text-brand-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg">
                                            Borrow</div>
                                        <div
                                            class="absolute inset-0 bg-brand-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button
                                                class="bg-brand-gold text-brand-900 px-6 py-2 rounded-full font-bold font-anek text-sm">পড়ুন</button>
                                        </div>
                                    </div>
                                    <h3 class="font-anek font-bold text-brand-900 text-lg mb-1">
                                        <?php echo htmlspecialchars($book['title']); ?>
                                    </h3>
                                    <p class="text-gray-400 text-xs font-anek mb-4">
                                        <?php echo htmlspecialchars($book['author']); ?>
                                    </p>
                                    <?php
        $days = getDaysRemaining($book['due_date']);
        $color = $days < 0 ? 'text-red-600' : ($days < 3 ? 'text-orange-500' : 'text-green-600');
?>
                                    <p class="text-[10px] <?php echo $color; ?> font-bold uppercase tracking-widest mb-1">
                                        <?php echo $days < 0 ? 'ফেরতে বিলম্ব: ' . abs($days) . ' দিন' : 'ফেরত দিতে বাকি: ' . $days . ' দিন'; ?>
                                    </p>
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                                        <div class="h-full bg-brand-gold"
                                            style="width: <?php echo $book['reading_progress']; ?>%"></div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest font-anek mb-2">
                                        <?php echo $book['reading_progress']; ?>% পড়া সম্পূর্ণ
                                    </p>

                                    <div class="flex items-center gap-2 mb-4">
                                        <input type="range" min="0" max="100" value="<?php echo $book['reading_progress']; ?>"
                                            class="flex-1 h-1 bg-gray-100 rounded-lg appearance-none cursor-pointer accent-brand-gold"
                                            id="progress-range-<?php echo $book['id']; ?>"
                                            oninput="document.getElementById('progress-val-<?php echo $book['id']; ?>').innerText = this.value + '%'">
                                        <span id="progress-val-<?php echo $book['id']; ?>"
                                            class="text-[10px] font-bold text-brand-900 w-8 text-right">
                                            <?php echo $book['reading_progress']; ?>%
                                        </span>
                                        <button onclick="updateProgress(<?php echo $book['id']; ?>)"
                                            class="p-1 px-2 bg-brand-light text-brand-900 rounded text-[8px] font-bold uppercase border border-gray-100 hover:bg-brand-900 hover:text-white transition-all">
                                            সেভ
                                        </button>
                                    </div>
                                    <?php if ($book['status'] === 'Processing' && $book['order_status'] === 'Processing'): ?>
                                        <button onclick="cancelBorrow(<?php echo $book['id']; ?>)"
                                            class="w-full py-2 bg-red-50 text-red-500 rounded-xl text-xs font-bold font-anek hover:bg-red-500 hover:text-white transition-colors uppercase tracking-widest shadow-sm border border-red-100">
                                            অর্ডার বাতিল করুন
                                        </button>
                                    <?php
        endif; ?>
                                </div>
                            <?php
    endforeach; ?>
                        <?php
else: ?>
                            <div
                                class="col-span-full py-10 text-center bg-white rounded-[32px] border border-dashed border-gray-200">
                                <p class="font-anek text-gray-400">বর্তমানে কোন বই ধার নেওয়া নেই</p>
                            </div>
                        <?php
endif; ?>
                    </div>
                </div>

                <!-- Purchased Books Section -->
                <div>
                    <h2
                        class="text-xl font-anek font-bold text-brand-900 mb-8 border-l-4 border-blue-500 pl-4 flex items-center justify-between">
                        কেনা বইসমূহ
                        <span
                            class="text-xs text-gray-400 font-bold uppercase tracking-widest ml-4 bg-gray-100 px-3 py-1 rounded-full"><?php echo count($purchased_books); ?>টি
                            বই</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php if (!empty($purchased_books)): ?>
                            <?php foreach ($purchased_books as $book): ?>
                                <div
                                    class="bg-white p-6 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 group">
                                    <div class="relative aspect-[3/4] mb-6 overflow-hidden rounded-2xl bg-gray-50">
                                        <img src="<?php
        $cover_image = !empty($book['cover_image']) ? trim($book['cover_image']) : '../assets/img/book-placeholder.png';
        // Check if this is a pre-order by checking preorder_id or if cover path suggests preorder
        $is_preorder = isset($book['preorder_id']) && $book['preorder_id'] !== null;
        if ($is_preorder) {
            echo strpos($cover_image, 'http') === 0 ? $cover_image : '../assets/img/preorders/' . trim($cover_image);
        }
        else {
            echo strpos($cover_image, 'http') === 0 ? $cover_image : '../admin/assets/book-images/' . trim($cover_image);
        }
?>"
                                            alt="book" class="w-full h-full object-cover">
                                        <div
                                            class="absolute top-4 right-4 bg-blue-500 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg">
                                            Owned</div>
                                        <div
                                            class="absolute inset-0 bg-brand-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button
                                                class="bg-brand-gold text-brand-900 px-6 py-2 rounded-full font-bold font-anek text-sm">পড়ুন</button>
                                        </div>
                                    </div>
                                    <h3 class="font-anek font-bold text-brand-900 text-lg mb-1">
                                        <?php echo htmlspecialchars($book['title']); ?>
                                    </h3>
                                    <p class="text-gray-400 text-xs font-anek mb-4">
                                        <?php echo htmlspecialchars($book['author']); ?>
                                    </p>
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                                        <div class="h-full bg-brand-gold"
                                            style="width: <?php echo $book['reading_progress']; ?>%"></div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest font-anek">
                                        <?php echo $book['reading_progress']; ?>% পড়া
                                        সম্পূর্ণ
                                    </p>
                                </div>
                            <?php
    endforeach; ?>
                        <?php
else: ?>
                            <div
                                class="col-span-full py-10 text-center bg-white rounded-[32px] border border-dashed border-gray-200">
                                <p class="font-anek text-gray-400">আপনার সংগ্রহের ঝুলি বর্তমানে শূন্য</p>
                            </div>
                        <?php
endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: My Orders -->
        <div id="tab-my-orders" class="p-8 lg:p-12 tab-content hidden">
            <div class="mb-12">
                <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">অর্ডার হিস্টোরি</h1>
                <p class="text-gray-500 font-light">আপনার কেনা বইয়ের তালিকা ও স্ট্যাটাস।</p>
            </div>

            <div class="space-y-6">
                <?php if (!empty($order_history)): ?>
                    <?php foreach ($order_history as $order): ?>
                        <div
                            class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-md transition-all">
                            <div
                                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 pb-6 border-b border-gray-50">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-1">ইনভয়েস নম্বর
                                    </p>
                                    <h3 class="text-xl font-mono font-bold text-brand-900">#<?php echo $order['invoice_no']; ?>
                                    </h3>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">অর্ডার
                                            তারিখ</p>
                                        <p class="text-sm font-bold text-brand-900 font-anek">
                                            <?php echo formatBanglaDate($order['order_date']); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="px-4 py-2 <?php echo getStatusClass($order['order_status']); ?> rounded-full text-[10px] font-bold uppercase tracking-widest">
                                        <?php echo getStatusLabel($order['order_status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-6 mb-8">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="flex items-center gap-6">
                                        <div class="w-16 h-20 bg-gray-50 rounded-xl overflow-hidden shadow-sm flex-shrink-0">
                                            <img src="<?php
            $cover_image = !empty($item['cover_image']) ? trim($item['cover_image']) : 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=200';
            // Check if this is a pre-order
            $is_preorder = isset($item['item_type']) && $item['item_type'] === 'Pre-order';
            if ($is_preorder) {
                echo strpos($cover_image, 'http') === 0 ? $cover_image : '../assets/img/preorders/' . trim($cover_image);
            }
            else {
                echo strpos($cover_image, 'http') === 0 ? $cover_image : '../admin/assets/book-images/' . trim($cover_image);
            }
?>"
                                                alt="book" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-brand-900 font-anek flex items-center gap-2">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                                <?php if ($item['item_type'] === 'Pre-order'): ?>
                                                    <span
                                                        class="text-[8px] bg-brand-gold/10 text-brand-gold px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">প্রি-অর্ডার</span>
                                                <?php
            endif; ?>
                                            </h4>
                                            <p class="text-xs text-gray-400 font-anek">
                                                <?php echo htmlspecialchars($item['author']); ?>
                                            </p>
                                            <div class="flex items-center gap-4 mt-1">
                                                <p class="text-xs font-bold text-brand-gold font-anek">
                                                    ৳<?php echo number_format($item['total_price']); ?> (১টি)</p>
                                                <?php if ($item['release_date']): ?>
                                                    <p
                                                        class="text-[9px] text-gray-500 font-bold bg-gray-50 px-2 py-0.5 rounded border border-gray-100 uppercase tracking-tighter">
                                                        রিলিজ ডেট: <?php echo date('d M, Y', strtotime($item['release_date'])); ?>
                                                    </p>
                                                <?php
            endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
        endforeach; ?>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-6 border-t border-gray-50">
                                <div class="flex items-center gap-6">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">পেমেন্ট
                                            পদ্ধতি</p>
                                        <p class="text-xs font-bold text-brand-900 font-anek">
                                            <?php echo $order['payment_method']; ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">মোট মূল্য
                                        </p>
                                        <p class="text-lg font-bold text-brand-gold font-anek">
                                            ৳<?php echo number_format($order['total_amount']); ?></p>
                                    </div>
                                </div>
                                <div class="flex gap-3 w-full sm:w-auto">
                                    <?php if ($order['order_status'] === 'Cancelled'): ?>
                                        <div class="mt-4 p-4 bg-orange-50 border border-orange-100 rounded-2xl w-full">
                                            <p class="text-xs text-orange-700 font-anek leading-relaxed">
                                                <svg class="w-4 h-4 inline-block mr-1 mb-0.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                অর্ডারটি বাতিল করা হয়েছে। আপনার পেমেন্ট (যদি প্রযোজ্য হয়ে থাকে) আপনার অ্যাকাউন্ট
                                                ফান্ডে রিফান্ড করে দেওয়া হয়েছে।
                                            </p>
                                        </div>
                                    <?php
        endif; ?>

                                    <?php if ($order['order_status'] === 'Processing'): ?>
                                        <?php
            // Calculate time left for cancellation using proper timezone
            $order_tz = new DateTimeZone('Asia/Dhaka');
            $order_dt = new DateTime($order['order_date'], $order_tz);
            $now_dt = new DateTime('now', $order_tz);
            $time_left = 180 - ($now_dt->getTimestamp() - $order_dt->getTimestamp());
?>
                                        <?php if ($time_left > 0): ?>
                                            <button onclick="cancelOrder(<?php echo $order['id']; ?>)"
                                                id="cancel-btn-<?php echo $order['id']; ?>" data-timeleft="<?php echo $time_left; ?>"
                                                class="cancel-order-btn flex-1 sm:flex-none px-6 py-3 bg-red-50 text-red-500 rounded-xl font-bold font-anek text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100">
                                                অর্ডার বাতিল করুন (<span
                                                    class="timer-text"><?php echo gmdate("i:s", $time_left); ?></span>)
                                            </button>
                                        <?php
            endif; ?>
                                    <?php
        endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php
    endforeach; ?>
                <?php
else: ?>
                    <div class="py-20 text-center bg-white rounded-[40px] border border-dashed border-gray-200">
                        <p class="font-anek text-gray-400">আপনার এখনো কোন অর্ডার নেই</p>
                    </div>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Tab Content: Profile -->
        <div id="tab-profile" class="p-8 lg:p-12 tab-content hidden">
            <div class="mb-12">
                <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">প্রোফাইল সেটিংস</h1>
                <p class="text-gray-500 font-light">আপনার ব্যক্তিগত তথ্য ও মেম্বারশিপ ম্যানেজ করুন।</p>
            </div>

            <div class="max-w-3xl">
                <div class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 mb-8">
                    <div class="flex items-center gap-8 mb-12">
                        <div
                            class="w-32 h-32 rounded-full bg-brand-gold/10 border-4 border-white shadow-xl flex items-center justify-center text-5xl font-anek font-bold text-brand-gold">
                            <?php echo mb_substr($user['full_name'], 0, 2); ?>
                        </div>
                        <div>
                            <h2 class="text-2xl font-anek font-bold text-brand-900">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </h2>
                            <p class="text-gray-400 font-anek mb-4">সদস্য হয়েছেন:
                                <?php echo date('F Y', strtotime($user['created_at'])); ?>
                            </p>
                            <div class="flex flex-wrap items-center gap-3">
                                <span
                                    class="px-4 py-2 bg-brand-gold text-brand-900 rounded-full text-xs font-bold uppercase tracking-widest"><?php echo htmlspecialchars($user['membership_plan']); ?>
                                    মেম্বার</span>
                                <?php if ($user['membership_plan'] != 'None' && $user['plan_expire_date']): ?>
                                    <span class="px-4 py-2 bg-red-100 text-red-600 rounded-full text-xs font-bold">মেয়াদ
                                        শেষ: <?php echo date('d M, Y', strtotime($user['plan_expire_date'])); ?></span>
                                <?php
endif; ?>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]">ID:
                                    <?php echo htmlspecialchars($user['membership_id']); ?></span>
                                <div
                                    class="bg-brand-900 text-brand-gold px-4 py-2 rounded-full flex items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    <span class="text-xs font-bold font-anek">তহবিল:
                                        ৳<?php echo number_format($user['acc_balance']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="profile-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">আপনার
                                নাম</label>
                            <input type="text" name="full_name" required
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ইমেইল</label>
                            <input type="email" name="email" required
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">মোবাইল</label>
                            <input type="tel" name="phone" required
                                value="<?php echo htmlspecialchars($user['phone']); ?>"
                                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড
                                পরিবর্তন করুন (ঐচ্ছিক)</label>
                            <input type="password" name="password" placeholder="••••••••"
                                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900">
                        </div>
                        <div class="md:col-span-2 pt-6">
                            <button type="submit"
                                class="bg-brand-900 text-white px-10 py-4 rounded-2xl font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10">সেভ
                                করুন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Fund Modal (Disabled) -->
    <!-- <div id="add-fund-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-brand-900/60 backdrop-blur-md transition-opacity" onclick="closeAddFundModal()">
        </div>
        <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-slide-up">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-anek font-bold text-brand-900 text-center">তহবিল যোগ করুন</h3>
                    <button onclick="closeAddFundModal()" class="text-gray-400 hover:text-brand-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="process_add_fund.php" method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">পরিমাণ
                            সিলেক্ট করুন</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" onclick="setAmount(100)"
                                class="py-3 px-4 bg-gray-50 border border-gray-100 rounded-xl font-bold font-anek hover:border-brand-gold hover:bg-brand-gold/5 transition-all">৳১০০</button>
                            <button type="button" onclick="setAmount(500)"
                                class="py-3 px-4 bg-gray-50 border border-gray-100 rounded-xl font-bold font-anek hover:border-brand-gold hover:bg-brand-gold/5 transition-all">৳৫০০</button>
                            <button type="button" onclick="setAmount(1000)"
                                class="py-3 px-4 bg-gray-50 border border-gray-100 rounded-xl font-bold font-anek hover:border-brand-gold hover:bg-brand-gold/5 transition-all">৳১০০০</button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">অন্য
                            পরিমাণ (৳)</label>
                        <input type="number" name="amount" id="custom-amount" required placeholder="0.00" min="1"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900 text-xl font-bold">
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10 mb-2">
                            টপ-আপ নিশ্চিত করুন
                        </button>
                        <p class="text-[10px] text-gray-400 text-center uppercase tracking-wider">বিকাশ/নগদ/রকেট পেমেন্ট
                            গেটওয়ের মাধ্যমে পেমেন্ট করুন</p>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

    <!-- Success Message Toast (Optional but good) -->
    <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
        <div id="success-toast"
            class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-green-600 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-[200] animate-slide-up">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-anek font-bold">আপনার তহবিল সফলভাবে আপডেট করা হয়েছে!</span>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('success-toast');
                if (toast) {
                    toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => toast.remove(), 500);
                }
            }, 3000);
        </script>
    <?php
endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="error-toast"
            class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-red-600 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-[200] animate-slide-up">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-anek font-bold"><?php echo $_SESSION['error_message'];
    unset($_SESSION['error_message']); ?></span>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('error-toast');
                if (toast) {
                    toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => toast.remove(), 500);
                }
            }, 3000);
        </script>
    <?php
endif; ?>

    <script>
        const sidebar = document.getElementById('dashboard-sidebar');
        const addFundModal = document.getElementById('add-fund-modal');
        const customAmountInput = document.getElementById('custom-amount');

        function toggleSidebar() {
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) overlay.classList.toggle('hidden');
        }

        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show requested tab
            document.getElementById(`tab-${tabId}`).classList.remove('hidden');

            // Update sidebar links
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
                link.classList.add('text-gray-400');
                link.classList.remove('text-brand-900'); // Ensure this is removed from inactive links
            });

            const activeLink = document.getElementById(`nav-${tabId}`);
            if (activeLink) {
                activeLink.classList.add('active');
                activeLink.classList.remove('text-gray-400');
                activeLink.classList.add('text-brand-900');
            }

            // Close sidebar on mobile after clicking
            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        }

        function openAddFundModal() {
            addFundModal.classList.remove('hidden');
            addFundModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeAddFundModal() {
            addFundModal.classList.add('hidden');
            addFundModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function setAmount(val) {
            customAmountInput.value = val;
        }

        // Countdown timer for cancel buttons
        setInterval(() => {
            const buttons = document.querySelectorAll('.cancel-order-btn');
            buttons.forEach(btn => {
                let timeLeft = parseInt(btn.getAttribute('data-timeleft'));
                if (timeLeft > 0) {
                    timeLeft--;
                    btn.setAttribute('data-timeleft', timeLeft);

                    const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                    const seconds = (timeLeft % 60).toString().padStart(2, '0');
                    const timerSpan = btn.querySelector('.timer-text');
                    if (timerSpan) {
                        timerSpan.innerText = `${minutes}:${seconds}`;
                    }

                    if (timeLeft <= 0) {
                        btn.remove();
                    }
                }
            });
        }, 1000);

        function cancelOrder(orderId) {
            if (!confirm('আপনি কি নিশ্চিত যে আপনি এই অর্ডারটি বাতিল করতে চান?')) return;

            fetch('cancel_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('অর্ডারটি সফলভাবে বাতিল করা হয়েছে।');
                        location.reload();
                    } else {
                        alert('ত্রুটি: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('অর্ডার বাতিল করতে সমস্যা হয়েছে।');
                });
        }

        function updateProgress(borrowId) {
            const progress = document.getElementById('progress-range-' + borrowId).value;

            fetch('update_reading_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `borrow_id=${borrowId}&progress=${progress}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('পাঠের অগ্রগতি সফলভাবে সেভ করা হয়েছে।');
                    } else {
                        alert('ত্রুটি: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('আপডেট করতে সমস্যা হয়েছে।');
                });
        }

        function cancelBorrow(borrowId) {
            if (!confirm('আপনি কি নিশ্চিত যে আপনি এই ধার অর্ডারটি বাতিল করতে চান?')) return;

            fetch('cancel_borrow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'borrow_id=' + borrowId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('অর্ডারটি সফলভাবে বাতিল করা হয়েছে।');
                        location.reload();
                    } else {
                        alert('ত্রুটি: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('অর্ডার বাতিল করতে সমস্যা হয়েছে।');
                });
        }

        // Handle Profile Update Form Submission
        document.getElementById('profile-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showToast !== 'undefined') showToast('প্রোফাইল সফলভাবে আপডেট করা হয়েছে।');
                        else alert('প্রোফাইল সফলভাবে আপডেট করা হয়েছে।');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        if (typeof showToast !== 'undefined') showToast('ত্রুটি: ' + data.message);
                        else alert('ত্রুটি: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof showToast !== 'undefined') showToast('প্রোফাইল আপডেট করতে সমস্যা হয়েছে।');
                    else alert('প্রোফাইল আপডেট করতে সমস্যা হয়েছে।');
                });
        });
    </script>
</body>

</html>
