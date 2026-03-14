<?php
$page_title = 'অন্ত্যমিল | বই ও লাইব্রেরি - প্রিমিয়াম অনলাইন বুকস্টোর';
$page_description = 'অন্ত্যমিল - একটি প্রিমিয়াম অনলাইন বুকস্টোর এবং আধুনিক লাইব্রেরি। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন। সাহিত্য ও জ্ঞানের এক অনন্য ভান্ডার।';
$page_keywords = 'বুকস্টোর, লাইব্রেরি, অনলাইন বুক শপ, বই ধার, সাহিত্য, অন্ত্যমিল, Ontomeel, Bookshop, Library, Vivago Digital, অনলাইন লাইব্রেরি, গল্পের বই';
$path_prefix = '';
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories LIMIT 6");
$categories = $stmt->fetchAll();

// Fetch Suggested Books (Filtered by is_suggested column)
$stmt = $pdo->query("SELECT b.*, c.name as category_name
FROM books b
LEFT JOIN categories c ON b.category_id = c.id
WHERE b.is_active = 1 AND b.is_suggested = 1
ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC LIMIT 12");
$suggested_books = $stmt->fetchAll();

// Fetch All Books for Search/Filter
$stmt = $pdo->query("SELECT b.*, c.name as category_name
FROM books b
LEFT JOIN categories c ON b.category_id = c.id
WHERE b.is_active = 1
ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC");
$all_books_db = $stmt->fetchAll();

function getBookImage($image)
{
    if (!empty($image)) {
        return 'admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

function bn_num($num)
{
    if ($num === null)
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}
?>

<!-- Hero Section -->
<header class="relative min-h-screen flex items-center overflow-hidden hero-bg bg-brand-900 pt-20">
    <!-- Overlay Layers -->
    <div class="mesh-gradient absolute inset-0"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-brand-900 via-brand-900/60 to-transparent"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 w-full py-12 lg:py-0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Content Column -->
            <div class="text-center lg:text-left">
                <!-- <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-brand-gold/20 bg-brand-gold/5 text-brand-gold_light text-xs md:text-sm mb-10 animate-slide-up shadow-sm"
                        style="animation-delay: 0.1s;">
                        <span class="relative flex h-2.5 w-2.5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-gold opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-gold"></span>
                        </span>
                        <span class="font-medium tracking-wide">গল্পের এক অনন্য অভয়ারণ্য</span>
                    </div> -->

                <h1 class="text-6xl md:text-7xl lg:text-[100px] font-anek font-extrabold text-white mb-8 leading-[1] sm:leading-[0.9] animate-slide-up"
                    style="animation-delay: 0.3s;">
                    পড়ুন, ধার নিন,<br>
                    <span class="text-gradient-gold">এবং সংগ্রহ করুন।</span>
                </h1>

                <p class="text-gray-300 text-lg md:text-xl font-light mb-12 max-w-xl mx-auto lg:mx-0 animate-slide-up leading-relaxed"
                    style="animation-delay: 0.5s;">
                    অন্ত্যমিল — একটি প্রিমিয়াম বুকস্টোর এবং আধুনিক লাইব্রেরির এক অপূর্ব মেলবন্ধন। আমাদের বাছাইকৃত
                    সংগ্রহে খুঁজে পান সাহিত্যের অমূল্য সম্পদ।
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 animate-slide-up"
                    style="animation-delay: 0.7s;">
                    <a href="#discover"
                        class="group relative px-10 py-5 bg-brand-gold text-brand-900 font-bold text-lg overflow-hidden transition-all duration-500 hover:shadow-[0_0_30px_rgba(205,168,115,0.4)]">
                        <span class="relative z-10 flex items-center gap-2">
                            এক্সপ্লোর করুন
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </a>
                    <a href="membership/"
                        class="px-10 py-5 bg-transparent border border-white/30 text-white font-medium text-lg btn-premium glass hover:border-brand-gold transition-all duration-300">
                        মেম্বারশিপ নিন
                    </a>
                </div>

                <!-- Stats Section -->
                <div class="mt-16 md:mt-20 grid grid-cols-2 lg:flex items-center gap-6 md:gap-12 animate-slide-up"
                    style="animation-delay: 0.9s;">
                    <div class="flex flex-col">
                        <span class="text-3xl md:text-4xl font-anek font-bold text-white">১৩হা<span
                                class="text-brand-gold">+</span></span>
                        <span
                            class="text-gray-400 text-[10px] md:text-xs uppercase tracking-[0.2em] mt-1 md:mt-2 font-medium">বইয়ের
                            সংগ্রহ</span>
                    </div>
                    <div class="hidden lg:block h-10 w-px bg-brand-gold/20"></div>
                    <div class="flex flex-col border-l border-brand-gold/20 pl-6 lg:border-0 lg:pl-0">
                        <span class="text-3xl md:text-4xl font-anek font-bold text-white">১হা<span
                                class="text-brand-gold">+</span></span>
                        <span
                            class="text-gray-400 text-[10px] md:text-xs uppercase tracking-[0.2em] mt-1 md:mt-2 font-medium">সক্রিয়া
                            पाठक</span>
                    </div>
                </div>
            </div>

            <!-- Visual Column -->
            <div class="hidden lg:flex justify-center items-center relative hero-visual-container min-h-[600px] reveal"
                style="transition-delay: 400ms;">
                <!-- Ambient Glow -->
                <div class="glow-aura glow-gold w-[500px] h-[500px]"></div>

                <!-- Main Feature: 3D Book Mockup -->
                <div class="relative z-20 w-[320px] book-3d floating-card">
                    <!-- Book Cover -->
                    <div
                        class="relative aspect-[2/3] rounded-r-lg overflow-hidden shadow-[20px_20px_50px_rgba(0,0,0,0.5)] border-l-2 border-white/20">
                        <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=1000&auto=format&fit=crop"
                            alt="Featured Book" class="w-full h-full object-cover">
                        <!-- Shine effect on cover -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/10 via-transparent to-transparent">
                        </div>
                    </div>

                    <!-- Floating Badge on Book -->
                    <div class="absolute -top-6 -right-10 glass px-6 py-4 rounded-2xl shadow-2xl border border-white/20 backdrop-blur-md animate-float"
                        style="animation-delay: -2s;">
                        <div class="flex items-center gap-3">
                            <div class="bg-brand-gold p-2 rounded-full">
                                <svg class="w-4 h-4 text-brand-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-brand-900 font-anek font-bold text-lg leading-none">৪.৯</p>
                                <p class="text-gray-600 text-[10px] uppercase font-bold tracking-widest mt-1">পাঠক
                                    রেটিং</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute -bottom-10 -right-20 w-64 glass p-6 rounded-3xl shadow-2xl border border-white/20 backdrop-blur-xl animate-slide-up"
                    style="animation-delay: 1s;">
                    <span class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em] mb-3 block">বিশেষ
                        কালেকশন</span>
                    <h4 class="text-black font-anek font-bold text-xl mb-4 leading-snug">রবীন্দ্রনাথের শ্রেষ্ঠ
                        গল্পগুচ্ছ</h4>
                    <div class="flex items-center justify-between">
                        <span class="text-brand-gold font-bold text-lg font-anek whitespace-nowrap">৳৪৭৫</span>
                        <a href="#" class="text-white/60 hover:text-brand-gold transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Ambient Floating Circles -->
                <div
                    class="absolute top-10 right-0 w-4 h-4 bg-brand-gold rounded-full blur-sm opacity-50 animate-pulse">
                </div>
                <div class="absolute bottom-20 left-10 w-2 h-2 bg-brand-gold rounded-full blur-[1px] opacity-30 animate-pulse"
                    style="animation-delay: 1s;"></div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce z-10 text-white cursor-pointer opacity-40 hover:opacity-100 transition-opacity"
        onclick="document.getElementById('collections').scrollIntoView({behavior: 'smooth'})">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3">
            </path>
        </svg>
    </div>
</header>

<!-- Categories / Collections -->
<section id="collections" class="py-20 px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="text-center mb-12 reveal">
        <span class="text-brand-gold font-medium tracking-wider text-sm">আপনার পছন্দের ধরণ খুঁজুন</span>
        <h2 class="text-3xl md:text-5xl font-serif text-brand-900 mt-2 mb-4">বইয়ের ক্যাটাগরি</h2>
        <div class="w-16 h-1 bg-brand-gold mx-auto mt-4 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($categories as $index => $cat):
            $cat_images = [
                'ফিকশন' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=60&w=600',
                'নন-ফিকশন' => 'https://images.unsplash.com/photo-1456324504439-367cee3b3c32?q=60&w=600',
                'শিল্প ও লাইফস্টাইল' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=60&w=600',
            ];
            $img = $cat_images[$cat['name']] ?? 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=60&w=600';
            ?>
            <div onclick="filterByCategory('<?php echo $cat['name']; ?>')"
                class="group relative h-64 md:h-80 rounded-2xl overflow-hidden cursor-pointer reveal shadow-md"
                style="transition-delay: <?php echo $index * 100; ?>ms;">
                <img src="<?php echo $img; ?>" alt="<?php echo $cat['name']; ?>" loading="lazy"
                    class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-900/90 via-brand-900/30 to-transparent"></div>
                <div
                    class="absolute bottom-0 left-0 p-6 w-full transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                    <h3 class="text-3xl font-serif text-white mb-1"><?php echo $cat['name']; ?></h3>
                    <p
                        class="text-brand-gold_light text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                        <?php echo $cat['description'] ?: 'সংগ্রহ দেখুন'; ?> &rarr;
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Curated Collection Section (Suggested Books & Search Results) -->
<section id="discover"
    class="py-16 px-6 lg:px-8 max-w-7xl mx-auto bg-white rounded-3xl shadow-sm my-10 border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-end mb-12">
        <div class="max-w-xl">
            <span id="section-subtitle" class="text-brand-gold font-medium tracking-wider text-sm">আমাদের
                কালেকশন</span>
            <h2 id="section-title" class="text-4xl md:text-5xl font-serif text-brand-900 mt-2 mb-3">সাজেস্টেড বই
            </h2>
            <p id="section-desc" class="text-gray-500 font-light">আপনার জন্য আমাদের বাছাইকৃত কিছু চমৎকার বই, যা আপনি
                কিনতে বা লাইব্রেরি থেকে ধার নিতে পারেন।</p>
        </div>
        <button onclick="clearFilters()" id="clearFilterBtn"
            class="hidden mt-6 md:mt-0 px-6 py-2 border border-brand-900 text-brand-900 rounded-full hover:bg-brand-900 hover:text-white transition-colors text-sm">
            সব বই দেখুন
        </button>
    </div>

    <!-- Empty State for Search -->
    <div id="no-results" class="hidden text-center py-20">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-2xl font-serif text-brand-900">কোনো বই পাওয়া যায়নি</h3>
        <p class="text-gray-500 mt-2">অনুগ্রহ করে অন্য কোনো নাম দিয়ে খুঁজুন অথবা ক্যাটাগরি পরিবর্তন করুন।</p>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-10" id="book-grid">
        <!-- Skeletons shown initially, then JS replaces them -->
        <?php for($i=0; $i<8; $i++): ?>
            <div class="book-card reveal active">
                <div class="skeleton aspect-[2/3] rounded-md mb-4"></div>
                <div class="px-1 flex flex-col items-center">
                    <div class="skeleton skeleton-text w-1/4 mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-title mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-author"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    </div>
</section>
</section>

<!-- The Library Experience (Split Section) -->
<section id="library" class="py-24 bg-brand-900 text-white overflow-hidden relative mt-20">
    <div
        class="absolute top-0 right-0 w-64 h-64 bg-brand-gold opacity-10 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2">
    </div>
    <div
        class="absolute bottom-0 left-0 w-96 h-96 bg-brand-gold opacity-10 rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1 reveal relative">
                <div class="relative rounded-2xl overflow-hidden aspect-[4/5] shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1568667256549-094345857637?q=60&w=800&auto=format&fit=crop"
                        alt="Library Interior" loading="lazy"
                        class="object-cover w-full h-full transform hover:scale-105 transition-transform duration-1000">
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-900/80 to-transparent"></div>
                </div>
                <!-- Floating Badge -->
                <div
                    class="absolute -bottom-6 -right-6 lg:-right-12 glass-dark p-6 rounded-xl animate-float max-w-xs shadow-2xl">
                    <div class="flex items-start gap-4">
                        <div class="bg-brand-gold/20 p-3 rounded-full text-brand-gold flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-serif font-bold text-lg mt-1">আনলিমিটেড বই পড়া</h4>
                            <p class="text-sm text-gray-400 mt-1">আমাদের ৫০,০০০+ বইয়ের বিশাল সংগ্রহ থেকে আপনার
                                পছন্দের বই বেছে নিন।</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2 reveal">
                <span class="text-brand-gold font-medium tracking-wider text-sm uppercase">শুধুমাত্র একটি দোকান
                    নয়</span>
                <h2 class="text-4xl md:text-6xl font-serif mt-4 mb-6 leading-tight">আধুনিক লাইব্রেরির <br><span
                        class="italic text-brand-gold_light">অভিজ্ঞতা</span></h2>
                <p class="text-gray-300 font-light text-lg mb-8 leading-relaxed">
                    অন্ত্যমিল আপনাকে দেয় বই কেনা এবং পড়ার সম্পূর্ণ স্বাধীনতা। কোনো বই খুব পছন্দ হয়েছে? কিনে ফেলুন।
                    শুধু
                    পড়ে দেখতে চান? আমাদের মেম্বারশিপ নিয়ে খুব সহজেই ধার নিন।
                </p>

                <ul class="space-y-6 mb-10">
                    <li class="flex items-center gap-4">
                        <div class="w-1.5 h-1.5 rounded-full bg-brand-gold flex-shrink-0"></div>
                        <span class="text-gray-200">বই সরাসরি আপনার ঠিকানায় ডেলিভারি করা হবে।</span>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="w-1.5 h-1.5 rounded-full bg-brand-gold flex-shrink-0"></div>
                        <span class="text-gray-200">আমাদের দৃষ্টিনন্দন রিডিং লাউঞ্জে বসে পড়ার সুবিধা।</span>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="w-1.5 h-1.5 rounded-full bg-brand-gold flex-shrink-0"></div>
                        <span class="text-gray-200">মেম্বারদের জন্য বই কেনার ক্ষেত্রে আকর্ষণীয় ছাড়।</span>
                    </li>
                </ul>

                <a href="membership/"
                    class="inline-block px-8 py-4 bg-brand-gold text-brand-900 font-bold rounded-sm hover:bg-white transition-colors duration-300">
                    মেম্বারশিপ গ্রহণ করুন
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Membership Pricing -->
<section id="membership" class="py-24 bg-brand-light">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <h2 class="text-4xl md:text-5xl font-serif text-brand-900 mb-6">আপনার প্ল্যান বেছে নিন</h2>
            <p class="text-gray-600 font-light text-lg">আপনি মাঝে মাঝে বই পড়েন নাকি নিয়মিত—সবার জন্যই আমাদের রয়েছে
                মানানসই প্ল্যান।</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-center">

            <!-- Plan 1 -->
            <div
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-shadow duration-300 reveal">
                <h3 class="text-2xl font-serif text-brand-900 mb-2 font-bold">সাধারণ পাঠক</h3>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-4xl font-bold text-brand-900">৳২০০</span>
                    <span class="text-gray-500 text-sm">/মাস</span>
                </div>
                <ul class="space-y-4 mb-8 text-sm text-gray-600">
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> একসাথে ২টি বই ধার নিতে পারবেন</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> ডিজিটাল লাইব্রেরি অ্যাক্সেস</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> বই কেনায় ৫% ছাড়</li>
                </ul>
                <button
                    class="w-full py-3 border border-brand-900 text-brand-900 font-medium rounded hover:bg-brand-900 hover:text-white transition-colors">প্ল্যানটি
                    কিনুন</button>
            </div>

            <!-- Plan 2 (Featured) -->
            <div class="bg-brand-900 p-8 rounded-2xl shadow-2xl relative transform md:-translate-y-4 reveal"
                style="transition-delay: 100ms;">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full text-center">
                    <span
                        class="bg-brand-gold text-brand-900 text-xs font-bold uppercase tracking-wider py-1.5 px-6 rounded-full inline-block">সবচেয়ে
                        জনপ্রিয়</span>
                </div>
                <h3 class="text-2xl font-serif text-white mb-2 font-bold mt-2">বইপ্রেমী</h3>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-4xl font-bold text-white">৳৫০০</span>
                    <span class="text-gray-400 text-sm">/মাস</span>
                </div>
                <ul class="space-y-4 mb-8 text-sm text-gray-300">
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> একসাথে ৫টি বই ধার নিতে পারবেন</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> ই-বুক ও অডিওবুক অ্যাক্সেস</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> বই কেনায় ১৫% ছাড়</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> ফ্রি হোম ডেলিভারি</li>
                </ul>
                <button
                    class="w-full py-3 bg-brand-gold text-brand-900 font-bold rounded hover:bg-white transition-colors">প্ল্যানটি
                    কিনুন</button>
            </div>

            <!-- Plan 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-shadow duration-300 reveal"
                style="transition-delay: 200ms;">
                <h3 class="text-2xl font-serif text-brand-900 mb-2 font-bold">সংগ্রাহক</h3>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-4xl font-bold text-brand-900">৳১০০০</span>
                    <span class="text-gray-500 text-sm">/মাস</span>
                </div>
                <ul class="space-y-4 mb-8 text-sm text-gray-600">
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> আনলিমিটেড বই ধার</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> রেয়ার এডিশনের আর্লি অ্যাক্সেস</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> বই কেনায় ২৫% ছাড়</li>
                    <li class="flex items-center gap-3"><svg class="w-5 h-5 text-brand-gold" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg> ভিআইপি লাউঞ্জ অ্যাক্সেস</li>
                </ul>
                <button
                    class="w-full py-3 border border-brand-900 text-brand-900 font-medium rounded hover:bg-brand-900 hover:text-white transition-colors">প্ল্যানটি
                    কিনুন</button>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // Populate allBooks from DB
    allBooks = [
        <?php foreach ($all_books_db as $book): ?>
                                                                        {
                id: <?php echo $book['id']; ?>,
                title: "<?php echo addslashes($book['title']); ?>",
                author: "<?php echo addslashes($book['author']); ?>",
                price: <?php echo $book['sell_price'] ?? 0; ?>,
                img: "<?php echo getBookImage($book['cover_image'] ?? ''); ?>",
                category: "<?php echo addslashes($book['category_name'] ?? ''); ?>",
                is_borrowable: <?php echo $book['is_borrowable'] ?? 0; ?>,
                is_suggested: <?php echo $book['is_suggested'] ?? 0; ?>,
                stock_qty: <?php echo $book['stock_qty'] ?? 0; ?>
            },
        <?php endforeach; ?>
    ];

    // Initial Suggested books are now rendered by PHP directly.
    // JS allBooks array is kept for Search and Filter functionality.
    document.addEventListener('DOMContentLoaded', () => {
        const isSubscribed = localStorage.getItem('is_subscribed') === 'true';
        if (isSubscribed) {
            document.querySelectorAll('.borrow-icon').forEach(el => el.classList.add('hidden'));
        }
        console.log("Suggested books rendered by PHP. Search enabled.");
    });
</script>