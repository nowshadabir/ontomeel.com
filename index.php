<?php
$page_title = 'অন্ত্যমিল | বই ও লাইব্রেরি - প্রিমিয়াম বুকস্টোর';
$page_description = 'অন্ত্যমিল - একটি প্রিমিয়াম বুকস্টোর। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন। সাহিত্য ও জ্ঞানের এক অনন্য ভান্ডার।';
$page_keywords = 'বুকস্টোর, লাইব্রেরি, বই ধার, সাহিত্য, অন্ত্যমিল, Ontomeel, Bookshop, Library, VIVAGO TECHNOLOGIES, অনলাইন লাইব্রেরি, গল্পের বই';
$path_prefix = '';
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch Suggested Books (Filtered by is_suggested column)
$stmt = $pdo->query("SELECT b.*, c.name as category_name
FROM books b
LEFT JOIN categories c ON b.category_id = c.id
WHERE b.is_active = 1 AND b.is_suggested = 1
ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC LIMIT 12");
$suggested_books = $stmt->fetchAll();

// We no longer fetch all books here for optimization. 
// Search will be redirected to the library page.
$all_books_db = []; 

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

<!-- Hero Section (Compact & Refined) -->
<header class="relative overflow-hidden bg-brand-900 pt-28 pb-14 sm:pt-32 sm:pb-18 md:pt-36 md:pb-20 flex items-center justify-center">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="assets/img/image-og.jpeg" class="w-full h-full object-cover opacity-30" alt="Background">
        <div class="absolute inset-0 bg-gradient-to-b from-brand-900/90 via-brand-900/60 to-brand-900"></div>
        <div class="absolute inset-0 mesh-gradient opacity-20"></div>
    </div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-brand-gold/15 border border-brand-gold/30 text-brand-gold text-xs font-anek font-semibold mb-4 shadow-sm animate-slide-up">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-gold animate-pulse"></span>
            <span>প্রিমিয়াম বুকস্টোর ও লাইব্রেরি</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-anek font-extrabold text-white mb-4 leading-tight tracking-tight animate-slide-up"
            style="animation-delay: 0.15s;">
            পড়ুন, ধার নিন, <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-gold via-amber-200 to-brand-gold">সংগ্রহ করুন।</span>
        </h1>

        <p class="text-gray-300 text-xs sm:text-sm md:text-base font-light mb-7 max-w-xl mx-auto leading-relaxed animate-slide-up"
            style="animation-delay: 0.25s;">
            অন্ত্যমিল—একটি আধুনিক বুকস্টোর ও রিডিং লাইব্রেরির মেলবন্ধন। আমাদের সমৃদ্ধ সংগ্রহশালা থেকে পছন্দের বই কিনুন অথবা মেম্বারশিপে ধার নিন।
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3 animate-slide-up"
            style="animation-delay: 0.35s;">
            <a href="#discover"
                class="px-6 sm:px-7 py-3 bg-brand-gold text-brand-900 font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-white hover:shadow-lg transition-all duration-200 shadow-md shadow-brand-gold/20 flex items-center gap-2">
                <span>বই কালেকশন দেখুন</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </a>
            <a href="membership/"
                class="px-6 sm:px-7 py-3 bg-white/10 hover:bg-white/15 border border-white/20 text-white font-anek font-semibold text-sm sm:text-base rounded-xl transition-all duration-200 backdrop-blur-md">
                মেম্বারশিপ প্ল্যান
            </a>
        </div>
    </div>
</header>

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
        <?php foreach ($suggested_books as $index => $book): ?>
            <div class="book-card reveal active <?php echo ($book['stock_qty'] <= 0) ? 'opacity-80' : ''; ?>" style="transition-delay: <?php echo $index * 50; ?>ms">
                <a href="book-details.php?id=<?php echo $book['id']; ?>" class="block group relative aspect-[2/3] rounded-2xl overflow-hidden mb-6 shadow-sm hover:shadow-2xl transition-all duration-500">
                    <img src="<?php echo getBookImage($book['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 <?php echo ($book['stock_qty'] <= 0) ? 'grayscale' : ''; ?>" 
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end p-6">
                        <span class="text-white text-xs font-bold uppercase tracking-widest bg-brand-gold/80 px-4 py-2 rounded-full">বিস্তারিত দেখুন</span>
                    </div>
                    <?php if ($book['stock_qty'] <= 0): ?>
                        <div class="stock-out-badge absolute top-4 left-4 bg-red-600/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg backdrop-blur-sm">স্টক আউট</div>
                    <?php elseif ($book['stock_qty'] > 0 && $book['stock_qty'] <= 5): ?>
                        <div class="absolute top-4 left-4 bg-amber-500/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg backdrop-blur-sm">অল্প কিছু বাকি</div>
                    <?php endif; ?>
                </a>
                <div class="px-2 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <p class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.2em]"><?php echo htmlspecialchars($book['category_name'] ?? 'বই'); ?></p>
                        <?php if ($book['is_borrowable']): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500" title="লাইব্রেরিতে রয়েছে"></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-brand-900 font-serif font-bold text-lg md:text-xl mb-1 line-clamp-1 hover:text-brand-gold transition-colors">
                        <a href="book-details.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                    </h3>
                    <p class="text-gray-500 text-xs md:text-sm italic font-light"><?php echo htmlspecialchars($book['author']); ?></p>
                    <?php 
                        $has_discount = ($book['discount_price'] > 0 && $book['discount_price'] < $book['sell_price']);
                        $effective_price = $has_discount ? $book['discount_price'] : $book['sell_price'];
                    ?>
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <span class="text-brand-900 font-bold text-lg font-anek">৳<?php echo bn_num($effective_price); ?></span>
                        <?php if ($has_discount): ?>
                            <span class="text-gray-400 text-xs line-through font-anek">৳<?php echo bn_num($book['sell_price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($suggested_books)): ?>
            <div class="col-span-full py-20 text-center">
                <p class="text-gray-400 font-anek">বর্তমানে কোনো সাজেস্টেড বই পাওয়া যায়নি।</p>
            </div>
        <?php endif; ?>
    </div>
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

            <!-- General Reader Plan -->
            <div class="bg-white p-10 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সাধারণ পাঠক</h3>
                    <p class="text-gray-500 text-sm mb-8 font-anek">বই ও সাহিত্যের সান্নিধ্যে যারা থাকতে ভালোবাসেন।</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-brand-900 font-anek">৳৫০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-600 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৫% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি  লাইব্রেরি থেকে বই ধার নেওয়ার সুযোগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=General"
                    class="block text-center w-full py-4 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Regular Reader Plan (Featured) -->
            <div class="bg-brand-900 p-12 rounded-3xl shadow-2xl relative transform lg:scale-105 border border-brand-gold/30 hover:border-brand-gold hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full" style="transition-delay: 100ms;">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold text-brand-900 px-6 py-2 rounded-full text-xs font-bold uppercase tracking-widest font-anek shadow-xl">
                    সর্বাধিক জনপ্রিয়
                </div>
                <div>
                    <h3 class="text-2xl font-anek font-bold text-white mb-2">নিয়মিত পাঠক</h3>
                    <p class="text-gray-400 text-sm mb-8 font-anek">যাদের নিত্যদিনের সঙ্গী প্রিয় বই।</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-white font-anek text-gradient-gold">৳৭০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-300 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ ও ব্র্যান্ডেড টি-শার্ট</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৮% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি  লাইব্রেরি থেকে বই ধার নেওয়ার সুযোগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=BookLover"
                    class="block text-center w-full py-4 rounded-xl bg-brand-gold text-brand-900 font-anek font-bold hover:bg-white transition-all shadow-xl shadow-brand-gold/20 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Literature Enthusiast Plan -->
            <div class="bg-white p-10 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 reveal flex flex-col justify-between h-full" style="transition-delay: 200ms;">
                <div>
                    <h3 class="text-2xl font-anek font-bold text-brand-900 mb-2">সাহিত্য অনুরাগী</h3>
                    <p class="text-gray-500 text-sm mb-8 font-anek">প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য।</p>
                    <div class="flex items-baseline gap-1 mb-8">
                        <span class="text-5xl font-bold text-brand-900 font-anek">৳১০০০</span>
                    </div>
                    <ul class="space-y-4 mb-10 text-gray-600 font-anek text-base">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ, ব্র্যান্ডেড টি-শার্ট, বুকমার্ক/পোস্টকার্ড ও কীরিং</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ১০% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি  লাইব্রেরি থেকে বই ধার নেওয়ার সুযোগ</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=Collector"
                    class="block text-center w-full py-4 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-lg shadow-brand-900/10 text-lg">প্ল্যানটি বেছে নিন</a>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // Populate allBooks from DB
    allBooks = [];

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