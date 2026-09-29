<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'suggested';
$page_title = 'সাজেস্টেড বই তালিকা';

// Fetch suggested books
$stmt = $pdo->query("
    SELECT b.*, c.name as cat_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.is_suggested = 1 
    ORDER BY b.id DESC
");
$suggested_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_suggested = count($suggested_books);
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
                            <?php echo bn_num($total_suggested); ?> টি বই
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">হোমপেজ ও বিশেষ সেকশনে প্রদর্শিত সাজেস্টেড বইসমূহের তালিকা।</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/admin/inventory" class="inline-flex items-center gap-2 px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>ইনভেন্টরি দেখুন</span>
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 font-bold">বই ও লেখক</th>
                                <th class="py-3.5 px-5 font-bold">ক্যাটাগরি</th>
                                <th class="py-3.5 px-5 font-bold">মূল্য</th>
                                <th class="py-3.5 px-5 font-bold">স্টক</th>
                                <th class="py-3.5 px-5 font-bold text-right">স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                            <?php if (empty($suggested_books)): ?>
                                <tr>
                                    <td colspan="5" class="py-16 text-center text-stone-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                            <p class="text-sm text-stone-800 font-bold">কোনো সাজেস্টেড বই নেই</p>
                                            <p class="text-xs text-stone-500">ইনভেন্টরি থেকে বই সম্পাদনা করে সাজেস্টেড অপশন চালু করুন।</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suggested_books as $book): ?>
                                    <tr class="hover:bg-[#faf8f5] transition-colors">
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-10 h-14 bg-[#f4f1ea] rounded border border-[#d8d3c7] overflow-hidden flex-shrink-0 shadow-xs">
                                                    <?php if (!empty($book['cover_image'])): ?>
                                                        <img src="/admin/assets/book-images/<?php echo htmlspecialchars($book['cover_image']); ?>"
                                                             class="w-full h-full object-cover"
                                                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                                                             onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-[10px] text-stone-400\'>NO IMG</span>';">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-[10px] text-stone-400 font-mono">N/A</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <a href="/admin/inventory" class="font-bold text-stone-900 hover:text-stone-700 transition-colors text-sm block">
                                                        <?php echo htmlspecialchars($book['title']); ?>
                                                    </a>
                                                    <span class="text-xs text-stone-500 block mt-0.5">
                                                        <?php echo htmlspecialchars($book['author']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="inline-block px-2.5 py-1 bg-[#f4f1ea] text-stone-800 rounded text-xs border border-[#ded8cc]">
                                                <?php echo htmlspecialchars($book['cat_name'] ?: 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 font-mono text-stone-900 font-bold">
                                            ৳<?php echo bn_num(number_format((float)($book['discount_price'] > 0 ? $book['discount_price'] : $book['sell_price']))); ?>
                                        </td>
                                        <td class="py-4 px-5 font-mono font-bold <?php echo ((int)$book['stock_qty'] <= 0) ? 'text-rose-600' : 'text-stone-800'; ?>">
                                            <?php echo bn_num((int)$book['stock_qty']); ?> টি
                                        </td>
                                        <td class="py-4 px-5 text-right font-mono">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>সাজেস্টেড এক্টিভ</span>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
