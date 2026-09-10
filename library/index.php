<?php
$page_title = 'লাইব্রেরি | সব ধরনের বইয়ের বিশাল সংগ্রহ - অন্ত্যমিল';
$page_description = 'অন্ত্যমিল লাইব্রেরি - গল্প, উপন্যাস, কবিতা এবং আরও অনেক বিষয়ের বইয়ের এক অফুরন্ত ভান্ডার। আধুনিক ও উন্নত অনুসন্ধানে আপনার পছন্দের বইটি খুঁজুন।';
$page_keywords = 'লাইব্রেরি, বইয়ের তালিকা, গল্পের বই, উপন্যাস, অন্ত্যমিল, VIVAGO TECHNOLOGIES, A Premium Library, Book Collection, Advanced Book Search';
$path_prefix = '../';
require_once __DIR__ . '/../includes/db_connect.php';
include __DIR__ . '/../includes/header.php';

// Helper for escaping SQL LIKE wildcards
function escapeLike($str) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $str);
}

// 1. Sanitize & Validate GET parameters for Initial SSR
$raw_search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
if (mb_strlen($raw_search, 'UTF-8') > 100) {
    $raw_search = mb_substr($raw_search, 0, 100, 'UTF-8');
}

$search_by = isset($_GET['search_by']) ? trim((string)$_GET['search_by']) : 'all';
$allowed_search_by = ['all', 'title', 'author', 'publisher', 'isbn'];
if (!in_array($search_by, $allowed_search_by, true)) {
    $search_by = 'all';
}

$category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
if (mb_strlen($category, 'UTF-8') > 100) {
    $category = mb_substr($category, 0, 100, 'UTF-8');
}

$stock = isset($_GET['stock']) ? trim((string)$_GET['stock']) : 'all';
$allowed_stocks = ['all', 'in_stock', 'out_of_stock'];
if (!in_array($stock, $allowed_stocks, true)) {
    $stock = 'all';
}

$borrowable = isset($_GET['borrowable']) ? trim((string)$_GET['borrowable']) : 'all';
$allowed_borrowable = ['all', '1', '0'];
if (!in_array($borrowable, $allowed_borrowable, true)) {
    $borrowable = 'all';
}

$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) && (float)$_GET['min_price'] >= 0 ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) && (float)$_GET['max_price'] >= 0 ? (float)$_GET['max_price'] : null;

$sort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : 'relevance';
$allowed_sorts = ['relevance', 'price_asc', 'price_desc', 'newest', 'title_asc', 'author_asc'];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = 'relevance';
}

