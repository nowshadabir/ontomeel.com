<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Helper function to safely escape SQL LIKE wildcards
function escapeLike($str) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $str);
}

// Helper function for book cover image path
function getBookImagePath($image) {
    if (!empty($image)) {
        return '../admin/assets/book-images/' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

// 1. Sanitize & Validate Inputs
$raw_search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
// Limit max length to prevent abuse
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

$discounted = isset($_GET['discounted']) ? trim((string)$_GET['discounted']) : 'all';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) && (float)$_GET['min_price'] >= 0 ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) && (float)$_GET['max_price'] >= 0 ? (float)$_GET['max_price'] : null;

$format = isset($_GET['format']) ? trim((string)$_GET['format']) : 'all';
$allowed_formats = ['all', 'Hardcover', 'Paperback', 'E-book'];
if (!in_array($format, $allowed_formats, true)) {
    $format = 'all';
}

$sort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : 'relevance';
$allowed_sorts = ['relevance', 'price_asc', 'price_desc', 'newest', 'title_asc', 'author_asc'];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = 'relevance';
}

$is_autocomplete = (isset($_GET['mode']) && $_GET['mode'] === 'autocomplete') || (isset($_GET['autocomplete']) && $_GET['autocomplete'] == '1');

$page = isset($_GET['page']) && (int)$_GET['page'] >= 1 ? (int)$_GET['page'] : 1;
$limit = $is_autocomplete ? 6 : (isset($_GET['limit']) && (int)$_GET['limit'] >= 1 && (int)$_GET['limit'] <= 100 ? (int)$_GET['limit'] : 40);
$offset = ($page - 1) * $limit;

// 2. Build Query & Parameters
$where_clauses = ["b.is_active = 1"];
$params = [];
$score_params = [];

// Category filter (by name or ID)
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

// Stock filter
if ($stock === 'in_stock') {
    $where_clauses[] = "b.stock_qty > 0";
} elseif ($stock === 'out_of_stock') {
    $where_clauses[] = "b.stock_qty <= 0";
}

// Borrowable filter
if ($borrowable === '1') {
    $where_clauses[] = "b.is_borrowable = 1";
} elseif ($borrowable === '0') {
    $where_clauses[] = "b.is_borrowable = 0";
}

// Discounted filter
if ($discounted === '1') {
    $where_clauses[] = "b.discount_price > 0 AND b.discount_price < b.sell_price";
}

// Price range
if ($min_price !== null) {
    $where_clauses[] = "b.sell_price >= ?";
    $params[] = $min_price;
}
if ($max_price !== null) {
    $where_clauses[] = "b.sell_price <= ?";
    $params[] = $max_price;
}

// Format filter
if ($format !== 'all') {
    $where_clauses[] = "b.format = ?";
    $params[] = $format;
}

// Search Query Logic with Smart Tokenization
$has_search = !empty($raw_search);
if ($has_search) {
    // Split into tokens (up to 5 keywords for security/performance)
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
        } else { // 'all'
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

// 3. Count Total Matching Records
$count_query = "SELECT COUNT(*) FROM books b LEFT JOIN categories c ON b.category_id = c.id $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_books = (int)$count_stmt->fetchColumn();

// 4. Determine Dynamic Sorting
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
} else { // 'relevance' (default)
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

// 5. Fetch Matching Books
$query = "SELECT b.id, b.title, b.title_en, b.author, b.author_en, b.publisher, b.isbn, b.sell_price, b.discount_price, b.cover_image, b.stock_qty, b.is_borrowable, b.is_suggested, b.format, c.name as category_name 
          FROM books b 
          LEFT JOIN categories c ON b.category_id = c.id 
          $where_sql 
          $order_by_sql 
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute(array_merge($params, $order_params));
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formatted_books = [];
foreach ($books as $book) {
    $formatted_books[] = [
        'id' => (int)$book['id'],
        'title' => (string)($book['title'] ?? ''),
        'title_en' => (string)($book['title_en'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'publisher' => (string)($book['publisher'] ?? ''),
        'isbn' => (string)($book['isbn'] ?? ''),
        'price' => (float)($book['sell_price'] ?? 0),
        'discount_price' => (float)($book['discount_price'] ?? 0),
        'img' => getBookImagePath($book['cover_image'] ?? ''),
        'category' => (string)($book['category_name'] ?? 'অন্যান্য'),
        'is_borrowable' => (int)($book['is_borrowable'] ?? 0),
        'is_suggested' => (int)($book['is_suggested'] ?? 0),
        'stock_qty' => (int)($book['stock_qty'] ?? 0),
        'format' => (string)($book['format'] ?? 'Paperback')
    ];
}

echo json_encode([
    'success' => true,
    'books' => $formatted_books,
    'total' => $total_books,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => ceil($total_books / max(1, $limit)),
    'has_more' => ($offset + count($books)) < $total_books
], JSON_UNESCAPED_UNICODE);
