<?php
$page_title = 'অন্ত্যমিল | বই ও লাইব্রেরি - প্রিমিয়াম বুকস্টোর';
$page_description = 'অন্ত্যমিল - একটি প্রিমিয়াম বুকস্টোর। এখানে আপনি বই কিনতে এবং ধার নিতে পারেন। সাহিত্য ও জ্ঞানের এক অনন্য ভান্ডার।';
$page_keywords = 'বুকস্টোর, লাইব্রেরি, বই ধার, সাহিত্য, অন্ত্যমিল, Ontomeel, Bookshop, Library, VIVAGO TECHNOLOGIES, অনলাইন লাইব্রেরি, গল্পের বই';
$path_prefix = '';
include 'includes/db_connect.php';
require_once 'includes/helpers.php';
include 'includes/header.php';

// Fetch Suggested Books (Filtered by is_suggested column)
$stmt = $pdo->query("SELECT b.*, c.name as category_name
FROM books b
LEFT JOIN categories c ON b.category_id = c.id
WHERE b.is_active = 1 AND b.is_suggested = 1
ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC LIMIT 15");
$suggested_books = $stmt->fetchAll();

// We no longer fetch all books here for optimization. 
// Search will be redirected to the library page.
$all_books_db = []; 
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
    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-[1440px] mx-auto bg-white rounded-3xl shadow-sm my-8 sm:my-10 border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 sm:mb-10">
        <div class="max-w-xl">
            <span id="section-subtitle" class="text-brand-gold font-medium tracking-wider text-xs sm:text-sm font-anek uppercase">আমাদের কালেকশন</span>
            <h2 id="section-title" class="text-3xl sm:text-4xl md:text-5xl font-serif text-brand-900 mt-1 mb-2">সাজেস্টেড বই</h2>
            <p id="section-desc" class="text-gray-500 font-light text-xs sm:text-sm leading-relaxed">আপনার জন্য আমাদের বাছাইকৃত কিছু চমৎকার বই, যা আপনি কিনতে বা লাইব্রেরি থেকে ধার নিতে পারেন।</p>
        </div>
        <button onclick="clearFilters()" id="clearFilterBtn"
            class="hidden mt-4 md:mt-0 px-5 py-2 border border-brand-900 text-brand-900 rounded-full hover:bg-brand-900 hover:text-white transition-colors text-xs font-semibold">
            সব বই দেখুন
        </button>
    </div>

    <!-- Empty State for Search -->
    <div id="no-results" class="hidden text-center py-16">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-xl font-serif text-brand-900">কোনো বই পাওয়া যায়নি</h3>
        <p class="text-gray-500 mt-1 text-xs sm:text-sm">অনুগ্রহ করে অন্য কোনো নাম দিয়ে খুঁজুন অথবা ক্যাটাগরি পরিবর্তন করুন।</p>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 sm:gap-4 md:gap-5" id="book-grid">
        <?php foreach ($suggested_books as $index => $book): 
            $book_url = !empty($book['slug']) ? 'books/' . urlencode($book['slug']) : 'book-details.php?id=' . $book['id'];
        ?>
            <div class="book-card reveal active <?php echo ($book['stock_qty'] <= 0) ? 'opacity-80' : ''; ?>" style="transition-delay: <?php echo ($index % 5) * 40; ?>ms">
                <a href="<?php echo $book_url; ?>" class="block group relative aspect-[2/3] rounded-xl overflow-hidden mb-2.5 shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100">
                    <img src="<?php echo getBookImage($book['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 <?php echo ($book['stock_qty'] <= 0) ? 'grayscale' : ''; ?>" 
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-3">
                        <span class="text-white text-[11px] font-bold uppercase tracking-wider bg-brand-gold/90 text-brand-900 px-3 py-1.5 rounded-full shadow">বিস্তারিত দেখুন</span>
                    </div>
                    <?php if ($book['stock_qty'] <= 0): ?>
                        <div class="stock-out-badge absolute top-2.5 left-2.5 bg-red-600/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-md backdrop-blur-sm">স্টক আউট</div>
                    <?php elseif ($book['stock_qty'] > 0 && $book['stock_qty'] <= 5): ?>
                        <div class="absolute top-2.5 left-2.5 bg-amber-500/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-md backdrop-blur-sm">অল্প বাকি</div>
                    <?php endif; ?>
                </a>
                <div class="px-1 text-center font-anek">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <p class="text-brand-gold text-[10px] font-bold uppercase tracking-wider truncate max-w-[120px]"><?php echo htmlspecialchars($book['category_name'] ?? 'বই'); ?></p>
                        <?php if ($book['is_borrowable']): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0" title="লাইব্রেরিতে রয়েছে"></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-brand-900 font-serif font-bold text-sm sm:text-base mb-0.5 line-clamp-1 hover:text-brand-gold transition-colors">
                        <a href="<?php echo $book_url; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                    </h3>
                    <p class="text-gray-500 text-xs font-light truncate"><?php echo htmlspecialchars($book['author']); ?></p>
                    <?php 
                        $has_discount = ($book['discount_price'] > 0 && $book['discount_price'] < $book['sell_price']);
                        $effective_price = $has_discount ? $book['discount_price'] : $book['sell_price'];
                    ?>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <span class="text-brand-900 font-bold text-sm sm:text-base font-anek">৳<?php echo bn_num($effective_price); ?></span>
                        <?php if ($has_discount): ?>
                            <span class="text-gray-400 text-xs line-through font-anek">৳<?php echo bn_num($book['sell_price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($suggested_books)): ?>
            <div class="col-span-full py-16 text-center">
                <p class="text-gray-400 font-anek text-sm">বর্তমানে কোনো সাজেস্টেড বই পাওয়া যায়নি।</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- The Library Experience (Split Section) -->
<section id="library" class="py-20 sm:py-28 bg-brand-900 text-white overflow-hidden relative">
    <!-- Ambient Glow Background Accents -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand-gold/10 rounded-full blur-3xl transform translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[30rem] h-[30rem] bg-brand-gold/10 rounded-full blur-3xl transform -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            <!-- Visual Showcase Column (Left / 5 Cols) -->
            <div class="lg:col-span-5 reveal">
                <div class="relative group">
                    <!-- Glowing Outline / Card Frame -->
                    <div class="absolute -inset-1.5 bg-gradient-to-tr from-brand-gold/40 via-brand-gold/10 to-transparent rounded-3xl blur-md opacity-70 group-hover:opacity-100 transition duration-1000"></div>
                    
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/5] shadow-2xl bg-brand-800 border border-white/10">
                        <img src="https://images.unsplash.com/photo-1568667256549-094345857637?q=80&w=1000&auto=format&fit=crop"
                            alt="অন্ত্যমিল আধুনিক লাইব্রেরি" loading="lazy"
                            class="object-cover w-full h-full transform group-hover:scale-105 transition-transform duration-1000">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-900 via-brand-900/30 to-transparent"></div>

                        <!-- Floating Feature Badge over image -->
                        <div class="absolute bottom-6 left-6 right-6 p-4 rounded-xl bg-brand-900/80 backdrop-blur-md border border-white/10 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-brand-gold/20 flex items-center justify-center text-brand-gold shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-anek font-bold text-white text-sm">‘বইয়ের আনন্দ’ লাইব্রেরি</p>
                                    <p class="font-anek text-xs text-gray-300">হাজারো বই পড়ার অফুরন্ত সুযোগ</p>
                                </div>
                            </div>
                            <span class="text-xs font-anek font-bold text-brand-gold bg-brand-gold/10 px-2.5 py-1 rounded-full border border-brand-gold/30">একটিভ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content & Features Column (Right / 7 Cols) -->
            <div class="lg:col-span-7 reveal">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-gold/10 border border-brand-gold/20 text-brand-gold text-xs font-anek font-bold tracking-wider uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-brand-gold animate-pulse"></span>
                    শুধু বই কেনা নয়, এক নতুন অভিজ্ঞতা
                </div>

                <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif leading-tight text-white mb-6">
                    আধুনিক লাইব্রেরির <br class="hidden sm:inline"><span class="italic text-brand-gold">অনন্য সান্নিধ্য</span>
                </h2>

                <p class="text-gray-300 font-anek font-light text-base sm:text-lg mb-8 leading-relaxed max-w-2xl">
                    অন্ত্যমিল আপনাকে দেয় বই কেনা এবং পড়ার সম্পূর্ণ স্বাধীনতা। নতুন বই সংগ্রহ করতে চান কিংবা নিয়মিত পছন্দের বই ধার নিয়ে পড়তে চান—আমাদের সমৃদ্ধ লাইব্রেরি ও সহজ মেম্বারশিপ সার্ভিস আপনার পড়াশোনাকে করবে আরও সাবলীল।
                </p>

                <!-- Feature Grid / Highlight Points -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-9">
                    <div class="p-4 rounded-xl bg-brand-800/60 border border-white/5 hover:border-brand-gold/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-gold/15 flex items-center justify-center text-brand-gold shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-anek font-bold text-white text-sm sm:text-base mb-1">সহজ বই ধার সুবিধা</h4>
                                <p class="font-anek text-xs sm:text-sm text-gray-400 font-light">অনলাইনে পছন্দের বই সিলেক্ট করে যেকোনো সময় ধার নেওয়ার সহজ ব্যবস্থা।</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-brand-800/60 border border-white/5 hover:border-brand-gold/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-gold/15 flex items-center justify-center text-brand-gold shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-anek font-bold text-white text-sm sm:text-base mb-1">দ্রুত ডেলিভারি সার্ভিস</h4>
                                <p class="font-anek text-xs sm:text-sm text-gray-400 font-light">আপনার পছন্দের বই সরাসরি আপনার দোরগোড়ায় পৌঁছে দেওয়ার নির্ভরযোগ্য ব্যবস্থা।</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-brand-800/60 border border-white/5 hover:border-brand-gold/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-gold/15 flex items-center justify-center text-brand-gold shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-anek font-bold text-white text-sm sm:text-base mb-1">বই ক্রয়ে বিশেষ ছাড়</h4>
                                <p class="font-anek text-xs sm:text-sm text-gray-400 font-light">আমাদের মেম্বারদের জন্য বই কেনায় রয়েছে সর্বোচ্চ ১০% পর্যন্ত নিশ্চিত ছাড়।</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-brand-800/60 border border-white/5 hover:border-brand-gold/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-gold/15 flex items-center justify-center text-brand-gold shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-anek font-bold text-white text-sm sm:text-base mb-1">এক্সপেরিয়েন্স বুথ ও লাউঞ্জ</h4>
                                <p class="font-anek text-xs sm:text-sm text-gray-400 font-light">নেসক্যাফে এক্সপেরিয়েন্স বুথ ও মনোরম পরিবেশে বই পড়ার চমৎকার সুযোগ।</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to action buttons -->
                <div class="flex flex-wrap items-center gap-4">
                    <a href="library/"
                        class="px-7 py-3.5 bg-brand-gold text-brand-900 font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300 shadow-md shadow-brand-gold/20 flex items-center gap-2">
                        <span>লাইব্রেরি এক্সপ্লোর করুন</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a href="#membership"
                        class="px-7 py-3.5 bg-white/10 hover:bg-white/15 border border-white/20 text-white font-anek font-semibold text-sm sm:text-base rounded-xl transition-all duration-300 backdrop-blur-sm">
                        মেম্বারশিপ সুবিধা দেখুন
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Membership Pricing Section -->
<section id="membership" class="py-20 sm:py-24 bg-brand-light relative border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-12 sm:mb-14 reveal">
            <span class="text-brand-gold font-medium tracking-wider text-xs sm:text-sm font-anek uppercase">সদস্যপদ প্ল্যান</span>
            <h2 class="text-3xl sm:text-4xl font-serif text-brand-900 mt-1 mb-3">আপনার প্ল্যান বেছে নিন</h2>
            <p class="text-gray-600 font-light text-xs sm:text-sm font-anek">আপনি মাঝে মাঝে বই পড়েন নাকি নিয়মিত—সবার জন্যই আমাদের রয়েছে মানানসই প্ল্যান।</p>
        </div>

        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 items-stretch">

            <!-- General Reader Plan -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-md border border-gray-100 hover:shadow-xl transition-all duration-300 reveal flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-xl font-anek font-bold text-brand-900 mb-1">সাধারণ পাঠক</h3>
                    <p class="text-gray-500 text-xs sm:text-sm mb-4 font-anek">বই ও সাহিত্যের সান্নিধ্যে যারা থাকতে ভালোবাসেন।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-900 font-anek">৳৫০০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-600 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৫% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=General"
                    class="block text-center w-full py-3 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-md text-sm sm:text-base">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Regular Reader Plan (Featured) -->
            <div class="bg-brand-900 p-6 sm:p-7 rounded-2xl shadow-xl relative border border-brand-gold/40 hover:border-brand-gold transition-all duration-300 reveal flex flex-col justify-between h-full" style="transition-delay: 100ms;">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-brand-gold text-brand-900 px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider font-anek shadow-md">
                    সর্বাধিক জনপ্রিয়
                </div>
                <div>
                    <h3 class="text-xl font-anek font-bold text-white mb-1">নিয়মিত পাঠক</h3>
                    <p class="text-gray-400 text-xs sm:text-sm mb-4 font-anek">যাদের নিত্যদিনের সঙ্গী প্রিয় বই।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-gold font-anek">৳৭৫০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-300 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ ও ব্র্যান্ডেড টি-শার্ট</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ৮% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=BookLover"
                    class="block text-center w-full py-3 rounded-xl bg-brand-gold text-brand-900 font-anek font-bold hover:bg-white transition-all shadow-md text-sm sm:text-base">প্ল্যানটি বেছে নিন</a>
            </div>

            <!-- Literature Enthusiast Plan -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-md border border-gray-100 hover:shadow-xl transition-all duration-300 reveal flex flex-col justify-between h-full" style="transition-delay: 200ms;">
                <div>
                    <h3 class="text-xl font-anek font-bold text-brand-900 mb-1">সাহিত্য অনুরাগী</h3>
                    <p class="text-gray-500 text-xs sm:text-sm mb-4 font-anek">প্রকৃত সাহিত্যপ্রেমী ও সংগ্রাহকদের জন্য।</p>
                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-3xl sm:text-4xl font-bold text-brand-900 font-anek">৳১০০০</span>
                    </div>
                    <ul class="space-y-2.5 mb-6 text-gray-600 font-anek text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>টোট ব্যাগ, ব্র্যান্ডেড টি-শার্ট, বুকমার্ক ও কীরিং</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>বই ক্রয়ে সর্বোচ্চ ১০% পর্যন্ত ছাড়</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>‘বইয়ের আনন্দ’ কমিউনিটি লাইব্রেরি থেকে বই ধার সুবিধা</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>নেসক্যাফে এক্সপেরিয়েন্স বুথ ব্যবহার সুবিধা</span>
                        </li>
                    </ul>
                </div>
                <a href="membership/request.php?plan=Collector"
                    class="block text-center w-full py-3 rounded-xl bg-brand-900 text-white font-anek font-bold hover:bg-brand-gold hover:text-brand-900 transition-all shadow-md text-sm sm:text-base">প্ল্যানটি বেছে নিন</a>
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