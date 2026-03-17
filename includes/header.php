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
    <title><?php echo $page_title ?? 'অন্ত্যমিল | বই ও লাইব্রেরি - একটি প্রিমিয়াম অনলাইন বুকস্টোর'; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $page_description ?? 'অন্ত্যমিল - একটি প্রিমিয়াম অনলাইন বুকস্টোর এবং আধুনিক লাইব্রেরি। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন। সাহিত্য ও জ্ঞানের এক অনন্য ভান্ডার।'; ?>">
    <meta name="keywords" content="<?php echo $page_keywords ?? 'বুকস্টোর, লাইব্রেরি, অনলাইন বুক শপ, বই ধার, সাহিত্য, অন্ত্যমিল, Ontomeel, Bookshop, Library, Vivago Digital, অনলাইন লাইব্রেরি'; ?>">
    <meta name="author" content="Vivago Digital">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:title" content="<?php echo $page_title ?? 'অন্ত্যমিল | বই ও লাইব্রেরি'; ?>">
    <meta property="og:description" content="<?php echo $page_description ?? 'অন্ত্যমিল - একটি প্রিমিয়াম অনলাইন বুকস্টোর এবং আধুনিক লাইব্রেরি। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন।'; ?>">
    <meta property="og:image" content="<?php echo $og_image ?? ($path_prefix ?? '') . 'assets/img/og-image.jpg'; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="twitter:title" content="<?php echo $page_title ?? 'অন্ত্যমিল | বই ও লাইব্রেরি'; ?>">
    <meta property="twitter:description" content="<?php echo $page_description ?? 'অন্ত্যমিল - একটি প্রিমিয়াম অনলাইন বুকস্টোর এবং আধুনিক লাইব্রেরি। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন।'; ?>">
    <meta property="twitter:image" content="<?php echo $og_image ?? ($path_prefix ?? '') . 'assets/img/og-image.jpg'; ?>">

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

    <!-- Custom JS - Version controlled for cache management -->
    <script src="<?php echo $path_prefix ?? ''; ?>assets/js/script.js?v=2.0.1" defer></script>

    <!-- Custom Styles - Version controlled for cache management -->
    <link rel="stylesheet" href="<?php echo $path_prefix ?? ''; ?>assets/css/style.css?v=2.0.1">

    <!-- favicon -->
    <link rel="icon" href="<?php echo $path_prefix ?? ''; ?>assets/img/logo.webp">

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
    require_once __DIR__ . '/db_connect.php';
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
            const user_id = <?php echo (int)$_SESSION['user_id']; ?>;
            const membership_plan = '<?php echo htmlspecialchars($m_plan, ENT_QUOTES, 'UTF-8'); ?>';
            localStorage.setItem('membership_plan', membership_plan);
        <?php
endif; ?>
    </script>

    <?php echo $additional_head ?? ''; ?>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white">

    <!-- Navigation -->
    <?php if (isset($is_checkout) && $is_checkout): ?>
        <nav class="bg-white border-b border-gray-100 py-6 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                <a href="<?php echo $path_prefix ?? ''; ?>index.php" class="flex items-center gap-3">
                    <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.webp" alt="logo" class="w-10 h-auto">
                    <span class="font-serif text-2xl font-bold tracking-tight text-brand-900 mt-1 uppercase">অন্ত্যমিল<span
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
    <?php
