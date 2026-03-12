<?php
$page_title = 'প্রি-বুকিং | অন্ত্যমিল - বই ও লাইব্রেরি';
$path_prefix = '../';
$nav_class = 'glass-dark';
$additional_head = '
    <style>
        .preorder-card:hover .preorder-badge {
            transform: rotate(-12deg) scale(1.1);
        }

        .timer-dot {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .book-shadow {
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.3));
        }

        .desc-clamped {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .desc-expanded {
            display: block;
        }

        .desc-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to top, #1a1a1a, transparent);
            pointer-events: none;
            transition: opacity 0.3s ease;
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

<!-- Hero Section -->
<header class="relative pt-32 pb-20 overflow-hidden bg-brand-900">
    <!-- Ambient Background -->
    <div class="absolute inset-0 opacity-20">
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-gold rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2">
        </div>
        <div
            class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-brand-gold rounded-full blur-[120px] translate-y-1/2 -translate-x-1/2">
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <span
                    class="inline-block px-4 py-1.5 bg-brand-gold/10 border border-brand-gold/20 text-brand-gold_light text-xs font-bold uppercase tracking-widest rounded-full mb-6">এক্সক্লুসিভ
                    প্রি-অর্ডার</span>
                <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 leading-tight">
                    প্রিয় লেখকের বই <br>
                    <span class="text-brand-gold">অপেক্ষা ফুরাক এবার।</span>
                </h1>
                <p class="text-gray-400 text-lg md:text-xl font-light mb-10 max-w-xl leading-relaxed">
                    বাজারে আসার আগেই আপনার কপিটি নিশ্চিত করুন। পান লেখকের অটোগ্রাফসহ বিশেষ সংস্করণ এবং এক্সক্লুসিভ
                    গিফট হ্যাম্পার।
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#featured-preorder"
                        class="px-8 py-4 bg-brand-gold text-brand-900 font-bold rounded-sm hover:bg-white transition-all shadow-xl shadow-brand-gold/10">
                        এখনই প্রি-বুক করুন
                    </a>
                    <a href="#how-it-works"
                        class="px-8 py-4 bg-transparent border border-white/20 text-white font-medium rounded-sm hover:border-brand-gold transition-all">
                        কিভাবে কাজ করে?
                    </a>
                </div>
            </div>

            <div class="relative hidden lg:block reveal" style="transition-delay: 200ms;">
                <div class="relative z-10 flex justify-center">
                    <!-- Main Featured Book -->
                    <div
                        class="w-[300px] relative book-shadow transform -rotate-6 hover:rotate-0 transition-transform duration-500 cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=1000&auto=format&fit=crop"
                            alt="Featured Pre-order Book" class="rounded-r-lg shadow-2xl">
                        <div
                            class="absolute top-4 -right-4 bg-red-600 text-white px-4 py-2 text-xs font-bold rounded shadow-lg animate-bounce">
                            প্রি-অর্ডার চলছে
                        </div>
                    </div>

                    <!-- Secondary Books -->
                    <div
                        class="absolute -bottom-10 -left-10 w-[180px] book-shadow transform rotate-12 z-0 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                        <img src="https://images.unsplash.com/photo-1589998059171-988d887df646?q=80&w=600&auto=format&fit=crop"
                            alt="Upcoming Book" class="rounded-r-md">
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Why Pre-order Section -->
<section class="py-16 md:py-24 bg-brand-light">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
            <!-- Benefit 1 -->
            <div
                class="bg-white p-5 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
                <div
                    class="w-10 h-10 md:w-12 md:h-12 bg-brand-gold/10 rounded-xl flex items-center justify-center text-brand-gold mb-4 md:mb-6 group-hover:bg-brand-gold group-hover:text-white transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base md:text-xl font-serif text-brand-900 mb-1 md:mb-3 font-bold">দ্রুত ডেলিভারি
                </h3>
                <p class="text-[10px] md:text-sm text-gray-500 leading-relaxed font-anek">বই বাজারে আসার দিনই সবার
                    আগে আপনার কাছে পৌঁছে যাবে।</p>
            </div>

            <!-- Benefit 2 -->
            <div
                class="bg-white p-5 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
                <div
                    class="w-10 h-10 md:w-12 md:h-12 bg-brand-gold/10 rounded-xl flex items-center justify-center text-brand-gold mb-4 md:mb-6 group-hover:bg-brand-gold group-hover:text-white transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base md:text-xl font-serif text-brand-900 mb-1 md:mb-3 font-bold">লেখকের স্বাক্ষর
                </h3>
                <p class="text-[10px] md:text-sm text-gray-500 leading-relaxed font-anek">ভাগ্যবান পাঠকরা পাবেন
                    লেখকের ব্যক্তিগত অটোগ্রাফসহ সংগৃহীত কপি।</p>
            </div>

            <!-- Benefit 3 -->
            <div
                class="bg-white p-5 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
                <div
                    class="w-10 h-10 md:w-12 md:h-12 bg-brand-gold/10 rounded-xl flex items-center justify-center text-brand-gold mb-4 md:mb-6 group-hover:bg-brand-gold group-hover:text-white transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base md:text-xl font-serif text-brand-900 mb-1 md:mb-3 font-bold">বিশেষ মূল্যছাড়
                </h3>
                <p class="text-[10px] md:text-sm text-gray-500 leading-relaxed font-anek">প্রি-অর্ডার করলে পাচ্ছেন
                    সর্বোচ্চ ২৫% পর্যন্ত বিশেষ সাশ্রয়ী মূল্য।</p>
            </div>

            <!-- Benefit 4 -->
            <div
                class="bg-white p-5 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
                <div
                    class="w-10 h-10 md:w-12 md:h-12 bg-brand-gold/10 rounded-xl flex items-center justify-center text-brand-gold mb-4 md:mb-6 group-hover:bg-brand-gold group-hover:text-white transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 11m8 4V21M4 11v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-base md:text-xl font-serif text-brand-900 mb-1 md:mb-3 font-bold">সারপ্রাইজ গিফট
                </h3>
                <p class="text-[10px] md:text-sm text-gray-500 leading-relaxed font-anek">প্রতিটি প্রি-অর্ডারে থাকছে
                    আকর্ষণীয় বুকমার্ক বা স্টিকার প্যাক।</p>
            </div>
        </div>
    </div>
</section>

<!-- Hot Deals Section -->
<section class="py-16 md:py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div
            class="bg-gradient-to-br from-brand-900 to-brand-800 rounded-[2.5rem] p-8 md:p-16 relative overflow-hidden shadow-2xl">
            <!-- Decorative circle -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-gold/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-gold/5 rounded-full blur-3xl"></div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative z-10">
                <?php if ($hot_deal): ?>
                    <div class="reveal">
                        <div
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-[10px] md:text-xs font-bold uppercase tracking-widest rounded-full mb-6 animate-pulse">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            সীমিত সময়ের হট ডিল!
                        </div>
                        <h2 class="text-3xl md:text-4xl font-serif text-white mb-6 leading-tight">
                            <?php echo $hot_deal['title']; ?> <br>
                            <span class="text-brand-gold"> <?php echo $hot_deal['sub_title']; ?></span>
                        </h2>
                        <div class="relative mb-8 group/desc max-w-lg">
                            <p id="hot-deal-desc"
                                class="text-gray-400 text-base md:text-lg font-anek leading-relaxed desc-clamped transition-all duration-500">
                                <?php echo $hot_deal['description']; ?>
                            </p>
                            <div id="desc-mask" class="desc-fade opacity-100"></div>
                            <button onclick="toggleHotDealDesc()" id="desc-toggle-btn"
                                class="hidden mt-4 text-brand-gold text-[10px] font-bold uppercase tracking-widest hover:text-white transition-colors relative z-10">
                                বিস্তারিত পড়ুন
                            </button>
                        </div>

                        <div class="flex items-center gap-6 mb-10">
                            <div class="flex flex-col">
                                <span
                                    class="text-gray-500 line-through text-lg">৳<?php echo bn_num((int) $hot_deal['price']); ?></span>
                                <span
                                    class="text-4xl font-bold text-white">৳<?php echo bn_num((int) $hot_deal['discount_price']); ?></span>
                            </div>
                            <div class="h-12 w-px bg-white/10 mx-2"></div>
                            <div class="text-brand-gold">
                                <p class="text-[10px] uppercase tracking-wider font-bold mb-1">আপনি সাশ্রয় করছেন</p>
                                <p class="text-2xl font-bold font-anek">
                                    ৳<?php echo bn_num((int) ($hot_deal['price'] - $hot_deal['discount_price'])); ?> ফ্ল্যাট
                                    অফ!</p>
                            </div>
                        </div>

                        <button
                            onclick="addToCart({id: 'pre_<?php echo $hot_deal['id']; ?>', title: '<?php echo addslashes($hot_deal['title']); ?>', author: '<?php echo addslashes($hot_deal['author']); ?>', price: <?php echo $hot_deal['discount_price']; ?>, img: '<?php echo strpos($hot_deal['cover_image'], 'http') === 0 ? $hot_deal['cover_image'] : $path_prefix . 'assets/img/preorders/' . $hot_deal['cover_image']; ?>'})"
                            class="w-full md:w-auto px-10 py-4 bg-brand-gold text-brand-900 font-bold rounded hover:bg-white transition-all transform hover:-translate-y-1 shadow-xl shadow-brand-gold/10">
                            অফারটি লুফে নিন
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Book Visuals -->
                <div class="relative reveal min-h-[300px] lg:min-h-[450px] flex items-center justify-center lg:justify-end"
                    style="transition-delay: 200ms;">
                    <?php if ($hot_deal): ?>
                        <!-- Main Book -->
                        <div
                            class="relative w-[180px] md:w-[260px] lg:w-[320px] book-shadow transform rotate-6 z-20 transition-all hover:rotate-0 hover:scale-105 duration-500 cursor-pointer">
                            <img src="<?php echo strpos($hot_deal['cover_image'], 'http') === 0 ? $hot_deal['cover_image'] : $path_prefix . 'assets/img/preorders/' . $hot_deal['cover_image']; ?>"
                                class="rounded-r-lg shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] border-l-[8px] border-brand-900/20"
                                alt="<?php echo $hot_deal['title']; ?>">
                            <div
                                class="absolute -top-4 -right-4 bg-brand-gold text-brand-900 text-xs font-bold px-4 py-2 rounded shadow-2xl animate-bounce">
                                <?php echo $hot_deal['status'] == 'Upcoming' ? 'আসন্ন' : 'চলছে'; ?>
                            </div>
                        </div>

                        <!-- Decorative circle background -->
                        <div
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] md:w-[450px] md:h-[450px] bg-brand-gold/5 rounded-full blur-[100px] -z-10">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Books Grid Section -->
<section id="featured-preorder" class="py-24 px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-end mb-16 reveal">
        <div class="max-w-xl">
            <span class="text-brand-gold font-medium tracking-wider text-sm">আসন্ন সংগ্রহ</span>
            <h2 class="text-4xl md:text-5xl font-serif text-brand-900 mt-2">প্রি-বুকিং এর জন্য শ্রেষ্ঠ বইগুলো</h2>
        </div>
        <div class="hidden md:flex gap-2">
            <button class="p-3 border border-gray-200 rounded hover:border-brand-gold transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                    </path>
                </svg>
            </button>
            <button
                class="p-3 border border-gray-200 rounded hover:border-brand-gold transition-colors bg-brand-900 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php foreach ($preorders as $index => $book):
            $isOpen = $book['status'] == 'Open';
            $isUpcoming = $book['status'] == 'Upcoming';
            ?>
            <div class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-2xl transition-all duration-500 reveal group preorder-card"
                style="transition-delay: <?php echo $index * 100; ?>ms;">
                <div class="relative mb-6 overflow-hidden rounded-2xl aspect-[3/4]">
                    <img src="<?php echo strpos($book['cover_image'], 'http') === 0 ? $book['cover_image'] : $path_prefix . 'assets/img/preorders/' . $book['cover_image']; ?>"
                        alt="<?php echo $book['title']; ?>"
                        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-brand-900/10 group-hover:bg-transparent transition-colors"></div>
                    <div class="absolute top-4 left-4">
                        <span
                            class="bg-white text-brand-900 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full shadow-lg preorder-badge transition-transform duration-300 origin-left">প্রি-অর্ডার</span>
                    </div>
                    <div class="absolute bottom-4 inset-x-4">
                        <div
                            class="bg-white/90 backdrop-blur-md p-3 rounded-xl flex justify-between items-center shadow-lg">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">প্রকাশের তারিখ
                                </p>
                                <p class="text-brand-900 font-bold text-xs"><?php echo bn_date($book['release_date']); ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <span
                                    class="w-1.5 h-1.5 <?php echo $isOpen ? 'bg-green-500' : 'bg-yellow-500'; ?> rounded-full timer-dot"></span>
                                <span
                                    class="text-[10px] font-bold text-brand-900"><?php echo $isOpen ? 'অর্ডার খোলা' : 'শীঘ্রই আসছে'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-serif text-brand-900 font-bold leading-snug"><?php echo $book['title']; ?></h3>
                    <p class="text-gray-500 font-anek text-sm"><?php echo $book['author']; ?></p>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-4">
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-2xl font-anek font-bold text-brand-900">৳<?php echo bn_num((int) $book['discount_price']); ?></span>
                            <?php if ($book['price'] > $book['discount_price']): ?>
                                <span
                                    class="text-gray-400 line-through text-sm">৳<?php echo bn_num((int) $book['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isOpen): ?>
                            <button
                                onclick="addToCart({id: 'pre_<?php echo $book['id']; ?>', title: '<?php echo addslashes($book['title']); ?>', author: '<?php echo addslashes($book['author']); ?>', price: <?php echo $book['discount_price']; ?>, img: '<?php echo strpos($book['cover_image'], 'http') === 0 ? $book['cover_image'] : $path_prefix . 'assets/img/preorders/' . $book['cover_image']; ?>'})"
                                class="bg-brand-gold text-brand-900 px-5 py-2.5 rounded font-bold text-sm hover:bg-brand-900 hover:text-white transition-colors">
                                অর্ডার করুন
                            </button>
                        <?php else: ?>
                            <button class="bg-gray-200 text-gray-400 px-5 py-2.5 rounded font-bold text-sm cursor-not-allowed">
                                অপেক্ষা করুন
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- How it works -->
<section id="how-it-works" class="py-24 bg-brand-900 text-white overflow-hidden relative">
    <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <h2 class="text-4xl md:text-5xl font-serif mb-6 leading-tight">কিভাবে <span
                    class="text-brand-gold">প্রি-বুকিং</span> করবেন?</h2>
            <div class="w-20 h-1 bg-brand-gold mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
            <div class="text-center reveal bg-white/5 p-6 rounded-2xl md:bg-transparent md:p-0">
                <div class="text-4xl md:text-6xl font-serif text-brand-gold/20 mb-2 md:mb-4">০১</div>
                <h4 class="text-lg md:text-xl font-bold mb-2 md:mb-4">বই নির্বাচন করুন</h4>
                <p class="text-xs md:text-base text-gray-400 font-anek">উপরে থাকা আসন্ন বইগুলোর তালিকা থেকে আপনার
                    প্রিয় বইটি বেছে নিন এবং
                    ‘অর্ডার করুন’ এ ক্লিক করুন।</p>
            </div>
            <div class="text-center reveal bg-white/5 p-6 rounded-2xl md:bg-transparent md:p-0"
                style="transition-delay: 100ms;">
                <div class="text-4xl md:text-6xl font-serif text-brand-gold/20 mb-2 md:mb-4">০২</div>
                <h4 class="text-lg md:text-xl font-bold mb-2 md:mb-4">অর্ডার নিশ্চিত করুন</h4>
                <p class="text-xs md:text-base text-gray-400 font-anek">আপনার নাম, ঠিকানা এবং ফোন নম্বর দিয়ে ফর্মটি
                    পূরণ করুন এবং স্বল্প
                    মূল্যে বইটির জন্য অগ্রিম পেমেন্ট করুন।</p>
            </div>
            <div class="text-center reveal bg-white/5 p-6 rounded-2xl md:bg-transparent md:p-0"
                style="transition-delay: 200ms;">
                <div class="text-4xl md:text-6xl font-serif text-brand-gold/20 mb-2 md:mb-4">০৩</div>
                <h4 class="text-lg md:text-xl font-bold mb-2 md:mb-4">বই হাতে পান</h4>
                <p class="text-xs md:text-base text-gray-400 font-anek">বই প্রকাশের দিনই আমাদের কুরিয়ার পার্টনাররা
                    আপনার ঠিকানায় বইটি
                    পৌঁছে দিবে। শুভ বই পড়া!</p>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleHotDealDesc() {
        const desc = document.getElementById('hot-deal-desc');
        const mask = document.getElementById('desc-mask');
        const btn = document.getElementById('desc-toggle-btn');

        if (desc.classList.contains('desc-clamped')) {
            desc.classList.remove('desc-clamped');
            desc.classList.add('desc-expanded');
            mask.classList.add('opacity-0');
            btn.innerText = 'সংক্ষেপ করুন';
        } else {
            desc.classList.add('desc-clamped');
            desc.classList.remove('desc-expanded');
            mask.classList.remove('opacity-0');
            btn.innerText = 'বিস্তারিত পড়ুন';
        }
    }

    // Check if description needs a toggle
    document.addEventListener('DOMContentLoaded', () => {
        const desc = document.getElementById('hot-deal-desc');
        const btn = document.getElementById('desc-toggle-btn');

        if (desc && desc.scrollHeight > desc.clientHeight) {
            btn.classList.remove('hidden');
        }
    });
</script>
<?php include '../includes/footer.php'; ?>