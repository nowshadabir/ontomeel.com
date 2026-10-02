<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep off from final output to avoid breaking JSON
require '../../includes/db_connect.php';

header('Content-Type: application/json');

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $pdo->beginTransaction();

    // 1. Handle Category
    $category_id = $_POST['category_id'] ?? null;
    if ($category_id === 'new') {
        $new_category_name = trim($_POST['new_category_name'] ?? '');
        if (empty($new_category_name)) {
            throw new Exception('নতুন ক্যাটাগরির নাম দিন');
        }

        // Check if category already exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$new_category_name]);
        $existing = $stmt->fetch();

        if ($existing) {
            $category_id = $existing['id'];
        } else {
            // Generate a simple unique slug for the category
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_category_name), '-'));
            if (empty($slug)) {
                $slug = 'category-' . uniqid();
            } else {
                $slug .= '-' . substr(uniqid(), -4);
            }
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$new_category_name, $slug]);
            $category_id = $pdo->lastInsertId();
        }
    }

    if (empty($category_id)) {
        throw new Exception('ক্যাটাগরি সিলেক্ট করুন');
    }

    // 2. Handle File Uploads
    $target_dir = "../../admin/assets/book-images/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    function uploadImage($file_key, $target_dir)
    {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmp_name = $_FILES[$file_key]["tmp_name"];
        $file_info = getimagesize($tmp_name);
        if (!$file_info) return null;

        $mime = $file_info['mime'];
        $file_name = time() . '_' . $file_key . '_' . uniqid() . '.webp';
        $target_file = $target_dir . $file_name;

        // Create image from source and compress ONLY if GD is enabled and supports WebP
        if (function_exists('imagewebp')) {
            $image = null;
            $gd_info = gd_info();
            $webp_supported = isset($gd_info['WebP Support']) && $gd_info['WebP Support'];

            if ($webp_supported) {
            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($tmp_name);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($tmp_name);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($tmp_name);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($tmp_name);
                    break;
                default:
                    $image = null;
            }

                if ($image && imagewebp($image, $target_file, 80)) {
                    imagedestroy($image);
                    return $file_name;
                }
                if ($image) imagedestroy($image);
            }
        }

        // Fallback: Standard upload if GD is missing or conversion fails
        $file_name = time() . '_' . $file_key . '_' . basename($_FILES[$file_key]["name"]);
        if (move_uploaded_file($tmp_name, $target_dir . $file_name)) {
            return $file_name;
        }

        return null;
    }

    $cover_image = uploadImage('cover_image', $target_dir);
    $photo_2 = uploadImage('photo_2', $target_dir);
    $photo_3 = uploadImage('photo_3', $target_dir);

    // 3. Prepare Data
    $title = trim($_POST['title'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $subtitle = !empty(trim($_POST['subtitle'] ?? '')) ? trim($_POST['subtitle']) : null;
    $description = !empty(trim($_POST['description'] ?? '')) ? trim($_POST['description']) : null;
    $genre = !empty(trim($_POST['genre'] ?? '')) ? trim($_POST['genre']) : null;
    $language = !empty(trim($_POST['language'] ?? '')) ? trim($_POST['language']) : 'Bengali';
    $author = trim($_POST['author'] ?? '');
    $author_en = trim($_POST['author_en'] ?? '');
    $co_author = !empty(trim($_POST['co_author'] ?? '')) ? trim($_POST['co_author']) : null;
    $publisher = !empty(trim($_POST['publisher'] ?? '')) ? trim($_POST['publisher']) : null;
    $publish_year = !empty(trim($_POST['publish_year'] ?? '')) ? trim($_POST['publish_year']) : null;
    $edition = !empty(trim($_POST['edition'] ?? '')) ? trim($_POST['edition']) : null;
    $isbn = !empty(trim($_POST['isbn'] ?? '')) ? trim($_POST['isbn']) : null;
    $format = $_POST['format'] ?? 'Paperback';
    $page_count = !empty($_POST['page_count']) ? (int)$_POST['page_count'] : 0;
    $book_condition = $_POST['book_condition'] ?? 'New';
    $shelf_location = !empty(trim($_POST['shelf_location'] ?? '')) ? trim($_POST['shelf_location']) : null;
    $rack_number = !empty(trim($_POST['rack_number'] ?? '')) ? trim($_POST['rack_number']) : null;
    $stock_qty = isset($_POST['stock_qty']) && $_POST['stock_qty'] !== '' ? (int)$_POST['stock_qty'] : 0;
    $min_stock_level = isset($_POST['min_stock_level']) && $_POST['min_stock_level'] !== '' ? (int)$_POST['min_stock_level'] : 2;
    $is_borrowable = isset($_POST['is_borrowable']) ? 1 : 0;
    $is_suggested = isset($_POST['is_suggested']) ? 1 : 0;
    $purchase_price = !empty($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : 0;
    $sell_price = !empty($_POST['sell_price']) ? (float)$_POST['sell_price'] : 0;
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : 0;
    $supplier_name = !empty(trim($_POST['supplier_name'] ?? '')) ? trim($_POST['supplier_name']) : null;
    $supplier_contact = !empty(trim($_POST['supplier_contact'] ?? '')) ? trim($_POST['supplier_contact']) : null;

    if (empty($title) || empty($title_en) || empty($author) || empty($author_en) || empty($sell_price)) {
        throw new Exception('আবশ্যকীয় তথ্যগুলো (বইয়ের নাম, ইংরেজি নাম, লেখক, ইংরেজি লেখক, দাম) পূরণ করুন');
    }

    // Check duplicate ISBN
    if (!empty($isbn)) {
        $book_id = !empty($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
        if ($book_id > 0) {
            $isbn_check_stmt = $pdo->prepare("SELECT id, title FROM books WHERE isbn = ? AND id != ? AND is_active = 1 LIMIT 1");
            $isbn_check_stmt->execute([$isbn, $book_id]);
        } else {
            $isbn_check_stmt = $pdo->prepare("SELECT id, title FROM books WHERE isbn = ? AND is_active = 1 LIMIT 1");
            $isbn_check_stmt->execute([$isbn]);
        }
        $existing_isbn_book = $isbn_check_stmt->fetch();
        if ($existing_isbn_book) {
            throw new Exception("এই ISBN নম্বরের ({$isbn}) বইটি ইতোমধ্যে সিস্টেমে রয়েছে ('{$existing_isbn_book['title']}')। একই ISBN-এ ডুপ্লিকেট বই যোগ করা যাবে না।");
        }
    }

    require_once '../../includes/helpers.php';

    // 4. INSERT or UPDATE
    if (!empty($_POST['book_id'])) {
        $book_id = (int)$_POST['book_id'];
        $slug = get_unique_book_slug($pdo, $title_en, $title, $book_id);

        // Build Dynamic SQL for Update
        $sql = "UPDATE books SET 
            title=?, title_en=?, slug=?, subtitle=?, description=?, category_id=?, genre=?, language=?, 
            author=?, author_en=?, co_author=?, publisher=?, publish_year=?, edition=?, isbn=?, 
            format=?, page_count=?, book_condition=?, shelf_location=?, rack_number=?, 
            stock_qty=?, min_stock_level=?, is_borrowable=?, is_suggested=?, 
            purchase_price=?, sell_price=?, discount_price=?, supplier_name=?, supplier_contact=?";

        $params = [
            $title,
            $title_en,
            $slug,
            $subtitle,
            $description,
            $category_id,
            $genre,
            $language,
            $author,
            $author_en,
            $co_author,
            $publisher,
            $publish_year,
            $edition,
            $isbn,
            $format,
            $page_count,
            $book_condition,
            $shelf_location,
            $rack_number,
            $stock_qty,
            $min_stock_level,
            $is_borrowable,
            $is_suggested,
            $purchase_price,
            $sell_price,
            $discount_price,
            $supplier_name,
            $supplier_contact
        ];

        if ($cover_image) {
            $sql .= ", cover_image=?";
            $params[] = $cover_image;
        }
        if ($photo_2) {
            $sql .= ", photo_2=?";
            $params[] = $photo_2;
        }
        if ($photo_3) {
            $sql .= ", photo_3=?";
            $params[] = $photo_3;
        }

        $sql .= " WHERE id = ?";
        $params[] = $book_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "বইটির তথ্য সফলভাবে আপডেট করা হয়েছে।";
    } else {
        $slug = get_unique_book_slug($pdo, $title_en, $title);

        $sql = "INSERT INTO books (
            title, title_en, slug, subtitle, description, category_id, genre, language, 
            author, author_en, co_author, publisher, publish_year, edition, isbn, 
            format, page_count, book_condition, shelf_location, rack_number, 
            stock_qty, min_stock_level, is_borrowable, is_suggested, 
            purchase_price, sell_price, discount_price, supplier_name, supplier_contact, 
            cover_image, photo_2, photo_3, is_active, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 1, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title,
            $title_en,
            $slug,
            $subtitle,
            $description,
            $category_id,
            $genre,
            $language,
            $author,
            $author_en,
            $co_author,
            $publisher,
            $publish_year,
            $edition,
            $isbn,
            $format,
            $page_count,
            $book_condition,
            $shelf_location,
            $rack_number,
            $stock_qty,
            $min_stock_level,
            $is_borrowable,
            $is_suggested,
            $purchase_price,
            $sell_price,
            $discount_price,
            $supplier_name,
            $supplier_contact,
            $cover_image,
            $photo_2,
            $photo_3
        ]);
        $message = "বইটি সফলভাবে যোগ করা হয়েছে।";
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