else: ?>
        <nav id="navbar" class="fixed w-full z-40 transition-all duration-300 py-4 <?php echo $nav_class ?? 'glass'; ?>">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <!-- Logo -->
                    <a href="<?php echo $path_prefix ?? ''; ?>index.php"
                        class="flex items-center gap-2 group flex-shrink-0">
                        <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.webp" alt="logo of ontomeel"
                            class="w-12 h-auto">
                        <span
                            class="font-serif text-2xl font-bold tracking-wide <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-900'; ?> mt-1">অন্ত্যমিল<span
                                class="text-brand-gold">.</span></span>
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="<?php echo $path_prefix ?? ''; ?>category/index.php"
                            class="text-[15px] font-medium <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">ক্যাটাগরি</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>library/index.php"
                            class="text-[15px] font-medium <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">লাইব্রেরি</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>membership/index.php"
                            class="text-[15px] font-medium <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-gray-300' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors">মেম্বারশিপ</a>
                        <a href="<?php echo $path_prefix ?? ''; ?>pre-booking/index.php"
                            class="text-[15px] font-medium <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-brand-gold border-b-2 border-brand-gold' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors font-bold">প্রি-বুকিং</a>
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
                            class="relative <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors group p-2"
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
                            class="relative <?php echo($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-800'; ?> hover:text-brand-gold transition-colors group p-2"
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
                            class="<?php echo($nav_class ?? '') == 'glass-dark' ? 'text-white' : 'text-brand-900'; ?> md:hidden focus:outline-none p-2"
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
                        <?php
    else: ?>
                            <a href="<?php echo $path_prefix ?? ''; ?>login/index.php"
                                class="hidden md:flex items-center gap-2 px-6 py-2 bg-brand-gold text-brand-900 rounded-full font-anek font-bold transition-all hover:bg-white text-sm shadow-lg shadow-brand-gold/20">
                                লগইন
                            </a>
                        <?php
    endif; ?>
                    </div>
                </div>
            </div>

        </nav>

        <!-- Mobile Menu Slider -->
        <div id="mobile-menu-overlay"
            class="fixed inset-0 bg-brand-900/40 z-[70] hidden opacity-0 transition-opacity duration-500 ease-in-out md:hidden"
            onclick="toggleMobileMenu()"></div>

        <div id="mobile-menu"
            class="fixed inset-y-0 right-0 w-[300px] sm:w-[350px] bg-white z-[80] transform translate-x-full transition-transform duration-500 ease-in-out md:hidden flex flex-col shadow-2xl overflow-hidden border-l border-brand-gold/20">

            <!-- Header of the drawer -->
            <div class="px-6 py-6 border-b border-gray-100 flex items-center justify-between bg-brand-light">
                <a href="<?php echo $path_prefix ?? ''; ?>index.php" class="flex items-center gap-2">
                    <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.webp" alt="logo" class="w-10 h-auto">
                    <span class="font-serif text-xl font-bold text-brand-900 mt-1">অন্ত্যমিল<span
                            class="text-brand-gold">.</span></span>
                </a>
                <button onclick="toggleMobileMenu()"
                    class="p-2 bg-gray-100 rounded-full text-brand-900 hover:bg-brand-900 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Menu Links -->
            <div class="flex-1 overflow-y-auto px-6 py-8 flex flex-col space-y-1">
                <!-- Search in menu -->
                <div class="relative w-full mb-8">
                    <input type="text" id="mobileSearchInput" onkeyup="searchBooks(event, true)" placeholder="বই খুঁজুন..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all font-sans">
                    <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4 px-2">ন্যাভিগেশন</p>

                <a href="<?php echo $path_prefix ?? ''; ?>index.php" onclick="toggleMobileMenu()"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-brand-900 hover:bg-brand-gold/10 hover:text-brand-gold transition-all group">
                    <span
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 group-hover:bg-brand-gold/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    </span>
                    <span class="font-anek font-semibold">হোম পেজ</span>
                </a>

                <a href="<?php echo $path_prefix ?? ''; ?>category/index.php" onclick="toggleMobileMenu()"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-brand-900 hover:bg-brand-gold/10 hover:text-brand-gold transition-all group">
                    <span
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 group-hover:bg-brand-gold/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </span>
                    <span class="font-anek font-semibold">ক্যাটাগরি</span>
                </a>

                <a href="<?php echo $path_prefix ?? ''; ?>library/index.php" onclick="toggleMobileMenu()"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-brand-900 hover:bg-brand-gold/10 hover:text-brand-gold transition-all group">
                    <span
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 group-hover:bg-brand-gold/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </span>
                    <span class="font-anek font-semibold">লাইব্রেরি</span>
                </a>

                <a href="<?php echo $path_prefix ?? ''; ?>membership/index.php" onclick="toggleMobileMenu()"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-brand-900 hover:bg-brand-gold/10 hover:text-brand-gold transition-all group">
                    <span
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 group-hover:bg-brand-gold/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </span>
                    <span class="font-anek font-semibold">মেম্বারশিপ</span>
                </a>

                <a href="<?php echo $path_prefix ?? ''; ?>pre-booking/index.php" onclick="toggleMobileMenu()"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-brand-900 hover:bg-brand-gold/10 hover:text-brand-gold transition-all group">
                    <span
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 group-hover:bg-brand-gold/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                            </path>
                        </svg>
                    </span>
                    <span class="font-anek font-bold">প্রি-বুকিং</span>
                </a>

                <div class="h-px bg-gray-100 my-4 mx-4"></div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $path_prefix ?? ''; ?>dashboard/" onclick="toggleMobileMenu()"
                        class="flex items-center gap-4 px-4 py-3.5 rounded-xl bg-brand-gold text-brand-900 transition-all shadow-lg shadow-brand-gold/20">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <span class="font-anek font-bold italic">আমার ড্যাশবোর্ড</span>
                    </a>
                <?php
    else: ?>
                    <a href="<?php echo $path_prefix ?? ''; ?>login/index.php" onclick="toggleMobileMenu()"
                        class="flex items-center gap-4 px-4 py-3.5 rounded-xl bg-brand-gold text-brand-900 transition-all shadow-lg shadow-brand-gold/20">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h18m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </span>
                        <span class="font-anek font-bold items-center">লগইন করুন</span>
                    </a>
                <?php
    endif; ?>
            </div>

            <!-- Footer of the drawer -->
            <div class="p-6 bg-brand-light border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">সাথে থাকুন</span>
                    <div class="flex gap-3">
                        <a href="#"
                            class="p-2 bg-white rounded-lg text-brand-800 hover:text-brand-gold transition-colors shadow-sm"><svg
                                class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg></a>
                        <a href="#"
                            class="p-2 bg-white rounded-lg text-brand-800 hover:text-brand-gold transition-colors shadow-sm"><svg
                                class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.315 2c2.43 0 2.784.012 3.845.06 1.157.052 1.93.242 2.37.412.585.226 1.082.528 1.57.92.54.43.91.82 1.25 1.43.23.41.4 1.05.5 2.22.1 1.08.11 1.41.11 3.55s-.01 2.47-.11 3.55c-.1 1.17-.27 1.81-.5 2.22-.34.61-.71 1-1.25 1.43-.49.39-.98.69-1.57.92-.44.17-1.213.36-2.37.41-1.06.05-1.415.06-3.845.06s-2.784-.01-3.845-.06c-1.157-.05-1.93-.24-2.37-.41-.585-.226-1.082-.528-1.57-.92-.54-.43-.91-.82-1.25-1.43-.23-.41-.4-1.05-.5-2.22-.1-1.08-.11-1.41-.11-3.55s.01-2.47.11-3.55c.1-1.17.27-1.81.5-2.22.34-.61.71-1 1.25-1.43.49-.39.98-.69 1.57-.92.44-.17-1.213-.36 2.37-.41 1.06-.05 1.415-.06 3.845-.06zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8a3 3 0 110-6 3 3 0 010 6zm5.885-9.35a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" />
                            </svg></a>
                    </div>
                </div>
                <p class="text-[10px] text-gray-500 text-center font-sans tracking-tight">© ২০২৪ অন্ত্যমিল। সর্বস্বত্ব
                    সংরক্ষিত।
                </p>
            </div>
        </div>
    <?php
endif; ?>

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
        class="fixed inset-0 bg-black/40 z-50 hidden transition-opacity opacity-0"
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