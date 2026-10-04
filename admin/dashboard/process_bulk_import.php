<?php
/**
 * Ontomeel Bookshop - CSV Bulk Import Processor
 * Imports books in bulk from a CSV file, with duplicate prevention (ISBN & Title/Author),
 * automatic WebP image compression, Bengali number normalization, and smart category mapping.
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/helpers.php';

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
    @mkdir($target_dir, 0777, true);
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

function downloadAndOptimizeImage($url_or_name, $target_dir, $prefix = 'cover', $max_width = 1600, $max_height = 1600, $quality = 82) {
    $url_or_name = trim($url_or_name);
    if (empty($url_or_name)) return null;

    // If it's already a local file name in target_dir
    if (strpos($url_or_name, 'http://') !== 0 && strpos($url_or_name, 'https://') !== 0) {
        if (file_exists($target_dir . $url_or_name)) {
            return $url_or_name;
        }
        return null;
    }

    // Remote URL: Download via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_or_name);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$data || $http_code < 200 || $http_code >= 300) {
        // Return null or external url
        return null;
    }

    $unique_suffix = time() . '_' . $prefix . '_' . bin2hex(random_bytes(6));

    // Convert & compress to WebP
    if (function_exists('imagewebp')) {
        $img = @imagecreatefromstring($data);
        if ($img) {
            $curr_w = imagesx($img);
            $curr_h = imagesy($img);
            $new_w = $curr_w;
            $new_h = $curr_h;

            if ($curr_w > $max_width || $curr_h > $max_height) {
                $ratio = min($max_width / $curr_w, $max_height / $curr_h);
                $new_w = max(1, (int)round($curr_w * $ratio));
                $new_h = max(1, (int)round($curr_h * $ratio));
            }

            $dst_img = imagecreatetruecolor($new_w, $new_h);
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
            $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
            imagefilledrectangle($dst_img, 0, 0, $new_w, $new_h, $transparent);

            imagecopyresampled($dst_img, $img, 0, 0, 0, 0, $new_w, $new_h, $curr_w, $curr_h);

            $filename = $unique_suffix . '.webp';
            $dest = $target_dir . $filename;

            if (imagewebp($dst_img, $dest, $quality)) {
                imagedestroy($img);
                imagedestroy($dst_img);
                return $filename;
            }

            imagedestroy($img);
            imagedestroy($dst_img);
        }
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

    return null;
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

// Track duplicates inside the current CSV file
$seen_isbns = [];
$seen_books = [];

$insert_sql = "INSERT INTO books (
    title, title_en, slug, subtitle, description, category_id, genre, language,
    author, author_en, co_author, publisher, publish_year, edition,
    isbn, format, page_count, book_condition, shelf_location, rack_number,
    stock_qty, min_stock_level, is_borrowable, is_suggested,
    purchase_price, sell_price, discount_price,
    supplier_name, supplier_contact, cover_image, photo_2, photo_3,
    is_active, created_at, item_type
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?, ?, ?,
    1, NOW(), 'Book'
)";
$insert_stmt = $pdo->prepare($insert_sql);

// Prepared statements for duplicate checks
$isbn_check_stmt = $pdo->prepare("SELECT id, title, author FROM books WHERE (isbn = ? OR REPLACE(REPLACE(REPLACE(isbn, '-', ''), ' ', ''), '_', '') = ?) AND is_active = 1 LIMIT 1");
$title_check_stmt = $pdo->prepare("SELECT id, title, author, isbn FROM books WHERE title = ? AND author = ? AND is_active = 1 LIMIT 1");

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

    $isbn_raw = $getVal('isbn', $getVal('isbn_no', $getVal('isbn_number', $getVal('issbn', ''))));
    $isbn = convertBnToEnNum($isbn_raw);

    if (empty($isbn)) {
        $errors[] = "সারি #{$row_number} ('{$title}'): আইএসবিএন (ISBN) নম্বর অনুপস্থিত থাকায় বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    $clean_isbn = preg_replace('/[^A-Za-z0-9]/', '', $isbn);

    // Duplicate Check 1: In-file duplicate ISBN check
    if (isset($seen_isbns[$clean_isbn])) {
        $errors[] = "সারি #{$row_number} ('{$title}'): এই ISBN ({$isbn}) একই CSV ফাইলের পূর্ববর্তী সারিতে (#{$$seen_isbns[$clean_isbn]}) রয়েছে। ডুপ্লিকেট এন্ট্রি বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    // Duplicate Check 2: Database duplicate ISBN check
    $isbn_check_stmt->execute([$isbn, $clean_isbn]);
    $existing_isbn_book = $isbn_check_stmt->fetch();
    if ($existing_isbn_book) {
        $errors[] = "সারি #{$row_number} ('{$title}'): এই ISBN ({$isbn}) নম্বরের বইটি ইতোমধ্যে ডেটাবেজে রয়েছে ('{$existing_isbn_book['title']}' - #{$existing_isbn_book['id']})। বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    // Duplicate Check 3: Title + Author duplicate check in-file
    $book_key = mb_strtolower(trim($title)) . '||' . mb_strtolower(trim($author));
    if (isset($seen_books[$book_key])) {
        $errors[] = "সারি #{$row_number} ('{$title}'): এই বই ও লেখক একই ফাইলে ডুপ্লিকেট রয়েছে (পূর্ববর্তী সারি #{$$seen_books[$book_key]})। বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    // Duplicate Check 4: Title + Author duplicate check in Database
    $title_check_stmt->execute([$title, $author]);
    $existing_title_book = $title_check_stmt->fetch();
    if ($existing_title_book) {
        $errors[] = "সারি #{$row_number} ('{$title}'): এই বইটি ইতোমধ্যে ডেটাবেজে বিদ্যমান রয়েছে (#{$existing_title_book['id']})। বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }

    // Mark as seen in this file batch
    $seen_isbns[$clean_isbn] = $row_number;
    $seen_books[$book_key] = $row_number;

    $price_a_raw = convertBnToEnNum($getVal('sell_price', $getVal('price', '')));
    if ($price_a_raw === '' || !is_numeric($price_a_raw)) {
        $errors[] = "সারি #{$row_number} ('{$title}'): বিক্রয় মূল্য (Sell Price) সঠিক সংখ্যা না থাকায় বাদ দেওয়া হয়েছে।";
        $skipped_count++;
        continue;
    }
    $price_a = (float)$price_a_raw;

    $price_b_raw = convertBnToEnNum($getVal('original_price', '0'));
    $price_b = is_numeric($price_b_raw) ? (float)$price_b_raw : 0;

    $price_c_raw = convertBnToEnNum($getVal('discount_price', $getVal('offer_price', '0')));
    $price_c = is_numeric($price_c_raw) ? (float)$price_c_raw : 0;

    if ($price_b > $price_a) {
        // Option A: original_price (550) and sell_price (440)
        $sell_price = $price_b;
        $discount_price = $price_a;
    } elseif ($price_c > 0 && $price_c < $price_a) {
        // Option B: sell_price (550) and discount_price (440)
        $sell_price = $price_a;
        $discount_price = $price_c;
    } else {
        // Option C: Regular price only (e.g. 550, no discount)
        $sell_price = $price_a;
        $discount_price = 0;
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
    $shelf_location = $getVal('shelf_location', '');
    $rack_number = $getVal('rack_number', '');
    $supplier_name = $getVal('supplier_name', '');
    $supplier_contact = $getVal('supplier_contact', '');
    $description = $getVal('description', '');

    // Generate unique slug for the imported book
    $slug = get_unique_book_slug($pdo, $title_en, $title);

    // Image URL Resolution & Optimization to WebP (Executed ONLY after passing duplicate checks)
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
            $slug,
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
            $isbn,
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
    'message' => "{$imported_count} টি বই সফলভাবে ইম্পোর্ট করা হয়েছে!" . ($skipped_count > 0 ? " ({$skipped_count} টি সারি ডুপ্লিকেট/ত্রুটির কারণে বাদ পড়েছে)" : "")
]);
exit();

