<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep off from final output to avoid breaking JSON
require '../../includes/db_connect.php';
require_once '../../includes/helpers.php';

header('Content-Type: application/json; charset=UTF-8');

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

/**
 * Upload and compress an image to WebP format with optional max-dimension resizing.
 *
 * @param string $file_key Form file input key
 * @param string $target_dir Destination directory
 * @param string $prefix Prefix for generated file name
 * @param int $max_width Maximum width constraint
 * @param int $max_height Maximum height constraint
 * @param int $quality WebP compression quality (0-100)
 * @return string|null Generated filename or null
 */
function uploadAndCompressWebP($file_key, $target_dir, $prefix = 'book', $max_width = 1600, $max_height = 1600, $quality = 82)
{
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmp_name = $_FILES[$file_key]["tmp_name"];
    if (!is_uploaded_file($tmp_name)) {
        return null;
    }

    $file_info = @getimagesize($tmp_name);
    if (!$file_info) {
        return null;
    }

    $mime = $file_info['mime'];
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    $random_suffix = bin2hex(random_bytes(6));
    $file_name = time() . '_' . $prefix . '_' . $random_suffix . '.webp';
    $target_file = $target_dir . $file_name;

    // Use GD library for image conversion & compression to WebP
    if (function_exists('imagewebp')) {
        $src_img = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/pjpeg':
                $src_img = @imagecreatefromjpeg($tmp_name);
                // Handle EXIF orientation if available
                if ($src_img && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($tmp_name);
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $src_img = imagerotate($src_img, 180, 0);
                                break;
                            case 6:
                                $src_img = imagerotate($src_img, -90, 0);
                                break;
                            case 8:
                                $src_img = imagerotate($src_img, 90, 0);
                                break;
                        }
                    }
                }
                break;
            case 'image/png':
                $src_img = @imagecreatefrompng($tmp_name);
                break;
            case 'image/webp':
                $src_img = @imagecreatefromwebp($tmp_name);
                break;
            case 'image/gif':
                $src_img = @imagecreatefromgif($tmp_name);
                break;
            case 'image/bmp':
            case 'image/x-ms-bmp':
                if (function_exists('imagecreatefrombmp')) {
                    $src_img = @imagecreatefrombmp($tmp_name);
                }
                break;
        }

        if ($src_img) {
            $curr_w = imagesx($src_img);
            $curr_h = imagesy($src_img);
            $new_w = $curr_w;
            $new_h = $curr_h;

            // Resize proportionally if larger than constraints
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

            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $curr_w, $curr_h);

            if (imagewebp($dst_img, $target_file, $quality)) {
                imagedestroy($src_img);
                imagedestroy($dst_img);
                return $file_name;
            }

            imagedestroy($src_img);
            imagedestroy($dst_img);
        }
    }

    // Fallback if GD conversion fails
    $fallback_ext = pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION);
    $clean_ext = in_array(strtolower($fallback_ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? strtolower($fallback_ext) : 'jpg';
    $fallback_name = time() . '_' . $prefix . '_' . $random_suffix . '.' . $clean_ext;
    if (move_uploaded_file($tmp_name, $target_dir . $fallback_name)) {
        return $fallback_name;
    }

    return null;
}

$newly_uploaded_files = [];
$target_dir = __DIR__ . '/../assets/book-images/';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // 1. Prepare and Sanitize Data
    $book_id = !empty($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
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
    $isbn = trim($_POST['isbn'] ?? '');
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

    // Convert Bengali numerals if any
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    if (!empty($isbn)) {
        $isbn = str_replace($bn_digits, $en_digits, $isbn);
    }

    // 2. Validate Required Fields
    if (empty($title)) {
        throw new Exception('বইয়ের নাম (বাংলা) আবশ্যক।');
    }
    if (empty($title_en)) {
        throw new Exception('বইয়ের ইংরেজি নাম আবশ্যক।');
    }
    if (empty($author)) {
        throw new Exception('লেখকের নাম (বাংলা) আবশ্যক।');
    }
    if (empty($author_en)) {
        throw new Exception('লেখকের ইংরেজি নাম আবশ্যক।');
    }
    if (empty($isbn)) {
        throw new Exception('বইয়ের আইএসবিএন (ISBN) নম্বর দেওয়া আবশ্যক।');
    }
    if ($sell_price <= 0) {
        throw new Exception('বইয়ের মুদ্রিত গায়ের দাম (MRP) অবশ্যই ০ এর চেয়ে বেশি হতে হবে।');
    }
    if ($discount_price < 0 || $purchase_price < 0 || $stock_qty < 0) {
        throw new Exception('কোনো মূল্য বা স্টকের মান ঋণাত্মক (নেগেটিভ) হতে পারবে না।');
    }

    // If discount price equals regular price, treat as no discount (0)
    if ($discount_price == $sell_price) {
        $discount_price = 0;
    } elseif ($discount_price > $sell_price) {
        throw new Exception('ছাড়ের পর বিক্রয় মূল্য (Offer Price: ৳' . $discount_price . ') মুদ্রিত গায়ের দামের (MRP: ৳' . $sell_price . ') চেয়ে বেশি হতে পারবে না। ছাড় না দিতে চাইলে ঘরটি খালি রাখুন।');
    }

    // 3. Prevent Double Book Entry (BEFORE any image uploads)
    $clean_isbn = preg_replace('/[^A-Za-z0-9]/', '', $isbn);

    if ($book_id > 0) {
        // Check ISBN duplicate on other books
        $isbn_check = $pdo->prepare("SELECT id, title, author FROM books WHERE (isbn = ? OR REPLACE(REPLACE(REPLACE(isbn, '-', ''), ' ', ''), '_', '') = ?) AND id != ? AND is_active = 1 LIMIT 1");
        $isbn_check->execute([$isbn, $clean_isbn, $book_id]);
        $existing_isbn = $isbn_check->fetch();
        if ($existing_isbn) {
            throw new Exception("অন্য একটি বইয়ে ইতোমধ্যে এই ISBN নম্বরটি ({$isbn}) ব্যবহৃত হচ্ছে ('{$existing_isbn['title']}' - লেখক: '{$existing_isbn['author']}'), বই আইডি: #{$existing_isbn['id']}। ডুপ্লিকেট ISBN ব্যবহার করা যাবে না।");
        }

        // Check Title + Author duplicate on other books
        $title_check = $pdo->prepare("SELECT id, title, author FROM books WHERE title = ? AND author = ? AND id != ? AND is_active = 1 LIMIT 1");
        $title_check->execute([$title, $author, $book_id]);
        $existing_title = $title_check->fetch();
        if ($existing_title) {
            throw new Exception("এই নাম ('{$title}') এবং লেখক ('{$author}') এর অধীনে অন্য একটি বই ইতোমধ্যে ডাটাবেজে রয়েছে (বই আইডি: #{$existing_title['id']})।");
        }
    } else {
        // Check ISBN duplicate for new book
        $isbn_check = $pdo->prepare("SELECT id, title, author FROM books WHERE (isbn = ? OR REPLACE(REPLACE(REPLACE(isbn, '-', ''), ' ', ''), '_', '') = ?) AND is_active = 1 LIMIT 1");
        $isbn_check->execute([$isbn, $clean_isbn]);
        $existing_isbn = $isbn_check->fetch();
        if ($existing_isbn) {
            throw new Exception("এই ISBN নম্বরের ({$isbn}) বইটি ইতোমধ্যে সিস্টেমে রয়েছে ('{$existing_isbn['title']}' - লেখক: '{$existing_isbn['author']}'), বই আইডি: #{$existing_isbn['id']}। একই বই পুনরায় যোগ করা যাবে না।");
        }

        // Check Title + Author duplicate for new book
        $title_check = $pdo->prepare("SELECT id, title, author, isbn FROM books WHERE title = ? AND author = ? AND is_active = 1 LIMIT 1");
        $title_check->execute([$title, $author]);
        $existing_title = $title_check->fetch();
        if ($existing_title) {
            $isbn_info = !empty($existing_title['isbn']) ? ", ISBN: {$existing_title['isbn']}" : "";
            throw new Exception("এই বইটি ('{$title}' - {$author}) ইতোমধ্যে ইনভেন্টরিতে বিদ্যমান রয়েছে (বই আইডি: #{$existing_title['id']}{$isbn_info})। একই বই পুনরায় যোগ করা যাবে না।");
        }
    }

    $pdo->beginTransaction();

    // 4. Handle Category
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

    // 5. Handle File Uploads (Executed ONLY after validation and duplicate checks pass)
    $cover_image = uploadAndCompressWebP('cover_image', $target_dir, 'cover');
    if ($cover_image) $newly_uploaded_files[] = $cover_image;

    $photo_2 = uploadAndCompressWebP('photo_2', $target_dir, 'photo2');
    if ($photo_2) $newly_uploaded_files[] = $photo_2;

    $photo_3 = uploadAndCompressWebP('photo_3', $target_dir, 'photo3');
    if ($photo_3) $newly_uploaded_files[] = $photo_3;

    // 6. INSERT or UPDATE
    if ($book_id > 0) {
        // Fetch old image filenames for cleanup if new ones are uploaded
        $old_img_stmt = $pdo->prepare("SELECT cover_image, photo_2, photo_3 FROM books WHERE id = ?");
        $old_img_stmt->execute([$book_id]);
        $old_book = $old_img_stmt->fetch(PDO::FETCH_ASSOC);

        $slug = get_unique_book_slug($pdo, $title_en, $title, $book_id);

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

        // Clean up old local images if replaced
        if ($old_book) {
            if ($cover_image && !empty($old_book['cover_image']) && strpos($old_book['cover_image'], 'http') !== 0 && $old_book['cover_image'] !== $cover_image) {
                @unlink($target_dir . $old_book['cover_image']);
            }
            if ($photo_2 && !empty($old_book['photo_2']) && strpos($old_book['photo_2'], 'http') !== 0 && $old_book['photo_2'] !== $photo_2) {
                @unlink($target_dir . $old_book['photo_2']);
            }
            if ($photo_3 && !empty($old_book['photo_3']) && strpos($old_book['photo_3'], 'http') !== 0 && $old_book['photo_3'] !== $photo_3) {
                @unlink($target_dir . $old_book['photo_3']);
            }
        }

        $message = "বইটির তথ্য সফলভাবে আপডেট করা হয়েছে।";
    } else {
        $slug = get_unique_book_slug($pdo, $title_en, $title);

        $sql = "INSERT INTO books (
            title, title_en, slug, subtitle, description, category_id, genre, language, 
            author, author_en, co_author, publisher, publish_year, edition, isbn, 
            format, page_count, book_condition, shelf_location, rack_number, 
            stock_qty, min_stock_level, is_borrowable, is_suggested, 
            purchase_price, sell_price, discount_price, supplier_name, supplier_contact, 
            cover_image, photo_2, photo_3, is_active, created_at, item_type
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 1, NOW(), 'Book'
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

    // Clean up any newly uploaded files on error
    foreach ($newly_uploaded_files as $file) {
        if (!empty($file) && file_exists($target_dir . $file)) {
            @unlink($target_dir . $file);
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

