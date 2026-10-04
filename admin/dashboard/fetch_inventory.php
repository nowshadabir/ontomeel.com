<?php
require_once __DIR__ . '/../../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'অনুমতি নেই (Unauthorized)']);
    exit();
}

$page = isset($_GET['page']) && (int)$_GET['page'] >= 1 ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && (int)$_GET['limit'] >= 1 && (int)$_GET['limit'] <= 100 ? (int)$_GET['limit'] : 20;
$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$category = isset($_GET['category']) ? trim((string)$_GET['category']) : 'all';

// Build WHERE conditions
$where_clauses = ["b.is_active = 1"];
$params = [];

if ($category !== 'all' && $category !== '' && $category !== '%') {
    if (is_numeric($category)) {
        $where_clauses[] = "b.category_id = ?";
        $params[] = (int)$category;
    } else {
        $where_clauses[] = "c.name = ?";
        $params[] = $category;
    }
}

if (!empty($search)) {
    $clean_search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
    $search_param = '%' . $clean_search . '%';
    $where_clauses[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.title_en LIKE ? OR b.author_en LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Count total records for pagination
$count_query = "SELECT COUNT(*) FROM books b LEFT JOIN categories c ON b.category_id = c.id $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_records / $limit));

if ($page > $total_pages && $total_records > 0) {
    $page = $total_pages;
}
$offset = ($page - 1) * $limit;

// Fetch paginated data
$inv_query = "SELECT b.*, c.name as category_name 
              FROM books b 
              LEFT JOIN categories c ON b.category_id = c.id 
              $where_sql
              ORDER BY b.created_at DESC
              LIMIT $limit OFFSET $offset";

$inv_stmt = $pdo->prepare($inv_query);
$inv_stmt->execute($params);
$inventory_books = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('bn_num')) {
    function bn_num($num) {
        if ($num === null || $num === '') return '০';
        $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace(range(0, 9), $bn_digits, (string)$num);
    }
}

// Generate HTML for the table rows
$html = '';
if (empty($inventory_books)) {
    $html = '<tr><td colspan="5" class="py-16 text-center text-stone-500 font-mono text-xs">কোনো বই পাওয়া যায়নি।</td></tr>';
} else {
    foreach ($inventory_books as $book) {
        $stock_qty = (int)($book['stock_qty'] ?? 0);
        $stock_percent = min(100, max(0, ($stock_qty / 20) * 100));
        
        $img_src = !empty($book['cover_image']) ? '/admin/assets/book-images/' . htmlspecialchars($book['cover_image'], ENT_QUOTES, 'UTF-8') : '';
        $book_json = htmlspecialchars(json_encode($book, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        
        $category_name = !empty($book['category_name']) ? htmlspecialchars($book['category_name'], ENT_QUOTES, 'UTF-8') : 'N/A';
        $title = htmlspecialchars((string)($book['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars((string)($book['author'] ?? ''), ENT_QUOTES, 'UTF-8');
        $has_disc = ($book['discount_price'] > 0 && $book['discount_price'] < $book['sell_price']);
        $eff_price = $has_disc ? $book['discount_price'] : $book['sell_price'];
        $price_html = '৳' . bn_num($eff_price);
        if ($has_disc) {
            $price_html .= ' <span class="text-xs text-stone-400 line-through ml-1 font-normal">৳' . bn_num($book['sell_price']) . '</span>';
        }
        $book_id = (int)$book['id'];
        $book_slug = (string)($book['slug'] ?? '');
        $book_url = !empty($book_slug) ? '/books/' . urlencode($book_slug) : '/book-details.php?id=' . $book_id;

        $html .= '
        <tr class="hover:bg-[#faf8f5] transition-colors">
            <td class="py-4 px-5">
                <div class="flex items-center gap-3.5">
                    <a href="'.$book_url.'" target="_blank" class="w-10 h-14 rounded bg-[#f4f1ea] border border-[#d8d3c7] overflow-hidden flex-shrink-0 block shadow-xs" title="বইটি দেখুন">
                        <img src="'.$img_src.'" class="w-full h-full object-cover" onerror="this.style.display=\'none\';">
                    </a>
                    <div>
                        <a href="'.$book_url.'" target="_blank" class="font-bold text-stone-900 hover:text-stone-700 transition-colors text-sm flex items-center gap-1.5" title="বইটি দেখুন">
                            <span>'.$title.'</span>
                            <svg class="w-3.5 h-3.5 text-stone-400 hover:text-stone-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <p class="text-xs text-stone-500 mt-0.5">'.$author.'</p>
                        '.(!empty($book['isbn']) ? '<p class="text-[10px] text-stone-500 font-mono mt-0.5">ISBN: '.htmlspecialchars((string)$book['isbn'], ENT_QUOTES, 'UTF-8').'</p>' : '').'
                    </div>
                </div>
            </td>
            <td class="py-4 px-5">
                <span class="inline-block px-2.5 py-1 bg-[#f4f1ea] text-stone-800 rounded text-xs border border-[#ded8cc]">
                    '.$category_name.'
                </span>
            </td>
            <td class="py-4 px-5 font-mono text-stone-900 font-bold">
                '.$price_html.'
            </td>
            <td class="py-4 px-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-20 bg-[#f0ece3] h-2 rounded-full overflow-hidden">
                        <div class="bg-stone-800 h-full rounded-full" style="width: '.$stock_percent.'%"></div>
                    </div>
                    <span class="text-xs font-mono font-bold '.($stock_qty <= 0 ? 'text-rose-600' : 'text-stone-900').'">'.bn_num($stock_qty).'টি</span>
                </div>
                <p class="text-[10px] font-mono mt-1 '.($stock_qty <= 0 ? 'text-rose-600 font-bold' : ($stock_qty <= 5 ? 'text-amber-700' : 'text-stone-500')).'">
                    '.($stock_qty <= 0 ? 'স্টক আউট' : ($stock_qty <= 5 ? 'স্টক সীমিত' : 'ইন স্টক')).'
                </p>
            </td>
            <td class="py-4 px-5 text-right font-mono">
                <div class="flex items-center justify-end gap-1.5">
                    <button type="button" onclick=\'editBook('.$book_json.')\' class="p-1.5 text-stone-600 hover:text-stone-900 hover:bg-[#eae5db] rounded transition-colors" title="সম্পাদনা">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button type="button" onclick="deleteBook('.$book_id.')" class="p-1.5 text-stone-600 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="মুছে ফেলুন">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        </tr>';
    }
}

// Pagination Info
$start_record = $total_records > 0 ? ($offset + 1) : 0;
$end_record = min($offset + $limit, $total_records);
$prev_page = max(1, $page - 1);
$next_page = min($total_pages, $page + 1);

$pagination_html = '
    <span class="text-xs font-mono text-stone-500">দেখানো হচ্ছে '.bn_num($start_record).'-'.bn_num($end_record).' (মোট '.bn_num($total_records).'টি)</span>
    <div class="flex gap-2">
        <button type="button" onclick="changeInventoryPage('.$prev_page.')" '.($page <= 1 ? 'disabled' : '').' class="px-3 py-1.5 bg-[#f4f1ea] border border-[#d8d3c7] rounded-lg text-xs font-mono text-stone-700 hover:bg-[#eae5db] transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-xs">পূর্ববর্তী</button>
        <button type="button" onclick="changeInventoryPage('.$next_page.')" '.($page >= $total_pages ? 'disabled' : '').' class="px-3 py-1.5 bg-stone-900 text-white font-semibold rounded-lg text-xs font-mono hover:bg-stone-800 transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-xs">পরবর্তী</button>
    </div>
';

echo json_encode([
    'success' => true,
    'html' => $html,
    'pagination' => $pagination_html,
    'total_records' => $total_records,
    'current_page' => $page,
    'total_pages' => $total_pages
], JSON_UNESCAPED_UNICODE);
