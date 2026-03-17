<?php
include '../../includes/db_connect.php';

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Fetch Overview Data - Optimized with indexes
$total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$total_sales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Processing'")->fetchColumn();
$borrowed_books = $pdo->query("SELECT COUNT(*) FROM borrows WHERE status = 'Active'")->fetchColumn();

// Categories for filter
$cats_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cats_stmt->fetchAll();

// Inventory Filter Logic - Use prepared statement
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$cat_id = isset($_GET['category']) && $_GET['category'] != 'all' ? (int)$_GET['category'] : '%';

$inv_stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                          FROM books b 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?) 
                          AND (COALESCE(b.category_id, '') LIKE ?)
                          AND b.is_active = 1
                          ORDER BY b.created_at DESC");
$inv_stmt->execute([$search, $search, $search, $cat_id]);
$inventory_books = $inv_stmt->fetchAll();

// Fetch Orders with JOIN
$orders_stmt = $pdo->query("SELECT o.*, 
                                   COALESCE(m.full_name, o.guest_name) as full_name, 
                                   COALESCE(m.phone, o.guest_phone) as phone,
                                   COALESCE(m.email, o.guest_email) as email
                            FROM orders o 
                            LEFT JOIN members m ON o.member_id = m.id 
                            ORDER BY o.order_date DESC");
$admin_orders = $orders_stmt->fetchAll();

// Pre-fetch all order items to avoid N+1 queries - PERFORMANCE OPTIMIZATION
$all_order_items_stmt = $pdo->query("SELECT oi.*, 
                                      COALESCE(b.title, po.title) as title, 
                                      COALESCE(b.cover_image, po.cover_image) as cover_image 
                               FROM order_items oi 
                               LEFT JOIN books b ON oi.book_id = b.id 
                               LEFT JOIN pre_orders po ON oi.preorder_id = po.id");
$all_order_items = $all_order_items_stmt->fetchAll();

// Create a lookup array indexed by order_id for O(1) access
$order_items_by_order = [];
foreach ($all_order_items as $item) {
    $order_items_by_order[$item['order_id']][] = $item;
}

// Fetch Active Borrows for Borrows Tab
$borrows_stmt = $pdo->query("SELECT br.*, m.full_name, m.phone, b.title, b.cover_image, o.order_status
                             FROM borrows br
                             JOIN members m ON br.member_id = m.id
                             JOIN books b ON br.book_id = b.id
                             LEFT JOIN orders o ON br.order_id = o.id
                             WHERE br.status IN ('Active', 'Overdue', 'Processing')
                             ORDER BY br.due_date ASC");
$active_borrows = $borrows_stmt->fetchAll();

$preorders_stmt = $pdo->query("SELECT * FROM pre_orders ORDER BY release_date ASC");
$admin_preorders = $preorders_stmt->fetchAll();

// Fetch Pre-order Bookings (New)
$po_bookings_stmt = $pdo->query("SELECT oi.*, o.id as order_id, o.invoice_no, o.order_date, o.order_status, o.trx_id, o.shipping_address, o.total_amount, 
                                          COALESCE(m.full_name, o.guest_name) as full_name, 
                                          COALESCE(m.phone, o.guest_phone) as phone, 
                                          COALESCE(m.email, o.guest_email) as email,
                                          po.title as po_title, po.release_date as po_release, po.is_hot_deal
                                   FROM order_items oi
                                   JOIN orders o ON oi.order_id = o.id
                                   LEFT JOIN members m ON o.member_id = m.id
                                   JOIN pre_orders po ON oi.preorder_id = po.id
                                   ORDER BY o.order_date DESC");
$admin_preorder_bookings = $po_bookings_stmt->fetchAll();

// Fetch Members for Members Tab
$members_stmt = $pdo->query("SELECT * FROM members ORDER BY created_at DESC");
$admin_members = $members_stmt->fetchAll();

// Fetch Payment Methods
$payments_stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY id ASC");
$payment_methods = $payments_stmt->fetchAll();

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
$inside_charge = getSetting($pdo, 'delivery_charge_inside', '60');
$outside_charge = getSetting($pdo, 'delivery_charge_outside', '120');

// Helper function using pre-fetched data
function getOrderItems($order_id, $order_items_by_order)
{
    return $order_items_by_order[$order_id] ?? [];
}

function bn_num($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ড্যাশবোর্ড | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">



    <style>
        .sidebar-link.active {
            background: #cda873;
            color: #0a0a0a;
        }

        /* Progress Modal Styles */
        #upload-progress-modal {
            transition: all 0.3s ease-in-out;
        }
        
        .progress-ring {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
    </style>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-[#f4f7f6] min-h-screen">

    <!-- Sidebar Navigation -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 w-72 bg-brand-900 z-50 transform -translate-x-full transition-transform lg:translate-x-0 overflow-y-auto">
        <div class="p-8 border-b border-white/5">
            <a href="../../index.php" class="flex items-center gap-2 group">
                <img src="../../assets/img/logo.webp" alt="logo" class="w-10 h-auto">
                <span class="font-serif text-2xl font-bold tracking-wide text-white mt-1 uppercase">অ্যাডমিন<span
                        class="text-brand-gold">.</span></span>
            </a>
        </div>

        <nav class="px-6 py-10 space-y-2">
            <button onclick="switchTab('overview')" id="nav-overview"
                class="sidebar-link active w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                ওভারভিউ
            </button>
            <button onclick="switchTab('orders')" id="nav-orders"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                অর্ডারসমূহ
            </button>
            <button onclick="switchTab('inventory')" id="nav-inventory"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                ইনভেন্টরি
            </button>
            <button onclick="switchTab('members')" id="nav-members"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                মেম্বার লিস্ট
            </button>
            <button onclick="switchTab('suggested')" id="nav-suggested"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                সাজেস্টেড বই
            </button>
            <button onclick="switchTab('borrows')" id="nav-borrows"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                বরো ট্র্যাকার
            </button>
            <button onclick="switchTab('preorders')" id="nav-preorders"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                প্রি-অর্ডার
            </button>
            <button onclick="switchTab('payments')" id="nav-payments"
                class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                পেমেন্ট সেটিংস
            </button>
            <a href="../logout.php"
                class="sidebar-link text-red-400 hover:text-white hover:bg-red-500/20 flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300 mt-20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                লগআউট
            </a>
        </nav>
    </aside>
    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-brand-900/40 z-40 hidden lg:hidden transition-all duration-300"></div>

    <!-- Main Content -->
    <main class="lg:ml-72 min-h-screen">

        <!-- Header -->
        <header class="bg-white px-8 py-5 border-b border-gray-100 sticky top-0 z-40 flex items-center justify-between">
            <button onclick="toggleSidebar()" class="lg:hidden text-brand-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h2 class="text-xl font-anek font-bold text-brand-900">মাস্টার ড্যাশবোর্ড</h2>
            <div class="flex items-center gap-4">
                <span class="p-2 bg-brand-light rounded-xl text-brand-900 relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                </span>
                <div class="flex items-center gap-3 pl-4 border-l border-gray-100">
                    <p class="text-sm font-anek font-bold text-brand-900 hidden sm:block">
                        <?php echo $_SESSION['admin_full_name']; ?>
                    </p>
                    <div
                        class="w-10 h-10 rounded-full bg-brand-900 text-brand-gold flex items-center justify-center font-bold">
                        <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Tab: Overview -->
        <div id="tab-overview" class="p-8 lg:p-12 tab-content">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div
                    class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm transition-all hover:shadow-xl">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">মোট বিক্রি</p>
                    <h3 class="text-3xl font-anek font-extrabold text-brand-900">
                        ৳<?php echo bn_num(number_format($total_sales)); ?></h3>
                    <p class="text-xs text-green-500 font-bold mt-2 font-anek">লাইভ ডেটা</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm transition-all hover:shadow-xl">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">নতুন মেম্বার</p>
                    <h3 class="text-3xl font-anek font-extrabold text-brand-900"><?php echo bn_num($total_members); ?>
                        জন</h3>
                    <p class="text-xs text-blue-500 font-bold mt-2 font-anek">মোট নিবন্ধিত</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm transition-all hover:shadow-xl">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">পেন্ডিং অর্ডার</p>
                    <h3 class="text-3xl font-anek font-extrabold text-brand-900">
                        <?php echo bn_num($pending_orders); ?>টি
                    </h3>
                    <p class="text-xs text-orange-500 font-bold mt-2 font-anek">ডেলিভারি প্রয়োজন</p>
                </div>
                <div
                    class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm transition-all hover:shadow-xl">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">লাইব্রেরি বই ধার</p>
                    <h3 class="text-3xl font-anek font-extrabold text-brand-900">
                        <?php echo bn_num($borrowed_books); ?>টি
                    </h3>
                    <p class="text-xs text-gray-400 font-bold mt-2 font-anek">বর্তমানে আউট</p>
                </div>
            </div>

            <!-- Dashboard Charts Area -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-anek font-bold text-brand-900">মাসিক আয়</h3>
                        <select
                            class="text-xs font-bold uppercase tracking-widest text-brand-gold bg-transparent border-none focus:outline-none">
                            <option>২০২৬</option>
                        </select>
                    </div>
                    <div class="h-64 flex items-end gap-2">
                        <div class="flex-1 bg-brand-light h-[40%] rounded-t-xl transition-all hover:bg-brand-gold">
                        </div>
                        <div class="flex-1 bg-brand-light h-[65%] rounded-t-xl transition-all hover:bg-brand-gold">
                        </div>
                        <div class="flex-1 bg-brand-gold h-[85%] rounded-t-xl"></div>
                        <div class="flex-1 bg-brand-light h-[55%] rounded-t-xl transition-all hover:bg-brand-gold">
                        </div>
                        <div class="flex-1 bg-brand-light h-[20%] rounded-t-xl transition-all hover:bg-brand-gold">
                        </div>
                    </div>
                    <div
                        class="flex justify-between mt-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                        <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span>
                    </div>
                </div>

                <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
                    <h3 class="text-2xl font-anek font-bold text-brand-900 mb-8">বেস্ট সেলিং বই</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-16 bg-gray-100 rounded shadow-sm"></div>
                            <div class="flex-1">
                                <h4 class="font-bold text-brand-900 font-anek">দ্য সিডনি ক্যাসেল</h4>
                                <p class="text-xs text-gray-400 font-anek">১২৪টি বিক্রি</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-gold font-anek">৳৪৫০</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-16 bg-gray-100 rounded shadow-sm"></div>
                            <div class="flex-1">
                                <h4 class="font-bold text-brand-900 font-anek">অ্যাটোমিক হ্যাবিটস</h4>
                                <p class="text-xs text-gray-400 font-anek">৯৮টি বিক্রি</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-gold font-anek">৳৩৯০</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Orders -->
        <div id="tab-orders" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">অর্ডার ম্যানেজমেন্ট</h1>
                    <p class="text-gray-500 font-light">সব কাস্টমার অর্ডারের বিস্তারিত তথ্য ও স্ট্যাটাস আপডেট করুন।</p>
                </div>
                <div class="flex gap-4">
                    <div class="relative">
                        <input type="text" id="orderSearchInput" onkeyup="filterOrders()"
                            placeholder="অর্ডার আইডি বা নাম সার্চ..."
                            class="bg-white border border-gray-100 rounded-2xl px-6 py-4 pr-12 focus:outline-none focus:ring-2 focus:ring-brand-gold font-anek w-80 shadow-sm">
                        <svg class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    ID</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    কাস্টমার</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বই</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    টাইপ</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    মূল্য</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    পেমেন্ট</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    স্ট্যাটাস</th>
                                <th
                                    class="px-10 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php foreach ($admin_orders as $order):
    $items = getOrderItems($order['id'], $order_items_by_order);
    $items_list = implode(', ', array_column($items, 'title'));
    $status_color = 'bg-gray-100 text-gray-600';
    if ($order['order_status'] == 'Processing')
        $status_color = 'bg-orange-100 text-orange-600';
    if ($order['order_status'] == 'Confirmed')
        $status_color = 'bg-amber-100 text-amber-600';
    if ($order['order_status'] == 'Shipped')
        $status_color = 'bg-blue-100 text-blue-600';
    if ($order['order_status'] == 'Delivered')
        $status_color = 'bg-green-100 text-green-600';
    if ($order['order_status'] == 'Cancelled')
        $status_color = 'bg-red-100 text-red-600';
?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-10 py-6 text-sm font-bold text-brand-900">
                                        #<?php echo $order['invoice_no']; ?></td>
                                    <td class="px-10 py-6">
                                        <div class="text-sm font-bold text-brand-900"><?php echo $order['full_name']; ?>
                                        </div>
                                        <div class="text-[10px] text-gray-400"><?php echo $order['phone']; ?></div>
                                    </td>
                                    <td class="px-10 py-6 text-sm text-gray-500 truncate max-w-[200px]">
                                        <?php echo $items_list; ?>
                                    </td>
                                    <td class="px-10 py-6">
                                        <?php if ($order['notes'] == 'Borrow Order'): ?>
                                            <span
                                                class="px-2 py-1 bg-purple-100 text-purple-600 rounded text-[9px] font-bold uppercase">Borrow</span>
                                        <?php
    else: ?>
                                            <span
                                                class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-[9px] font-bold uppercase">Buy</span>
                                        <?php
    endif; ?>
                                    </td>
                                    <td class="px-10 py-6 text-sm font-bold text-brand-900">
                                        ৳<?php echo bn_num(number_format($order['total_amount'])); ?></td>
                                    <td class="px-10 py-6">
                                        <div class="flex flex-col gap-1 relative">
                                            <span
                                                class="text-[10px] text-gray-400 font-bold tracking-wider uppercase"><?php echo $order['payment_method']; ?></span>
                                            <button
                                                onclick="toggleActionMenu('pay-menu-<?php echo $order['id']; ?>', event)"
                                                class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest w-fit transition-all hover:ring-2 hover:ring-offset-2 <?php echo $order['payment_status'] == 'Paid' ? 'bg-green-100 text-green-600 hover:ring-green-200' : 'bg-red-100 text-red-600 hover:ring-red-200'; ?>">
                                                <?php echo $order['payment_status'] == 'Paid' ? 'Paid' : 'Pending'; ?>
                                            </button>

                                            <!-- Payment Status Dropdown -->
                                            <div id="pay-menu-<?php echo $order['id']; ?>"
                                                class="action-menu w-32 bg-white rounded-xl shadow-2xl border border-gray-100 hidden overflow-hidden transition-all duration-200 z-[99999]">
                                                <button onclick="updatePaymentStatus(<?php echo $order['id']; ?>, 'Paid')"
                                                    class="w-full text-left px-4 py-2 text-[10px] font-bold uppercase text-green-600 hover:bg-green-50 border-b border-gray-50">Paid</button>
                                                <button
                                                    onclick="updatePaymentStatus(<?php echo $order['id']; ?>, 'Pending')"
                                                    class="w-full text-left px-4 py-2 text-[10px] font-bold uppercase text-red-600 hover:bg-red-50">Pending</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 relative">
                                        <?php if (in_array($order['order_status'], ['Delivered', 'Cancelled'])): ?>
                                            <span
                                                class="px-3 py-1 <?php echo $status_color; ?> rounded-full text-[10px] font-bold uppercase tracking-widest opacity-80 cursor-not-allowed">
                                                <?php
        $st = $order['order_status'];
        if ($st == 'Delivered')
            echo 'ডেলিভারড';
        else if ($st == 'Cancelled')
            echo 'বাতিল';
?>
                                            </span>
                                        <?php
    else: ?>
                                            <button onclick="toggleActionMenu('status-menu-<?php echo $order['id']; ?>', event)"
                                                class="px-3 py-1 <?php echo $status_color; ?> rounded-full text-[10px] font-bold uppercase tracking-widest transition-all hover:ring-2 hover:ring-offset-2 <?php
        if ($order['order_status'] == 'Processing')
            echo 'hover:ring-orange-200';
        else if ($order['order_status'] == 'Confirmed')
            echo 'hover:ring-amber-200';
        else if ($order['order_status'] == 'Shipped')
            echo 'hover:ring-blue-200';
?>">
                                                <?php
        $st = $order['order_status'];
        if ($st == 'Processing')
            echo 'পেন্ডিং';
        else if ($st == 'Confirmed')
            echo 'কনফার্মড';
        else if ($st == 'Shipped')
            echo 'শিপড';
        else
            echo $st;
?>
                                            </button>

                                            <!-- Order Status Dropdown -->
                                            <div id="status-menu-<?php echo $order['id']; ?>"
                                                class="action-menu w-40 bg-white rounded-xl shadow-2xl border border-gray-100 hidden overflow-hidden transition-all duration-200 z-[99999]">
                                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Processing')"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase text-orange-600 hover:bg-orange-50 border-b border-gray-50">পেন্ডিং</button>
                                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Confirmed')"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase text-amber-600 hover:bg-amber-50 border-b border-gray-50">কনফার্মড</button>
                                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Shipped')"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase text-blue-600 hover:bg-blue-50 border-b border-gray-50">শিপড</button>
                                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Delivered')"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase text-green-600 hover:bg-green-50 border-b border-gray-50">ডেলিভারড</button>
                                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Cancelled')"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase text-red-600 hover:bg-red-50">বাতিল</button>
                                            </div>
                                        <?php
    endif; ?>
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <button
                                                onclick="viewOrderDetails(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($items), ENT_QUOTES); ?>)"
                                                class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-light/50 text-brand-gold hover:bg-brand-gold hover:text-white transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <div class="relative">
                                                <button
                                                    onclick="toggleActionMenu('action-menu-<?php echo $order['id']; ?>', event)"
                                                    class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-brand-900 transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                                    </svg>
                                                </button>
                                                <div id="action-menu-<?php echo $order['id']; ?>"
                                                    class="action-menu w-40 bg-white rounded-xl shadow-2xl border border-gray-100 hidden overflow-hidden transition-all duration-200 z-[99999]">
                                                    <?php if ($order['payment_status'] == 'Pending'): ?>
                                                        <button
                                                            onclick="updatePaymentStatus(<?php echo $order['id']; ?>, 'Paid')"
                                                            class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-green-600 hover:bg-green-50 border-b border-gray-50">পেমেন্ট
                                                            পেড</button>
                                                    <?php
    endif; ?>
                                                    <?php if ($order['order_status'] == 'Processing'): ?>
                                                        <button
                                                            onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Confirmed')"
                                                            class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-amber-600 hover:bg-amber-50">কনফার্ম
                                                            করুন</button>
                                                    <?php
    endif; ?>
                                                    <?php if (in_array($order['order_status'], ['Processing', 'Confirmed'])): ?>
                                                        <button
                                                            onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Shipped')"
                                                            class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-blue-600 hover:bg-blue-50">শিপড
                                                            করুন</button>
                                                    <?php
    endif; ?>
                                                    <?php if ($order['order_status'] == 'Shipped'): ?>
                                                        <button
                                                            onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Delivered')"
                                                            class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-green-600 hover:bg-green-50">ডেলিভারড
                                                            করুন</button>
                                                    <?php
    endif; ?>
                                                    <?php if ($order['order_status'] != 'Delivered' && $order['order_status'] != 'Cancelled'): ?>
                                                        <button
                                                            onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'Cancelled')"
                                                            class="w-full text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-red-600 hover:bg-red-50">বাতিল
                                                            করুন</button>
                                                    <?php
    endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Inventory -->
        <div id="tab-inventory" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">বই ইনভেন্টরি</h1>
                    <p class="text-gray-500 font-light">আপনার সংগ্রহের সকল বই এখান থেকে ম্যানেজ করুন।</p>
                </div>
                <button onclick="openAddBookModal()"
                    class="bg-brand-900 text-white px-8 py-4 rounded-2xl font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/10 flex items-center gap-3 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    নতুন বই যোগ করুন
                </button>
            </div>

            <!-- Inventory Controls -->
            <form action="" method="GET"
                class="bg-white p-6 rounded-[32px] border border-gray-100 shadow-sm mb-8 flex flex-col lg:flex-row gap-6 items-center">
                <div class="relative flex-1 w-full">
                    <input type="text" name="search"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        placeholder="বইয়ের নাম, লেখক অথবা আইএসবিএন (ISBN) দিয়ে সার্চ করুন..."
                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-brand-gold rounded-2xl px-12 py-4 focus:outline-none transition-all font-anek text-brand-900 shadow-inner">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="flex gap-4 w-full lg:w-auto">
                    <select name="category"
                        class="bg-gray-50 border border-transparent focus:bg-white focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 appearance-none min-w-[150px] shadow-inner">
                        <option value="all">সব ক্যাটাগরি</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo(isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>
                    <button type="submit"
                        class="px-6 py-4 bg-brand-light text-brand-900 rounded-2xl font-anek font-bold hover:bg-white border border-transparent hover:border-brand-gold transition-all shadow-sm">বই
                        ফিল্টার</button>
                </div>
            </form>

            <!-- Inventory Table -->
            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th
                                    class="px-8 py-6 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বইয়ের তথ্য</th>
                                <th
                                    class="px-8 py-6 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    ক্যাটাগরি</th>
                                <th
                                    class="px-8 py-6 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    মূল্য</th>
                                <th
                                    class="px-8 py-6 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    স্টক স্ট্যাটাস</th>
                                <th
                                    class="px-8 py-6 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php foreach ($inventory_books as $book): ?>
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-12 h-16 rounded-lg overflow-hidden bg-gray-100 shadow-sm flex-shrink-0">
                                                <img src="<?php echo !empty($book['cover_image']) ? '../../admin/assets/book-images/' . $book['cover_image'] : ''; ?>"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.style.display='none'; this.parentElement.style.background='#f3f4f6';">
                                            </div>
                                            <div>
                                                <p class="font-bold text-brand-900"><?php echo $book['title']; ?></p>
                                                <p class="text-xs text-gray-400"><?php echo $book['author']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-medium text-gray-500">
                                        <?php echo $book['category_name'] ?: 'N/A'; ?>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-brand-900">
                                        ৳<?php echo bn_num($book['sell_price']); ?></td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <?php
    $stock_percent = min(100, ($book['stock_qty'] / 20) * 100);
    $stock_color = ($book['stock_qty'] <= 5) ? 'bg-red-500' : 'bg-green-500';
?>
                                            <div class="w-24 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                <div class="<?php echo $stock_color; ?> h-full"
                                                    style="width: <?php echo $stock_percent; ?>%"></div>
                                            </div>
                                            <span
                                                class="text-xs font-bold text-brand-900"><?php echo bn_num($book['stock_qty']); ?>টি</span>
                                        </div>
                                        <p
                                            class="text-[9px] <?php echo($book['stock_qty'] <= 5) ? 'text-red-500' : 'text-green-500'; ?> font-bold uppercase mt-1">
                                            <?php echo($book['stock_qty'] <= 5) ? 'স্টক কম' : 'ইন স্টক'; ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="editBook(<?php echo htmlspecialchars(json_encode($book)); ?>)"
                                                class="p-2 text-gray-400 hover:text-brand-gold transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button onclick="deleteBook(<?php echo $book['id']; ?>)"
                                                class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Simple Pagination -->
                <div
                    class="px-8 py-5 bg-gray-50/30 flex items-center justify-between border-t border-gray-50 font-anek">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">দেখানো হচ্ছে ১-১০ (মোট ৫০০টি
                        বইয়ের মধ্যে)</p>
                    <div class="flex gap-2">
                        <button
                            class="px-4 py-2 bg-white border border-gray-100 rounded-xl text-xs font-bold text-brand-900 shadow-sm hover:border-brand-gold transition-all">পূর্ববর্তী</button>
                        <button
                            class="px-4 py-2 bg-brand-900 text-white rounded-xl text-xs font-bold shadow-md hover:bg-brand-gold hover:text-brand-900 transition-all">পরবর্তী</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Members -->
        <div id="tab-members" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">মেম্বার ডেটাবেস</h1>
                    <p class="text-gray-500 font-light">নিবন্ধিত লাইব্রেরি মেম্বারদের তথ্য ও স্ট্যাটাস ম্যানেজ করুন।</p>
                </div>
                <div class="relative w-full md:w-80">
                    <input type="text" placeholder="মেম্বার নাম বা আইডি দিয়ে সার্চ..."
                        class="w-full bg-white border border-gray-100 rounded-2xl px-12 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold transition-all font-anek text-brand-900 shadow-sm">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    মেম্বার</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    ID</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    প্যাকেজ</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    যোগদানের তারিখ</th>
                                <th
                                    class="px-10 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php if (empty($admin_members)): ?>
                                <tr>
                                    <td colspan="5" class="px-10 py-20 text-center text-gray-400 font-anek">আপাতত কোনো
                                        মেম্বার পাওয়া যায়নি।</td>
                                </tr>
                            <?php
else: ?>
                                <?php foreach ($admin_members as $member):
        $first_char = mb_substr($member['full_name'], 0, 2, 'UTF-8');
        $join_date = date('d F, Y', strtotime($member['created_at']));
        // Translate month to Bengali if needed, but standard date is fine for now
?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-10 py-6">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-brand-light flex items-center justify-center text-brand-900 font-bold">
                                                    <?php echo $first_char; ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-brand-900">
                                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                                    </div>
                                                    <div class="text-[10px] text-gray-400">
                                                        <?php echo htmlspecialchars($member['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-10 py-6 text-xs text-gray-500 font-mono">
                                            <?php echo !empty($member['membership_id']) ? $member['membership_id'] : 'N/A'; ?>
                                        </td>
                                        <td class="px-10 py-6">
                                            <span
                                                class="px-3 py-1 bg-brand-gold/20 text-brand-900 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                                <?php
        $plan = $member['membership_plan'] ?? 'None';
        if ($plan == 'General')
            echo 'সাধারণ';
        else if ($plan == 'BookLover')
            echo 'বইপ্রেমী';
        else if ($plan == 'Collector')
            echo 'সংগ্রাহক';
        else
            echo 'বেসিক';
?>
                                            </span>
                                        </td>
                                        <td class="px-10 py-6 text-sm text-gray-500">
                                            <?php echo $join_date; ?>
                                        </td>
                                        <td class="px-10 py-6 text-center">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="text-right mr-4">
                                                    <div class="text-xs font-bold text-brand-900">
                                                        ৳<?php echo bn_num($member['acc_balance']); ?></div>
                                                    <div class="text-[8px] text-gray-400 uppercase tracking-widest">ওয়ালেট
                                                        ব্যালেন্স</div>
                                                </div>
                                                <button class="text-gray-400 hover:text-brand-900 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Suggested Books -->
        <div id="tab-suggested" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">সাজেস্টেড বই তালিকা</h1>
                    <p class="text-gray-500 font-light">হোমপেজে প্রদর্শিত বইগুলো এখান থেকে ম্যানেজ করুন।</p>
                </div>
            </div>

            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বই</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    ক্যাটাগরি</th>
                                <th
                                    class="px-10 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    মূল্য</th>
                                <th
                                    class="px-10 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php
include '../../includes/db_connect.php';
$stmt = $pdo->query("SELECT b.*, c.name as cat_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.is_suggested = 1");
$suggested = $stmt->fetchAll();
foreach ($suggested as $book):
?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-14 bg-gray-100 rounded shadow-sm overflow-hidden">
                                                <img src="<?php echo !empty($book['cover_image']) ? '../../admin/assets/book-images/' . $book['cover_image'] : ''; ?>"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.style.display='none'; this.parentElement.style.background='#f3f4f6';">
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-brand-900">
                                                    <?php echo $book['title']; ?>
                                                </div>
                                                <div class="text-[10px] text-gray-400">
                                                    <?php echo $book['author']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 text-sm text-gray-500">
                                        <?php echo $book['cat_name']; ?>
                                    </td>
                                    <td class="px-10 py-6 text-sm font-bold text-brand-900">৳
                                        <?php echo $book['sell_price']; ?>
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <span
                                            class="px-3 py-1 bg-brand-gold/10 text-brand-900 rounded-full text-[10px] font-bold uppercase tracking-widest">সাজেস্টেড</span>
                                    </td>
                                </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Borrows -->
        <div id="tab-borrows" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md::items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">বরো ট্র্যাকার</h1>
                    <p class="text-gray-500 font-light">সকল সক্রিয় বরো অনুরোধ এবং ফেরত প্রদানের স্ট্যাটাস ম্যানেজে করুন।
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    গ্রাহক</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বই</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    বরো তারিখ</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    শেষ তারিখ (Due)</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    স্ট্যাটাস</th>
                                <th
                                    class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-anek">
                            <?php if (empty($active_borrows)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-gray-400 font-anek">কোনো সক্রিয় বরো নেই।
                                    </td>
                                </tr>
                            <?php
else: ?>
                                <?php foreach ($active_borrows as $borrow):
        // 1. Sync Service: If order is Shipped/Delivered, Borrow MUST be Active
        if ($borrow['status'] === 'Processing' && in_array($borrow['order_status'], ['Shipped', 'Delivered'])) {
            $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$borrow['id']]);
            $borrow['status'] = 'Active';
            $borrow['borrow_date'] = date('Y-m-d H:i:s');
        }

        // 2. Overdue Check
        $is_overdue = $borrow['status'] === 'Active' && strtotime($borrow['due_date'] . ' 23:59:59') < time();
        if ($is_overdue) {
            $pdo->prepare("UPDATE borrows SET status = 'Overdue' WHERE id = ?")->execute([$borrow['id']]);
            $borrow['status'] = 'Overdue';
        }
        switch ($borrow['status']) {
            case 'Processing':
                $bstatus_color = 'bg-yellow-100 text-yellow-700';
                break;
            case 'Active':
                $bstatus_color = 'bg-green-100 text-green-700';
                break;
            case 'Overdue':
                $bstatus_color = 'bg-red-100 text-red-600';
                break;
            default:
                $bstatus_color = 'bg-gray-100 text-gray-600';
                break;
        }
        switch ($borrow['status']) {
            case 'Processing':
                $bstatus_label = 'প্রসেসিং';
                break;
            case 'Active':
                $bstatus_label = 'সক্রিয়';
                break;
            case 'Overdue':
                $bstatus_label = 'মেয়াদ পার';
                break;
            default:
                $bstatus_label = $borrow['status'];
                break;
        }
?>
                                    <tr class="hover:bg-gray-50/30 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="font-bold text-brand-900 text-sm">
                                                <?php echo htmlspecialchars($borrow['full_name']); ?>
                                            </div>
                                            <div class="text-[10px] text-gray-400"><?php echo $borrow['phone']; ?></div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-11 rounded bg-gray-100 overflow-hidden flex-shrink-0">
                                                    <?php if (!empty($borrow['cover_image'])): ?>
                                                        <img src="../../admin/assets/book-images/<?php echo htmlspecialchars($borrow['cover_image']); ?>"
                                                            class="w-full h-full object-cover"
                                                            onerror="this.style.display='none'; this.parentElement.style.background='#f3f4f6';">
                                                    <?php
        else: ?>
                                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    <?php
        endif; ?>
                                                </div>
                                                <span
                                                    class="text-sm font-bold text-brand-900"><?php echo htmlspecialchars($borrow['title']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-sm text-gray-500">
                                            <?php echo date('d M Y', strtotime($borrow['borrow_date'])); ?>
                                        </td>
                                        <td
                                            class="px-8 py-5 text-sm <?php echo $is_overdue ? 'text-red-600 font-bold' : 'text-gray-500'; ?>">
                                            <?php echo date('d M Y', strtotime($borrow['due_date'])); ?>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span
                                                class="px-3 py-1 <?php echo $bstatus_color; ?> rounded-full text-[10px] font-bold uppercase tracking-widest">
                                                <?php echo $bstatus_label; ?>
                                            </span>
                                            <?php if ($borrow['status'] === 'Active'): ?>
                                                <div class="mt-2 text-[8px] text-gray-400 font-bold uppercase tracking-widest">
                                                    অগ্রগতি: <?php echo bn_num($borrow['reading_progress']); ?>%</div>
                                                <div class="w-16 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                                    <div class="bg-brand-gold h-full"
                                                        style="width: <?php echo $borrow['reading_progress']; ?>%"></div>
                                                </div>
                                            <?php
        endif; ?>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <?php if ($borrow['status'] === 'Active' || $borrow['status'] === 'Overdue'): ?>
                                                <button onclick="returnBook(<?php echo $borrow['id']; ?>)"
                                                    class="px-4 py-2 bg-brand-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all">
                                                    ফেরত নিন
                                                </button>
                                            <?php
        else: ?>
                                                <span class="text-[10px] text-gray-400">অপেক্ষায়</span>
                                            <?php
        endif; ?>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pre-orders Tab -->
        <div id="tab-preorders" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">প্রি-অর্ডার ম্যানেজমেন্ট</h1>
                    <p class="text-gray-500 font-light">আসন্ন বইগুলোর প্রি-বুকিং তথ্য এবং হট ডিল সেটআপ করুন।</p>
                </div>
                <button onclick="openPreorderModal()"
                    class="px-8 py-4 bg-brand-900 text-white font-bold rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/20 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    নতুন প্রি-অর্ডার
                </button>
            </div>

            <!-- Sub-tabs Nav -->
            <div class="flex gap-8 mb-8 border-b border-gray-100 px-2">
                <button onclick="switchPreorderSubTab('manage')" id="subnav-manage"
                    class="pb-4 text-sm font-bold border-b-2 border-brand-900 text-brand-900 transition-all">ম্যানেজমেন্ট</button>
                <button onclick="switchPreorderSubTab('bookings')" id="subnav-bookings"
                    class="pb-4 text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-brand-900 transition-all">প্রি-অর্ডার
                    লিস্ট (অর্ডারসমূহ)</button>
            </div>

            <!-- Subtab: Management -->
            <div id="preorder-subtab-manage" class="preorder-subcontent">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        বই ও লেখক</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        মূল্য (ছাড়)</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        রিলিজ ডেট</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        স্ট্যাটাস</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        টাইপ</th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admin_preorders)): ?>
                                    <tr>
                                        <td colspan="6" class="px-8 py-20 text-center text-gray-400">কোনো প্রি-অর্ডার ডাটা
                                            পাওয়া
                                            যায়নি।</td>
                                    </tr>
                                <?php
else:
    foreach ($admin_preorders as $po): ?>
                                        <tr class="hover:bg-gray-50/30 transition-colors">
                                            <td class="px-8 py-5">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-14 rounded bg-gray-100 overflow-hidden">
                                                        <img src="<?php echo strpos($po['cover_image'], 'http') === 0 ? htmlspecialchars($po['cover_image']) : '../../assets/img/preorders/' . htmlspecialchars(trim($po['cover_image'])); ?>"
                                                            class="w-full h-full object-cover">
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-brand-900 text-sm">
                                                            <?php 
                                                            $combo_title = htmlspecialchars($po['title']);
                                                            if (!empty($po['second_title'])) {
                                                                $combo_title .= ' এবং ' . htmlspecialchars($po['second_title']) . ' (কম্বো)';
                                                            }
                                                            echo $combo_title; 
                                                            ?>
                                                        </div>
                                                        <div class="text-[10px] text-gray-400">
                                                            <?php echo htmlspecialchars($po['author']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="text-sm font-bold text-brand-900">
                                                    ৳<?php echo bn_num((int)$po['discount_price']); ?></div>
                                                <div class="text-[10px] text-gray-400 line-through">
                                                    ৳<?php echo bn_num((int)$po['price']); ?></div>
                                            </td>
                                            <td class="px-8 py-5 text-sm text-gray-500">
                                                <?php echo date('d M Y', strtotime($po['release_date'])); ?>
                                            </td>
                                            <td class="px-8 py-5">
                                                <?php
        $po_status_class = $po['status'] == 'Open' ? 'bg-green-100 text-green-700' : ($po['status'] == 'Upcoming' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
        $po_status_label = $po['status'] == 'Open' ? 'চলছে' : ($po['status'] == 'Upcoming' ? 'আসন্ন' : 'বন্ধ');
?>
                                                <span
                                                    class="px-3 py-1 <?php echo $po_status_class; ?> rounded-full text-[10px] font-bold uppercase tracking-widest">
                                                    <?php echo $po_status_label; ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="flex flex-col gap-1">
                                                    <?php if ($po['is_hot_deal']): ?>
                                                        <span
                                                            class="w-fit text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded">হট
                                                            ডিল</span>
                                                    <?php
        endif; ?>
                                                    <?php if ($po['free_delivery']): ?>
                                                        <span
                                                            class="w-fit text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">ফ্রি
                                                            ডেলিভারি</span>
                                                    <?php
        endif; ?>
                                                    <?php if (!$po['is_hot_deal'] && !$po['free_delivery']): ?>
                                                        <span class="text-xs text-gray-400">সাধারণ</span>
                                                    <?php
        endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="../../pre-booking/<?php echo !empty($po['slug']) ? 'book/' . $po['slug'] : 'book-details.php?id=' . $po['id']; ?>" target="_blank"
                                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    <button onclick='editPreorder(<?php echo json_encode($po); ?>)'
                                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deletePreorder(<?php echo $po['id']; ?>)"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
    endforeach;
endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subtab: Pre-order Bookings List -->
            <div id="preorder-subtab-bookings" class="preorder-subcontent hidden">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        অর্ডার / তারিখ</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        ক্রেতা</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        বই</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        রিলিজ ডেট</th>
                                    <th
                                        class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        স্ট্যাটাস</th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admin_preorder_bookings)): ?>
                                    <tr>
                                        <td colspan="6" class="px-8 py-20 text-center text-gray-400 font-anek">আপাতত কোনো
                                            প্রি-অর্ডার বুকিং নেই।</td>
                                    </tr>
                                <?php
else:
    foreach ($admin_preorder_bookings as $booking): ?>
                                        <tr class="hover:bg-gray-50/30 transition-colors border-b border-gray-50 last:border-0">
                                            <td class="px-8 py-5">
                                                <div class="font-bold text-brand-900 text-xs">
                                                    #<?php echo $booking['invoice_no']; ?></div>
                                                <div class="text-[10px] text-gray-400 mt-1">
                                                    <?php echo date('d M, Y', strtotime($booking['order_date'])); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="font-bold text-brand-900 text-xs">
                                                    <?php echo htmlspecialchars($booking['full_name']); ?>
                                                </div>
                                                <div class="text-[10px] text-gray-400">
                                                    <?php echo htmlspecialchars($booking['phone']); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="font-bold text-brand-900 text-xs">
                                                    <?php echo htmlspecialchars($booking['po_title']); ?>
                                                </div>
                                                <div class="text-[10px] text-gray-400">পরিমাণ:
                                                    <?php echo bn_num($booking['quantity']); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="text-xs text-gray-500">
                                                    <?php echo date('d M, Y', strtotime($booking['po_release'])); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <?php
        $s_class = $booking['order_status'] == 'Delivered' ? 'bg-green-100 text-green-700' : ($booking['order_status'] == 'Processing' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700');
?>
                                                <span
                                                    class="px-3 py-1 <?php echo $s_class; ?> rounded-full text-[10px] font-bold">
                                                    <?php echo $booking['order_status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <button onclick="viewPreOrderDetails(<?php echo htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8'); ?>)"
                                                    class="p-2 text-brand-900 hover:bg-brand-gold/10 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
    endforeach; ?>
                                <?php
endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tab: Payments -->
        <div id="tab-payments" class="p-8 lg:p-12 tab-content hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-3xl font-anek font-bold text-brand-900 mb-2">পেমেন্ট সেটিংস</h1>
                    <p class="text-gray-500 font-light">আপনার বুকশপের পেমেন্ট মেথডগুলো এখান থেকে কন্ট্রোল করুন।</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($payment_methods as $method):
    $config = json_decode($method['config_json'], true) ?: [];
?>
                    <div
                        class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm transition-all hover:shadow-xl relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center">
                                    <?php if ($method['method_key'] == 'bkash'): ?>
                                        <img src="../../assets/img/bkash-logo.jpg" class="w-8 h-auto"
                                            onerror="this.src='https://raw.githubusercontent.com/bikashpoudel/bkash-logo/master/bkash_logo.webp'">
                                    <?php
    elseif ($method['method_key'] == 'nagad'): ?>
                                        <img src="../../assets/img/nagad-logo.jpg" class="w-8 h-auto"
                                            onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'">
                                    <?php
    elseif ($method['method_key'] == 'cod'): ?>
                                        <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                    <?php
    else: ?>
                                        <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    <?php
    endif; ?>
                                </div>
                                <div>
                                    <h3 class="font-anek font-bold text-brand-900">
                                        <?php echo htmlspecialchars($method['method_name']); ?>
                                    </h3>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                        <?php echo strtoupper($method['method_key']); ?>
                                    </p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                    onchange="togglePaymentMethod('<?php echo $method['method_key']; ?>', this.checked)"
                                    class="sr-only peer" <?php echo $method['is_active'] ? 'checked' : ''; ?>>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-gold">
                                </div>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <?php if ($method['method_key'] == 'bkash' || $method['method_key'] == 'nagad'): ?>
                                <button
                                    onclick='openPaymentConfigModal("<?php echo $method['method_key']; ?>", <?php echo json_encode($config); ?>)'
                                    class="w-full py-4 bg-brand-light text-brand-900 rounded-2xl font-anek font-bold text-xs hover:bg-brand-900 hover:text-white transition-all">API
                                    কনফিগারেশন আপডেট করুন</button>
                            <?php
    else: ?>
                                <p class="text-xs text-gray-400 font-anek leading-relaxed">এই মেথডটির জন্য কোনো বিশেষ API
                                    কনফিগারেশন প্রয়োজন নেই। এটি সরাসরি কাস্টমার চেকআউট পেজে প্রদর্শিত হবে।</p>
                            <?php
    endif; ?>
                        </div>
                    </div>
                <?php
endforeach; ?>
            </div>

            <div class="mt-12">
                <h2 class="text-2xl font-anek font-bold text-brand-900 mb-8">ডেলিভারি চার্জ সেটিংস</h2>
                <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm max-w-2xl">
                    <form onsubmit="updateDeliveryCharges(event)" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">কক্সবাজার শহর (Inside)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold">৳</span>
                                    <input type="number" id="charge_inside" value="<?php echo $inside_charge; ?>" required
                                        class="w-full bg-gray-50 border border-transparent rounded-2xl pl-12 pr-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-bold text-brand-900 shadow-inner">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">আউটসাইড কক্সবাজার (Outside)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold">৳</span>
                                    <input type="number" id="charge_outside" value="<?php echo $outside_charge; ?>" required
                                        class="w-full bg-gray-50 border border-transparent rounded-2xl pl-12 pr-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-bold text-brand-900 shadow-inner">
                                </div>
                            </div>
                        </div>
                        <button type="submit" 
                            class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/20">চার্জ আপডেট করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Book Modal -->
    <div id="add-book-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-brand-900/40" onclick="closeAddBookModal()"></div>
        <div
            class="bg-white w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl relative z-10 overflow-hidden animate-slide-up border border-white/20 flex flex-col">
            <!-- Modal Header -->
            <div class="bg-brand-900 p-8 flex justify-between items-center shrink-0">
                <div>
                    <h3 id="modal-title" class="text-2xl font-anek font-bold text-white">নতুন বই যোগ করুন (বিস্তারিত)
                    </h3>
                    <p class="text-brand-gold text-[10px] font-bold uppercase tracking-widest mt-1">পূর্ণাঙ্গ ইনভেন্টরি
                        ম্যানেজমেন্ট</p>
                </div>
                <button onclick="closeAddBookModal()" class="text-white/50 hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Form Body -->
            <form id="add-book-form" class="p-10 overflow-y-auto space-y-12" onsubmit="handleAddBook(event)"
                enctype="multipart/form-data">
                <input type="hidden" name="book_id" id="book_id">

                <!-- Section 1: সাধারণ তথ্য -->
                <div>
                    <h4
                        class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-brand-gold/30"></span> সাধারণ তথ্য
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">বইয়ের
                                নাম (Title) *</label>
                            <input type="text" name="title" required placeholder="বইয়ের শিরোনাম"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">Book Title (English) *</label>
                            <input type="text" name="title_en" required placeholder="Book title in English"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">উপ-শিরোনাম
                                (Subtitle)</label>
                            <input type="text" name="subtitle" placeholder="যদি থাকে"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">বইয়ের
                                বিবরণ (Description / Notes)</label>
                            <textarea name="description" rows="3" placeholder="বই সম্পর্কে বিস্তারিত তথ্য..."
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:col-span-2">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ক্যাটাগরি</label>
                                <select name="category_id" id="category_select" onchange="toggleNewCategoryInput()"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold appearance-none">
                                    <option value="">সিলেক্ট করুন</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php
endforeach; ?>
                                    <option value="new">-- নতুন ক্যাটাগরি যোগ করুন --</option>
                                </select>
                            </div>
                            <div id="new_category_div" class="space-y-2 hidden">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">নতুন
                                    ক্যাটাগরির নাম</label>
                                <input type="text" name="new_category_name" placeholder="ক্যাটাগরির নাম লিখুন"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">জেনার
                                    (Genre)</label>
                                <input type="text" name="genre" placeholder="উদা: থ্রিলার, রোমান্টিক"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ভাষা
                                    (Language)</label>
                                <input type="text" name="language" placeholder="উদা: বাংলা, ইংরেজি"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: লেখক ও প্রকাশনা -->
                <div>
                    <h4
                        class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-brand-gold/30"></span> লেখক ও প্রকাশনা
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">প্রধান
                                লেখক (Author) *</label>
                            <input type="text" name="author" required placeholder="লেখকের নাম"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">Author (English) *</label>
                            <input type="text" name="author_en" required placeholder="Author name in English"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সহ-
                                লেখক (Co-author)</label>
                            <input type="text" name="co_author" placeholder="সহ-লেখকের নাম লিখুন"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">প্রকাশক
                                (Publisher)</label>
                            <input type="text" name="publisher" placeholder="প্রকাশনীর নাম"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1 space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">প্রকাশ
                                    সাল</label>
                                <input type="text" name="publish_year" placeholder="২০২৬"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-4 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                            <div class="col-span-1 space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সংস্করণ</label>
                                <input type="text" name="edition" placeholder="১ম"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-4 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                            <div class="col-span-1 space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ISBN</label>
                                <input type="text" name="isbn" placeholder="ISBN নং"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-4 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: ইনভেন্টরি ও অবস্থান -->
                <div>
                    <h4
                        class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-brand-gold/30"></span> ইনভেন্টরি ও অবস্থান
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ফরম্যাট
                                (Format)</label>
                            <select name="format"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 appearance-none">
                                <option value="Paperback">Paperback</option>
                                <option value="Hardcover">Hardcover</option>
                                <option value="E-book">E-book</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">পৃষ্ঠা
                                সংখ্যা</label>
                            <input type="number" name="page_count" placeholder="০"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">অবস্থা
                                (Condition)</label>
                            <select name="book_condition"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 appearance-none">
                                <option value="New">New</option>
                                <option value="Used">Used</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">শেল্ফ
                                লোকেশন</label>
                            <input type="text" name="shelf_location" placeholder="A1, B2"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">র‍্যাক
                                নাম্বার</label>
                            <input type="text" name="rack_number" placeholder="Rack-05"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">স্টক
                                    পরিমাণ (Qty)</label>
                                <input type="number" name="stock_qty" required placeholder="১০"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-4 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">মিনিমাম
                                    স্টক লেভেল</label>
                                <input type="number" name="min_stock_level" placeholder="২"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-4 py-4 focus:outline-none transition-all font-anek text-brand-900 text-red-500">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ধার
                                দেওয়া যাবে? (Borrowable)</label>
                            <div
                                class="flex items-center gap-4 px-6 py-3.5 bg-brand-light rounded-2xl border border-transparent">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_borrowable" value="1" class="sr-only peer" checked>
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-gold">
                                    </div>
                                </label>
                                <span class="text-xs font-bold text-brand-900 font-anek">হ্যাঁ, ধার দেওয়া যাবে</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সাজেস্টেড
                                বই? (Suggested)</label>
                            <div
                                class="flex items-center gap-4 px-6 py-3.5 bg-brand-light rounded-2xl border border-transparent">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_suggested" value="1" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-gold">
                                    </div>
                                </label>
                                <span class="text-xs font-bold text-brand-900 font-anek">হ্যাঁ, সাজেস্টেড বই</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: মূল্য ও সাপ্লায়ার -->
                <div>
                    <h4
                        class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-brand-gold/30"></span> মূল্য ও সাপ্লায়ার
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">ক্রয়
                                    মূল্য (৳)</label>
                                <input type="number" name="purchase_price" placeholder="৳০০০"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">বিক্রয়
                                    মূল্য (৳) *</label>
                                <input type="number" name="sell_price" required placeholder="৳০০০"
                                    class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900 font-bold">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সাপ্লায়ারের
                                নাম</label>
                            <input type="text" name="supplier_name" placeholder="সরবরাহকারী প্রতিষ্ঠান / ব্যক্তির নাম"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সাপ্লায়ার
                                কন্টাক্ট (Phone/Email)</label>
                            <input type="text" name="supplier_contact" placeholder="যোগাযোগের তথ্য"
                                class="w-full bg-brand-light border border-transparent focus:border-brand-gold rounded-2xl px-6 py-4 focus:outline-none transition-all font-anek text-brand-900">
                        </div>
                    </div>
                </div>

                <!-- Section 5: মিডিয়া ও মেটাডেটা -->
                <div>
                    <h4
                        class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-[1px] bg-brand-gold/30"></span> মিডিয়া ও মেটাডেটা
                    </h4>
                    <div class="space-y-8">
                        <!-- Image Upload Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Cover Photo -->
                            <div class="space-y-4">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">বইয়ের
                                    কভার (Cover)</label>
                                <div class="relative group">
                                    <input type="file" name="cover_image" id="cover_image" accept="image/*"
                                        class="hidden" onchange="previewImage(this, 'cover-preview')">
                                    <label for="cover_image"
                                        class="flex flex-col items-center justify-center w-full aspect-[3/4] bg-brand-light border-2 border-dashed border-brand-gold/20 rounded-3xl cursor-pointer hover:border-brand-gold/50 hover:bg-brand-gold/5 transition-all group overflow-hidden">
                                        <div id="cover-preview" class="absolute inset-0 hidden">
                                            <img src="" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-xs font-bold font-anek">পরিবর্তন
                                                    করুন</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center p-6 text-center">
                                            <svg class="w-10 h-10 text-brand-gold/40 mb-3 group-hover:scale-110 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-bold text-brand-900 font-anek">কভার আপলোড</span>
                                            <span class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">JPG,
                                                PNG (Max 5MB)</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Second Photo -->
                            <div class="space-y-4">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">দ্বিতীয়
                                    ছবি (Photo 2)</label>
                                <div class="relative group">
                                    <input type="file" name="photo_2" id="photo_2" accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'photo2-preview')">
                                    <label for="photo_2"
                                        class="flex flex-col items-center justify-center w-full aspect-[3/4] bg-brand-light border-2 border-dashed border-gray-100 rounded-3xl cursor-pointer hover:border-brand-gold/50 hover:bg-brand-gold/5 transition-all group overflow-hidden">
                                        <div id="photo2-preview" class="absolute inset-0 hidden">
                                            <img src="" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-xs font-bold font-anek">পরিবর্তন
                                                    করুন</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center p-6 text-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-3 group-hover:scale-110 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span class="text-xs font-bold text-gray-500 font-anek">যোগ করুন</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Third Photo -->
                            <div class="space-y-4">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">তৃতীয়
                                    ছবি (Photo 3)</label>
                                <div class="relative group">
                                    <input type="file" name="photo_3" id="photo_3" accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'photo3-preview')">
                                    <label for="photo_3"
                                        class="flex flex-col items-center justify-center w-full aspect-[3/4] bg-brand-light border-2 border-dashed border-gray-100 rounded-3xl cursor-pointer hover:border-brand-gold/50 hover:bg-brand-gold/5 transition-all group overflow-hidden">
                                        <div id="photo3-preview" class="absolute inset-0 hidden">
                                            <img src="" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-xs font-bold font-anek">পরিবর্তন
                                                    করুন</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center p-6 text-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-3 group-hover:scale-110 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span class="text-xs font-bold text-gray-500 font-anek">যোগ করুন</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">যোগ
                                    করার তারিখ</label>
                                <input type="text" readonly value="০৭ মার্চ, ২০২৬"
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:outline-none font-anek text-gray-400 cursor-not-allowed">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">সর্বশেষ
                                    আপডেট</label>
                                <input type="text" readonly value="এখনই"
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:outline-none font-anek text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer Actions -->
                <div
                    class="sticky bottom-0 bg-white pt-6 pb-2 mt-10 border-t border-gray-100 flex flex-col sm:flex-row gap-4 shrink-0">
                    <button type="button" onclick="closeAddBookModal()"
                        class="flex-1 py-4 sm:py-5 bg-gray-100 text-gray-500 font-anek font-bold text-lg rounded-2xl hover:bg-gray-200 transition-all">বাতিল
                        করুন</button>
                    <button id="modal-submit-btn" type="submit"
                        class="flex-[2] py-4 sm:py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/20">ইনভেন্টরিতে
                        সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="order-details-modal"
        class="fixed inset-0 bg-brand-900/60 z-[100] hidden items-center justify-center p-4">
        <div class="bg-white rounded-[40px] w-full max-w-2xl max-h-[90vh] overflow-y-auto relative shadow-2xl">
            <button onclick="closeOrderDetailsModal()"
                class="absolute top-8 right-8 text-gray-400 hover:text-brand-900 transition-colors z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div id="order-details-content" class="p-10 md:p-12">
                <!-- Content injected by JS -->
            </div>
        </div>
    </div>

    <!-- Pre-order Modal -->
    <div id="preorder-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-brand-900/40" onclick="closePreorderModal()"></div>
        <div
            class="bg-white w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl relative z-10 overflow-hidden animate-slide-up border border-white/20 flex flex-col">
            <!-- Modal Header -->
            <div class="bg-brand-900 p-8 flex justify-between items-center shrink-0">
                <div>
                    <h3 id="po-modal-title" class="text-2xl font-anek font-bold text-white">নতুন প্রি-অর্ডার</h3>
                    <p class="text-brand-gold text-[10px] font-bold uppercase tracking-widest mt-1">প্রি-বুকিং
                        ম্যানেজমেন্ট</p>
                </div>
                <button onclick="closePreorderModal()" class="text-white/50 hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="preorder-form" onsubmit="handlePreorder(event)" class="flex-1 overflow-y-auto p-8 md:p-12">
                <input type="hidden" name="po_id" id="po_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Left Column: Basic Info -->
                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">১ম বইয়ের নাম (প্রধান)</label>
                            <input type="text" name="title" required placeholder="বইয়ের নাম লিখুন"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">২য় বইয়ের নাম (কম্বো হলে)</label>
                            <input type="text" name="second_title" placeholder="২য় বইয়ের নাম লিখুন"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Book Title (English)</label>
                            <input type="text" name="title_en" placeholder="Book title in English"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">সাব-টাইটেল
                                (Sub-title)</label>
                            <input type="text" name="sub_title" placeholder="যেমন: কম্বো প্যাকে বিশেষ ছাড়!"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">লেখক</label>
                            <input type="text" name="author" required placeholder="লেখকের নাম লিখুন"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Author (English)</label>
                            <input type="text" name="author_en" placeholder="Author name in English"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">১ম বইয়ের বর্ণনা</label>
                            <textarea name="description" rows="3"
                                placeholder="১ম বইয়ের বিস্তারিত বর্ণনা বা কম্বো অফারের বিবরণ লিখুন"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">২য় বইয়ের বর্ণনা (ঐচ্ছিক)</label>
                            <textarea name="description_2" rows="3"
                                placeholder="২য় বইয়ের বর্ণনা লিখুন"
                                class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">মূল
                                    মূল্য</label>
                                <input type="number" name="price" required placeholder="৳ ০০"
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">ছাড়ের
                                    মূল্য</label>
                                <input type="number" name="discount_price" placeholder="৳ ০০"
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings & Image -->
                    <div class="space-y-8">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">রিলিজ
                                    ডেট</label>
                                <input type="date" name="release_date" required
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">স্ট্যাটাস</label>
                                <select name="status"
                                    class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold focus:bg-white outline-none transition-all font-anek font-medium shadow-inner">
                                    <option value="Upcoming">Upcoming (আসন্ন)</option>
                                    <option value="Open">Open (চলছে)</option>
                                    <option value="Closed">Closed (বন্ধ)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 bg-orange-50 p-6 rounded-3xl border border-orange-100">
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-orange-900 font-anek">হট ডিল হিসেবে দেখান</h4>
                                <p class="text-[10px] text-orange-700 font-anek">এটি স্লাইডার বা বিশেষ সেকশনে প্রদর্শিত
                                    হবে</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_hot_deal" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-orange-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center gap-4 bg-blue-50 p-6 rounded-3xl border border-blue-100">
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-blue-900 font-anek">ফ্রি ডেলিভারি সুবিধা</h4>
                                <p class="text-[10px] text-blue-700 font-anek">এটি অন থাকলে ডেলিভারি চার্জ যোগ হবে না</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="free_delivery" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-blue-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">কাভার ইমেজ
                                আপলোড করুন</label>
                            <div class="flex items-center gap-6">
                                <div id="po-cover-preview"
                                    class="w-24 h-32 rounded-2xl bg-gray-50 border border-brand-gold/20 overflow-hidden flex-shrink-0 relative group <?php echo empty($po['cover_image']) ? 'hidden' : ''; ?>">
                                    <img src="" class="w-full h-full object-cover">
                                    <div
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-[8px] font-bold">পরিবর্তন</span>
                                    </div>
                                </div>
                                <label class="flex-1">
                                    <input type="file" name="cover_image" accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'po-cover-preview')">
                                    <div
                                        class="w-full bg-gray-50 border-2 border-dashed border-brand-gold/20 rounded-2xl px-6 py-8 flex flex-col items-center justify-center cursor-pointer hover:bg-brand-gold/5 hover:border-brand-gold/50 transition-all group">
                                        <svg class="w-8 h-8 text-brand-gold/40 mb-2 group-hover:scale-110 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">ইমেজ
                                            আপলোড</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Second Cover Image -->
                        <div class="space-y-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">দ্বিতীয়
                                কাভার ইমেজ (ঐচ্ছিক)</label>
                            <div class="flex items-center gap-6">
                                <div id="po-second-cover-preview"
                                    class="w-24 h-32 rounded-2xl bg-gray-50 border border-brand-gold/20 overflow-hidden flex-shrink-0 relative group <?php echo empty($po['second_cover_image']) ? 'hidden' : ''; ?>">
                                    <img src="" class="w-full h-full object-cover">
                                    <div
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-[8px] font-bold">পরিবর্তন</span>
                                    </div>
                                </div>
                                <label class="flex-1">
                                    <input type="file" name="second_cover_image" accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'po-second-cover-preview')">
                                    <div
                                        class="w-full bg-gray-50 border-2 border-dashed border-brand-gold/20 rounded-2xl px-6 py-8 flex flex-col items-center justify-center cursor-pointer hover:bg-brand-gold/5 hover:border-brand-gold/50 transition-all group">
                                        <svg class="w-8 h-8 text-brand-gold/40 mb-2 group-hover:scale-110 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">দ্বিতীয়
                                            ইমেজ
                                            আপলোড</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div
                    class="sticky bottom-0 bg-white pt-6 pb-2 mt-10 border-t border-gray-100 flex flex-col sm:flex-row gap-4 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                    <button type="button" onclick="closePreorderModal()"
                        class="flex-1 py-4 sm:py-5 bg-gray-100 text-gray-500 font-anek font-bold text-lg rounded-2xl hover:bg-gray-200 transition-all">বাতিল
                        করুন</button>
                    <button type="submit"
                        class="flex-[2] py-4 sm:py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/20">সংরক্ষণ
                        করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast"
        class="fixed bottom-10 right-10 z-[100] transform translate-y-20 opacity-0 transition-all duration-500 pointer-events-none">
        <div
            class="bg-brand-900 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-brand-gold/20">
            <div class="w-2 h-2 rounded-full bg-brand-gold animate-pulse"></div>
            <p id="toast-msg" class="font-anek font-bold"></p>
        </div>
    </div>

    <!-- Upload Progress Modal -->
    <div id="upload-progress-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-brand-900/60"></div>
        <div class="bg-white w-full max-w-sm rounded-[40px] shadow-2xl relative z-10 p-10 text-center space-y-8 border border-white/20">
            <div class="relative w-32 h-32 mx-auto">
                <svg class="w-full h-full">
                    <circle class="text-gray-100" stroke-width="8" stroke="currentColor" fill="transparent" r="58" cx="64" cy="64" />
                    <circle id="progress-circle" class="text-brand-gold progress-ring" stroke-width="8" stroke-dasharray="364.4" stroke-dashoffset="364.4" stroke-linecap="round" stroke="currentColor" fill="transparent" r="58" cx="64" cy="64" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span id="progress-percent" class="text-2xl font-anek font-extrabold text-brand-900">০%</span>
                </div>
            </div>
            <div>
                <h3 id="progress-title" class="text-xl font-anek font-bold text-brand-900 mb-2">প্রসেসিং হচ্ছে</h3>
                <p id="progress-status" class="text-sm text-gray-400 font-anek loading-dots">আপনার ছবিগুলো কম্প্রেস করা হচ্ছে</p>
            </div>
            <div class="pt-4">
                <div class="w-full bg-gray-50 h-2 rounded-full overflow-hidden">
                    <div id="progress-bar" class="bg-brand-gold h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Config Modal -->
    <div id="payment-config-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-brand-900/40" onclick="closePaymentConfigModal()"></div>
        <div
            class="bg-white w-full max-w-lg rounded-[40px] shadow-2xl relative z-10 overflow-hidden animate-slide-up border border-white/20">
            <div class="bg-brand-900 p-8 flex justify-between items-center">
                <div>
                    <h3 id="payment-modal-title" class="text-2xl font-anek font-bold text-white">API কনফিগারেশন</h3>
                    <p class="text-brand-gold text-[10px] font-bold uppercase tracking-widest mt-1">পেমেন্ট গেটওয়ে
                        সেটিংস</p>
                </div>
                <button onclick="closePaymentConfigModal()" class="text-white/50 hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form id="payment-config-form" class="p-10 space-y-6" onsubmit="savePaymentConfig(event)">
                <input type="hidden" name="method_key" id="config_method_key">
                <div id="config-fields" class="space-y-6">
                    <!-- Dynamic fields based on method -->
                </div>
                <div class="pt-6 border-t border-gray-100 flex gap-4">
                    <button type="button" onclick="closePaymentConfigModal()"
                        class="flex-1 py-4 bg-gray-100 text-gray-500 font-anek font-bold rounded-2xl hover:bg-gray-200 transition-all">বাতিল</button>
                    <button type="submit"
                        class="flex-[2] py-4 bg-brand-900 text-white font-anek font-bold rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-xl shadow-brand-900/20">সেভ
                        করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Use a more robust way to get modal reference
        function getAddBookModal() {
            return document.getElementById('add-book-modal');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
            if (overlay) overlay.classList.toggle('hidden');
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            const targetTab = document.getElementById(`tab-${tabId}`);
            if (targetTab) targetTab.classList.remove('hidden');

            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
                link.classList.add('text-gray-400');
            });
            const activeLink = document.getElementById(`nav-${tabId}`);
            if (activeLink) {
                if (activeLink) activeLink.classList.add('active');
                if (activeLink) activeLink.classList.remove('text-gray-400');
            }

            // Update URL Hash to preserve tab on reload
            window.location.hash = tabId;

            if (window.innerWidth < 1024) toggleSidebar();
        }

        // On Page Load, check hash and switch tab
        document.addEventListener('DOMContentLoaded', () => {
            let hash = window.location.hash.substring(1);
            if (hash) {
                // Check if it's a subtab
                if (hash.startsWith('preorder-')) {
                    switchTab('preorders');
                    let subId = hash.replace('preorder-', '');
                    setTimeout(() => { switchPreorderSubTab(subId); }, 100);
                } else {
                    switchTab(hash);
                }
            }
        });

        function switchPreorderSubTab(subId) {
            document.querySelectorAll('.preorder-subcontent').forEach(c => c.classList.add('hidden'));
            document.getElementById(`preorder-subtab-${subId}`).classList.remove('hidden');

            // Nav Styling
            document.querySelectorAll('[id^="subnav-"]').forEach(btn => {
                btn.classList.remove('border-brand-900', 'text-brand-900');
                btn.classList.add('border-transparent', 'text-gray-400');
            });
            const activeBtn = document.getElementById(`subnav-${subId}`);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-gray-400');
                activeBtn.classList.add('border-brand-900', 'text-brand-900');
            }
            window.location.hash = 'preorder-' + subId;
        }

        function openAddBookModal() {
            const modal = getAddBookModal();
            if (modal) {
                document.getElementById('modal-title').innerText = "নতুন বই যোগ করুন (বিস্তারিত)";
                document.getElementById('modal-submit-btn').innerText = "ইনভেন্টরিতে সেভ করুন";
                document.getElementById('add-book-form').reset();
                document.getElementById('book_id').value = "";
                document.querySelectorAll('[id$="-preview"]').forEach(p => p.classList.add('hidden'));
                document.getElementById('new_category_div').classList.add('hidden');

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function editBook(book) {
            const modal = getAddBookModal();
            if (modal) {
                document.getElementById('modal-title').innerText = "বইয়ের তথ্য আপডেট করুন";
                document.getElementById('modal-submit-btn').innerText = "আপডেট করুন";
                document.getElementById('add-book-form').reset();

                // Populate Fields
                document.getElementById('book_id').value = book.id;
                document.querySelector('[name="title"]').value = book.title;
                document.querySelector('[name="subtitle"]').value = book.subtitle || "";
                document.querySelector('[name="description"]').value = book.description || "";
                document.querySelector('[name="category_id"]').value = book.category_id;
                document.querySelector('[name="genre"]').value = book.genre || "";
                document.querySelector('[name="language"]').value = book.language || "";
                document.querySelector('[name="author"]').value = book.author;
                document.querySelector('[name="co_author"]').value = book.co_author || "";
                document.querySelector('[name="publisher"]').value = book.publisher || "";
                document.querySelector('[name="publish_year"]').value = book.publish_year || "";
                document.querySelector('[name="edition"]').value = book.edition || "";
                document.querySelector('[name="isbn"]').value = book.isbn || "";
                document.querySelector('[name="format"]').value = book.format;
                document.querySelector('[name="page_count"]').value = book.page_count;
                document.querySelector('[name="book_condition"]').value = book.book_condition;
                document.querySelector('[name="shelf_location"]').value = book.shelf_location || "";
                document.querySelector('[name="rack_number"]').value = book.rack_number || "";
                document.querySelector('[name="stock_qty"]').value = book.stock_qty;
                document.querySelector('[name="min_stock_level"]').value = book.min_stock_level;
                document.querySelector('[name="is_borrowable"]').checked = parseInt(book.is_borrowable);
                document.querySelector('[name="is_suggested"]').checked = parseInt(book.is_suggested);
                document.querySelector('[name="purchase_price"]').value = book.purchase_price;
                document.querySelector('[name="sell_price"]').value = book.sell_price;
                document.querySelector('[name="supplier_name"]').value = book.supplier_name || "";
                document.querySelector('[name="supplier_contact"]').value = book.supplier_contact || "";

                // Image Previews
                if (book.cover_image) {
                    const cp = document.getElementById('cover-preview');
                    cp.querySelector('img').src = '../../admin/assets/book-images/' + book.cover_image;
                    cp.classList.remove('hidden');
                }
                if (book.photo_2) {
                    const p2 = document.getElementById('photo2-preview');
                    p2.querySelector('img').src = '../../admin/assets/book-images/' + book.photo_2;
                    p2.classList.remove('hidden');
                }
                if (book.photo_3) {
                    const p3 = document.getElementById('photo3-preview');
                    p3.querySelector('img').src = '../../admin/assets/book-images/' + book.photo_3;
                    p3.classList.remove('hidden');
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAddBookModal() {
            const modal = getAddBookModal();
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toast-msg');
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 3000);
            }
        }

        function toggleNewCategoryInput() {
            const select = document.getElementById('category_select');
            const newCatDiv = document.getElementById('new_category_div');
            if (select.value === 'new') {
                newCatDiv.classList.remove('hidden');
                newCatDiv.querySelector('input').setAttribute('required', 'required');
            } else {
                newCatDiv.classList.add('hidden');
                newCatDiv.querySelector('input').removeAttribute('required');
            }
        }

        function updateProgress(percent, title, status) {
            const modal = document.getElementById('upload-progress-modal');
            const bar = document.getElementById('progress-bar');
            const circle = document.getElementById('progress-circle');
            const percentText = document.getElementById('progress-percent');
            const titleText = document.getElementById('progress-title');
            const statusText = document.getElementById('progress-status');

            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            const offset = 364.4 - (percent / 100 * 364.4);
            circle.style.strokeDashoffset = offset;
            bar.style.width = percent + '%';
            percentText.innerText = bn_num(Math.round(percent)) + '%';
            if (title) titleText.innerText = title;
            if (status) statusText.innerText = status;
        }

        function hideProgress() {
            const modal = document.getElementById('upload-progress-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reset for next time
            updateProgress(0);
        }

        async function compressImage(file, { maxWidth = 1200, maxHeight = 1200, quality = 0.8, maxSizeBytes = 150 * 1024 } = {}) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxWidth) {
                                height *= maxWidth / width;
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width *= maxHeight / height;
                                height = maxHeight;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        let currentQuality = quality;
                        
                        const attemptCompression = (q) => {
                            canvas.toBlob((blob) => {
                                if (blob.size <= maxSizeBytes || q <= 0.1) {
                                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".webp", {
                                        type: 'image/webp',
                                        lastModified: Date.now()
                                    });
                                    resolve(compressedFile);
                                } else {
                                    attemptCompression(q - 0.1);
                                }
                            }, 'image/webp', q);
                        };

                        attemptCompression(currentQuality);
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        async function handleAddBook(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            updateProgress(10, "প্রসেসিং হচ্ছে", "ছবিগুলো অপ্টিমাইজ করা হচ্ছে...");

            try {
                // Compress Images
                const filesToCompress = ['cover_image', 'photo_2', 'photo_3'];
                let progress = 10;
                const step = 20;

                for (const key of filesToCompress) {
                    const file = form.querySelector(`input[name="${key}"]`).files[0];
                    if (file) {
                        updateProgress(progress, "ছবি কম্প্রেস করা হচ্ছে", `${key === 'cover_image' ? 'কভার' : (key === 'photo_2' ? 'দ্বিতীয়' : 'তৃতীয়')} ছবি প্রসেস হচ্ছে...`);
                        const compressed = await compressImage(file);
                        formData.set(key, compressed);
                    }
                    progress += step;
                }

                updateProgress(70, "আপলোড হচ্ছে", "তথ্যগুলো ইনভেন্টরিতে সেভ করা হচ্ছে...");

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'process_add_book.php', true);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const uploadPercent = 70 + (e.loaded / e.total * 25);
                        updateProgress(uploadPercent);
                    }
                };

                xhr.onload = function() {
                    hideProgress();
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast(data.message || "নতুন বই সফলভাবে ইনভেন্টরিতে যোগ করা হয়েছে।");
                        closeAddBookModal();
                        form.reset();
                        document.querySelectorAll('[id$="-preview"]').forEach(p => p.classList.add('hidden'));
                        document.getElementById('new_category_div').classList.add('hidden');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                };

                xhr.onerror = function() {
                    hideProgress();
                    showToast("বই যোগ করতে সমস্যা হয়েছে।");
                };

                xhr.send(formData);

            } catch (err) {
                console.error(err);
                hideProgress();
                showToast("ছবি প্রসেস করতে সমস্যা হয়েছে।");
            }
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        function viewOrderDetails(order, items) {
            const modal = document.getElementById('order-details-modal');
            const content = document.getElementById('order-details-content');

            let itemsHtml = '';
            items.forEach(item => {
                itemsHtml += `
                    <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                        <div class="w-10 h-14 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                            <img src="${item.cover_image ? (item.cover_image.startsWith('http') ? item.cover_image : (item.preorder_id ? '../../assets/img/preorders/' + item.cover_image.trim() : '../../admin/assets/book-images/' + item.cover_image.trim())) : 'https://via.placeholder.com/100x140?text=Book'}" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/100x140?text=Book'">
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-brand-900 leading-tight text-sm">${item.title}</p>
                            <p class="text-[10px] text-gray-400">${bn_num(item.quantity)}টি x ৳${bn_num(item.unit_price)}</p>
                        </div>
                        <p class="font-bold text-brand-900">৳${bn_num(item.total_price)}</p>
                    </div>
                `;
            });

            content.innerHTML = `
                <div class="space-y-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">অর্ডার আইডি</p>
                            <h4 class="text-xl font-bold text-brand-900">#${order.invoice_no}</h4>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">অর্ডার তারিখ</p>
                            <p class="font-bold text-brand-900">${new Date(order.order_date).toLocaleDateString('bn-BD')}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl flex flex-wrap gap-8 border border-gray-100">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 text-gray-400">ক্রেতার নাম</p>
                            <p class="text-sm font-bold text-brand-900">${order.full_name}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 text-gray-400">ফোন নম্বর</p>
                            <p class="text-sm font-bold text-brand-900 tracking-wider">${order.phone}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 text-gray-400">ইমেইল</p>
                            <p class="text-sm font-bold text-brand-900">${order.email || '(Guest Order)'}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">শিপিং অ্যাড্রেস</p>
                            <p class="text-sm text-brand-900 leading-relaxed italic">"${order.shipping_address}"</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">পেমেন্ট মেথড</p>
                            <div class="bg-white px-4 py-2 rounded-lg inline-block border border-gray-100">
                                <span class="text-xs font-bold text-brand-900">${order.payment_method}</span>
                                <span class="mx-2 text-gray-200">|</span>
                                <span class="text-xs font-bold ${order.payment_status === 'Paid' ? 'text-green-600' : 'text-orange-600'}">${order.payment_status === 'Paid' ? 'Paid' : 'Pending'}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">অর্ডার আইটেম</p>
                        <div class="space-y-1">${itemsHtml}</div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">সাবটোটাল</span>
                            <span class="font-bold text-brand-900">৳${bn_num(order.subtotal)}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">শিপিং চার্জ</span>
                            <span class="font-bold text-brand-900">৳${bn_num(order.shipping_cost)}</span>
                        </div>
                        <div class="flex justify-between text-lg mt-4 pt-4 border-t border-dashed border-gray-200">
                            <span class="font-anek font-bold text-brand-900">সর্বমোট</span>
                            <span class="font-anek font-extrabold text-brand-gold">৳${bn_num(order.total_amount)}</span>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function viewPreOrderDetails(booking) {
            const modal = document.getElementById('order-details-modal');
            const content = document.getElementById('order-details-content');

            content.innerHTML = `
                <div class="space-y-6">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <div class="flex items-center gap-3">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">ইনভয়েস নং</p>
                                ${parseInt(booking.is_hot_deal || 0) === 1
                    ? '<span class="bg-orange-100 text-orange-600 px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-tighter">🔥 Hot Deal</span>'
                    : '<span class="bg-brand-gold/10 text-brand-900 px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-tighter">Pre-Order</span>'}
                            </div>
                            <h4 class="text-xl font-bold text-brand-900">#${booking.invoice_no}</h4>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">অর্ডার তারিখ</p>
                            <p class="font-bold text-brand-900">${new Date(booking.order_date).toLocaleDateString('bn-BD')}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-brand-light/20 p-6 rounded-3xl border border-brand-gold/10">
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">ক্রেতার তথ্য</p>
                                <p class="text-sm font-bold text-brand-900">${booking.full_name}</p>
                                <p class="text-xs text-gray-500">${booking.phone}</p>
                                <p class="text-xs text-gray-500">${booking.email ? booking.email : '(Guest Order)'}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">ডেলিভারি ঠিকানা</p>
                                <p class="text-sm text-brand-900 leading-relaxed">${booking.shipping_address || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">বইয়ের তথ্য</p>
                                <p class="text-sm font-bold text-brand-900">${booking.po_title}</p>
                                <p class="text-xs text-gray-500">পরিমাণ: ${bn_num(booking.quantity)}টি</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">ট্রানজেকশন আইডি</p>
                                <p class="text-sm font-bold text-[#D12053] tracking-widest">${booking.trx_id || 'N/A'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">বর্তমান স্ট্যাটাস</p>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                ${booking.order_status}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">মোট পরিশোধযোগ্য</p>
                            <p class="text-2xl font-anek font-extrabold text-brand-900">৳${bn_num(Math.round(booking.total_amount))}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-6">
                        ${booking.order_status === 'Processing' ? `
                            <button onclick="updateOrderStatus(${booking.order_id}, 'Confirmed')" 
                                class="py-4 bg-brand-gold text-brand-900 font-bold rounded-2xl hover:bg-brand-900 hover:text-white transition-all">
                                কনফার্ম করুন
                            </button>
                        ` : ''}
                        ${['Processing', 'Confirmed'].includes(booking.order_status) ? `
                            <button onclick="updateOrderStatus(${booking.order_id}, 'Delivered')" 
                                class="py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition-all ${booking.order_status === 'Processing' ? 'col-span-1' : 'col-span-2'}">
                                ডেলিভারড মার্ক করুন
                            </button>
                        ` : ''}
                        
                        <button onclick="updateOrderStatus(${booking.order_id}, 'Cancelled')" 
                            class="py-4 bg-red-50 text-red-600 font-bold rounded-2xl hover:bg-red-600 hover:text-white transition-all col-span-2 mt-2">
                            অর্ডার বাতিল করুন
                        </button>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeOrderDetailsModal() {
            const modal = document.getElementById('order-details-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function updateOrderStatus(orderId, status) {
            if (!confirm(`আপনি কি এই অর্ডারের স্ট্যাটাস '${status}' করতে চান?`)) return;

            fetch('update_order_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `order_id=${orderId}&status=${status}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("অর্ডার স্ট্যাটাস আপডেট করা হয়েছে।");
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("আপডেট করতে সমস্যা হয়েছে।");
                });
        }

        function updateDeliveryCharges(e) {
            e.preventDefault();
            const inside = document.getElementById('charge_inside').value;
            const outside = document.getElementById('charge_outside').value;
            
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = "আপডেট হচ্ছে...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'update_charges');
            formData.append('inside', inside);
            formData.append('outside', outside);

            fetch('process_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                } else {
                    showToast("ত্রুটি: " + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                showToast("চার্জ আপডেট করতে সমস্যা হয়েছে।");
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }

        function deleteBook(bookId) {
            if (!confirm('আপনি কি নিশ্চিত যে এই বইটি ডিলিট করতে চান? এটি ইনভেন্টরি থেকে স্থায়ীভাবে মুছে যাবে।')) return;

            fetch('delete_book.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `book_id=${bookId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("বইটি সফলভাবে মুছে ফেলা হয়েছে।");
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("মুছে ফেলতে সমস্যা হয়েছে।");
                });
        }

        function returnBook(borrowId) {
            if (!confirm('আপনি কি নিশ্চিত যে বইটি ফেরত নিয়েছেন? এটি ইনভেন্টরি আপডেট করবে।')) return;

            fetch('return_book.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `borrow_id=${borrowId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('বই ফেরত নেওয়া হয়েছে এবং ইনভেন্টরি আপডেট হয়েছে।');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('ত্রুটি: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('ফেরত নিতে সমস্যা হয়েছে।');
                });
        }

        function openPreorderModal() {
            const modal = document.getElementById('preorder-modal');
            document.getElementById('po-modal-title').innerText = "নতুন প্রি-অর্ডার";
            document.getElementById('preorder-form').reset();
            document.getElementById('po_id').value = "";
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closePreorderModal() {
            const modal = document.getElementById('preorder-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function editPreorder(po) {
            openPreorderModal();
            document.getElementById('po-modal-title').innerText = "প্রি-অর্ডার আপডেট করুন";
            document.getElementById('po_id').value = po.id;
            const form = document.getElementById('preorder-form');
            form.title.value = po.title;
            form.sub_title.value = po.sub_title || "";
            form.author.value = po.author;
            form.title_en.value = po.title_en || "";
            form.author_en.value = po.author_en || "";
            form.description.value = po.description || "";
            form.second_title.value = po.second_title || "";
            form.description_2.value = po.description_2 || "";
            form.price.value = po.price;
            form.discount_price.value = po.discount_price || "";
            form.release_date.value = po.release_date;
            form.status.value = po.status;
            form.is_hot_deal.checked = parseInt(po.is_hot_deal) === 1;
            form.free_delivery.checked = parseInt(po.free_delivery) === 1;

            // Image Preview
            const preview = document.getElementById('po-cover-preview');
            const img = preview.querySelector('img');
            if (po.cover_image) {
                img.src = po.cover_image.startsWith('http') ? po.cover_image : '../../assets/img/preorders/' + po.cover_image.trim();
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }

            // Second Cover Image Preview
            const secondPreview = document.getElementById('po-second-cover-preview');
            const secondImg = secondPreview.querySelector('img');
            if (po.second_cover_image) {
                secondImg.src = po.second_cover_image.startsWith('http') ? po.second_cover_image : '../../assets/img/preorders/' + po.second_cover_image.trim();
                secondPreview.classList.remove('hidden');
            } else {
                secondPreview.classList.add('hidden');
            }
        }

        async function handlePreorder(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            updateProgress(10, "প্রসেসিং হচ্ছে", "ছবিগুলো অপ্টিমাইজ করা হচ্ছে...");

            try {
                // Compress Images
                const filesToCompress = ['cover_image', 'second_cover_image'];
                let progress = 10;
                const step = 30;

                for (const key of filesToCompress) {
                    const fileInput = form.querySelector(`input[name="${key}"]`);
                    if (fileInput && fileInput.files[0]) {
                        updateProgress(progress, "ছবি কম্প্রেস করা হচ্ছে", `${key === 'cover_image' ? 'প্রথম' : 'দ্বিতীয়'} ছবি প্রসেস হচ্ছে...`);
                        const compressed = await compressImage(fileInput.files[0]);
                        formData.set(key, compressed);
                    }
                    progress += step;
                }

                updateProgress(70, "আপলোড হচ্ছে", "প্রি-অর্ডার তথ্য সেভ করা হচ্ছে...");

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'process_preorder.php', true);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const uploadPercent = 70 + (e.loaded / e.total * 25);
                        updateProgress(uploadPercent);
                    }
                };

                xhr.onload = function() {
                    hideProgress();
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast(data.message);
                        closePreorderModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                };

                xhr.onerror = function() {
                    hideProgress();
                    showToast("সংরক্ষণ করতে সমস্যা হয়েছে।");
                };

                xhr.send(formData);

            } catch (err) {
                console.error(err);
                hideProgress();
                showToast("ছবি প্রসেস করতে সমস্যা হয়েছে।");
            }
        }

        function deletePreorder(id) {
            if (!confirm('আপনি কি নিশ্চিত যে এই প্রি-অর্ডারটি ডিলিট করতে চান?')) return;

            fetch('delete_preorder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("মুছে ফেলতে সমস্যা হয়েছে।");
                });
        }

        function bn_num(num) {
            if (!num) return '০';
            const bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            return num.toString().replace(/[0-9]/g, w => bn_digits[+w]);
        }
        function filterOrders() {
            const input = document.getElementById('orderSearchInput');
            const filter = input.value.toLowerCase();
            const tbody = document.querySelector('#tab-orders tbody');
            const tr = tbody.getElementsByTagName('tr');

            for (let i = 0; i < tr.length; i++) {
                const id = tr[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const name = tr[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const books = tr[i].getElementsByTagName('td')[2].textContent.toLowerCase();

                if (id.indexOf(filter) > -1 || name.indexOf(filter) > -1 || books.indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
        function togglePaymentMethod(methodKey, isActive) {
            fetch('update_payment_method.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `method_key=${methodKey}&is_active=${isActive ? 1 : 0}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(`${methodKey.toUpperCase()} স্ট্যাটাস আপডেট করা হয়েছে।`);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("আপডেট করতে সমস্যা হয়েছে।");
                });
        }

        function openPaymentConfigModal(methodKey, config) {
            const modal = document.getElementById('payment-config-modal');
            const fieldsContainer = document.getElementById('config-fields');
            document.getElementById('config_method_key').value = methodKey;
            document.getElementById('payment-modal-title').innerText = `${methodKey.toUpperCase()} কনফিগারেশন`;

            let fieldsHtml = '';
            if (methodKey === 'bkash') {
                fieldsHtml = `
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">App Key</label>
                        <input type="text" name="app_key" value="${config.app_key || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">App Secret</label>
                        <input type="password" name="app_secret" value="${config.app_secret || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Username</label>
                        <input type="text" name="username" value="${config.username || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Password</label>
                        <input type="password" name="password" value="${config.password || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Base URL</label>
                        <select name="base_url" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                            <option value="https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout" ${config.base_url?.includes('sandbox') ? 'selected' : ''}>Sandbox (Test)</option>
                            <option value="https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout" ${!config.base_url?.includes('sandbox') ? 'selected' : ''}>Live (Production)</option>
                        </select>
                    </div>
                `;
            } else if (methodKey === 'nagad') {
                fieldsHtml = `
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Merchant ID</label>
                        <input type="text" name="merchant_id" value="${config.merchant_id || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-2">Merchant Phone</label>
                        <input type="text" name="merchant_phone" value="${config.merchant_phone || ''}" class="w-full bg-gray-50 border border-transparent rounded-2xl px-6 py-4 focus:ring-2 focus:ring-brand-gold outline-none transition-all font-anek">
                    </div>
                `;
            }

            fieldsContainer.innerHTML = fieldsHtml;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closePaymentConfigModal() {
            const modal = document.getElementById('payment-config-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function savePaymentConfig(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch('update_payment_method.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("কনফিগারেশন সেভ করা হয়েছে।");
                        closePaymentConfigModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("সেভ করতে সমস্যা হয়েছে।");
                });
        }

        function updatePaymentStatus(orderId, status) {
            if (!confirm(`পেমেন্ট স্ট্যাটাস '${status}' হিসেবে আপডেট করতে চান?`)) return;

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('update_payment_status.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(`পেমেন্ট স্ট্যাটাস '${status}' এ আপডেট হয়েছে।`);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast("ত্রুটি: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("আপডেট করতে সমস্যা হয়েছে।");
                });
        }

        function toggleActionMenu(menuId, event) {
            event.stopPropagation();
            const btn = event.currentTarget;
            const targetMenu = document.getElementById(menuId);
            const allMenus = document.querySelectorAll('.action-menu');

            let isOpening = targetMenu.classList.contains('hidden');

            allMenus.forEach(menu => {
                menu.classList.add('hidden');
                menu.style.opacity = '0';
            });

            if (isOpening) {
                // Move menu to body to avoid being cut off by overflow-hidden tables
                document.body.appendChild(targetMenu);

                const rect = btn.getBoundingClientRect();
                targetMenu.style.position = 'fixed';

                // Responsive width handling
                const menuWidth = 160; // w-40
                let left = rect.right - menuWidth;

                // Ensure it doesn't go off-screen left
                if (left < 10) left = 10;

                // Ensure it doesn't go off-screen right
                if (left + menuWidth > window.innerWidth) {
                    left = window.innerWidth - menuWidth - 10;
                }

                targetMenu.style.left = left + 'px';

                const windowHeight = window.innerHeight;
                const menuHeight = 150; // approximate
                if (rect.bottom + menuHeight > windowHeight) {
                    targetMenu.style.top = 'auto';
                    targetMenu.style.bottom = (windowHeight - rect.top + 8) + 'px';
                } else {
                    targetMenu.style.bottom = 'auto';
                    targetMenu.style.top = (rect.bottom + 8) + 'px';
                }

                targetMenu.classList.remove('hidden');

                // Trigger reflow for fade-in effect
                void targetMenu.offsetWidth;
                targetMenu.style.opacity = '1';
                targetMenu.style.zIndex = '99999';
            }
        }

        // Close action menus when clicking outside
        document.addEventListener('click', (event) => {
            const allMenus = document.querySelectorAll('.action-menu');
            allMenus.forEach(menu => {
                if (!menu.contains(event.target) && !event.target.closest('button[onclick*="toggleActionMenu"]')) {
                    menu.classList.add('hidden');
                    menu.style.opacity = '0';
                }
            });
        });

        // Hide menus on scroll for safe positioning
        window.addEventListener('scroll', () => {
            const allMenus = document.querySelectorAll('.action-menu:not(.hidden)');
            allMenus.forEach(menu => {
                menu.classList.add('hidden');
                menu.style.opacity = '0';
            });
        }, { passive: true });
    </script>
</body>

</html>