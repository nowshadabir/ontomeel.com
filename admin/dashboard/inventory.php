<?php
// admin/dashboard/inventory.php
// Dedicated Management Page for Book Inventory (White & Cream UI)

require_once __DIR__ . '/includes/admin_helpers.php';

$current_page = 'inventory';
$page_title = 'বই ইনভেন্টরি';

// Categories for filter
$cats_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Inventory Filter Logic - Use prepared statement
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$cat_id = isset($_GET['category']) && $_GET['category'] != 'all' ? (int) $_GET['category'] : '%';

// Optimized: Fetch only the first 20 books for initial SSR
$inv_stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                          FROM books b 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?) 
                          AND (COALESCE(b.category_id, '') LIKE ?)
                          AND b.is_active = 1
                          ORDER BY b.created_at DESC
                          LIMIT 20");
$inv_stmt->execute([$search, $search, $search, $cat_id]);
$inventory_books = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM books b WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?) AND (COALESCE(b.category_id, '') LIKE ?) AND b.is_active = 1");
$count_stmt->execute([$search, $search, $search, $cat_id]);
$total_inventory_books = (int)$count_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title><?php echo $page_title; ?> - অন্ত্যমিল অ্যাডমিন</title>
</head>

<body class="bg-[#faf8f5] text-stone-900 font-anek antialiased min-h-screen flex selection:bg-stone-200 selection:text-stone-900">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#faf8f5] lg:pl-64">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <main class="flex-1 p-6 lg:p-10 space-y-8 max-w-7xl w-full mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e7e3da] pb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                            মোট <?php echo bn_num($total_inventory_books); ?> টি বই
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">সংগ্রহের সকল বই, স্টক ব্যবস্থাপনা, প্রাইসিং ও বাল্ক ইম্পোর্ট।</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button onclick="openBulkImportModal()"
                        class="px-3.5 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] hover:border-stone-400 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shadow-xs">
                        <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>CSV ইম্পোর্ট</span>
                    </button>
                    <button onclick="openAddBookModal()"
                        class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg text-xs transition-all flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>নতুন বই যোগ করুন</span>
                    </button>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>

            <!-- Controls (Search + Category Filter) -->
            <form action="" method="GET" onsubmit="return handleInventoryFilter(event)"
                class="bg-white border border-[#e7e3da] p-3 rounded-xl flex flex-col md:flex-row gap-3 items-center shadow-xs">
                <div class="relative flex-1 w-full">
                    <input type="text" id="inventory-search-input" name="search"
                        onkeyup="debounceInventorySearch()"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        placeholder="বইয়ের নাম, লেখক অথবা আইএসবিএন (ISBN) দিয়ে খুঁজুন..."
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-stone-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <select id="inventory-category-select" name="category" onchange="loadInventory(1)"
                        class="bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-800 focus:outline-none focus:border-stone-800 cursor-pointer">
                        <option value="all">সব ক্যাটাগরি</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <!-- Inventory Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 font-bold">বইয়ের তথ্য</th>
                                <th class="py-3.5 px-5 font-bold">ক্যাটাগরি</th>
                                <th class="py-3.5 px-5 font-bold">মূল্য</th>
                                <th class="py-3.5 px-5 font-bold">স্টক স্ট্যাটাস</th>
                                <th class="py-3.5 px-5 font-bold text-right">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table-body" class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                            <?php if (empty($inventory_books)): ?>
                                <tr><td colspan="5" class="py-16 text-center text-stone-500 font-mono text-xs">কোনো বই পাওয়া যায়নি।</td></tr>
                            <?php else: ?>
                                <?php foreach ($inventory_books as $book): 
                                    $has_disc = ($book['discount_price'] > 0 && $book['discount_price'] < $book['sell_price']);
                                    $eff_price = $has_disc ? $book['discount_price'] : $book['sell_price'];
                                    $stock_qty = (int)($book['stock_qty'] ?? 0);
                                    $stock_percent = min(100, max(0, ($stock_qty / 20) * 100));
                                    $book_json = htmlspecialchars(json_encode($book, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    $book_slug = (string)($book['slug'] ?? '');
                                    $book_url = !empty($book_slug) ? '/books/' . urlencode($book_slug) : '/book-details.php?id=' . $book['id'];
                                ?>
                                    <tr class="hover:bg-[#faf8f5] transition-colors">
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3.5">
                                                <a href="<?php echo $book_url; ?>" target="_blank" class="w-10 h-14 rounded bg-[#f4f1ea] border border-[#d8d3c7] overflow-hidden flex-shrink-0 block shadow-xs" title="বইটি দেখুন">
                                                    <?php if (!empty($book['cover_image'])): ?>
                                                        <img src="/admin/assets/book-images/<?php echo htmlspecialchars($book['cover_image']); ?>"
                                                             class="w-full h-full object-cover"
                                                             onerror="this.style.display='none';">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-[10px] text-stone-400 font-mono">N/A</div>
                                                    <?php endif; ?>
                                                </a>
                                                <div>
                                                    <a href="<?php echo $book_url; ?>" target="_blank" class="font-bold text-stone-900 hover:text-stone-700 transition-colors text-sm flex items-center gap-1.5" title="বইটি দেখুন">
                                                        <span><?php echo htmlspecialchars($book['title']); ?></span>
                                                        <svg class="w-3.5 h-3.5 text-stone-400 hover:text-stone-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    </a>
                                                    <p class="text-xs text-stone-500 mt-0.5"><?php echo htmlspecialchars($book['author']); ?></p>
                                                    <?php if (!empty($book['isbn'])): ?>
                                                        <p class="text-[10px] text-stone-500 font-mono mt-0.5">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="inline-block px-2.5 py-1 bg-[#f4f1ea] text-stone-800 rounded text-xs border border-[#ded8cc]">
                                                <?php echo htmlspecialchars($book['category_name'] ?: 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 font-mono text-stone-900 font-bold">
                                            <span class="font-bold">৳<?php echo bn_num($eff_price); ?></span>
                                            <?php if ($has_disc): ?>
                                                <span class="text-xs text-stone-400 line-through ml-1.5 font-normal">৳<?php echo bn_num($book['sell_price']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-20 bg-[#f0ece3] h-2 rounded-full overflow-hidden">
                                                    <div class="bg-stone-800 h-full rounded-full" style="width: <?php echo $stock_percent; ?>%"></div>
                                                </div>
                                                <span class="text-xs font-mono font-bold <?php echo $stock_qty <= 0 ? 'text-rose-600' : 'text-stone-900'; ?>"><?php echo bn_num($stock_qty); ?>টি</span>
                                            </div>
                                            <p class="text-[10px] font-mono mt-1 <?php echo $stock_qty <= 0 ? 'text-rose-600 font-bold' : ($stock_qty <= 5 ? 'text-amber-700' : 'text-stone-500'); ?>">
                                                <?php echo $stock_qty <= 0 ? 'স্টক আউট' : ($stock_qty <= 5 ? 'স্টক সীমিত' : 'ইন স্টক'); ?>
                                            </p>
                                        </td>
                                        <td class="py-4 px-5 text-right font-mono">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" onclick='editBook(<?php echo $book_json; ?>)' class="p-1.5 text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] rounded transition-colors" title="সম্পাদনা">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </button>
                                                <button type="button" onclick="deleteBook(<?php echo (int)$book['id']; ?>)" class="p-1.5 text-stone-600 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="মুছে ফেলুন">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div id="inventory-pagination" class="p-4 bg-[#faf8f5] border-t border-[#e7e3da] flex items-center justify-between text-xs font-mono">
                    <span class="text-stone-500">দেখানো হচ্ছে ১-<?php echo min(20, $total_inventory_books); ?> (মোট <?php echo bn_num($total_inventory_books); ?>টি)</span>
                    <div class="flex gap-2">
                        <button type="button" disabled class="px-3 py-1.5 bg-[#f4f1ea] border border-[#d8d3c7] rounded-lg text-xs font-mono text-stone-400 opacity-40 cursor-not-allowed">পূর্ববর্তী</button>
                        <button type="button" onclick="loadInventory(2)" <?php echo $total_inventory_books <= 20 ? 'disabled' : ''; ?> class="px-3 py-1.5 bg-stone-900 text-white font-semibold rounded-lg text-xs font-mono hover:bg-stone-800 transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-xs">পরবর্তী</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Book Modal (White & Cream Theme) -->
    <div id="add-book-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col text-xs font-sans">
            <!-- Modal Header -->
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5] shrink-0">
                <div>
                    <h3 id="modal-title" class="text-base font-bold text-stone-900">নতুন বই যুক্ত করুন</h3>
                    <p class="text-xs text-stone-500 mt-0.5">বইয়ের বিবরণ, ক্যাটাগরি, প্রকাশনা, মূল্য, স্টক এবং ছবি আপলোড করুন।</p>
                </div>
                <button onclick="closeAddBookModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Form (Scrollable) -->
            <form id="book-form" onsubmit="handleBookSubmit(event)" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-6">
                <input type="hidden" name="book_id" id="form-book-id">

                <!-- 1. Primary & Categorization Info -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold font-mono text-stone-700 uppercase tracking-wider pb-2 border-b border-[#f0ece3] flex items-center justify-between">
                        <span>১. বইয়ের প্রাথমিক ও পরিচিতি তথ্য</span>
                        <span class="text-[10px] text-stone-400 font-normal lowercase">* চিহ্নিত ফিল্ড আবশ্যক</span>
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">বইয়ের নাম (বাংলা) *</label>
                            <input type="text" name="title" required placeholder="যেমন: পথের পাঁচালী"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">বইয়ের নাম (English) *</label>
                            <input type="text" name="title_en" required placeholder="e.g. Pather Panchali"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">উপশিরোনাম (Subtitle)</label>
                            <input type="text" name="subtitle" placeholder="ঐচ্ছিক উপশিরোনাম"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">ক্যাটাগরি *</label>
                            <div class="space-y-2">
                                <select name="category_id" id="modal-category-select" required onchange="checkNewCategory(this)"
                                    class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 focus:outline-none focus:border-stone-800">
                                    <option value="">নির্বাচন করুন</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                    <option value="new">+ নতুন ক্যাটাগরি তৈরি করুন</option>
                                </select>
                                <div id="new-category-input-wrapper" class="hidden">
                                    <input type="text" name="new_category_name" placeholder="নতুন ক্যাটাগরির নাম দিন"
                                        class="w-full bg-white border border-amber-300 rounded-lg px-3 py-2 text-stone-900 focus:outline-none focus:border-amber-600 text-xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">লেখক (বাংলা) *</label>
                            <input type="text" name="author" required placeholder="লেখকের নাম"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">লেখক (English) *</label>
                            <input type="text" name="author_en" required placeholder="Author name in English"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">সহ-লেখক / অনুবাদক</label>
                            <input type="text" name="co_author" placeholder="অনুবাদক / সহ-লেখক"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">বইয়ের ধরন / Genre</label>
                            <input type="text" name="genre" placeholder="যেমন: ক্লাসিক, থ্রিলার, জীবনী"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">ভাষা (Language)</label>
                            <input type="text" name="language" value="Bengali" placeholder="যেমন: Bengali, English"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>
                </div>

                <!-- 2. Publication & Library Logistics -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold font-mono text-stone-700 uppercase tracking-wider pb-2 border-b border-[#f0ece3]">
                        ২. প্রকাশনা ও লাইব্রেরি বিবরণ
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">প্রকাশনী (Publisher)</label>
                            <input type="text" name="publisher" placeholder="প্রকাশনীর নাম"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">প্রকাশনার বছর</label>
                            <input type="text" name="publish_year" placeholder="যেমন: 2024" maxlength="4"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">সংস্করণ (Edition)</label>
                            <input type="text" name="edition" placeholder="যেমন: ১ম সংস্করণ"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">আইএসবিএন (ISBN)</label>
                            <input type="text" name="isbn" placeholder="যেমন: 978-984-..."
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">ফরম্যাট (Format)</label>
                            <select name="format"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 focus:outline-none focus:border-stone-800">
                                <option value="Paperback">Paperback (পেপারব্যাক)</option>
                                <option value="Hardcover">Hardcover (হার্ডকভার)</option>
                                <option value="E-book">E-book (ডিজিটাল বই)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">পৃষ্ঠা সংখ্যা (Pages)</label>
                            <input type="number" name="page_count" min="0" placeholder="যেমন: ২৫০"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">বইয়ের অবস্থা (Condition)</label>
                            <select name="book_condition"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 focus:outline-none focus:border-stone-800">
                                <option value="New">New (একদম নতুন)</option>
                                <option value="Used">Used (ব্যবহৃত / সেকেন্ড হ্যান্ড)</option>
                                <option value="Damaged">Damaged (সামান্য ক্ষতিগ্রস্ত)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">শেল্ফ লোকেশন (Shelf)</label>
                            <input type="text" name="shelf_location" placeholder="যেমন: Shelf-A1"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">র‍্যাক নম্বর (Rack)</label>
                            <input type="text" name="rack_number" placeholder="যেমন: Rack-03"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>
                </div>

                <!-- 3. Pricing, Stock & Supplier -->
                <div class="space-y-4">
                    <div class="pb-2 border-b border-[#f0ece3] flex items-center justify-between">
                        <h4 class="text-xs font-bold font-mono text-stone-700 uppercase tracking-wider">
                            ৩. মূল্য, স্টক ও সাপ্লায়ার তথ্য
                        </h4>
                        <span class="text-[11px] text-stone-400 font-mono">মুদ্রিত মূল্য ও অফার মূল্য আলাদা রাখুন</span>
                    </div>

                    <!-- Pricing 3-Box Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- 1. Sell Price (Regular / MRP) -->
                        <div class="p-3.5 bg-[#faf8f5] border border-[#e7e3da] rounded-xl space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-stone-900 font-mono font-bold text-xs">
                                    মুদ্রিত মূল্য / গায়ের দাম (MRP) *
                                </label>
                                <span class="text-[10px] bg-stone-200 text-stone-700 px-1.5 py-0.5 rounded font-mono">মূল দাম</span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">৳</span>
                                <input type="number" step="any" min="0" name="sell_price" id="form_sell_price" required placeholder="যেমন: 500"
                                    oninput="updatePricePreview()"
                                    class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-7 pr-3 py-2 text-stone-900 font-mono text-sm font-bold focus:outline-none focus:border-stone-800">
                            </div>
                            <p class="text-[10.5px] text-stone-500 leading-tight">বইটির স্বাভাবিক বিক্রয় মূল্য। কোনো ছাড় না থাকলে গ্রাহক এই মূল্যে কিনবেন।</p>
                        </div>

                        <!-- 2. Discount / Offer Price -->
                        <div class="p-3.5 bg-amber-50/40 border border-amber-200/80 rounded-xl space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-amber-950 font-mono font-bold text-xs">
                                    ছাড়ের পর বিক্রয় মূল্য (Offer)
                                </label>
                                <span class="text-[10px] bg-amber-200/80 text-amber-800 px-1.5 py-0.5 rounded font-mono">ঐচ্ছিক</span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 font-bold">৳</span>
                                <input type="number" step="any" min="0" name="discount_price" id="form_discount_price" placeholder="যেমন: 420"
                                    oninput="updatePricePreview()"
                                    class="w-full bg-white border border-amber-300 rounded-lg pl-7 pr-3 py-2 text-stone-900 font-mono text-sm font-bold focus:outline-none focus:border-amber-600">
                            </div>
                            <p class="text-[10.5px] text-stone-500 leading-tight">ছাড় দিতে চাইলে ছাড়ের পরের চূড়ান্ত দাম লিখুন। কোনো ছাড় না থাকলে <strong class="text-stone-700">খালি রাখুন (বা গায়ের দাম লিখুন)</strong>।</p>
                        </div>

                        <!-- 3. Purchase / Cost Price -->
                        <div class="p-3.5 bg-stone-100/50 border border-stone-200 rounded-xl space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-stone-800 font-mono font-bold text-xs">
                                    কেনা খরচ / ক্রয়মূল্য (Cost)
                                </label>
                                <span class="text-[10px] bg-stone-200 text-stone-600 px-1.5 py-0.5 rounded font-mono">অ্যাডমিন অনলি</span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">৳</span>
                                <input type="number" step="any" min="0" name="purchase_price" id="form_purchase_price" placeholder="যেমন: 300"
                                    oninput="updatePricePreview()"
                                    class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-7 pr-3 py-2 text-stone-900 font-mono text-sm font-bold focus:outline-none focus:border-stone-800">
                            </div>
                            <p class="text-[10.5px] text-stone-500 leading-tight">বইটি আপনার কিনতে কত খরচ হয়েছে (গ্রাহক দেখতে পাবে না, শুধুমাত্র হিসাবের জন্য)।</p>
                        </div>
                    </div>

                    <!-- Dynamic Live Price Calculation Preview Box -->
                    <div id="price-summary-box" class="p-3 bg-white border border-[#e7e3da] rounded-xl text-xs flex flex-wrap items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-stone-400" id="price-status-dot"></span>
                            <span class="font-mono text-stone-600" id="price-preview-label">গ্রাহক পরিশোধ করবেন:</span>
                            <span class="font-mono font-bold text-stone-900 text-sm" id="price-preview-final">৳০</span>
                            <span class="font-mono text-stone-400 line-through text-xs hidden" id="price-preview-strike"></span>
                            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 hidden" id="price-preview-badge"></span>
                        </div>
                        <div class="font-mono text-xs text-stone-500" id="price-profit-text"></div>
                    </div>

                    <!-- Stock & Supplier Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">স্টক পরিমাণ (Qty) *</label>
                            <input type="number" name="stock_qty" required placeholder="10" value="1" min="0"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">লো-স্টক অ্যালার্ট সীমা</label>
                            <input type="number" name="min_stock_level" min="0" value="2" placeholder="2"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">সাপ্লায়ারের নাম</label>
                            <input type="text" name="supplier_name" placeholder="সাপ্লায়ার বা ভেন্ডর"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">সাপ্লায়ারের যোগাযোগ</label>
                            <input type="text" name="supplier_contact" placeholder="ফোন বা ঠিকানা"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800">
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <label class="flex items-center gap-3 p-3 bg-[#faf8f5] border border-[#e7e3da] rounded-xl cursor-pointer hover:bg-[#f4f1ea] transition-colors">
                            <input type="checkbox" name="is_borrowable" value="1" class="w-4 h-4 text-stone-900 rounded border-stone-300 focus:ring-stone-800">
                            <div>
                                <span class="font-mono font-semibold text-stone-900 block text-xs">মেম্বারশিপে ধারযোগ্য (Borrowable)</span>
                                <span class="text-[11px] text-stone-500 block">সদস্যরা বইটি পড়ার জন্য ধার নেওয়ার রিকোয়েস্ট করতে পারবে (ডিফল্ট: বন্ধ)।</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-[#faf8f5] border border-[#e7e3da] rounded-xl cursor-pointer hover:bg-[#f4f1ea] transition-colors">
                            <input type="checkbox" name="is_suggested" value="1" class="w-4 h-4 text-stone-900 rounded border-stone-300 focus:ring-stone-800" checked>
                            <div>
                                <span class="font-mono font-semibold text-stone-900 block text-xs">হোমপেজে সাজেস্টেড বই</span>
                                <span class="text-[11px] text-stone-500 block">হোমপেজ ও লাইব্রেরির সাজেস্টেড সেকশনে প্রদর্শিত হবে।</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 4. Description & Images -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold font-mono text-stone-700 uppercase tracking-wider pb-2 border-b border-[#f0ece3]">
                        ৪. বইয়ের বিবরণ ও কভার ছবি
                    </h4>

                    <div>
                        <label class="block text-stone-600 mb-1.5 font-mono font-semibold">বইয়ের বিবরণ / পরিচিতি</label>
                        <textarea name="description" rows="4" placeholder="বইটির সংক্ষিপ্ত বিবরণ, প্লট বা ভূমিকা লিখুন..."
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">মূল কভার ছবি (Cover)</label>
                            <input type="file" name="cover_image" accept="image/*"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs text-stone-800 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">ভিতরের পাতা / ছবি ২</label>
                            <input type="file" name="photo_2" accept="image/*"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs text-stone-800 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                        </div>
                        <div>
                            <label class="block text-stone-600 mb-1.5 font-mono font-semibold">অতিরিক্ত ছবি ৩</label>
                            <input type="file" name="photo_3" accept="image/*"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs text-stone-800 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="pt-4 border-t border-[#e7e3da] flex justify-end gap-3 sticky bottom-0 bg-white py-2">
                    <button type="button" onclick="closeAddBookModal()"
                        class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 font-medium rounded-lg transition-colors">
                        বাতিল
                    </button>
                    <button type="submit" id="modal-submit-btn"
                        class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg transition-colors shadow-xs">
                        সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CSV Bulk Import Modal (White & Cream Theme) -->
    <div id="bulk-import-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden flex flex-col text-xs font-sans">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5]">
                <div>
                    <h3 class="text-base font-bold text-stone-900">CSV বাল্ক ইম্পোর্ট</h3>
                    <p class="text-xs text-stone-500 mt-0.5">একসাথে একাধিক বই ইনভেন্টরিতে আপলোড করুন।</p>
                </div>
                <button onclick="closeBulkImportModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div class="bg-[#faf8f5] border border-[#e7e3da] rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-stone-900">স্যাম্পল CSV ফাইল টেমপ্লেট</h4>
                        <p class="text-[11px] text-stone-500 mt-0.5">টেমপ্লেট ডাউনলোড করে প্রয়োজনীয় কলাম পূরণ করুন</p>
                    </div>
                    <a href="download_sample_csv.php" class="px-3.5 py-1.5 bg-white hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg font-mono text-xs transition-colors shadow-xs">
                        ডাউনলোড
                    </a>
                </div>

                <form id="bulk-import-form" onsubmit="handleBulkImport(event)" class="space-y-4">
                    <div>
                        <label class="block text-stone-600 font-mono font-semibold mb-1.5">CSV ফাইল নির্বাচন করুন *</label>
                        <input type="file" id="csv_file_input" name="csv_file" accept=".csv, text/csv" required
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg p-2 text-xs text-stone-800 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#f4f1ea] file:text-stone-800">
                    </div>

                    <div id="bulk-import-results" class="hidden"></div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-[#e7e3da]">
                        <button type="button" onclick="closeBulkImportModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 rounded-lg">
                            বন্ধ করুন
                        </button>
                        <button type="submit" id="bulk-submit-btn" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg shadow-xs">
                            ইম্পোর্ট শুরু করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let inventoryState = {
            search: '',
            category: 'all',
            page: 1,
            limit: 20
        };

        function showToast(message, isError = false) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `bg-stone-900 text-white px-4 py-3 rounded-lg shadow-2xl text-xs font-mono flex items-center gap-3 transform transition-all duration-300 translate-y-2 opacity-0`;
            const dotClass = isError ? 'bg-rose-400' : 'bg-emerald-400';
            toast.innerHTML = `<span class="w-2 h-2 rounded-full ${dotClass} animate-pulse"></span><span>${message}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 50);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        let debounceTimer;
        function debounceInventorySearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadInventory(1);
            }, 300);
        }

        function handleInventoryFilter(e) {
            e.preventDefault();
            loadInventory(1);
        }

        function loadInventory(page = 1) {
            const search = document.getElementById('inventory-search-input').value;
            const category = document.getElementById('inventory-category-select').value;
            
            inventoryState.page = page;
            inventoryState.search = search;
            inventoryState.category = category;

            const tbody = document.getElementById('inventory-table-body');
            tbody.innerHTML = '<tr><td colspan="5" class="py-16 text-center text-stone-500 font-mono text-xs">লোড হচ্ছে...</td></tr>';

            const url = `/admin/dashboard/fetch_inventory.php?page=${page}&limit=${inventoryState.limit}&search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        tbody.innerHTML = data.html;
                        document.getElementById('inventory-pagination').innerHTML = data.pagination;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-rose-500">ডাটা লোড করতে সমস্যা হয়েছে।</td></tr>';
                    }
                })
                .catch(() => {
                    tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-rose-500">সার্ভার সমস্যা।</td></tr>';
                });
        }

        function changeInventoryPage(page) {
            loadInventory(page);
        }

        function openAddBookModal() {
            document.getElementById('modal-title').innerText = "নতুন বই যুক্ত করুন";
            const form = document.getElementById('book-form');
            form.reset();
            document.getElementById('form-book-id').value = "";
            document.getElementById('new-category-input-wrapper').classList.add('hidden');
            form.language.value = "Bengali";
            form.format.value = "Paperback";
            form.book_condition.value = "New";
            form.stock_qty.value = "1";
            form.min_stock_level.value = "2";
            form.is_borrowable.checked = false;
            form.is_suggested.checked = true;
            updatePricePreview();
            document.getElementById('add-book-modal').classList.remove('hidden');
            document.getElementById('add-book-modal').classList.add('flex');
        }

        function closeAddBookModal() {
            document.getElementById('add-book-modal').classList.add('hidden');
            document.getElementById('add-book-modal').classList.remove('flex');
        }

        function updatePricePreview() {
            const sellInput = document.getElementById('form_sell_price');
            const discInput = document.getElementById('form_discount_price');
            const buyInput = document.getElementById('form_purchase_price');
            
            const sell = parseFloat(sellInput ? sellInput.value : 0) || 0;
            const disc = parseFloat(discInput ? discInput.value : 0) || 0;
            const buy = parseFloat(buyInput ? buyInput.value : 0) || 0;
            
            const finalElem = document.getElementById('price-preview-final');
            const strikeElem = document.getElementById('price-preview-strike');
            const badgeElem = document.getElementById('price-preview-badge');
            const profitElem = document.getElementById('price-profit-text');
            const dotElem = document.getElementById('price-status-dot');

            if (!finalElem) return;

            // Input visual state reset
            if (discInput) {
                discInput.classList.remove('border-rose-500', 'bg-rose-50', 'text-rose-900');
            }
            if (sellInput) {
                sellInput.classList.remove('border-rose-400');
            }

            if (sell <= 0) {
                finalElem.textContent = '৳০';
                strikeElem.classList.add('hidden');
                badgeElem.textContent = '⚠️ গায়ের দাম দিন';
                badgeElem.classList.remove('hidden');
                badgeElem.className = 'font-mono text-xs font-semibold px-2 py-0.5 rounded bg-stone-100 text-stone-600';
                if (sellInput && sellInput.value !== '') {
                    sellInput.classList.add('border-rose-400');
                }
                if (profitElem) profitElem.innerHTML = '';
                if (dotElem) dotElem.className = 'w-2 h-2 rounded-full bg-stone-300';
                return;
            }

            let customerPrice = sell;
            if (disc > 0 && disc < sell) {
                customerPrice = disc;
                finalElem.textContent = '৳' + disc;
                strikeElem.textContent = '৳' + sell;
                strikeElem.classList.remove('hidden');
                
                const savings = sell - disc;
                const percent = Math.round((savings / sell) * 100);
                badgeElem.textContent = `${percent}% ছাড় (৳${savings} সাশ্রয়)`;
                badgeElem.classList.remove('hidden');
                badgeElem.className = 'font-mono text-xs font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800';
                if (dotElem) dotElem.className = 'w-2 h-2 rounded-full bg-amber-500 animate-pulse';
            } else if (disc > sell) {
                customerPrice = sell;
                finalElem.textContent = '৳' + sell;
                strikeElem.classList.add('hidden');
                badgeElem.textContent = '⚠️ ভুল ইনপুট: অফার মূল্য গায়ের দামের (৳' + sell + ') চেয়ে বেশি হতে পারবে না!';
                badgeElem.classList.remove('hidden');
                badgeElem.className = 'font-mono text-xs font-semibold px-2.5 py-1 rounded bg-rose-100 text-rose-700 border border-rose-300 animate-pulse';
                if (dotElem) dotElem.className = 'w-2 h-2 rounded-full bg-rose-500';
                if (discInput) {
                    discInput.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-900');
                }
            } else if (disc < 0) {
                badgeElem.textContent = '⚠️ মূল্য ঋণাত্মক হতে পারবে না';
                badgeElem.classList.remove('hidden');
                badgeElem.className = 'font-mono text-xs font-semibold px-2 py-0.5 rounded bg-rose-100 text-rose-700';
                if (discInput) discInput.classList.add('border-rose-500', 'bg-rose-50');
            } else {
                // disc === 0, empty, or disc === sell (treated as regular price)
                finalElem.textContent = '৳' + sell;
                strikeElem.classList.add('hidden');
                badgeElem.textContent = 'রেগুলার মূল্য (কোনো ছাড় নেই)';
                badgeElem.classList.remove('hidden');
                badgeElem.className = 'font-mono text-xs font-semibold px-2 py-0.5 rounded bg-stone-100 text-stone-700';
                if (dotElem) dotElem.className = 'w-2 h-2 rounded-full bg-emerald-500';
            }

            if (profitElem) {
                if (buy > 0) {
                    const profit = customerPrice - buy;
                    if (profit > 0) {
                        profitElem.innerHTML = `বই প্রতি লাভ: <span class="text-emerald-700 font-bold">৳${profit}</span> (কেনা: ৳${buy})`;
                    } else if (profit === 0) {
                        profitElem.innerHTML = `বই প্রতি লাভ: <span class="text-stone-600 font-bold">৳০ (কেনা দামে বিক্রয়)</span>`;
                    } else {
                        profitElem.innerHTML = `⚠️ <span class="text-rose-600 font-bold">৳${Math.abs(profit)} ক্ষতিতে বিক্রয়!</span> (কেনা: ৳${buy})`;
                    }
                } else {
                    profitElem.innerHTML = '';
                }
            }
        }

        function editBook(book) {
            openAddBookModal();
            document.getElementById('modal-title').innerText = "বইয়ের তথ্য সম্পাদনা";
            document.getElementById('form-book-id').value = book.id;
            
            const form = document.getElementById('book-form');
            form.title.value = book.title || '';
            form.title_en.value = book.title_en || '';
            form.subtitle.value = book.subtitle || '';
            form.author.value = book.author || '';
            form.author_en.value = book.author_en || '';
            form.co_author.value = book.co_author || '';
            form.category_id.value = book.category_id || '';
            form.genre.value = book.genre || '';
            form.language.value = book.language || 'Bengali';
            form.publisher.value = book.publisher || '';
            form.publish_year.value = book.publish_year || '';
            form.edition.value = book.edition || '';
            form.isbn.value = book.isbn || '';
            form.format.value = book.format || 'Paperback';
            form.page_count.value = book.page_count || '';
            form.book_condition.value = book.book_condition || 'New';
            form.shelf_location.value = book.shelf_location || '';
            form.rack_number.value = book.rack_number || '';
            form.sell_price.value = book.sell_price || '';
            form.discount_price.value = (parseFloat(book.discount_price) > 0) ? book.discount_price : '';
            form.purchase_price.value = (parseFloat(book.purchase_price) > 0) ? book.purchase_price : '';
            form.stock_qty.value = book.stock_qty !== undefined ? book.stock_qty : 0;
            form.min_stock_level.value = book.min_stock_level !== undefined ? book.min_stock_level : 2;
            form.supplier_name.value = book.supplier_name || '';
            form.supplier_contact.value = book.supplier_contact || '';
            form.description.value = book.description || '';
            form.is_borrowable.checked = parseInt(book.is_borrowable) === 1;
            form.is_suggested.checked = parseInt(book.is_suggested) === 1;
            updatePricePreview();
        }

        function checkNewCategory(select) {
            const wrapper = document.getElementById('new-category-input-wrapper');
            if (select.value === 'new') {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
        }

        function handleBookSubmit(e) {
            e.preventDefault();
            const form = e.target;

            // Amount & Entry Validation
            const sellInput = document.getElementById('form_sell_price');
            const discInput = document.getElementById('form_discount_price');
            const buyInput = document.getElementById('form_purchase_price');
            const stockInput = form.stock_qty;

            const sell = parseFloat(sellInput ? sellInput.value : 0) || 0;
            const disc = parseFloat(discInput ? discInput.value : 0) || 0;
            const buy = parseFloat(buyInput ? buyInput.value : 0) || 0;
            const stock = parseInt(stockInput ? stockInput.value : 0);

            if (sell <= 0) {
                alert('⚠️ অনুগ্রহ করে বইয়ের সঠিক মুদ্রিত গায়ের দাম (MRP) দিন। এটি অবশ্যই ০ এর চেয়ে বেশি হতে হবে।');
                if (sellInput) sellInput.focus();
                return;
            }

            if (disc > sell) {
                alert(`⚠️ ভুল অফার মূল্য!\n\nছাড়ের পর বিক্রয় মূল্য (৳${disc}) গায়ের দামের (৳${sell}) চেয়ে বেশি হতে পারবে না।\n\nকোনো ছাড় না থাকলে অফার মূল্যের ঘরটি খালি রাখুন (অথবা গায়ের দাম লিখুন)।`);
                if (discInput) {
                    discInput.focus();
                    discInput.select();
                }
                return;
            }

            if (disc < 0 || buy < 0 || sell < 0) {
                alert('⚠️ কোনো মূল্যের মান ঋণাত্মক (নেগেটিভ) হতে পারবে না।');
                return;
            }

            if (stock < 0) {
                alert('⚠️ স্টক পরিমাণ ঋণাত্মক হতে পারবে না।');
                if (stockInput) stockInput.focus();
                return;
            }

            if (disc < 0 || buy < 0 || sell < 0) {
                alert('⚠️ কোনো মূল্যের মান ঋণাত্মক (নেগেটিভ) হতে পারবে না।');
                return;
            }

            if (stock < 0) {
                alert('⚠️ স্টক পরিমাণ ঋণাত্মক হতে পারবে না।');
                if (stockInput) stockInput.focus();
                return;
            }

            const formData = new FormData(form);
            const btn = document.getElementById('modal-submit-btn');

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            fetch('/admin/dashboard/process_add_book.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('বই সফলভাবে সংরক্ষিত হয়েছে!');
                    closeAddBookModal();
                    loadInventory(inventoryState.page);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'সংরক্ষণ ব্যর্থ'));
                }
            })
            .catch(() => alert('সার্ভার সমস্যা।'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'সংরক্ষণ করুন';
            });
        }

        function deleteBook(bookId) {
            if (!confirm('আপনি কি নিশ্চিত যে এই বইটি ইনভেন্টরি থেকে মুছে ফেলতে চান?')) return;

            const formData = new FormData();
            formData.append('id', bookId);

            fetch('/admin/dashboard/delete_book.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('বই সফলভাবে মুছে ফেলা হয়েছে।');
                    loadInventory(inventoryState.page);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'মুছে ফেলতে সমস্যা'));
                }
            })
            .catch(() => alert('সার্ভার সংযোগে ত্রুটি।'));
        }

        function openBulkImportModal() {
            document.getElementById('bulk-import-modal').classList.remove('hidden');
            document.getElementById('bulk-import-modal').classList.add('flex');
        }

        function closeBulkImportModal() {
            document.getElementById('bulk-import-modal').classList.add('hidden');
            document.getElementById('bulk-import-modal').classList.remove('flex');
        }

        function handleBulkImport(e) {
            e.preventDefault();
            const fileInput = document.getElementById('csv_file_input');
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);

            const btn = document.getElementById('bulk-submit-btn');
            const resultsDiv = document.getElementById('bulk-import-results');
            
            btn.disabled = true;
            btn.textContent = 'ইম্পোর্ট হচ্ছে...';
            resultsDiv.classList.add('hidden');

            fetch('/admin/dashboard/process_bulk_import.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                resultsDiv.classList.remove('hidden');
                if (data.success) {
                    resultsDiv.innerHTML = `<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg font-mono">✅ ${data.message}</div>`;
                    showToast('বাল্ক ইম্পোর্ট সফল!');
                    loadInventory(1);
                } else {
                    resultsDiv.innerHTML = `<div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg font-mono">❌ ${data.message}</div>`;
                }
            })
            .catch(() => {
                resultsDiv.classList.remove('hidden');
                resultsDiv.innerHTML = `<div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg font-mono">❌ সার্ভার সমস্যা হয়েছে।</div>`;
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'ইম্পোর্ট শুরু করুন';
            });
        }
    </script>
</body>
</html>
