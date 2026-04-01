<?php
include '../../includes/db_connect.php';

// Check Authentication (Standard for admin files)
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$cat_id = isset($_GET['category']) && $_GET['category'] != 'all' ? (int)$_GET['category'] : '%';

// Count total records for pagination
$count_query = "SELECT COUNT(*) FROM books b WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?) AND (COALESCE(b.category_id, '') LIKE ?) AND b.is_active = 1";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute([$search, $search, $search, $cat_id]);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch paginated data
$inv_query = "SELECT b.*, c.name as category_name 
              FROM books b 
              LEFT JOIN categories c ON b.category_id = c.id 
              WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?) 
              AND (COALESCE(b.category_id, '') LIKE ?)
              AND b.is_active = 1
              ORDER BY b.created_at DESC
              LIMIT ? OFFSET ?";
$inv_stmt = $pdo->prepare($inv_query);
$inv_stmt->execute([$search, $search, $search, $cat_id, $limit, $offset]);
$inventory_books = $inv_stmt->fetchAll();

function bn_num($num) {
    if ($num === null || $num === '') return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}

// Generate HTML for the table rows
$html = '';
if (empty($inventory_books)) {
    $html = '<tr><td colspan="5" class="px-8 py-20 text-center text-gray-400 font-anek">কোনো বই পাওয়া যায়নি।</td></tr>';
} else {
    foreach ($inventory_books as $book) {
        $stock_percent = min(100, ($book['stock_qty'] / 20) * 100);
        $stock_color = ($book['stock_qty'] <= 5) ? 'bg-red-500' : 'bg-green-500';
        $stock_label = ($book['stock_qty'] <= 5) ? 'স্টক কম' : 'ইন স্টক';
        $stock_label_color = ($book['stock_qty'] <= 5) ? 'text-red-500' : 'text-green-500';
        
        $img_src = !empty($book['cover_image']) ? '../../admin/assets/book-images/' . $book['cover_image'] : '';
        $book_json = htmlspecialchars(json_encode($book), ENT_QUOTES, 'UTF-8');
        
        $html .= '
        <tr class="hover:bg-gray-50/30 transition-colors">
            <td class="px-8 py-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-16 rounded-lg overflow-hidden bg-gray-100 shadow-sm flex-shrink-0">
                        <img src="'.$img_src.'" class="w-full h-full object-cover" onerror="this.style.display=\'none\'; this.parentElement.style.background=\'#f3f4f6\';">
                    </div>
                    <div>
                        <p class="font-bold text-brand-900">'.htmlspecialchars($book['title']).'</p>
                        <p class="text-xs text-gray-400">'.htmlspecialchars($book['author']).'</p>
                    </div>
                </div>
            </td>
            <td class="px-8 py-5 text-sm font-medium text-gray-500">
                '.($book['category_name'] ?: 'N/A').'
            </td>
            <td class="px-8 py-5 text-sm font-bold text-brand-900">
                ৳'.bn_num($book['sell_price']).'</td>
            <td class="px-8 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-24 bg-gray-100 h-2 rounded-full overflow-hidden">
                        <div class="'.$stock_color.' h-full" style="width: '.$stock_percent.'%"></div>
                    </div>
                    <span class="text-xs font-bold text-brand-900">'.bn_num($book['stock_qty']).'টি</span>
                </div>
                <p class="text-[9px] '.$stock_label_color.' font-bold uppercase mt-1">
                    '.$stock_label.'
                </p>
            </td>
            <td class="px-8 py-5 text-right">
                <div class="flex justify-end gap-2">
                    <button onclick=\'editBook('.$book_json.')\' class="p-2 text-gray-400 hover:text-brand-gold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button onclick="deleteBook('.$book['id'].')" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        </tr>';
    }
}

// Pagination Info
$start_record = ($offset + 1);
$end_record = min($offset + $limit, $total_records);
$pagination_html = '
    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">দেখানো হচ্ছে '.bn_num($start_record).'-'.bn_num($end_record).' (মোট '.bn_num($total_records).'টি বইয়ের মধ্যে)</p>
    <div class="flex gap-2">
        <button onclick="changeInventoryPage('.($page - 1).')" '.($page <= 1 ? 'disabled' : '').' class="px-4 py-2 bg-white border border-gray-100 rounded-xl text-xs font-bold text-brand-900 shadow-sm hover:border-brand-gold transition-all disabled:opacity-50 disabled:cursor-not-allowed">পূর্ববর্তী</button>
        <button onclick="changeInventoryPage('.($page + 1).')" '.($page >= $total_pages ? 'disabled' : '').' class="px-4 py-2 bg-brand-900 text-white rounded-xl text-xs font-bold shadow-md hover:bg-brand-gold hover:text-brand-900 transition-all disabled:opacity-50 disabled:cursor-not-allowed">পরবর্তী</button>
    </div>
';

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'html' => $html,
    'pagination' => $pagination_html,
    'total_records' => $total_records,
    'current_page' => $page,
    'total_pages' => $total_pages
]);
