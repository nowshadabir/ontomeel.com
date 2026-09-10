<?php
/**
 * Ontomeel Bookshop - CSV Bulk Import Processor
 * Imports books in bulk from a CSV file, with automatic image downloading,
 * Bengali number normalization, and smart category mapping.
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../includes/db_connect.php';

// Set execution limits for batch processing
set_time_limit(300);
ini_set('memory_limit', '256M');

// 1. Authenticate Admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে পুনরায় লগইন করুন।']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
    echo json_encode(['success' => false, 'message' => 'অনুগ্রহ করে একটি বৈধ CSV ফাইল আপলোড করুন।']);
    exit();
}

$file = $_FILES['csv_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'ফাইল আপলোডে ত্রুটি ঘটেছে (Error Code: ' . $file['error'] . ')']);
    exit();
}

// Validate file extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv' && $ext !== 'txt') {
    echo json_encode(['success' => false, 'message' => 'শুধুমাত্র .csv ফরম্যাটের ফাইল সমর্থিত।']);
    exit();
}

$target_dir = __DIR__ . '/../assets/book-images/';
if (!is_dir($target_dir)) {
    @mkdir($target_dir, 0755, true);
}

// 2. Fetch all existing categories for smart matching
$categories_stmt = $pdo->query("SELECT id, name, slug FROM categories");
$categories_list = $categories_stmt->fetchAll();
$category_map = [];
$default_book_cat_id = 33; // Fallback to "Books" category

foreach ($categories_list as $cat) {
    $category_map[strtolower(trim($cat['name']))] = (int)$cat['id'];
    $category_map[strtolower(trim($cat['slug']))] = (int)$cat['id'];
    $category_map[(string)$cat['id']] = (int)$cat['id'];
    if (strtolower(trim($cat['name'])) === 'books' || strtolower(trim($cat['slug'])) === 'books') {
        $default_book_cat_id = (int)$cat['id'];
    }
}

// 3. Helper Functions
function convertBnToEnNum($str) {
    if ($str === null || $str === '') return '';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $converted = str_replace($bn_digits, $en_digits, (string)$str);
    // Remove any currency symbols, commas or extra whitespace
    return trim(str_replace(['৳', '$', ',', ' '], '', $converted));
}

function downloadAndOptimizeImage($url_or_name, $target_dir, $prefix = 'cover') {
    $url_or_name = trim($url_or_name);
    if (empty($url_or_name)) return null;

    // If it's already a local file name in target_dir
    if (strpos($url_or_name, 'http://') !== 0 && strpos($url_or_name, 'https://') !== 0) {
        if (file_exists($target_dir . $url_or_name)) {
            return $url_or_name;
        }
        return null;
    }

    // It's a remote URL: Download via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_or_name);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$data || $http_code < 200 || $http_code >= 300) {
        // Fallback: Return raw external URL if download fails so image is not lost
        return $url_or_name;
    }

    // Attempt GD conversion to webp
    $img = @imagecreatefromstring($data);
    $unique_suffix = time() . '_' . mt_rand(100, 999) . '_' . $prefix;
    
    if ($img && function_exists('imagewebp')) {
        $filename = $unique_suffix . '.webp';
        $dest = $target_dir . $filename;
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        if (imagewebp($img, $dest, 82)) {
            imagedestroy($img);
            return $filename;
        }
        imagedestroy($img);
    }

    // Fallback: Save original image directly
    $ext = 'jpg';
    if (strpos($content_type, 'png') !== false) $ext = 'png';
    elseif (strpos($content_type, 'webp') !== false) $ext = 'webp';
    elseif (strpos($content_type, 'gif') !== false) $ext = 'gif';

    $filename = $unique_suffix . '.' . $ext;
    if (file_put_contents($target_dir . $filename, $data)) {
        return $filename;
    }

    return $url_or_name;
}

// 4. Open and Parse CSV File
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'CSV ফাইলটি পড়া সম্ভব হয়নি।']);
    exit();
}

// Read Header Row
$header = fgetcsv($handle, 0, ',', '"', '\\');
if (!$header) {
    fclose($handle);
    echo json_encode(['success' => false, 'message' => 'CSV ফাইলটিতে কোনো ডাটা বা হেডার পাওয়া যায়নি।']);
    exit();
}

// Remove UTF-8 BOM from the first header key if present
$header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
$header[0] = trim($header[0]);

// Normalize header map (lowercase and trimmed)
$header_map = [];
foreach ($header as $idx => $col_name) {
    $clean_col = strtolower(trim($col_name));
    $header_map[$clean_col] = $idx;
}

// Ensure required title and author columns exist in headers
$title_idx = $header_map['title'] ?? $header_map['book_title'] ?? $header_map['name'] ?? null;
$author_idx = $header_map['author'] ?? $header_map['author_name'] ?? $header_map['writer'] ?? null;

if ($title_idx === null || $author_idx === null) {
    fclose($handle);
    echo json_encode([
        'success' => false,
        'message' => 'CSV হেডার সঠিক নয়। "title" এবং "author" কলাম দুটি থাকা বাধ্যতামূলক। স্যাম্পল CSV টেমপ্লেট ডাউনলোড করে যাচাই করুন।'
    ]);
    exit();
}

// 5. Process Rows
$imported_count = 0;
$skipped_count = 0;
$row_number = 1;
$errors = [];
$imported_titles = [];

$insert_sql = "INSERT INTO books (
    title, title_en, subtitle, description, category_id, genre, language,
    author, author_en, co_author, publisher, publish_year, edition,
    isbn, format, page_count, book_condition, shelf_location, rack_number,
    stock_qty, min_stock_level, is_borrowable, is_suggested,
    purchase_price, sell_price, discount_price,
    supplier_name, supplier_contact, cover_image, photo_2, photo_3,
    is_active, created_at, item_type
) VALUES (
    ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?, ?, ?,
    1, NOW(), 'Book'
)";
$insert_stmt = $pdo->prepare($insert_sql);

while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
    $row_number++;

    // Skip empty lines
    if (empty(array_filter($row))) {
        continue;
    }

    $getVal = function($key, $default = '') use ($row, $header_map) {
        $idx = $header_map[$key] ?? null;
        return ($idx !== null && isset($row[$idx])) ? trim($row[$idx]) : $default;
    };

    $title = $getVal('title', $getVal('book_title', ''));
    $author = $getVal('author', $getVal('writer', ''));

    if (empty($title)) {
        $errors[] = "সারি #{$row_number}: বইয়ের নাম (Title) খালি থাকায় বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    if (empty($author)) {
        $errors[] = "সারি #{$row_number} ('{$title}'): লেখকের নাম (Author) খালি থাকায় বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    // Sell Price Parsing
    $sell_price_raw = convertBnToEnNum($getVal('sell_price', $getVal('price', '')));
    if ($sell_price_raw === '' || !is_numeric($sell_price_raw)) {
        $errors[] = "সারি #{$row_number} ('{$title}'): বিক্রয় মূল্য (Sell Price) সঠিক সংখ্যা না থাকায় বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }
    $sell_price = (float)$sell_price_raw;

    // Original Price & Discount Calculation
    $original_price_raw = convertBnToEnNum($getVal('original_price', '0'));
    $original_price = is_numeric($original_price_raw) ? (float)$original_price_raw : 0;
    $discount_price = 0;

    if ($original_price > $sell_price) {
        // Option A: Original MRP (800) and Sell Price (750) -> Discount = 50
        $discount_price = $original_price - $sell_price;
    } else {
        // Option B: Direct discount amount passed (e.g. 50)
        $direct_discount_raw = convertBnToEnNum($getVal('discount_price', '0'));
        if (is_numeric($direct_discount_raw) && (float)$direct_discount_raw > 0) {
            $discount_price = (float)$direct_discount_raw;
        }
    }

    // Purchase Price
    $purchase_price_raw = convertBnToEnNum($getVal('purchase_price', '0'));
    $purchase_price = is_numeric($purchase_price_raw) ? (float)$purchase_price_raw : 0.00;

    // Stock Quantity & Alerts
    $stock_qty_raw = convertBnToEnNum($getVal('stock_qty', $getVal('stock', '1')));
    $stock_qty = is_numeric($stock_qty_raw) ? (int)$stock_qty_raw : 1;

    $min_stock_raw = convertBnToEnNum($getVal('min_stock_level', '2'));
    $min_stock_level = is_numeric($min_stock_raw) ? (int)$min_stock_raw : 2;

    // Borrowable flag
    $is_borrow_raw = strtolower($getVal('is_borrowable', '1'));
    $is_borrowable = ($is_borrow_raw === '1' || $is_borrow_raw === 'yes' || $is_borrow_raw === 'true' || $is_borrow_raw === 'হ্যাঁ') ? 1 : 0;

    // Category Resolution
    $category_input = strtolower(trim($getVal('category', '')));
    $category_id = $category_map[$category_input] ?? $default_book_cat_id;

    // Format & Condition validation
    $format_input = ucfirst(strtolower($getVal('format', 'Paperback')));
    if (!in_array($format_input, ['Paperback', 'Hardcover', 'E-book'])) {
        $format_input = 'Paperback';
    }

    $condition_input = ucfirst(strtolower($getVal('book_condition', 'New')));
    if (!in_array($condition_input, ['New', 'Used', 'Damaged'])) {
        $condition_input = 'New';
    }

    // Page count & Publish year
    $page_count_raw = convertBnToEnNum($getVal('page_count', '0'));
    $page_count = is_numeric($page_count_raw) ? (int)$page_count_raw : 0;

    $publish_year = convertBnToEnNum($getVal('publish_year', ''));
    if (strlen($publish_year) > 4) $publish_year = substr($publish_year, 0, 4);

    // Metadata Fields
    $title_en = $getVal('title_en', '');
    $subtitle = $getVal('subtitle', '');
    $author_en = $getVal('author_en', '');
    $co_author = $getVal('co_author', '');
    $genre = $getVal('genre', '');
    $publisher = $getVal('publisher', '');
    $edition = $getVal('edition', '');
    $language = $getVal('language', 'Bengali');
    $isbn = convertBnToEnNum($getVal('isbn', ''));
    $shelf_location = $getVal('shelf_location', '');
    $rack_number = $getVal('rack_number', '');
    $supplier_name = $getVal('supplier_name', '');
    $supplier_contact = $getVal('supplier_contact', '');
    $description = $getVal('description', '');

    // Image URL Resolution & Download
    $cover_url = $getVal('cover_image_url', $getVal('cover_image', ''));
    $photo_2_url = $getVal('photo_2_url', $getVal('photo_2', ''));
    $photo_3_url = $getVal('photo_3_url', $getVal('photo_3', ''));

    $cover_image = !empty($cover_url) ? downloadAndOptimizeImage($cover_url, $target_dir, 'cover') : null;
    $photo_2 = !empty($photo_2_url) ? downloadAndOptimizeImage($photo_2_url, $target_dir, 'p2') : null;
    $photo_3 = !empty($photo_3_url) ? downloadAndOptimizeImage($photo_3_url, $target_dir, 'p3') : null;

    try {
        $insert_stmt->execute([
            $title,
            $title_en ?: null,
            $subtitle ?: null,
            $description ?: null,
            $category_id,
            $genre ?: null,
            $language,
            $author,
            $author_en ?: null,
            $co_author ?: null,
            $publisher ?: null,
            $publish_year ?: null,
            $edition ?: null,
            $isbn ?: null,
            $format_input,
            $page_count,
            $condition_input,
            $shelf_location ?: null,
            $rack_number ?: null,
            $stock_qty,
            $min_stock_level,
            $is_borrowable,
            0, // is_suggested
            $purchase_price,
            $sell_price,
            $discount_price,
            $supplier_name ?: null,
            $supplier_contact ?: null,
            $cover_image,
            $photo_2,
            $photo_3
        ]);

        $imported_count++;
        $imported_titles[] = $title;
    } catch (PDOException $e) {
        $errors[] = "সারি #{$row_number} ('{$title}'): ডাটাবেজ ত্রুটি - " . $e->getMessage();
        $skipped_count++;
    }
}

fclose($handle);

echo json_encode([
    'success' => true,
    'total_processed' => $imported_count + $skipped_count,
    'imported_count' => $imported_count,
    'skipped_count' => $skipped_count,
    'errors' => $errors,
    'imported_titles' => array_slice($imported_titles, 0, 10),
    'message' => "{$imported_count} টি বই সফলভাবে ইম্পোর্ট করা হয়েছে!" . ($skipped_count > 0 ? " ({$skipped_count} টি সারি বাদ পড়েছে)" : "")
]);
exit();
