<?php
require '../../includes/db_connect.php';

header('Content-Type: application/json');

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
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$new_category_name]);
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

        $file_name = time() . '_' . basename($_FILES[$file_key]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            return null;
        }

        if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
            return $file_name;
        }
        return null;
    }

    $cover_image = uploadImage('cover_image', $target_dir);
    $photo_2 = uploadImage('photo_2', $target_dir);
    $photo_3 = uploadImage('photo_3', $target_dir);

    // 3. Prepare Data
    $title = $_POST['title'] ?? '';
    $subtitle = $_POST['subtitle'] ?? null;
    $description = $_POST['description'] ?? null;
    $genre = $_POST['genre'] ?? null;
    $language = $_POST['language'] ?? null;
    $author = $_POST['author'] ?? '';
    $co_author = $_POST['co_author'] ?? null;
    $publisher = $_POST['publisher'] ?? null;
    $publish_year = $_POST['publish_year'] ?? null;
    $edition = $_POST['edition'] ?? null;
    $isbn = $_POST['isbn'] ?? null;
    $format = $_POST['format'] ?? 'Paperback';
    $page_count = $_POST['page_count'] ?: 0;
    $book_condition = $_POST['book_condition'] ?? 'New';
    $shelf_location = $_POST['shelf_location'] ?? null;
    $rack_number = $_POST['rack_number'] ?? null;
    $stock_qty = $_POST['stock_qty'] ?: 0;
    $min_stock_level = $_POST['min_stock_level'] ?: 0;
    $is_borrowable = isset($_POST['is_borrowable']) ? 1 : 0;
    $is_suggested = isset($_POST['is_suggested']) ? 1 : 0;
    $purchase_price = $_POST['purchase_price'] ?: 0;
    $sell_price = $_POST['sell_price'] ?: 0;
    $supplier_name = $_POST['supplier_name'] ?? null;
    $supplier_contact = $_POST['supplier_contact'] ?? null;

    if (empty($title) || empty($author) || empty($sell_price)) {
        throw new Exception('আবশ্যকীয় তথ্যগুলো (বইয়ের নাম, লেখক, দাম) পূরণ করুন');
    }

    // 4. INSERT or UPDATE
    if (!empty($_POST['book_id'])) {
        $book_id = $_POST['book_id'];

        // Build Dynamic SQL for Update
        $sql = "UPDATE books SET 
            title=?, subtitle=?, description=?, category_id=?, genre=?, language=?, 
            author=?, co_author=?, publisher=?, publish_year=?, edition=?, isbn=?, 
            format=?, page_count=?, book_condition=?, shelf_location=?, rack_number=?, 
            stock_qty=?, min_stock_level=?, is_borrowable=?, is_suggested=?, 
            purchase_price=?, sell_price=?, supplier_name=?, supplier_contact=?";

        $params = [
            $title,
            $subtitle,
            $description,
            $category_id,
            $genre,
            $language,
            $author,
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
        $sql = "INSERT INTO books (
            title, subtitle, description, category_id, genre, language, 
            author, co_author, publisher, publish_year, edition, isbn, 
            format, page_count, book_condition, shelf_location, rack_number, 
            stock_qty, min_stock_level, is_borrowable, is_suggested, 
            purchase_price, sell_price, supplier_name, supplier_contact, 
            cover_image, photo_2, photo_3, is_active, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, 1, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title,
            $subtitle,
            $description,
            $category_id,
            $genre,
            $language,
            $author,
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
