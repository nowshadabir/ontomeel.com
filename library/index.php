<?php
$page_title = 'লাইব্রেরি | সব ধরনের বইয়ের বিশাল সংগ্রহ - অন্ত্যমিল';
$page_description = 'অন্ত্যমিল লাইব্রেরি - গল্প, উপন্যাস, কবিতা এবং আরও অনেক বিষয়ের বইয়ের এক অফুরন্ত ভান্ডার। অনলাইনে বই খুঁজুন এবং আপনার পছন্দের বইটি অর্ডার করুন।';
$page_keywords = 'লাইব্রেরি, বইয়ের তালিকা, গল্পের বই, উপন্যাস, অন্ত্যমিল, Vivago Digital, Online Library, Book Collection';
$path_prefix = '../';
include '../includes/db_connect.php';
include '../includes/header.php';

// Fetch All Books for Library
$stmt = $pdo->query("SELECT b.*, c.name as category_name 
                    FROM books b 
                    LEFT JOIN categories c ON b.category_id = c.id 
                    WHERE b.is_active = 1
                    ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC");
$all_temp = $stmt->fetchAll();

// Separate in-stock and out-of-stock
$in_stock = array_filter($all_temp, function ($b) {
    return $b['stock_qty'] > 0;
});
$out_of_stock = array_filter($all_temp, function ($b) {
    return $b['stock_qty'] <= 0;
});

// Keep top 12 recent IN-STOCK books, shuffle the rest of IN-STOCK
$in_stock_recent = array_slice($in_stock, 0, 12);
$in_stock_remaining = array_slice($in_stock, 12);
shuffle($in_stock_remaining);

// Out of stock can be shuffled too, but they will be at the bottom
shuffle($out_of_stock);

$all_books_db = array_merge($in_stock_recent, $in_stock_remaining, $out_of_stock);

function getBookImage($image)
{
    if (!empty($image)) {
        return '../admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}
?>

<!-- Library Header -->
<div class="pt-32 pb-16 bg-brand-900 relative overflow-hidden">
    <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-anek font-extrabold text-white mb-6">আমাদের লাইব্রেরি</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg mb-10">আপনার পছন্দের বই খুঁজে নিন আমাদের বিশাল সংগ্রহ
            থেকে। কেনা বা ধার নেওয়ার জন্য হাজারো বইয়ের তালিকা।</p>

        <!-- Dedicated Search Bar -->
        <div class="max-w-3xl mx-auto relative group">
            <input type="text" id="librarySearchInput" onkeyup="searchBooksLibrary(event)"
                placeholder="বইয়ের নাম অথবা লেখকের নাম দিয়ে খুঁজুন..."
                class="w-full bg-white/80 border border-gray-200 rounded-2xl px-8 py-5 text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-lg">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-brand-gold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Books Collection Section -->
<section class="py-20 px-6 lg:px-8 max-w-7xl mx-auto min-h-[600px]">
    <div class="flex justify-between items-end mb-12 border-b border-gray-200 pb-8">
        <div>
            <span id="section-subtitle"
                class="text-brand-gold font-medium tracking-wider text-sm uppercase">সংগ্রহশালা</span>
            <h2 id="section-title" class="text-3xl md:text-4xl font-anek font-bold text-brand-900 mt-2">সব বইয়ের
                তালিকা</h2>
        </div>
        <div class="text-gray-500 text-sm font-medium">
            মোট <span id="book-count-total" class="text-brand-900 font-bold">০</span>টি বই পাওয়া গেছে
        </div>
    </div>

    <!-- Empty State -->
    <div id="no-results" class="hidden text-center py-32 bg-white rounded-3xl shadow-sm border border-gray-100">
        <svg class="w-20 h-20 text-gray-200 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-2xl font-anek font-bold text-brand-900">দুঃখিত, কোনো বই খুঁজে পাওয়া যায়নি!</h3>
        <p class="text-gray-500 mt-3 font-light text-lg">অনুগ্রহ করে সঠিক বানান চেক করুন অথবা অন্য নাম দিয়ে চেষ্টা
            করুন।</p>
        <button onclick="clearLibrarySearch()"
            class="mt-8 text-brand-gold font-bold hover:underline flex items-center gap-2 mx-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            সব বই পুনরায় দেখান
        </button>
    </div>

    <!-- Library Books Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12" id="library-book-grid">
        <!-- Skeletons shown initially, then JS replaces them -->
        <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="book-card reveal active">
                <div class="skeleton aspect-[2/3] rounded-md mb-4"></div>
                <div class="px-1 flex flex-col items-center">
                    <div class="skeleton skeleton-text w-1/4 mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-title mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-author"></div>
                </div>
            </div>
        <?php
endfor; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
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
        <?php
endforeach; ?>
    ];

    // Initial render for library
    document.addEventListener('DOMContentLoaded', () => {
        renderBooks(allBooks);
    });
</script>
</body>

</html>