// Fetch all available categories with active book counts for the filter menu
$cats_stmt = $pdo->query("SELECT c.id, c.name, COUNT(b.id) as book_count 
                          FROM categories c 
                          LEFT JOIN books b ON c.id = b.category_id AND b.is_active = 1 
                          GROUP BY c.id, c.name 
                          ORDER BY (COUNT(b.id) > 0) DESC, c.name ASC");
$all_categories = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build Initial SSR Query
$where_clauses = ["b.is_active = 1"];
$params = [];
$score_params = [];

if (!empty($category) && $category !== 'all') {
    if (is_numeric($category)) {
        $where_clauses[] = "b.category_id = ?";
        $params[] = (int)$category;
    } else {
        $where_clauses[] = "(c.name = ? OR c.id = ?)";
        $params[] = $category;
        $params[] = is_numeric($category) ? (int)$category : 0;
    }
}

if ($stock === 'in_stock') {
    $where_clauses[] = "b.stock_qty > 0";
} elseif ($stock === 'out_of_stock') {
    $where_clauses[] = "b.stock_qty <= 0";
}

if ($borrowable === '1') {
    $where_clauses[] = "b.is_borrowable = 1";
} elseif ($borrowable === '0') {
    $where_clauses[] = "b.is_borrowable = 0";
}

if ($min_price !== null) {
    $where_clauses[] = "b.sell_price >= ?";
    $params[] = $min_price;
}
if ($max_price !== null) {
    $where_clauses[] = "b.sell_price <= ?";
    $params[] = $max_price;
}

$has_search = !empty($raw_search);
if ($has_search) {
    $tokens = preg_split('/\s+/', $raw_search, 5, PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        $escaped_token = '%' . escapeLike($token) . '%';
        if ($search_by === 'title') {
            $where_clauses[] = "(b.title LIKE ? OR b.title_en LIKE ? OR b.subtitle LIKE ?)";
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
        } elseif ($search_by === 'author') {
            $where_clauses[] = "(b.author LIKE ? OR b.author_en LIKE ? OR b.co_author LIKE ?)";
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
        } elseif ($search_by === 'publisher') {
            $where_clauses[] = "(b.publisher LIKE ?)";
            $params[] = $escaped_token;
        } elseif ($search_by === 'isbn') {
            $where_clauses[] = "(b.isbn LIKE ?)";
            $params[] = $escaped_token;
        } else {
            $where_clauses[] = "(b.title LIKE ? OR b.author LIKE ? OR b.title_en LIKE ? OR b.author_en LIKE ? OR b.co_author LIKE ? OR b.publisher LIKE ? OR b.isbn LIKE ? OR b.genre LIKE ? OR c.name LIKE ?)";
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
            $params[] = $escaped_token;
        }
    }
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Count Total
$count_query = "SELECT COUNT(*) FROM books b LEFT JOIN categories c ON b.category_id = c.id $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_books_count = (int)$count_stmt->fetchColumn();

// Sorting
$order_by_sql = "";
$order_params = [];
if ($sort === 'price_asc') {
    $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.sell_price ASC, b.id DESC";
} elseif ($sort === 'price_desc') {
    $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.sell_price DESC, b.id DESC";
} elseif ($sort === 'newest') {
    $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC, b.id DESC";
} elseif ($sort === 'title_asc') {
    $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.title ASC, b.id DESC";
} elseif ($sort === 'author_asc') {
    $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.author ASC, b.id DESC";
} else {
    if ($has_search) {
        $exact_search = escapeLike($raw_search);
        $prefix_search = $exact_search . '%';
        $contains_search = '%' . $exact_search . '%';
        $order_by_sql = "ORDER BY 
            (b.stock_qty > 0) DESC,
            (CASE 
                WHEN b.title = ? THEN 1
                WHEN b.title LIKE ? THEN 2
                WHEN b.title LIKE ? THEN 3
                WHEN b.author LIKE ? THEN 4
                WHEN b.title_en LIKE ? THEN 5
                WHEN b.author_en LIKE ? THEN 6
                WHEN b.isbn = ? THEN 7
                ELSE 8
            END) ASC,
            b.created_at DESC";
        $order_params = [
            $raw_search,
            $prefix_search,
            $contains_search,
            $contains_search,
            $contains_search,
            $contains_search,
            $raw_search
        ];
    } else {
        $order_by_sql = "ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC, b.id DESC";
    }
}

// Initial limit: 40 items per page
$limit = 40;
$query = "SELECT b.id, b.title, b.title_en, b.author, b.author_en, b.publisher, b.isbn, b.sell_price, b.discount_price, b.cover_image, b.stock_qty, b.is_borrowable, b.is_suggested, b.format, c.name as category_name 
          FROM books b 
          LEFT JOIN categories c ON b.category_id = c.id 
          $where_sql 
          $order_by_sql 
          LIMIT $limit";

$stmt = $pdo->prepare($query);
$stmt->execute(array_merge($params, $order_params));
$initial_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getBookImage($image)
{
    if (!empty($image)) {
        return '../admin/assets/book-images/' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

function bn_num($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, (string)$num);
}
?>

<style>
/* Custom slim neutral scrollbar for autocomplete dropdown */
.autocomplete-scroll::-webkit-scrollbar {
    width: 5px;
}
.autocomplete-scroll::-webkit-scrollbar-track {
    background: #f9fafb;
    border-radius: 8px;
}
.autocomplete-scroll::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 8px;
}
.autocomplete-scroll::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

<!-- Library Search Hero Section -->
<div class="pt-36 sm:pt-40 pb-16 sm:pb-20 bg-brand-900 relative">
    <!-- Background overlay with independent overflow hidden -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-brand-gold/15 border border-brand-gold/30 text-brand-gold text-xs font-anek font-semibold mb-4 shadow-sm animate-slide-up">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-gold animate-pulse"></span>
            <span>স্মার্ট ও অ্যাডভান্সড বুক সার্চ</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-anek font-extrabold text-white mb-4 leading-tight tracking-tight animate-slide-up" style="animation-delay: 0.1s;">
            আমাদের লাইব্রেরি
        </h1>
        <p class="text-gray-300 max-w-2xl mx-auto text-xs sm:text-sm md:text-base font-light mb-8 leading-relaxed animate-slide-up" style="animation-delay: 0.2s;">
            বইয়ের নাম, প্রিয় লেখক, প্রকাশক অথবা বিষয় দিয়ে দ্রুত খুঁজুন আমাদের সমৃদ্ধ সংগ্রহশালা থেকে।
        </p>

        <!-- Advanced Search Box Container (High Z-Index for Dropdown) -->
        <div class="relative max-w-3xl mx-auto z-30 animate-slide-up" style="animation-delay: 0.3s;">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-2 sm:p-2.5 shadow-2xl border border-white/20 flex flex-col md:flex-row items-center gap-2">
                
                <!-- Search Scope Selector -->
                <div class="w-full md:w-auto relative flex-shrink-0">
                    <select id="searchScopeSelect" onchange="onScopeChange()" 
                        class="w-full md:w-auto appearance-none bg-gray-100 hover:bg-gray-200/70 text-brand-900 font-anek text-xs sm:text-sm font-bold rounded-xl pl-3.5 pr-8 py-3 focus:outline-none focus:ring-2 focus:ring-brand-gold/50 cursor-pointer transition-all border-0">
                        <option value="all" <?php echo $search_by === 'all' ? 'selected' : ''; ?>>🔍 সার্বিক খোঁজ</option>
                        <option value="title" <?php echo $search_by === 'title' ? 'selected' : ''; ?>>📖 বইয়ের নাম</option>
                        <option value="author" <?php echo $search_by === 'author' ? 'selected' : ''; ?>>✍️ লেখক</option>
                        <option value="publisher" <?php echo $search_by === 'publisher' ? 'selected' : ''; ?>>🏢 প্রকাশক</option>
                        <option value="isbn" <?php echo $search_by === 'isbn' ? 'selected' : ''; ?>>🏷️ আইএসবিএন</option>
                    </select>
                    <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs">
                        ▼
                    </div>
                </div>

                <!-- Search Input Field -->
                <div class="relative flex-1 w-full">
                    <input type="text" id="librarySearchInput" 
                        value="<?php echo htmlspecialchars($raw_search, ENT_QUOTES, 'UTF-8'); ?>"
                        oninput="onSearchInput(event)"
                        onkeydown="onSearchKeyDown(event)"
                        placeholder="বইয়ের নাম, লেখক, প্রকাশক বা বিষয় লিখুন..."
                        autocomplete="off"
                        class="w-full bg-transparent border-0 px-4 py-2.5 sm:py-3 text-brand-900 placeholder-gray-400 focus:outline-none font-anek text-sm sm:text-base">
                    
                    <!-- Search Controls: Clear & Spinner -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                        <!-- Clear Button -->
                        <button type="button" id="clearSearchBtn" onclick="clearLibrarySearch()"
                            class="<?php echo empty($raw_search) ? 'hidden' : ''; ?> text-gray-400 hover:text-brand-900 p-1 rounded-full hover:bg-gray-100 transition-colors" title="মুছে ফেলুন">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Spinner Indicator -->
                        <svg id="search-spinner" class="w-5 h-5 hidden animate-spin text-brand-gold" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Submit / Search Button -->
                <button type="button" onclick="triggerSearchNow()"
                    class="w-full md:w-auto px-6 py-3 bg-brand-gold hover:bg-amber-400 text-brand-900 font-anek font-bold text-sm sm:text-base rounded-xl transition-all shadow-md flex items-center justify-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>খুঁজুন</span>
                </button>
            </div>

            <!-- Live Autocomplete Suggestions Dropdown -->
            <div id="autocompleteDropdown" 
                class="hidden absolute left-0 right-0 top-[calc(100%+8px)] bg-white rounded-2xl shadow-2xl border border-gray-100/90 overflow-hidden z-50 text-left ring-1 ring-black/10 animate-slide-up">
                <div id="autocompleteResults" class="divide-y divide-gray-100 max-h-[360px] overflow-y-auto autocomplete-scroll"></div>
                <div id="autocompleteFooter" class="p-3 bg-gray-50/90 text-center border-t border-gray-100">
                    <button type="button" onclick="triggerSearchNow()" class="text-xs sm:text-sm font-anek font-bold text-brand-gold hover:text-brand-900 transition-colors inline-flex items-center gap-1">
                        <span>সব ফলাফল দেখুন</span>
                        <span>&raquo;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Filter Toggle & Category Pills -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5 animate-slide-up" style="animation-delay: 0.4s;">
            <button type="button" onclick="toggleFilterDrawer()" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-anek text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span>অ্যাডভান্সড ফিল্টার</span>
                <span id="activeFiltersBadge" class="hidden px-2 py-0.5 rounded-full bg-brand-gold text-brand-900 font-bold text-[10px]">0</span>
            </button>

            <!-- Quick Category Pill Shortcut -->
            <?php foreach (array_slice($all_categories, 0, 4) as $qcat): 
                if ($qcat['book_count'] > 0 || $category === $qcat['name']): ?>
                <button type="button" onclick="selectCategory('<?php echo htmlspecialchars($qcat['name'], ENT_QUOTES, 'UTF-8'); ?>')"
                    class="px-3.5 py-2 rounded-xl text-xs font-anek font-medium transition-all <?php echo $category === $qcat['name'] ? 'bg-brand-gold text-brand-900 font-bold shadow-md' : 'bg-white/10 text-gray-300 hover:bg-white/15 hover:text-white border border-white/10'; ?>">
                    <?php echo htmlspecialchars($qcat['name']); ?> (<?php echo bn_num($qcat['book_count']); ?>)
                </button>
            <?php endif; endforeach; ?>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel (Collapsible) -->
<section id="advancedFilterPanel" class="hidden bg-gray-50/90 border-b border-gray-200 transition-all duration-300 shadow-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Filter 1: Category -->
            <div>
                <label class="block text-xs font-anek font-bold text-gray-700 uppercase tracking-wider mb-2">
                    বইয়ের ক্যাটাগরি
                </label>
                <select id="filterCategory" onchange="applyFilters()"
                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-anek text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold">
                    <option value="all">সব ক্যাটাগরি (<?php echo bn_num($total_books_count); ?>)</option>
                    <?php foreach ($all_categories as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $c['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']); ?> (<?php echo bn_num($c['book_count']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter 2: Stock & Availability -->
            <div>
                <label class="block text-xs font-anek font-bold text-gray-700 uppercase tracking-wider mb-2">
                    প্রাপ্যতা / স্টক
                </label>
                <select id="filterStock" onchange="applyFilters()"
                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-anek text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold">
                    <option value="all" <?php echo $stock === 'all' && $borrowable === 'all' ? 'selected' : ''; ?>>সব বই</option>
                    <option value="in_stock" <?php echo $stock === 'in_stock' ? 'selected' : ''; ?>>✅ স্টকে আছে</option>
                    <option value="borrowable" <?php echo $borrowable === '1' ? 'selected' : ''; ?>>📖 লাইব্রেরিতে ধারযোগ্য</option>
                    <option value="out_of_stock" <?php echo $stock === 'out_of_stock' ? 'selected' : ''; ?>>❌ স্টক আউট</option>
                </select>
            </div>

            <!-- Filter 3: Price Range -->
            <div>
                <label class="block text-xs font-anek font-bold text-gray-700 uppercase tracking-wider mb-2">
                    মূল্য সীমা (টাকা)
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="filterMinPrice" placeholder="সর্বনিম্ন" 
                        value="<?php echo $min_price !== null ? (float)$min_price : ''; ?>"
                        min="0" class="w-1/2 bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs sm:text-sm font-anek text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold">
                    <span class="text-gray-400 font-bold">-</span>
                    <input type="number" id="filterMaxPrice" placeholder="সর্বোচ্চ"
                        value="<?php echo $max_price !== null ? (float)$max_price : ''; ?>"
                        min="0" class="w-1/2 bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs sm:text-sm font-anek text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold">
                    <button type="button" onclick="applyFilters()" class="px-3 py-2 bg-brand-900 hover:bg-brand-gold hover:text-brand-900 text-white rounded-xl text-xs font-anek font-bold transition-colors">
                        প্রয়োগ
                    </button>
                </div>
            </div>

            <!-- Filter 4: Sort Options -->
            <div>
                <label class="block text-xs font-anek font-bold text-gray-700 uppercase tracking-wider mb-2">
                    সাজানোর ক্রম (Sort By)
                </label>
                <select id="filterSort" onchange="applyFilters()"
                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-anek text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold">
                    <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>✨ প্রাসঙ্গিকতা ও সেরা মিল</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>💲 দাম: কম থেকে বেশি</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>💲 দাম: বেশি থেকে কম</option>
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>🆕 নতুন সংযোজিত</option>
                    <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>🔤 বইয়ের নাম (A to Z)</option>
                    <option value="author_asc" <?php echo $sort === 'author_asc' ? 'selected' : ''; ?>>✍️ লেখকের নাম (A to Z)</option>
                </select>
            </div>
        </div>

        <!-- Quick Price Range Pills & Reset -->
        <div class="mt-4 pt-4 border-t border-gray-200/80 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-anek font-bold text-gray-500">দ্রুত মূল্য ফিল্টার:</span>
                <button type="button" onclick="setPriceRange(0, 200)" class="px-2.5 py-1 rounded-lg text-xs font-anek bg-white border border-gray-200 hover:border-brand-gold text-gray-700 hover:text-brand-900 transition-colors">৳০ - ৳২০০</button>
                <button type="button" onclick="setPriceRange(201, 500)" class="px-2.5 py-1 rounded-lg text-xs font-anek bg-white border border-gray-200 hover:border-brand-gold text-gray-700 hover:text-brand-900 transition-colors">৳২০১ - ৳৫০০</button>
                <button type="button" onclick="setPriceRange(501, 1000)" class="px-2.5 py-1 rounded-lg text-xs font-anek bg-white border border-gray-200 hover:border-brand-gold text-gray-700 hover:text-brand-900 transition-colors">৳৫০১ - ৳১০০০</button>
                <button type="button" onclick="setPriceRange(1001, null)" class="px-2.5 py-1 rounded-lg text-xs font-anek bg-white border border-gray-200 hover:border-brand-gold text-gray-700 hover:text-brand-900 transition-colors">৳১০০০+</button>
            </div>

            <!-- Reset All Filters Button -->
            <button type="button" onclick="resetAllFilters()" class="text-xs font-anek font-bold text-red-600 hover:text-red-800 transition-colors inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                সব ফিল্টার মুছুন
            </button>
        </div>
    </div>
</section>

<!-- Active Filter Chips Bar -->
<div id="activeChipsContainer" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2 flex flex-wrap items-center gap-2"></div>

<!-- Books Collection Section -->
<section class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-[600px]">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 border-b border-gray-200 pb-5">
        <div>
            <span id="section-subtitle" class="text-brand-gold font-bold tracking-wider text-xs uppercase font-anek block">
                সংগ্রহশালা ও ফলাফল
            </span>
            <h2 id="section-title" class="text-2xl sm:text-3xl font-anek font-bold text-brand-900 mt-1">
                বইয়ের তালিকা
            </h2>
        </div>
        <div class="text-gray-600 text-xs sm:text-sm font-anek font-medium bg-white px-3.5 py-2 rounded-xl border border-gray-100 shadow-sm">
            মোট <span id="book-count-total" class="text-brand-900 font-bold"><?php echo bn_num($total_books_count); ?></span>টি বই পাওয়া গেছে
        </div>
    </div>

    <!-- Empty State -->
    <div id="no-results" class="<?php echo ($total_books_count > 0) ? 'hidden' : ''; ?> text-center py-24 px-4 bg-white rounded-3xl shadow-sm border border-gray-100">
        <div class="w-20 h-20 rounded-full bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-5 text-3xl">
            🔍
        </div>
        <h3 class="text-xl sm:text-2xl font-anek font-bold text-brand-900">দুঃখিত, আপনার অনুসন্ধানে কোনো বই পাওয়া যায়নি!</h3>
        <p class="text-gray-500 mt-2 font-light text-sm sm:text-base max-w-md mx-auto">
            অনুগ্রহ করে সঠিক বানান চেক করুন, অন্য লেখক/বইয়ের নাম দিয়ে অনুসন্ধান করুন অথবা ফিল্টারগুলো রিসেট করুন।
        </p>
        <button onclick="resetAllFilters()"
            class="mt-6 px-6 py-2.5 rounded-xl bg-brand-gold text-brand-900 font-anek font-bold text-sm hover:bg-amber-400 transition-colors inline-flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            সব বই পুনরায় দেখান
        </button>
    </div>

    <!-- Library Books Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-8" id="library-book-grid">
        <!-- Initial Books rendered by PHP for SEO and Instant Load -->
        <?php foreach ($initial_books as $index => $book): 
            $img = getBookImage($book['cover_image']);
            $isOutOfStock = $book['stock_qty'] <= 0;
            $canBorrow = ($book['is_borrowable'] == 1 && !$isOutOfStock);
            $delay = ($index % 4) * 60;
        ?>
            <div class="book-card group reveal active <?php echo $isOutOfStock ? 'opacity-80' : ''; ?>" style="transition-delay: <?php echo $delay; ?>ms;">
                <div class="relative book-cover-container aspect-[2/3] rounded-xl overflow-hidden bg-gray-100 mb-3 shadow-sm border border-gray-100">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="object-cover w-full h-full transition-all duration-500 group-hover:scale-105 <?php echo $isOutOfStock ? 'grayscale' : ''; ?>" loading="lazy">
                    
                    <?php if ($isOutOfStock): ?>
                    <div class="absolute top-3 left-3 bg-red-600/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider z-20 backdrop-blur-sm shadow-md">
                        স্টক আউট
                    </div>
                    <?php elseif ($book['stock_qty'] > 0 && $book['stock_qty'] <= 5): ?>
                    <div class="absolute top-3 left-3 bg-amber-500/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider z-20 backdrop-blur-sm shadow-md">
                        অল্প কিছু বাকি
                    </div>
                    <?php endif; ?>

                    <!-- Action Overlay -->
                    <div class="absolute inset-x-0 bottom-0 md:inset-0 bg-brand-900/95 md:bg-brand-900/75 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-row md:flex-col justify-center items-center gap-2 p-2 md:p-4 z-30 backdrop-blur-sm">
                        <?php if ($isOutOfStock): ?>
                            <button disabled class="flex-1 md:flex-none md:w-full bg-gray-700 text-gray-400 py-2 rounded-lg font-bold text-xs cursor-not-allowed border border-white/10 font-anek">স্টকে নেই</button>
                        <?php else: ?>
                            <?php $cartData = htmlspecialchars(json_encode(['id' => (int)$book['id'], 'title' => (string)($book['title']??''), 'price' => (float)($book['sell_price']??0), 'img' => $img, 'author' => (string)($book['author']??'')]), ENT_QUOTES, 'UTF-8'); ?>
                            <button onclick='addToCart(<?php echo $cartData; ?>)' 
                                     class="flex-1 md:flex-none md:w-full bg-brand-gold hover:bg-white text-brand-900 py-2 rounded-lg font-bold transition-all text-xs sm:text-sm shadow-lg font-anek">
                                <span>কিনুন ৳<?php echo bn_num($book['sell_price']); ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($canBorrow): ?>
                            <?php $borrowData = htmlspecialchars(json_encode(['id' => (int)$book['id'], 'title' => (string)($book['title']??''), 'price' => 0, 'img' => $img, 'author' => (string)($book['author']??'')]), ENT_QUOTES, 'UTF-8'); ?>
                            <button onclick='borrowBook(<?php echo $borrowData; ?>)' 
                                     class="flex-1 md:flex-none md:w-full bg-white/20 hover:bg-white text-white hover:text-brand-900 border border-white/30 py-2 rounded-lg font-semibold transition-all text-xs sm:text-sm font-anek">
                                <span>ধার নিন</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center px-1">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <span class="text-[10px] text-brand-gold font-bold uppercase tracking-wider font-anek"><?php echo htmlspecialchars($book['category_name'] ?? 'অন্যান্য'); ?></span>
                        <?php if ($book['is_borrowable'] == 1): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500" title="লাইব্রেরিতে ধারযোগ্য"></span>
                        <?php endif; ?>
                    </div>
                    <a href="../book-details.php?id=<?php echo (int)$book['id']; ?>" class="block hover:text-brand-gold transition-colors">
                        <h3 class="font-serif text-sm sm:text-base text-brand-900 line-clamp-1 font-bold"><?php echo htmlspecialchars($book['title']); ?></h3>
                    </a>
                    <p class="text-gray-500 text-xs font-light mt-0.5 line-clamp-1 font-anek"><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination-controls" class="flex flex-wrap justify-center items-center gap-2 mt-12 mb-16">
        <?php
        $total_pages = ceil($total_books_count / $limit);
        if ($total_pages > 1) {
            echo '<button disabled class="px-3 sm:px-4 py-2 rounded-xl border border-brand-gold bg-brand-gold text-brand-900 font-bold font-anek text-xs sm:text-sm">১</button>';
            if ($total_pages >= 2) echo '<button onclick="fetchPage(2)" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">' . bn_num(2) . '</button>';
            if ($total_pages >= 3) echo '<button onclick="fetchPage(3)" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">' . bn_num(3) . '</button>';
            if ($total_pages > 3) {
                echo '<span class="px-2 text-gray-400 font-anek">...</span>';
                echo '<button onclick="fetchPage('.$total_pages.')" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">' . bn_num($total_pages) . '</button>';
            }
            echo '<button onclick="fetchPage(2)" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-xs sm:text-sm">পরবর্তী &raquo;</button>';
        }
        ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Advanced Search & Filter Engine Script -->
<script>
    let currentPage = 1;
    let isLoading = false;
    const itemsPerPage = 40;
    
    // State Store
    let searchState = {
        search: '<?php echo addslashes($raw_search); ?>',
        search_by: '<?php echo addslashes($search_by); ?>',
        category: '<?php echo addslashes($category); ?>',
        stock: '<?php echo addslashes($stock); ?>',
        borrowable: '<?php echo addslashes($borrowable); ?>',
        min_price: '<?php echo $min_price !== null ? (float)$min_price : ''; ?>',
        max_price: '<?php echo $max_price !== null ? (float)$max_price : ''; ?>',
        sort: '<?php echo addslashes($sort); ?>',
        page: 1
    };

    const searchInput = document.getElementById('librarySearchInput');
    const searchScopeSelect = document.getElementById('searchScopeSelect');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchSpinner = document.getElementById('search-spinner');
    const autocompleteDropdown = document.getElementById('autocompleteDropdown');
    const autocompleteResults = document.getElementById('autocompleteResults');
    const grid = document.getElementById('library-book-grid');
    const totalCountEl = document.getElementById('book-count-total');
    const noResultsEl = document.getElementById('no-results');
    const paginationContainer = document.getElementById('pagination-controls');
    const activeChipsContainer = document.getElementById('activeChipsContainer');
    const activeFiltersBadge = document.getElementById('activeFiltersBadge');

    // Bengali Number Converter
    const bnNum = (num) => {
        if (num === null || num === undefined) return '০';
        const digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return num.toString().split('').map(d => digits[d] || d).join('');
    };

    // Initialize UI on load
    document.addEventListener('DOMContentLoaded', () => {
        updateActiveChips();
        updateActiveFilterBadge();
    });

    // Close autocomplete on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#autocompleteDropdown') && !e.target.closest('#librarySearchInput')) {
            hideAutocomplete();
        }
    });

    let searchDebounceTimer = null;
    let autocompleteDebounceTimer = null;

    function onSearchInput(event) {
        const query = event.target.value.trim();
        searchState.search = query;
        
        if (query.length > 0) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
            hideAutocomplete();
        }

        // Fast instant autocomplete debounced at 250ms
        clearTimeout(autocompleteDebounceTimer);
        if (query.length >= 2) {
            autocompleteDebounceTimer = setTimeout(() => {
                fetchAutocomplete(query);
            }, 250);
        } else {
            hideAutocomplete();
        }

        // Main Grid Search debounced at 600ms
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchPage(1);
        }, 600);
    }

    function onSearchKeyDown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            hideAutocomplete();
            clearTimeout(searchDebounceTimer);
            fetchPage(1);
        } else if (event.key === 'Escape') {
            hideAutocomplete();
        }
    }

    function onScopeChange() {
        searchState.search_by = searchScopeSelect.value;
        fetchPage(1);
    }

    function triggerSearchNow() {
        hideAutocomplete();
        clearTimeout(searchDebounceTimer);
        fetchPage(1);
    }

    function clearLibrarySearch() {
        searchInput.value = '';
        searchState.search = '';
        clearSearchBtn.classList.add('hidden');
        hideAutocomplete();
        fetchPage(1);
    }

    function selectCategory(catName) {
        searchState.category = catName;
        const select = document.getElementById('filterCategory');
        if (select) select.value = catName;
        fetchPage(1);
    }

    function setPriceRange(min, max) {
        searchState.min_price = min !== null ? min : '';
        searchState.max_price = max !== null ? max : '';
        document.getElementById('filterMinPrice').value = searchState.min_price;
        document.getElementById('filterMaxPrice').value = searchState.max_price;
        fetchPage(1);
    }

    function toggleFilterDrawer() {
        const panel = document.getElementById('advancedFilterPanel');
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }

    function applyFilters() {
        searchState.category = document.getElementById('filterCategory').value;
        
        const stockVal = document.getElementById('filterStock').value;
        if (stockVal === 'borrowable') {
            searchState.borrowable = '1';
            searchState.stock = 'all';
        } else {
            searchState.borrowable = 'all';
            searchState.stock = stockVal;
        }

        searchState.min_price = document.getElementById('filterMinPrice').value.trim();
        searchState.max_price = document.getElementById('filterMaxPrice').value.trim();
        searchState.sort = document.getElementById('filterSort').value;
        
        fetchPage(1);
    }

    function resetAllFilters() {
        searchState = {
            search: '',
            search_by: 'all',
            category: 'all',
            stock: 'all',
            borrowable: 'all',
            min_price: '',
            max_price: '',
            sort: 'relevance',
            page: 1
        };

        searchInput.value = '';
        searchScopeSelect.value = 'all';
        clearSearchBtn.classList.add('hidden');
        document.getElementById('filterCategory').value = 'all';
        document.getElementById('filterStock').value = 'all';
        document.getElementById('filterMinPrice').value = '';
        document.getElementById('filterMaxPrice').value = '';
        document.getElementById('filterSort').value = 'relevance';

        hideAutocomplete();
        fetchPage(1);
    }

    function removeFilter(key) {
        if (key === 'search') {
            searchState.search = '';
            searchInput.value = '';
            clearSearchBtn.classList.add('hidden');
        } else if (key === 'category') {
            searchState.category = 'all';
            document.getElementById('filterCategory').value = 'all';
        } else if (key === 'stock' || key === 'borrowable') {
            searchState.stock = 'all';
            searchState.borrowable = 'all';
            document.getElementById('filterStock').value = 'all';
        } else if (key === 'price') {
            searchState.min_price = '';
            searchState.max_price = '';
            document.getElementById('filterMinPrice').value = '';
            document.getElementById('filterMaxPrice').value = '';
        }
        fetchPage(1);
    }

    function updateActiveChips() {
        if (!activeChipsContainer) return;
        let chipsHtml = '';

        if (searchState.search) {
            chipsHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-anek font-semibold bg-brand-light text-brand-900 border border-brand-gold/30">
                🔍 "${escapeHtml(searchState.search)}"
                <button onclick="removeFilter('search')" class="hover:text-red-500 font-bold ml-1">&times;</button>
            </span>`;
        }
        if (searchState.category && searchState.category !== 'all') {
            chipsHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-anek font-semibold bg-brand-light text-brand-900 border border-brand-gold/30">
                📁 ${escapeHtml(searchState.category)}
                <button onclick="removeFilter('category')" class="hover:text-red-500 font-bold ml-1">&times;</button>
            </span>`;
        }
        if (searchState.borrowable === '1') {
            chipsHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-anek font-semibold bg-green-50 text-green-800 border border-green-200">
                📖 ধারযোগ্য
                <button onclick="removeFilter('borrowable')" class="hover:text-red-500 font-bold ml-1">&times;</button>
            </span>`;
        } else if (searchState.stock === 'in_stock') {
            chipsHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-anek font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                ✅ স্টকে আছে
                <button onclick="removeFilter('stock')" class="hover:text-red-500 font-bold ml-1">&times;</button>
            </span>`;
        }
        if (searchState.min_price || searchState.max_price) {
            const minStr = searchState.min_price ? '৳' + bnNum(searchState.min_price) : '৳০';
            const maxStr = searchState.max_price ? '৳' + bnNum(searchState.max_price) : 'অসীম';
            chipsHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-anek font-semibold bg-brand-light text-brand-900 border border-brand-gold/30">
                💲 দাম: ${minStr} - ${maxStr}
                <button onclick="removeFilter('price')" class="hover:text-red-500 font-bold ml-1">&times;</button>
            </span>`;
        }

        activeChipsContainer.innerHTML = chipsHtml;
    }

    function updateActiveFilterBadge() {
        let count = 0;
        if (searchState.search) count++;
        if (searchState.category && searchState.category !== 'all') count++;
        if (searchState.stock !== 'all') count++;
        if (searchState.borrowable !== 'all') count++;
        if (searchState.min_price || searchState.max_price) count++;
        if (searchState.sort !== 'relevance') count++;

        if (activeFiltersBadge) {
            if (count > 0) {
                activeFiltersBadge.innerText = bnNum(count);
                activeFiltersBadge.classList.remove('hidden');
            } else {
                activeFiltersBadge.classList.add('hidden');
            }
        }
    }

    // Live Instant Autocomplete Fetcher
    async function fetchAutocomplete(query) {
        try {
            const params = new URLSearchParams({
                search: query,
                search_by: searchState.search_by || 'all',
                mode: 'autocomplete',
                limit: 6
            });
            const res = await fetch(`fetch_books.php?${params.toString()}`);
            const data = await res.json();

            if (data.success && data.books && data.books.length > 0) {
                let html = '';
                data.books.forEach(book => {
                    const isOut = parseInt(book.stock_qty) <= 0;
                    html += `
                        <a href="../book-details.php?id=${book.id}" class="flex items-center gap-3.5 p-3 sm:p-3.5 hover:bg-amber-50/70 transition-colors group">
                            <img src="${escapeHtml(book.img)}" class="w-11 h-14 object-cover rounded-lg shadow-sm border border-gray-100 flex-shrink-0" alt="">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-anek font-bold text-brand-900 group-hover:text-brand-gold transition-colors truncate">${escapeHtml(book.title)}</h4>
                                <p class="text-xs text-gray-500 font-anek truncate mt-0.5">${escapeHtml(book.author || 'লেখক অজানা')}</p>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="text-[10px] font-bold text-brand-gold bg-brand-light px-2 py-0.5 rounded uppercase tracking-wider">${escapeHtml(book.category)}</span>
                                    <span class="text-xs font-bold text-brand-900 font-sans">৳${bnNum(book.price)}</span>
                                    ${book.is_borrowable == 1 ? '<span class="text-[10px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-100 font-anek">ধারযোগ্য</span>' : ''}
                                    ${isOut ? '<span class="text-[10px] px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-100 font-anek">স্টক নেই</span>' : ''}
                                </div>
                            </div>
                            <div class="text-gray-300 group-hover:text-brand-gold transition-colors pr-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    `;
                });
                autocompleteResults.innerHTML = html;
                autocompleteDropdown.classList.remove('hidden');
            } else {
                hideAutocomplete();
            }
        } catch (err) {
            console.error('Autocomplete error:', err);
            hideAutocomplete();
        }
    }

    function hideAutocomplete() {
        if (autocompleteDropdown) {
            autocompleteDropdown.classList.add('hidden');
        }
    }

    // Main Grid Fetching with Animation & URL State Sync
    async function fetchPage(pageNum) {
        if (isLoading) return;
        isLoading = true;
        currentPage = pageNum;
        searchState.page = pageNum;

        // Visual feedback
        searchSpinner.classList.remove('hidden');
        grid.style.transition = 'opacity 0.25s ease-out, transform 0.25s ease-out';
        grid.style.opacity = '0.4';
        grid.style.pointerEvents = 'none';

        // Synchronize browser URL bar without page refresh
        const urlParams = new URLSearchParams();
        if (searchState.search) urlParams.set('search', searchState.search);
        if (searchState.search_by && searchState.search_by !== 'all') urlParams.set('search_by', searchState.search_by);
        if (searchState.category && searchState.category !== 'all') urlParams.set('category', searchState.category);
        if (searchState.stock && searchState.stock !== 'all') urlParams.set('stock', searchState.stock);
        if (searchState.borrowable && searchState.borrowable !== 'all') urlParams.set('borrowable', searchState.borrowable);
        if (searchState.min_price) urlParams.set('min_price', searchState.min_price);
        if (searchState.max_price) urlParams.set('max_price', searchState.max_price);
        if (searchState.sort && searchState.sort !== 'relevance') urlParams.set('sort', searchState.sort);
        if (pageNum > 1) urlParams.set('page', pageNum);

        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({ ...searchState }, '', newUrl);

        updateActiveChips();
        updateActiveFilterBadge();

        try {
            const apiParams = new URLSearchParams({
                search: searchState.search,
                search_by: searchState.search_by,
                category: searchState.category,
                stock: searchState.stock,
                borrowable: searchState.borrowable,
                min_price: searchState.min_price,
                max_price: searchState.max_price,
                sort: searchState.sort,
                page: currentPage,
                limit: itemsPerPage
            });

            const res = await fetch(`fetch_books.php?${apiParams.toString()}`);
            const data = await res.json();

            totalCountEl.innerText = bnNum(data.total);
            grid.innerHTML = '';

            if (!data.books || data.books.length === 0) {
                noResultsEl.classList.remove('hidden');
                if (paginationContainer) paginationContainer.innerHTML = '';
            } else {
                noResultsEl.classList.add('hidden');
                
                data.books.forEach((book, index) => {
                    const isOutOfStock = parseInt(book.stock_qty) <= 0;
                    const isLowStock = !isOutOfStock && parseInt(book.stock_qty) <= 5;
                    const canBorrow = parseInt(book.is_borrowable) === 1 && !isOutOfStock;
                    const delay = (index % 4) * 40;

                    const cartPayload = JSON.stringify({
                        id: Number(book.id),
                        title: String(book.title || ''),
                        price: Number(book.price || 0),
                        img: String(book.img || ''),
                        author: String(book.author || '')
                    }).replace(/'/g, '&#39;').replace(/"/g, '&quot;');

                    const borrowPayload = JSON.stringify({
                        id: Number(book.id),
                        title: String(book.title || ''),
                        price: 0,
                        img: String(book.img || ''),
                        author: String(book.author || '')
                    }).replace(/'/g, '&#39;').replace(/"/g, '&quot;');

                    let stockBadgeHTML = '';
                    if (isOutOfStock) {
                        stockBadgeHTML = '<div class="absolute top-3 left-3 bg-red-600/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider z-20 backdrop-blur-sm shadow-md">স্টক আউট</div>';
                    } else if (isLowStock) {
                        stockBadgeHTML = '<div class="absolute top-3 left-3 bg-amber-500/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider z-20 backdrop-blur-sm shadow-md">অল্প কিছু বাকি</div>';
                    }

                    const buyButtonHTML = isOutOfStock ?
                        '<button disabled class="flex-1 md:flex-none md:w-full bg-gray-700 text-gray-400 py-2 rounded-lg font-bold text-xs cursor-not-allowed border border-white/10 font-anek">স্টকে নেই</button>' :
                        `<button onclick='addToCart(${cartPayload})' class="flex-1 md:flex-none md:w-full bg-brand-gold hover:bg-white text-brand-900 py-2 rounded-lg font-bold transition-all text-xs sm:text-sm shadow-lg font-anek"><span>কিনুন ৳${bnNum(book.price)}</span></button>`;

                    const borrowButtonHTML = canBorrow ?
                        `<button onclick='borrowBook(${borrowPayload})' class="flex-1 md:flex-none md:w-full bg-white/20 hover:bg-white text-white hover:text-brand-900 border border-white/30 py-2 rounded-lg font-semibold transition-all text-xs sm:text-sm font-anek"><span>ধার নিন</span></button>` : '';

                    const borrowDot = parseInt(book.is_borrowable) === 1 ? '<span class="w-1.5 h-1.5 rounded-full bg-green-500" title="লাইব্রেরিতে ধারযোগ্য"></span>' : '';

                    const cardHtml = `
                        <div class="book-card group reveal active animate-slide-up ${isOutOfStock ? 'opacity-80' : ''}" style="animation-delay: ${delay}ms;">
                            <div class="relative book-cover-container aspect-[2/3] rounded-xl overflow-hidden bg-gray-100 mb-3 shadow-sm border border-gray-100">
                                <img src="${escapeHtml(book.img)}" alt="${escapeHtml(book.title)}" class="object-cover w-full h-full transition-all duration-500 group-hover:scale-105 ${isOutOfStock ? 'grayscale' : ''}" loading="lazy">
                                ${stockBadgeHTML}
                                <div class="absolute inset-x-0 bottom-0 md:inset-0 bg-brand-900/95 md:bg-brand-900/75 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-row md:flex-col justify-center items-center gap-2 p-2 md:p-4 z-30 backdrop-blur-sm">
                                    ${buyButtonHTML}
                                    ${borrowButtonHTML}
                                </div>
                            </div>
                            <div class="text-center px-1">
                                <div class="flex items-center justify-center gap-1.5 mb-1.5">
                                    <span class="text-[10px] text-brand-gold font-bold uppercase tracking-wider font-anek">${escapeHtml(book.category)}</span>
                                    ${borrowDot}
                                </div>
                                <a href="../book-details.php?id=${book.id}" class="block hover:text-brand-gold transition-colors">
                                    <h3 class="font-serif text-sm sm:text-base text-brand-900 line-clamp-1 font-bold">${escapeHtml(book.title)}</h3>
                                </a>
                                <p class="text-gray-500 text-xs font-light mt-0.5 line-clamp-1 font-anek">${escapeHtml(book.author || '')}</p>
                            </div>
                        </div>
                    `;
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });

                renderPagination(data.total, currentPage);
            }

        } catch (err) {
            console.error('Fetch books error:', err);
        } finally {
            isLoading = false;
            searchSpinner.classList.add('hidden');
            grid.style.opacity = '1';
            grid.style.pointerEvents = 'auto';
        }
    }

    function renderPagination(totalItems, currentPg) {
        if (!paginationContainer) return;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        let html = '';
        if (currentPg > 1) {
            html += `<button onclick="fetchPage(${currentPg - 1})" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-xs sm:text-sm">&laquo; পূর্ববর্তী</button>`;
        }

        let startPage = Math.max(1, currentPg - 2);
        let endPage = Math.min(totalPages, currentPg + 2);

        if (startPage > 1) {
            html += `<button onclick="fetchPage(1)" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">${bnNum(1)}</button>`;
            if (startPage > 2) html += `<span class="px-2 text-gray-400 font-anek">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPg) {
                html += `<button disabled class="px-3 sm:px-4 py-2 rounded-xl border border-brand-gold bg-brand-gold text-brand-900 font-bold font-anek text-xs sm:text-sm">${bnNum(i)}</button>`;
            } else {
                html += `<button onclick="fetchPage(${i})" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">${bnNum(i)}</button>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="px-2 text-gray-400 font-anek">...</span>`;
            html += `<button onclick="fetchPage(${totalPages})" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek text-xs sm:text-sm">${bnNum(totalPages)}</button>`;
        }

        if (currentPg < totalPages) {
            html += `<button onclick="fetchPage(${currentPg + 1})" class="px-3 sm:px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-xs sm:text-sm">পরবর্তী &raquo;</button>`;
        }

        paginationContainer.innerHTML = html;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
</body>
</html>