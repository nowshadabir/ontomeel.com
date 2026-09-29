<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'borrows';
$page_title = 'বরো ট্র্যাকার';

// Fetch active & ongoing borrows
$borrows_stmt = $pdo->query("
    SELECT br.*, b.title, b.cover_image, b.sell_price, m.full_name, m.phone, m.membership_id, o.order_status 
    FROM borrows br 
    JOIN books b ON br.book_id = b.id 
    JOIN members m ON br.member_id = m.id 
    LEFT JOIN orders o ON br.order_id = o.id 
    ORDER BY br.borrow_date DESC
");
$all_borrows = $borrows_stmt->fetchAll(PDO::FETCH_ASSOC);

// Synchronize statuses & counts
$active_count = 0;
$overdue_count = 0;
$processing_count = 0;
$returned_count = 0;

foreach ($all_borrows as &$borrow) {
    // 1. Sync Service: If order is Shipped/Delivered, Borrow MUST be Active
    if ($borrow['status'] === 'Processing' && in_array($borrow['order_status'], ['Shipped', 'Delivered'])) {
        $pdo->prepare("UPDATE borrows SET status = 'Active', borrow_date = CURRENT_TIMESTAMP WHERE id = ?")
            ->execute([$borrow['id']]);
        $borrow['status'] = 'Active';
        $borrow['borrow_date'] = date('Y-m-d H:i:s');
    }

    // 2. Overdue Check
    $is_overdue = $borrow['status'] === 'Active' && strtotime($borrow['due_date'] . ' 23:59:59') < time();
    if ($is_overdue) {
        $pdo->prepare("UPDATE borrows SET status = 'Overdue' WHERE id = ?")->execute([$borrow['id']]);
        $borrow['status'] = 'Overdue';
    }

    if ($borrow['status'] === 'Active') $active_count++;
    elseif ($borrow['status'] === 'Overdue') $overdue_count++;
    elseif ($borrow['status'] === 'Processing') $processing_count++;
    elseif ($borrow['status'] === 'Returned') $returned_count++;
}
unset($borrow);
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
                            মোট <?php echo bn_num(count($all_borrows)); ?> টি রেকর্ড
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">পাঠকদের বই ধার নেওয়ার অনুরোধ, সক্রিয় পড়ার অগ্রগতি ও রিটার্ন ম্যানেজমেন্ট।</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f3f0e8] text-stone-700 border border-[#d8d3c7] hover:border-stone-400 rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>রিফ্রেশ</span>
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">সক্রিয় ধার</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2"><?php echo bn_num($active_count); ?></div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">বর্তমানে পাঠকদের হাতে আছে</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মেয়াদোত্তীর্ণ (Overdue)</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-rose-600 mt-2"><?php echo bn_num($overdue_count); ?></div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">ফেরতের নির্ধারিত সময় পার হয়েছে</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">প্রসেসিং হচ্ছে</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-amber-700 mt-2"><?php echo bn_num($processing_count); ?></div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">ডেলিভারির অপেক্ষায় রয়েছে</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">ফেরত গৃহীত (Returned)</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2"><?php echo bn_num($returned_count); ?></div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">সফলভাবে লাইব্রেরিতে ফেরত</span>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white border border-[#e7e3da] p-3 rounded-xl flex flex-wrap items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto">
                    <button onclick="filterBorrowStatus('all')" class="bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white shadow-xs" data-status="all">
                        সকল (<?php echo count($all_borrows); ?>)
                    </button>
                    <button onclick="filterBorrowStatus('Active')" class="bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-700 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Active">
                        সক্রিয় (<?php echo $active_count; ?>)
                    </button>
                    <button onclick="filterBorrowStatus('Overdue')" class="bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-700 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Overdue">
                        মেয়াদোত্তীর্ণ (<?php echo $overdue_count; ?>)
                    </button>
                    <button onclick="filterBorrowStatus('Processing')" class="bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-700 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]" data-status="Processing">
                        প্রসেসিং (<?php echo $processing_count; ?>)
                    </button>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" id="borrowSearch" onkeyup="searchBorrows()" placeholder="সদস্য বা বই খুঁজুন..."
                           class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-1.5 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                    <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 font-bold">গ্রাহক ও মেম্বার আইডি</th>
                                <th class="py-3.5 px-5 font-bold">বই</th>
                                <th class="py-3.5 px-5 font-bold">বরো তারিখ</th>
                                <th class="py-3.5 px-5 font-bold">শেষ তারিখ (Due)</th>
                                <th class="py-3.5 px-5 font-bold">অগ্রগতি ও স্ট্যাটাস</th>
                                <th class="py-3.5 px-5 font-bold text-right">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans" id="borrowsTableBody">
                            <?php if (empty($all_borrows)): ?>
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-stone-500">
                                        <p class="text-sm text-stone-600 font-medium">কোনো সক্রিয় বা পূর্বের বরো রেকর্ড পাওয়া যায়নি।</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_borrows as $borrow):
                                    $is_overdue = $borrow['status'] === 'Overdue';
                                    $search_text = strtolower($borrow['full_name'] . ' ' . $borrow['phone'] . ' ' . ($borrow['membership_id'] ?? '') . ' ' . $borrow['title']);
                                    ?>
                                    <tr class="borrow-row hover:bg-[#faf8f5] transition-colors" data-status="<?php echo htmlspecialchars($borrow['status']); ?>" data-search="<?php echo htmlspecialchars($search_text); ?>">
                                        <td class="py-4 px-5">
                                            <div class="font-bold text-stone-900 text-sm">
                                                <?php echo htmlspecialchars($borrow['full_name']); ?>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-stone-500 font-mono">
                                                <span><?php echo htmlspecialchars($borrow['phone']); ?></span>
                                                <?php if (!empty($borrow['membership_id'])): ?>
                                                    <span class="text-stone-400">•</span>
                                                    <span class="text-stone-800 font-semibold"><?php echo htmlspecialchars($borrow['membership_id']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-11 rounded bg-[#f4f1ea] border border-[#d8d3c7] overflow-hidden flex-shrink-0 shadow-xs">
                                                    <?php if (!empty($borrow['cover_image'])): ?>
                                                        <img src="/admin/assets/book-images/<?php echo htmlspecialchars($borrow['cover_image']); ?>"
                                                             class="w-full h-full object-cover"
                                                             onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-xs font-semibold text-stone-900 line-clamp-2 max-w-[200px]">
                                                    <?php echo htmlspecialchars($borrow['title']); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 font-mono text-xs text-stone-500">
                                            <?php echo !empty($borrow['borrow_date']) ? date('d M, Y', strtotime($borrow['borrow_date'])) : '—'; ?>
                                        </td>
                                        <td class="py-4 px-5 font-mono text-xs <?php echo $is_overdue ? 'text-rose-600 font-bold' : 'text-stone-700'; ?>">
                                            <?php echo !empty($borrow['due_date']) ? date('d M, Y', strtotime($borrow['due_date'])) : '—'; ?>
                                        </td>
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-2">
                                                <?php if ($borrow['status'] === 'Active'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        সক্রিয়
                                                    </span>
                                                <?php elseif ($borrow['status'] === 'Overdue'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-800 border border-rose-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        মেয়াদ পার
                                                    </span>
                                                <?php elseif ($borrow['status'] === 'Processing'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        প্রসেসিং
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                                                        <?php echo htmlspecialchars($borrow['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($borrow['status'] === 'Active' || $borrow['status'] === 'Overdue'): ?>
                                                <div class="mt-2 flex items-center gap-2">
                                                    <div class="w-20 h-1.5 bg-[#f0ece3] rounded-full overflow-hidden">
                                                        <div class="bg-stone-800 h-full rounded-full" style="width: <?php echo (int)$borrow['reading_progress']; ?>%"></div>
                                                    </div>
                                                    <span class="text-[10px] font-mono text-stone-500"><?php echo bn_num((int)$borrow['reading_progress']); ?>%</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5 text-right font-mono">
                                            <?php if ($borrow['status'] === 'Active' || $borrow['status'] === 'Overdue'): ?>
                                                <button onclick="returnBook(<?php echo (int)$borrow['id']; ?>)"
                                                        class="px-3 py-1.5 bg-stone-900 hover:bg-stone-800 text-white font-semibold text-xs rounded-lg transition-all shadow-xs">
                                                    ফেরত নিন
                                                </button>
                                            <?php else: ?>
                                                <span class="text-stone-400 text-xs">—</span>
                                            <?php endif; ?>
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

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-stone-900 text-white px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 text-xs font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="toast-message">নোটিফিকেশন বার্তা</span>
        </div>
    </div>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        let currentStatusFilter = 'all';

        function filterBorrowStatus(status) {
            currentStatusFilter = status;
            document.querySelectorAll('.bfilter-btn').forEach(btn => {
                if (btn.getAttribute('data-status') === status) {
                    btn.className = 'bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white shadow-xs';
                } else {
                    btn.className = 'bfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-700 hover:text-stone-900 hover:bg-[#eae5db] border border-[#d8d3c7]';
                }
            });
            applyFilters();
        }

        function searchBorrows() {
            applyFilters();
        }

        function applyFilters() {
            const query = (document.getElementById('borrowSearch').value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.borrow-row');
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowSearch = row.getAttribute('data-search') || '';

                const matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
                const matchesSearch = !query || rowSearch.includes(query);

                if (matchesStatus && matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function returnBook(borrowId) {
            if (!confirm('আপনি কি নিশ্চিত যে বইটি ফেরত নিয়েছেন? এটি ইনভেন্টরি স্টক আপডেট করবে।')) return;

            fetch('/admin/dashboard/return_book.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `borrow_id=${borrowId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('বই ফেরত নেওয়া হয়েছে এবং ইনভেন্টরি আপডেট হয়েছে।');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('ত্রুটি: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('ফেরত নিতে সমস্যা হয়েছে।');
            });
        }
    </script>
</body>
</html>
