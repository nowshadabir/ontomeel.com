<?php
include '../includes/db_connect.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$where = "WHERE b.is_active = 1";
$params = [];

if (!empty($search)) {
    $where .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.title_en LIKE ? OR b.author_en LIKE ? OR c.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; // title
    $params[] = $search_param; // author
    $params[] = $search_param; // title_en
    $params[] = $search_param; // author_en
    $params[] = $search_param; // category name
}

if (!empty($category)) {
    $where .= " AND c.name = ?";
    $params[] = $category;
}

// Total Count for this query
$count_query = "SELECT COUNT(*) FROM books b LEFT JOIN categories c ON b.category_id = c.id $where";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_books = $stmt->fetchColumn();

// Fetch Books
$query = "SELECT b.id, b.title, b.author, b.sell_price, b.cover_image, b.stock_qty, b.is_borrowable, b.is_suggested, c.name as category_name 
          FROM books b 
          LEFT JOIN categories c ON b.category_id = c.id 
          $where
          ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

function getBookImagePath($image)
{
    if (!empty($image)) {
        return '../admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

$formatted_books = [];
foreach ($books as $book) {
    $formatted_books[] = [
        'id' => $book['id'],
        'title' => $book['title'],
        'author' => $book['author'],
        'price' => $book['sell_price'] ?? 0,
        'img' => getBookImagePath($book['cover_image'] ?? ''),
        'category' => $book['category_name'] ?? 'Uncategorized',
        'is_borrowable' => $book['is_borrowable'] ?? 0,
        'is_suggested' => $book['is_suggested'] ?? 0,
        'stock_qty' => $book['stock_qty'] ?? 0
    ];
}

echo json_encode([
    'books' => $formatted_books,
    'total' => (int)$total_books,
    'page' => $page,
    'has_more' => ($offset + count($books)) < $total_books
]);
