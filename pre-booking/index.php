<?php
$page_title = 'প্রি-বুকিং | আসন্ন বইয়ের একচেটিয়া সংগ্রহ - অন্ত্যমিল';
$page_description = 'বাজারে আসার আগেই সংগ্রহ করুন আপনার কাঙ্ক্ষিত বইটি। অন্ত্যমিল প্রি-বুকিং অফারে পান বিশেষ ছাড় এবং নিশ্চিত ডেলিভারি। আপনার পাঠ্য তালিকার পরবর্তী চমকটি বুক করে রাখুন এখনই।';
$page_keywords = 'প্রি-বুকিং, আসন্ন বই, ডিসকাউন্ট, বইয়ের অফার, অন্ত্যমিল, Vivago Digital, Pre-order Books, অনলাইন বুকস্টোর';
$path_prefix = '../';
$nav_class = 'glass';
$additional_head = '
    <style>
        :root {
            --brand-gold: #cda873;
            --brand-gold-light: #e6c89b;
            --brand-dark: #ffffff;
            --brand-card: #ffffff;
            --brand-accent-blue: #2c3e50;
        }

        body {
            background-color: #fcfcfc;
            color: #1a1a1a;
        }

        .premium-gradient {
            background: radial-gradient(circle at 50% -20%, #fcfcfc 0%, #ffffff 100%);
        }

        .paper-texture {
            position: relative;
        }
        
        .paper-texture::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("https://www.transparenttextures.com/patterns/paper-fibers.png");
            opacity: 0.15;
            pointer-events: none;
            z-index: 1;
        }

        .hot-deal-container {
            background: #ffffff;
            border-radius: 3rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.1);
        }

        .price-box {
            background: #f9f9f7;
            border-radius: 1.5rem;
            padding: 2rem;
            display: inline-flex;
            gap: 3rem;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .book-3d-hot {
            transform: perspective(1000px) rotateY(-25deg) rotateX(10deg);
            transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1);
            filter: drop-shadow(30px 40px 60px rgba(0,0,0,0.15));
        }

        .book-3d-hot:hover {
            transform: perspective(1000px) rotateY(-10deg) rotateX(5deg) scale(1.02);
        }

        /* Dual Book Animation Container */


        .dual-book-container {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 2000px;
        }

        .dual-book-container .book-wrapper {
            position: absolute;
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dual-book-container .book-1 {
            left: 50%;
            margin-left: -220px;
            transform: rotateY(-25deg) rotateX(5deg) translateZ(50px);
            z-index: 20;
        }

        .dual-book-container .book-2 {
            left: 50%;
            margin-left: 20px;
            transform: rotateY(-15deg) rotateX(5deg) scale(0.95);
            z-index: 10;
            opacity: 0.98;
        }

        .dual-book-container .book-wrapper:hover {
            transform: rotateY(-10deg) rotateX(0deg) scale(1.05) translateZ(100px);
            z-index: 30;
        }

        @media (max-width: 768px) {
            .dual-book-container {
                height: 400px !important;
                margin-top: 2rem !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
            }
            .dual-book-container .book-wrapper {
                position: absolute !important;
                left: 50% !important;
            }
            .dual-book-container .book-1 {
                margin-left: -140px !important;
                width: 180px !important;
            }
            .dual-book-container .book-2 {
                margin-left: -20px !important;
                width: 180px !important;
            }
        }

        .status-label {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 20px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            white-space: nowrap;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            z-index: 100;
            font-family: "Anek Bangla", sans-serif;
        }

        .label-preorder {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        .label-released {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .badge-limited {
            background: rgba(205, 168, 115, 0.1);
            border: 1px solid rgba(205, 168, 115, 0.2);
            color: var(--brand-gold);
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-premium {
            background: var(--brand-gold);
            color: #ffffff;
            padding: 1.2rem 2.5rem;
            border-radius: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px -5px rgba(205, 168, 115, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px -5px rgba(205, 168, 115, 0.5);
            background: #1a1a1a;
        }

        .desc-clamped {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Hero Section Styles */
        .hero-floating-element {
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        .hero-glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dot-pattern {
            background-image: radial-gradient(rgba(205, 168, 115, 0.2) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .animate-gradient {
            animation: gradientShift 3s ease infinite;
        }

        @keyframes bounceSlow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .animate-bounce-slow {
            animation: bounceSlow 3s ease-in-out infinite;
        }

        /* Mockup Inspired Cards */
        .mockup-card {
            background: #ffffff;
            border-radius: 40px;
            padding: 30px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05);
            transition: all 0.5s cubic-bezier(0.2, 1, 0.3, 1);
            border: 1px solid rgba(0,0,0,0.02);
            cursor: pointer;
        }

        .mockup-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.1);
            border-color: var(--brand-gold-light);
        }

        .mockup-img-wrapper {
            border-radius: 30px;
            overflow: hidden;
            aspect-ratio: 10/14;
            position: relative;
            background-color: #f7f7f7;
            max-width: 320px;
            margin: 0 auto;
        }

        .mockup-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #f0f0f0;
            color: #1a1a1a;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
        }

        .mockup-arrow-btn {
            background: var(--brand-gold);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(205, 168, 115, 0.3);
        }

        .mockup-card:hover .mockup-arrow-btn {
            background: #1a1a1a;
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .hot-deal-container {
                border-radius: 2rem;
                padding: 2rem !important;
            }
            .price-box {
                gap: 1.5rem;
                padding: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }
            .price-box div {
                width: 100%;
            }
            .price-box .h-16 {
                display: none;
            }
            .book-3d-hot {
                transform: none !important;
                filter: drop-shadow(0 20px 40px rgba(0,0,0,0.1));
                width: 250px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                left: 0 !important;
                right: 0 !important;
                position: relative !important;
            }


            .mockup-card {
                padding: 20px;
                border-radius: 24px;
            }
            .mockup-img-wrapper {
                border-radius: 20px;
                max-width: 100%;
            }
            .text-6xl {
                font-size: 2.5rem !important;
            }
            .text-7xl {
                font-size: 3rem !important;
            }
            .text-3xl {
                font-size: 1.5rem !important;
            }
            .text-4xl {
                font-size: 1.75rem !important;
            }
        }
    </style>';
include '../includes/db_connect.php';
include '../includes/header.php';

// Fetch Pre-orders from DB
$featured_stmt = $pdo->query("SELECT * FROM pre_orders WHERE is_hot_deal = 1 AND status != 'Closed' LIMIT 1");
$hot_deal = $featured_stmt->fetch();

$all_preorders_stmt = $pdo->query("SELECT * FROM pre_orders WHERE is_hot_deal = 0 AND status != 'Closed' ORDER BY release_date ASC");
$preorders = $all_preorders_stmt->fetchAll();

function bn_date($date)
{
    if (!$date)
        return '';
    $months = [
        'January' => 'জানুয়ারি',
        'February' => 'ফেব্রুয়ারি',
        'March' => 'মার্চ',
        'April' => 'এপ্রিল',
        'May' => 'মে',
        'June' => 'জুন',
        'July' => 'জুলাই',
        'August' => 'আগস্ট',
        'September' => 'সেপ্টেম্বর',
        'October' => 'অক্টোবর',
        'November' => 'নভেম্বর',
        'December' => 'ডিসেম্বর'
    ];
    $date_str = date('d F, Y', strtotime($date));
    foreach ($months as $en => $bn) {
        $date_str = str_replace($en, $bn, $date_str);
    }
    return $date_str;
}

function bn_num($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}
?>

<div class="paper-texture min-h-screen pt-20 pb-20 premium-gradient">
    <!-- Hero Section -->
    <section class="relative overflow-hidden mb-20 py-20 px-6 sm:px-12">
        <div class="absolute inset-0 dot-pattern opacity-50"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-gold/10 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-gold/10 rounded-full blur-[100px]"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <!-- Hero Content -->
                <div class="flex-1 text-center lg:text-left space-y-8">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-gold/10 text-brand-gold rounded-full text-xs font-black uppercase tracking-[0.2em] animate-fade-in">
                        <span class="w-2 h-2 bg-brand-gold rounded-full animate-ping"></span>
                        আগামী দিনের সেরা সংগ্রহ
                    </div>

                    <h1
                        class="text-5xl sm:text-7xl lg:text-8xl font-sans font-black text-brand-900 leading-[1.1] tracking-tight">
                        বইয়ের জগতে <br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-brand-900 via-brand-gold to-brand-900 bg-[length:200%_auto] animate-gradient">প্রথম
                            দখল।</span>
                    </h1>

                    <p class="text-xl text-gray-500 font-anek max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        বাজারে আসার আগেই সংগ্রহ করুন আপনার কাঙ্ক্ষিত বইটি। প্রি-বুকিং অফারে পান বিশেষ ছাড় এবং নিশ্চিত
                        ডেলিভারি। আপনার পাঠ্য তালিকার পরবর্তী চমকটি বুক করে রাখুন এখনই।
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-6 pt-4">
                        <a href="#hot-deal-desc"
                            class="px-8 py-4 bg-brand-900 text-white rounded-2xl font-bold font-anek hover:bg-brand-gold hover:text-brand-900 transition-all shadow-2xl shadow-brand-900/20 transform hover:-translate-y-1">
                            অফার দেখুন
                        </a>
                        <div
                            class="flex items-center gap-4 px-6 py-4 bg-white/80 rounded-2xl border border-white">
                            <div class="flex -space-x-3">
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-200 overflow-hidden">
                                        <img src="https://i.pravatar.cc/100?img=<?php echo $i + 10; ?>" alt="user">
                                    </div>
                                <?php
endfor; ?>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-brand-900">১২০০+ পাঠক</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">প্রি-বুকিং
                                    করেছেন</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Graphics/Book Showcase -->
                <div class="flex-1 w-full max-w-lg lg:max-w-none relative">
                    <div class="hero-floating-element relative z-20">
                        <div class="relative group">
                            <!-- Background glow -->
                            <div
                                class="absolute inset-0 bg-brand-gold/30 rounded-[40px] blur-[60px] transform rotate-12 group-hover:bg-brand-gold/40 transition-all">
                            </div>

                            <!-- Hero Image Main -->
                            <div class="relative hero-glass p-2 md:p-4 rounded-[40px] shadow-2xl overflow-hidden skeleton">
                                <img src="<?php echo $path_prefix; ?>assets/img/modern_book_collage_hero_1773421328987.png"
                                    alt="Premium Book Collection" class="w-full h-auto rounded-[30px] object-cover" fetchpriority="high" loading="eager" onload="this.parentElement.classList.remove('skeleton')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <?php if ($hot_deal): ?>
            <!-- Modern Hot Deal Section -->
            <section onclick="window.location.href='book-details.php?id=<?php echo $hot_deal['id']; ?>'"
                class="hot-deal-container p-10 md:p-20 mb-24 reveal active cursor-pointer group">
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-gold/5 rounded-full blur-[120px] pointer-events-none">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center relative z-10">
                    <div class="space-y-8">
                        <div class="badge-limited">
                            <span class="w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse"></span>
                            সীমিত সময়ের অফার
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-4xl sm:text-6xl md:text-7xl font-sans font-black text-gray-900 leading-tight">
                                <?php echo $hot_deal['title']; ?>
                            </h2>
                            <h3 class="text-xl sm:text-3xl md:text-4xl font-serif italic text-brand-gold opacity-90">
                                <?php echo $hot_deal['sub_title']; ?>
                            </h3>
                        </div>

                        <p id="hot-deal-desc" class="text-gray-600 text-lg font-anek leading-relaxed desc-clamped max-w-lg">
                            <?php echo $hot_deal['description']; ?>
                        </p>

                        <div class="price-box">
                            <div class="space-y-1">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">विशेष मूल्य
                                </p>
                                <div class="flex items-baseline gap-4">
                                    <span
                                        class="text-4xl sm:text-6xl font-sans font-black text-gray-900">৳<?php echo bn_num((int)$hot_deal['discount_price']); ?></span>
                                    <span
                                        class="text-lg text-gray-400 line-through">৳<?php echo bn_num((int)$hot_deal['price']); ?></span>
                                </div>
                            </div>
                            <div class="h-16 w-[1px] bg-gray-200 hidden sm:block"></div>
                            <div class="space-y-1">
                                <p class="text-[10px] text-brand-gold uppercase tracking-widest font-bold mb-1">আপনি
                                    বাঁচাচ্ছেন</p>
                                <p class="text-lg sm:text-2xl font-bold font-anek text-brand-gold">
                                    ৳<?php echo bn_num((int)($hot_deal['price'] - $hot_deal['discount_price'])); ?> সাশ্রয়
                                </p>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button class="btn-premium group-hover:bg-[#1a1a1a] transition-colors">
                                বিস্তারিত দেখুন
                            </button>
                        </div>
                    </div>

                    <div class="relative flex justify-center lg:justify-end" style="min-height: 500px;">
                        <?php if (!empty($hot_deal['second_cover_image'])): ?>
                            <!-- Dual Book Display with Animation -->
                            <div class="dual-book-container w-full h-[450px]">
                                <div class="book-wrapper book-1 w-[220px] md:w-[280px] skeleton rounded-r-xl">
                                    <img data-src="<?php echo strpos($hot_deal['cover_image'], 'http') === 0 ? $hot_deal['cover_image'] : $path_prefix . 'assets/img/preorders/' . $hot_deal['cover_image']; ?>"
                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3C/svg%3E"
                                        class="lazy-image rounded-r-xl shadow-2xl" alt="<?php echo $hot_deal['title']; ?>">
                                    <div class="status-label label-preorder">
                                        <span class="inline-block w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                                        প্রি-অর্ডার চলছে
                                    </div>
                                </div>
                                <div class="book-wrapper book-2 w-[220px] md:w-[280px] skeleton rounded-r-xl">
                                    <img data-src="<?php echo strpos($hot_deal['second_cover_image'], 'http') === 0 ? $hot_deal['second_cover_image'] : $path_prefix . 'assets/img/preorders/' . trim($hot_deal['second_cover_image']); ?>"
                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3C/svg%3E"
                                        class="lazy-image rounded-r-xl shadow-2xl" alt="<?php echo $hot_deal['title']; ?> - Second Cover">
                                    <div class="status-label label-released">
                                        <span class="inline-block w-2 h-2 bg-white rounded-full mr-2"></span>
                                        সদ্য প্রকাশিত
                                    </div>
                                </div>
                            </div>
                        <?php
    else: ?>
                            <!-- Single Book Display -->
                            <div class="book-3d-hot w-[280px] md:w-[400px] skeleton rounded-r-xl">
                                <img data-src="<?php echo strpos($hot_deal['cover_image'], 'http') === 0 ? $hot_deal['cover_image'] : $path_prefix . 'assets/img/preorders/' . $hot_deal['cover_image']; ?>"
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3C/svg%3E"
                                     class="lazy-image rounded-r-xl shadow-2xl" alt="<?php echo $hot_deal['title']; ?>">
                            </div>
                        <?php
    endif; ?>
                    </div>
                </div>
            </section>
        <?php
endif; ?>

        <!-- Other Pre-orders Grid -->
        <div class="reveal">
            <div class="flex items-center gap-4 mb-20">
                <span class="text-brand-gold font-bold uppercase tracking-[0.3em] text-xs">আসন্ন সংগ্রহ</span>
                <div class="h-[1px] flex-1 bg-gray-100"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <?php foreach ($preorders as $index => $book):
    $isOpen = $book['status'] == 'Open';
?>
                    <div onclick="window.location.href='book-details.php?id=<?php echo $book['id']; ?>'"
                        class="mockup-card reveal group">

                        <div class="mockup-img-wrapper mb-8 skeleton rounded-xl">
                            <span class="mockup-badge"><?php echo $isOpen ? 'ওপেন' : 'আসন্ন'; ?></span>
                            <img data-src="<?php echo strpos($book['cover_image'], 'http') === 0 ? $book['cover_image'] : $path_prefix . 'assets/img/preorders/' . trim($book['cover_image']); ?>"
                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3C/svg%3E"
                                alt="<?php echo $book['title']; ?>"
                                class="lazy-image w-full h-full object-cover transform scale-105 group-hover:scale-110 transition-transform duration-700">

                            <!-- Detailed Preview Overlay (Subtle) -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-8">
                                <p class="text-white text-xs font-anek leading-relaxed line-clamp-4">
                                    <?php echo strip_tags($book['description']); ?>
                                </p>
                                <span class="text-brand-gold text-[10px] font-bold uppercase tracking-widest mt-4">বিস্তারিত
                                    পড়তে ক্লিক করুন</span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <h3 class="text-2xl font-sans font-bold text-gray-800 line-clamp-1">
                                    <?php echo $book['title']; ?>
                                </h3>
                                <p class="text-gray-400 font-anek text-sm"><?php echo $book['author']; ?></p>
                            </div>

                            <div class="h-[1px] w-full bg-gray-50 mt-4"></div>

                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="flex items-baseline gap-3">
                                        <span
                                            class="text-3xl font-sans font-black text-gray-900">৳<?php echo bn_num((int)$book['discount_price']); ?></span>
                                        <span
                                            class="text-sm text-gray-300 line-through">৳<?php echo bn_num((int)$book['price']); ?></span>
                                    </div>
                                </div>

                                <div class="mockup-arrow-btn">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
endforeach; ?>
            </div>
        </div>

    </div>
</div>

<script>
    // Use the global imageObserver system defined in script.js
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof observeImages === 'function') {
            observeImages();
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('bg-white/90', 'shadow-sm', 'py-3');
            } else {
                navbar.classList.remove('bg-white/90', 'backdrop-blur-xl', 'shadow-sm', 'py-3');
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>