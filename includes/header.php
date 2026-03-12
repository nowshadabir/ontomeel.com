<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'অন্ত্যমিল | বই ও লাইব্রেরি'; ?></title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://images.unsplash.com">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="<?php echo $path_prefix ?? ''; ?>assets/js/tailwind-config.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo $path_prefix ?? ''; ?>assets/js/script.js?v=<?php echo time(); ?>" defer></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo $path_prefix ?? ''; ?>assets/css/style.css?v=<?php echo time(); ?>">

    <!-- favicon -->
    <link rel="icon" href="<?php echo $path_prefix ?? ''; ?>assets/img/logo.png">

    <?php
    $script_name = $_SERVER['SCRIPT_NAME'];
    $p_prefix = $path_prefix ?? '';
    $depth = substr_count($p_prefix, '../');
    $path_array = explode('/', trim($script_name, '/'));
    $parts_to_keep = count($path_array) - $depth - 1;
    $project_root = ($parts_to_keep > 0) ? '/' . implode('/', array_slice($path_array, 0, $parts_to_keep)) . '/' : '/';
    ?>
    <?php
    $m_plan = 'None';
    if (isset($_SESSION['user_id'])) {
        require_once $path_prefix . 'includes/db_connect.php';
        $m_stmt = $pdo->prepare("SELECT membership_plan FROM members WHERE id = ?");
        $m_stmt->execute([$_SESSION['user_id']]);
        $m_user = $m_stmt->fetch();
        if ($m_user) {
            $m_plan = $m_user['membership_plan'];
            $_SESSION['membership_plan'] = $m_plan;
        }
    }
    ?>
    <script>
        const PROJECT_ROOT = '<?php echo htmlspecialchars($project_root, ENT_QUOTES, 'UTF-8'); ?>';
        <?php if (isset($_SESSION['user_id'])): ?>
            const user_id = <?php echo (int) $_SESSION['user_id']; ?>;
            const membership_plan = '<?php echo htmlspecialchars($m_plan, ENT_QUOTES, 'UTF-8'); ?>';
            localStorage.setItem('membership_plan', membership_plan);
        <?php endif; ?>
    </script>

    <?php echo $additional_head ?? ''; ?>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white">

    <!-- Navigation -->
    <?php if (isset($is_checkout) && $is_checkout): ?>
        <nav class="bg-white border-b border-gray-100 py-6 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                <a href="<?php echo $path_prefix ?? ''; ?>index.php" class="flex items-center gap-3">
                    <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.png" alt="logo" class="w-10 h-auto">
                    <span class="font-serif text-2xl font-bold tracking-tight text-brand-900 mt-1 uppercase">ANTYAMIL<span
                            class="text-brand-gold">.</span></span>
                </a>
                <div class="flex items-center gap-4">
                    <span class="hidden md:block text-xs font-bold text-gray-400 uppercase tracking-widest">নিরাপদ
                        চেকআউট</span>
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </nav>
    <?php else: ?>
        <nav id="navbar" class="fixed w-full z-40 transition-all duration-300 py-4 <?php echo $nav_class ?? 'glass'; ?>">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <!-- Logo -->
                    <a href="<?php echo $path_prefix ?? ''; ?>index.php"
                        class="flex items-center gap-2 group flex-shrink-0">
                        <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.png" alt="logo of ontomeel"
                            class="w-12 h-auto">
                        <span
                            class="font-serif text-2xl font-bold tracking-wide <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-900'; ?> mt-1">অন্ত্যমিল<span
                                class="text-brand-gold">.</span></span>
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="<?php echo $path_prefix ?? ''; ?>category/index.php"
                            class="text-[15px] font-medium <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">ক্যাটাগরি</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>library/index.php"
                            class="text-[15px] font-medium <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">লাইব্রেরি</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>membership/index.php"
                            class="text-[15px] font-medium <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">মেম্বারশিপ</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>pre-booking/index.php"
                            class="text-[15px] font-medium <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-brand-gold border-b-2 border-brand-gold' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors font-bold">প্রি-বুকিং</a>
                    </div>

                    <!-- Search & Icons -->
                    <div class="flex items-center space-x-3 md:space-x-5 flex-1 justify-end">
                        <!-- Search Bar -->
                        <div class="relative w-full max-w-[200px] hidden sm:block">
                            <input type="text" id="searchInput" onkeyup="searchBooks(event)"
                                placeholder="বইয়ের নাম লিখুন..."
                                class="w-full bg-white/50 border border-gray-300 rounded-full pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-gold focus:bg-white transition-all font-sans">
                            <svg class="w-4 h-4 text-gray-500 absolute left-4 top-1/2 transform -translate-y-1/2"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Borrow Cart Icon -->
                        <button
                            class="relative <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors group p-2"
                            onclick="toggleBorrowCartDrawer()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            <span id="borrow-cart-count"
                                class="absolute top-0 right-0 bg-brand-900 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center transform group-hover:scale-110 transition-transform">0</span>
                        </button>

                        <!-- Purchase Cart Icon -->
                        <button
                            class="relative <?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors group p-2"
                            onclick="toggleCartDrawer()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span id="cart-count"
                                class="absolute top-0 right-0 bg-brand-gold text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center transform group-hover:scale-110 transition-transform">0</span>
                        </button>

                        <!-- Mobile Menu Button -->
                        <button
                            class="<?php echo ($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-900'; ?> md:hidden focus:outline-none p-2"
                            onclick="toggleMobileMenu()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Login/Dashboard Button -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo $path_prefix ?? ''; ?>dashboard/"
                                class="hidden md:flex items-center gap-2 px-6 py-2 bg-brand-gold text-brand-900 rounded-full font-anek font-bold transition-all hover:bg-white text-sm shadow-lg shadow-brand-gold/20">
                                ড্যাশবোর্ড
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $path_prefix ?? ''; ?>login/index.php"
                                class="hidden md:flex items-center gap-2 px-6 py-2 bg-brand-gold text-brand-900 rounded-full font-anek font-bold transition-all hover:bg-white text-sm shadow-lg shadow-brand-gold/20">
                                লগইন
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Overlay -->
            <div id="mobile-menu"
                class="fixed inset-0 bg-brand-light z-40 transform translate-x-full transition-transform duration-500 ease-in-out md:hidden flex flex-col pt-24 px-6">
                <button onclick="toggleMobileMenu()" class="absolute top-6 right-6 text-brand-900">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <div class="relative w-full mb-8">
                    <input type="text" id="mobileSearchInput" onkeyup="searchBooks(event, true)" placeholder="বই খুঁজুন..."
                        class="w-full bg-white border border-gray-300 rounded-full pl-10 pr-4 py-3 focus:outline-none focus:border-brand-gold font-sans">
                    <svg class="w-5 h-5 text-gray-500 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex flex-col space-y-6 text-2xl font-serif">
                    <a href="<?php echo $path_prefix ?? ''; ?>index.php#discover" onclick="toggleMobileMenu()"
                        class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2">সাজেস্টেড বই</a>
                    <a href="<?php echo $path_prefix ?? ''; ?>category/index.php" onclick="toggleMobileMenu()"
                        class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2">ক্যাটাগরি</a>
                    <a href="<?php echo $path_prefix ?? ''; ?>library/index.php" onclick="toggleMobileMenu()"
                        class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2">লাইব্রেরি</a>
                    <a href="<?php echo $path_prefix ?? ''; ?>membership/index.php" onclick="toggleMobileMenu()"
                        class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2">মেম্বারশিপ</a>
                    <a href="<?php echo $path_prefix ?? ''; ?>pre-booking/index.php" onclick="toggleMobileMenu()"
                        class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2 font-bold">প্রি-বুকিং</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $path_prefix ?? ''; ?>dashboard/" onclick="toggleMobileMenu()"
                            class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2 font-bold text-brand-gold">ড্যাশবোর্ড</a>
                    <?php else: ?>
                        <a href="<?php echo $path_prefix ?? ''; ?>login/index.php" onclick="toggleMobileMenu()"
                            class="hover:text-brand-gold transition-colors border-b border-gray-200 pb-2 font-bold text-brand-gold">লগইন</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Cart Drawer Slider -->
    <div id="cart-overlay" class="fixed inset-0 bg-black/40 z-50 hidden transition-opacity opacity-0 backdrop-blur-sm"
        onclick="toggleCartDrawer()"></div>
    <div id="cart-drawer"
        class="fixed inset-y-0 right-0 w-full sm:w-[400px] bg-white shadow-2xl z-[60] transform translate-x-full transition-transform duration-500 ease-in-out flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-brand-light">
            <h2 class="text-2xl font-serif text-brand-900 mt-1">আপনার কার্ট</h2>
            <button onclick="toggleCartDrawer()"
                class="p-2 bg-gray-100 rounded-full hover:bg-brand-gold hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Cart Items Container -->
        <div id="cart-items-container" class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">
            <!-- Empty State -->
            <div id="cart-empty" class="h-full flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <p class="font-medium">আপনার কার্ট খালি!</p>
                <button onclick="toggleCartDrawer()" class="mt-4 text-sm text-brand-gold hover:underline">বই এক্সপ্লোর
                    করুন</button>
            </div>
        </div>

        <div class="p-6 border-t border-gray-100 bg-brand-light">
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-600 font-medium">মোট মূল্য:</span>
                <span id="cart-total" class="text-2xl font-serif font-bold text-brand-900">৳০</span>
            </div>
            <button onclick="goToCheckout()"
                class="w-full py-4 bg-brand-gold text-brand-900 font-bold text-lg rounded-sm hover:bg-brand-900 hover:text-white transition-colors shadow-lg">
                চেকআউট করুন
            </button>
        </div>
    </div>

    <!-- Borrow Cart Drawer Slider -->
    <div id="borrow-cart-overlay"
        class="fixed inset-0 bg-black/40 z-50 hidden transition-opacity opacity-0 backdrop-blur-sm"
        onclick="toggleBorrowCartDrawer()"></div>
    <div id="borrow-cart-drawer"
        class="fixed inset-y-0 right-0 w-full sm:w-[400px] bg-white shadow-2xl z-[60] transform translate-x-full transition-transform duration-500 ease-in-out flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-brand-light">
            <h2 class="text-2xl font-serif text-brand-900 mt-1">ধার নেওয়ার কার্ট</h2>
            <button onclick="toggleBorrowCartDrawer()"
                class="p-2 bg-gray-100 rounded-full hover:bg-brand-900 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Borrow Cart Items Container -->
        <div id="borrow-cart-items-container" class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">
            <!-- Empty State -->
            <div id="borrow-cart-empty" class="h-full flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                </svg>
                <p class="font-medium">আপনার ধার নেওয়ার কার্ট খালি!</p>
                <button onclick="toggleBorrowCartDrawer()" class="mt-4 text-sm text-brand-gold hover:underline">বই
                    এক্সপ্লোর
                    করুন</button>
            </div>
        </div>

        <div class="p-6 border-t border-gray-100 bg-brand-light">
            <button onclick="goToBorrowCheckout()"
                class="w-full py-4 bg-brand-900 text-white font-bold text-lg rounded-sm hover:bg-brand-gold hover:text-brand-900 transition-colors shadow-lg">
                ধার নিতে অগ্রসর হোন
            </button>
        </div>
    </div>