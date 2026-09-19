<?php
// admin/dashboard/student_memberships.php
// Dedicated Management Page for "বইয়ের আনন্দ-পাঠ" Student Memberships

require_once __DIR__ . '/../../includes/db_connect.php';

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Counts for Badges & Summary
$pending_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Pending'")->fetchColumn();
$approved_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Approved'")->fetchColumn();
$rejected_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Rejected'")->fetchColumn();
$total_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests")->fetchColumn();
$pending_membership_requests = (int)$pdo->query("SELECT COUNT(*) FROM membership_requests WHERE status = 'Pending'")->fetchColumn();

// Fetch all student membership requests with member details
$stmt = $pdo->query("
    SELECT sr.*, m.full_name, m.email, m.phone, m.membership_id, m.membership_plan, m.plan_expire_date, m.created_at as member_join_date
    FROM student_membership_requests sr
    JOIN members m ON sr.member_id = m.id
    ORDER BY 
        CASE sr.status 
            WHEN 'Pending' THEN 1 
            WHEN 'Approved' THEN 2 
            WHEN 'Rejected' THEN 3 
            ELSE 4 
        END,
        sr.created_at DESC
");
$student_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function bn_num($number) {
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($en, $bn, (string)$number);
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্টুডেন্ট মেম্বারশিপ ম্যানেজমেন্ট | অন্ত্যমিল অ্যাডমিন</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .sidebar-link.active {
            background: #cda873;
            color: #0a0a0a;
        }
    </style>
</head>

<body class="bg-gray-50 font-anek text-gray-800 antialiased">
    <!-- Sidebar Navigation -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 w-72 bg-brand-900 z-50 transform -translate-x-full transition-transform lg:translate-x-0 overflow-y-auto">
        <div class="p-8 border-b border-white/5">
            <a href="../../index.php" class="flex items-center gap-2 group">
                <img src="../../assets/img/logo.webp" alt="logo" class="w-10 h-auto">
                <span class="font-serif text-2xl font-bold tracking-wide text-white mt-1 uppercase">অ্যাডমিন<span class="text-brand-gold">.</span></span>
            </a>
        </div>

        <nav class="px-6 py-10 space-y-2">
            <a href="index.php" class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                ওভারভিউ
            </a>
            <a href="index.php" class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                অর্ডারসমূহ
            </a>
            <a href="index.php" class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                ইনভেন্টরি
            </a>
            <a href="index.php" class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                মেম্বার লিস্ট
            </a>
            
            <!-- Student Memberships (Active Dedicated Tab) -->
            <a href="student_memberships.php" class="sidebar-link active w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
                <span>স্টুডেন্ট মেম্বারশিপ</span>
                <?php if ($pending_student_requests > 0): ?>
                    <span class="ml-auto px-2 py-0.5 bg-brand-gold text-brand-900 rounded-full text-xs font-bold font-mono">
                        <?php echo $pending_student_requests; ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="index.php" class="sidebar-link text-gray-400 hover:text-white w-full flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>মেম্বারশিপ ম্যানেজমেন্ট</span>
                <?php if ($pending_membership_requests > 0): ?>
                    <span class="ml-auto px-2 py-0.5 bg-brand-gold text-brand-900 rounded-full text-xs font-bold font-mono"><?php echo $pending_membership_requests; ?></span>
                <?php endif; ?>
            </a>

            <a href="../logout.php" class="sidebar-link text-red-400 hover:text-white hover:bg-red-500/20 flex items-center gap-4 px-5 py-4 rounded-xl font-anek font-bold transition-all duration-300 mt-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                লগআউট
            </a>
        </nav>
    </aside>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden"></div>

    <!-- Main Content Area -->
    <main class="lg:ml-72 min-h-screen">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-30 px-6 sm:px-10 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden text-brand-900 p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-anek text-brand-900">
                        স্টুডেন্ট মেম্বারশিপ ম্যানেজমেন্ট
                    </h1>
                    <p class="text-xs text-gray-500 font-anek">‘বইয়ের আনন্দ-পাঠ’ পাঠ-উদ্যোগের আবেদন ও ভেরিফিকেশন</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-brand-900 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-1.5">
                    <span>← মূল ড্যাশবোর্ড</span>
                </a>
            </div>
        </header>

        <div class="p-6 sm:p-10 space-y-8 max-w-7xl mx-auto">
            
            <!-- Toast Notification Container -->
            <div id="toastContainer" class="fixed top-20 right-6 z-50 space-y-3"></div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Applications -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase text-gray-400">সর্বমোট আবেদন</span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            🎓
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-brand-900 font-mono" id="stat-total">
                        <?php echo bn_num($total_student_requests); ?>
                    </div>
                    <span class="text-xs text-gray-400 mt-1 block">বইয়ের আনন্দ-পাঠ পাঠক</span>
                </div>

                <!-- Pending Review -->
                <div class="bg-white p-6 rounded-2xl border border-amber-200/80 shadow-sm hover:shadow-md transition-shadow bg-amber-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase text-amber-700">অপেক্ষমান (Pending)</span>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                            ⏳
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-amber-600 font-mono" id="stat-pending">
                        <?php echo bn_num($pending_student_requests); ?>
                    </div>
                    <span class="text-xs text-amber-700 font-medium mt-1 block">যাচাই ও অনুমোদন প্রয়োজন</span>
                </div>

                <!-- Approved Students -->
                <div class="bg-white p-6 rounded-2xl border border-emerald-200/80 shadow-sm hover:shadow-md transition-shadow bg-emerald-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase text-emerald-700">অনুমোদিত (Approved)</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                            ✓
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-emerald-600 font-mono" id="stat-approved">
                        <?php echo bn_num($approved_student_requests); ?>
                    </div>
                    <span class="text-xs text-emerald-700 font-medium mt-1 block">সক্রিয় স্টুডেন্ট মেম্বার</span>
                </div>

                <!-- Rejected -->
                <div class="bg-white p-6 rounded-2xl border border-red-200/80 shadow-sm hover:shadow-md transition-shadow bg-red-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase text-red-700">বাতিলকৃত (Rejected)</span>
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-bold text-lg">
                            ✕
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-red-600 font-mono" id="stat-rejected">
                        <?php echo bn_num($rejected_student_requests); ?>
                    </div>
                    <span class="text-xs text-red-700 font-medium mt-1 block">অনুপযুক্ত বা বাতিল আবেদন</span>
                </div>
            </div>

            <!-- Management Table Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-xl overflow-hidden">
                
                <!-- Table Controls Header -->
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                    
                    <!-- Filter Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
                        <button onclick="filterStatus('all')" id="btn-filter-all" class="status-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-brand-900 text-white shadow-sm">
                            সকল আবেদন (<?php echo bn_num($total_student_requests); ?>)
                        </button>
                        <button onclick="filterStatus('Pending')" id="btn-filter-Pending" class="status-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
                            পেন্ডিং (<?php echo bn_num($pending_student_requests); ?>)
                        </button>
                        <button onclick="filterStatus('Approved')" id="btn-filter-Approved" class="status-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
                            অনুমোদিত (<?php echo bn_num($approved_student_requests); ?>)
                        </button>
                        <button onclick="filterStatus('Rejected')" id="btn-filter-Rejected" class="status-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
                            বাতিল (<?php echo bn_num($rejected_student_requests); ?>)
                        </button>
                    </div>

                    <!-- Search Box -->
                    <div class="w-full md:w-80 relative">
                        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="নাম, ফোন, প্রতিষ্ঠান বা আইডি খুঁজুন..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-brand-gold focus:border-brand-gold transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="studentReqTable">
                        <thead>
                            <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-4 px-6">শিক্ষার্থী ও অ্যাকাউন্ট</th>
                                <th class="py-4 px-6">শিক্ষা প্রতিষ্ঠান ও স্টুডেন্ট আইডি</th>
                                <th class="py-4 px-6 text-center">জন্ম তারিখ ও বয়স</th>
                                <th class="py-4 px-6 text-center">আইডি কার্ড ডকুমেন্ট</th>
                                <th class="py-4 px-6">আবেদনের সময়</th>
                                <th class="py-4 px-6 text-center">স্ট্যাটাস</th>
                                <th class="py-4 px-6 text-right">পদক্ষেপ (Action)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs sm:text-sm" id="tableBody">
                            <?php if (empty($student_requests)): ?>
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-gray-400 font-medium">
                                        কোনো স্টুডেন্ট মেম্বারশিপ আবেদন পাওয়া যায়নি।
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($student_requests as $req): 
                                    $is_age_valid = ($req['age'] >= 12 && $req['age'] <= 19);
                                ?>
                                    <tr class="req-row hover:bg-gray-50/60 transition-colors" 
                                        id="req-row-<?php echo $req['id']; ?>"
                                        data-status="<?php echo $req['status']; ?>"
                                        data-search="<?php echo htmlspecialchars(strtolower($req['full_name'] . ' ' . $req['phone'] . ' ' . $req['email'] . ' ' . $req['institution_name'] . ' ' . $req['student_id_number'] . ' ' . $req['membership_id'])); ?>">
                                        
                                        <!-- Member Info -->
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-brand-900 text-sm">
                                                <?php echo htmlspecialchars($req['full_name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                ID: <span class="font-bold text-brand-900"><?php echo htmlspecialchars($req['membership_id']); ?></span>
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                📞 <?php echo htmlspecialchars($req['phone'] ?: 'N/A'); ?>
                                            </div>
                                            <?php if (!empty($req['email'])): ?>
                                                <div class="text-xs text-gray-400 truncate max-w-[180px]">
                                                    ✉️ <?php echo htmlspecialchars($req['email']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Institution & Student ID -->
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-brand-900">
                                                🏫 <?php echo htmlspecialchars($req['institution_name']); ?>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-600">
                                                আইডি নম্বর: <span class="font-mono font-bold text-brand-900 bg-gray-100 px-2 py-0.5 rounded"><?php echo htmlspecialchars($req['student_id_number']); ?></span>
                                            </div>
                                        </td>

                                        <!-- DOB & Age -->
                                        <td class="py-4 px-6 text-center">
                                            <div class="font-mono text-xs text-gray-600">
                                                <?php echo date('d M, Y', strtotime($req['dob'])); ?>
                                            </div>
                                            <div class="mt-1">
                                                <?php if ($is_age_valid): ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                                                        <span>✓</span> <?php echo $req['age']; ?> বছর
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded-full text-xs font-bold">
                                                        <span>⚠️</span> <?php echo $req['age']; ?> বছর (অনুপযুক্ত)
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- ID Card Photo Modal Trigger -->
                                        <td class="py-4 px-6 text-center">
                                            <?php if (!empty($req['student_id_image'])): 
                                                $img_path = '../../' . $req['student_id_image'];
                                                $is_pdf = (strtolower(pathinfo($req['student_id_image'], PATHINFO_EXTENSION)) === 'pdf');
                                            ?>
                                                <?php if ($is_pdf): ?>
                                                    <a href="<?php echo htmlspecialchars($img_path); ?>" target="_blank" 
                                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs font-bold hover:bg-red-100 transition-all shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span>PDF দেখুন</span>
                                                    </a>
                                                <?php else: ?>
                                                    <button onclick="openImageModal('<?php echo htmlspecialchars($img_path); ?>', '<?php echo htmlspecialchars(addslashes($req['full_name'])); ?>', '<?php echo htmlspecialchars(addslashes($req['institution_name'])); ?>', '<?php echo htmlspecialchars(addslashes($req['student_id_number'])); ?>')" 
                                                            class="group relative inline-block rounded-xl overflow-hidden border border-gray-200 shadow-sm hover:shadow-md hover:border-brand-gold transition-all">
                                                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Student ID" class="w-14 h-14 object-cover">
                                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                                            🔍
                                                        </div>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">ছবি নেই</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Applied Time -->
                                        <td class="py-4 px-6 text-xs text-gray-500">
                                            <div class="font-mono"><?php echo date('d M, Y', strtotime($req['created_at'])); ?></div>
                                            <div class="text-[11px] text-gray-400"><?php echo date('h:i A', strtotime($req['created_at'])); ?></div>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-4 px-6 text-center" id="status-cell-<?php echo $req['id']; ?>">
                                            <?php if ($req['status'] === 'Pending'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    পেন্ডিং
                                                </span>
                                            <?php elseif ($req['status'] === 'Approved'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    অনুমোদিত
                                                </span>
                                            <?php elseif ($req['status'] === 'Rejected'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold" title="<?php echo htmlspecialchars($req['rejection_reason'] ?? ''); ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    বাতিল
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-4 px-6 text-right" id="action-cell-<?php echo $req['id']; ?>">
                                            <?php if ($req['status'] === 'Pending'): ?>
                                                <div class="inline-flex items-center gap-2">
                                                    <button onclick="approveStudentReq(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars(addslashes($req['full_name'])); ?>')" 
                                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        <span>অনুমোদন</span>
                                                    </button>
                                                    
                                                    <button onclick="openRejectModal(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars(addslashes($req['full_name'])); ?>')" 
                                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-bold text-xs transition-all flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        <span>বাতিল</span>
                                                    </button>
                                                </div>
                                            <?php elseif ($req['status'] === 'Approved'): ?>
                                                <span class="text-xs text-emerald-600 font-bold">✓ মেম্বারশিপ সক্রিয়</span>
                                            <?php elseif ($req['status'] === 'Rejected'): ?>
                                                <button onclick="alert('বাতিলের কারণ: <?php echo htmlspecialchars(addslashes($req['rejection_reason'] ?? 'কোনো কারণ উল্লেখ নেই')); ?>')" class="text-xs text-gray-500 hover:underline">
                                                    কারণ দেখুন
                                                </button>
                                            <?php endif; ?>
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

    <!-- Image Preview Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative overflow-hidden animate-scale-up">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold transition-all">
                ✕
            </button>
            <div class="mb-4">
                <span class="text-xs font-bold uppercase text-brand-gold tracking-widest block">স্টুডেন্ট আইডি কার্ড ভেরিফিকেশন</span>
                <h3 id="modalStudentName" class="text-xl font-extrabold text-brand-900"></h3>
                <p id="modalStudentMeta" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <div class="max-h-[70vh] overflow-auto flex items-center justify-center bg-gray-100 rounded-2xl p-2 border border-gray-200">
                <img id="modalFullImage" src="#" alt="Student ID Document" class="max-h-[60vh] max-w-full rounded-xl object-contain shadow-md">
            </div>
            <div class="mt-4 flex justify-between items-center text-xs">
                <a id="modalDownloadLink" href="#" target="_blank" download class="font-bold text-brand-gold hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>মূল ছবি ডাউনলোড করুন</span>
                </a>
                <button onclick="closeImageModal()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">
                    বন্ধ করুন
                </button>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative animate-scale-up">
            <h3 class="text-lg font-bold text-brand-900 mb-1">আবেদন বাতিলের কারণ</h3>
            <p id="rejectStudentName" class="text-xs text-gray-500 mb-4"></p>
            
            <input type="hidden" id="rejectRequestId">
            <div class="mb-4">
                <label for="rejectReasonInput" class="block text-xs font-bold text-gray-700 mb-1.5">
                    বাতিলের সুনির্দিষ্ট কারণ (শিক্ষার্থীকে ইমেইলে জানানো হবে):
                </label>
                <textarea id="rejectReasonInput" rows="3" 
                          placeholder="উদাঃ স্টুডেন্ট আইডি কার্ড স্পষ্ট নয় অথবা বয়সসীমা ১২-১৯ বছরের বহির্ভূত।" 
                          class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-gold focus:border-brand-gold text-gray-800 transition-all"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button onclick="closeRejectModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-all">
                    ফিরে যান
                </button>
                <button onclick="confirmReject()" id="confirmRejectBtn" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                    বাতিল নিশ্চিত করুন
                </button>
            </div>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Filter by Status Tabs
    function filterStatus(status) {
        document.querySelectorAll('.status-filter-btn').forEach(btn => {
            btn.classList.remove('bg-brand-900', 'text-white', 'shadow-sm');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        });
        
        const activeBtn = document.getElementById('btn-filter-' + status);
        if (activeBtn) {
            activeBtn.classList.add('bg-brand-900', 'text-white', 'shadow-sm');
            activeBtn.classList.remove('bg-gray-100', 'text-gray-600');
        }

        const rows = document.querySelectorAll('.req-row');
        rows.forEach(row => {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Live Search
    function searchTable() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.req-row');
        rows.forEach(row => {
            const data = row.getAttribute('data-search') || '';
            if (data.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Modal Handlers
    function openImageModal(imgSrc, name, inst, idNum) {
        document.getElementById('modalFullImage').src = imgSrc;
        document.getElementById('modalDownloadLink').href = imgSrc;
        document.getElementById('modalStudentName').textContent = name;
        document.getElementById('modalStudentMeta').textContent = inst + ' • ID: ' + idNum;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    function openRejectModal(id, name) {
        document.getElementById('rejectRequestId').value = id;
        document.getElementById('rejectStudentName').textContent = 'শিক্ষার্থী: ' + name;
        document.getElementById('rejectReasonInput').value = '';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Toast Notifications
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const bg = type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white';
        toast.className = `${bg} px-5 py-3 rounded-2xl shadow-xl text-xs sm:text-sm font-bold flex items-center gap-2 transform transition-all duration-300 translate-y-2 opacity-0`;
        toast.innerHTML = `<span>${type === 'success' ? '✓' : '⚠️'}</span><span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 50);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Approve Student Action
    function approveStudentReq(id, name) {
        if (!confirm(`আপনি কি নিশ্চিত যে আপনি শিক্ষার্থী '${name}'-এর ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপ অনুমোদন করতে চান?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('request_id', id);
        formData.append('action', 'approve');

        fetch('update_student_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Update DOM elements
                const row = document.getElementById('req-row-' + id);
                if (row) {
                    row.setAttribute('data-status', 'Approved');
                    document.getElementById('status-cell-' + id).innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            অনুমোদিত
                        </span>
                    `;
                    document.getElementById('action-cell-' + id).innerHTML = `
                        <span class="text-xs text-emerald-600 font-bold">✓ মেম্বারশিপ সক্রিয়</span>
                    `;
                }
            } else {
                showToast(data.message || 'ত্রুটি ঘটেছে', 'error');
            }
        })
        .catch(err => {
            showToast('নেটওয়ার্ক ত্রুটি ঘটেছে', 'error');
        });
    }

    // Confirm Reject Action
    function confirmReject() {
        const id = document.getElementById('rejectRequestId').value;
        const reason = document.getElementById('rejectReasonInput').value.trim();
        const btn = document.getElementById('confirmRejectBtn');

        btn.disabled = true;
        btn.textContent = 'প্রসেসিং...';

        const formData = new FormData();
        formData.append('request_id', id);
        formData.append('action', 'reject');
        formData.append('reason', reason);

        fetch('update_student_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'বাতিল নিশ্চিত করুন';
            closeRejectModal();

            if (data.success) {
                showToast(data.message, 'success');
                const row = document.getElementById('req-row-' + id);
                if (row) {
                    row.setAttribute('data-status', 'Rejected');
                    document.getElementById('status-cell-' + id).innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            বাতিল
                        </span>
                    `;
                    document.getElementById('action-cell-' + id).innerHTML = `
                        <span class="text-xs text-gray-500">বাতিল করা হয়েছে</span>
                    `;
                }
            } else {
                showToast(data.message || 'ত্রুটি ঘটেছে', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'বাতিল নিশ্চিত করুন';
            showToast('নেটওয়ার্ক ত্রুটি ঘটেছে', 'error');
        });
    }
    </script>
</body>
</html>
