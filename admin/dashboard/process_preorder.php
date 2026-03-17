<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

function create_slug($string) {
    if (empty($string)) return '';
    // Replace non-alphanumeric/non-bangla/non-spaces with -
    $slug = preg_replace('/[^\p{L}\p{N} ]/u', '-', $string);
    $slug = preg_replace('/\s+/', '-', $slug);
    return strtolower(trim($slug, '-'));
}

try {
    $id = $_POST['po_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $second_title = $_POST['second_title'] ?? '';
    $title_en = $_POST['title_en'] ?? '';
    $sub_title = $_POST['sub_title'] ?? '';
    $author = $_POST['author'] ?? '';
    $author_en = $_POST['author_en'] ?? '';
    
    // Generate slug from English title if available, otherwise Bengali title
    $slug_base = !empty($title_en) ? $title_en : $title;
    $slug = create_slug($slug_base);
    
    // Ensure slug uniqueness (simplified for now, adding ID if exists is better but let's stick to basics)
    if (empty($slug)) $slug = uniqid('po-');

    $description = $_POST['description'] ?? '';
    $description_2 = $_POST['description_2'] ?? '';
    $price = $_POST['price'] ?? 0;
    $discount_price = $_POST['discount_price'] ?? 0;
    $release_date = $_POST['release_date'] ?? '';
    $status = $_POST['status'] ?? 'Upcoming';
    $is_hot_deal = isset($_POST['is_hot_deal']) ? 1 : 0;
    $free_delivery = isset($_POST['free_delivery']) ? 1 : 0;

    // Handle Image Upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/img/preorders/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('po_') . '.' . $file_ext;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = $filename;
        }
    }

    // Handle Second Cover Image Upload
    $second_cover_image = '';
    if (isset($_FILES['second_cover_image']) && $_FILES['second_cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/img/preorders/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['second_cover_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('po2_') . '.' . $file_ext;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['second_cover_image']['tmp_name'], $target_file)) {
            $second_cover_image = $filename;
        }
    }

    if (empty($id)) {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO pre_orders (title, second_title, title_en, slug, sub_title, author, author_en, description, description_2, price, discount_price, release_date, cover_image, second_cover_image, status, is_hot_deal, free_delivery) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $second_title, $title_en, $slug, $sub_title, $author, $author_en, $description, $description_2, $price, $discount_price, $release_date, $cover_image, $second_cover_image, $status, $is_hot_deal, $free_delivery]);
        echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার সফলভাবে যোগ করা হয়েছে।']);
    } else {
        // Update
        if ($cover_image && $second_cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, second_title = ?, title_en = ?, slug = ?, sub_title = ?, author = ?, author_en = ?, description = ?, description_2 = ?, price = ?, discount_price = ?, release_date = ?, cover_image = ?, second_cover_image = ?, status = ?, is_hot_deal = ?, free_delivery = ? WHERE id = ?");
            $stmt->execute([$title, $second_title, $title_en, $slug, $sub_title, $author, $author_en, $description, $description_2, $price, $discount_price, $release_date, $cover_image, $second_cover_image, $status, $is_hot_deal, $free_delivery, $id]);
        } elseif ($cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, second_title = ?, title_en = ?, slug = ?, sub_title = ?, author = ?, author_en = ?, description = ?, description_2 = ?, price = ?, discount_price = ?, release_date = ?, cover_image = ?, status = ?, is_hot_deal = ?, free_delivery = ? WHERE id = ?");
            $stmt->execute([$title, $second_title, $title_en, $slug, $sub_title, $author, $author_en, $description, $description_2, $price, $discount_price, $release_date, $cover_image, $status, $is_hot_deal, $free_delivery, $id]);
        } elseif ($second_cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, second_title = ?, title_en = ?, slug = ?, sub_title = ?, author = ?, author_en = ?, description = ?, description_2 = ?, price = ?, discount_price = ?, release_date = ?, second_cover_image = ?, status = ?, is_hot_deal = ?, free_delivery = ? WHERE id = ?");
            $stmt->execute([$title, $second_title, $title_en, $slug, $sub_title, $author, $author_en, $description, $description_2, $price, $discount_price, $release_date, $second_cover_image, $status, $is_hot_deal, $free_delivery, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, second_title = ?, title_en = ?, slug = ?, sub_title = ?, author = ?, author_en = ?, description = ?, description_2 = ?, price = ?, discount_price = ?, release_date = ?, status = ?, is_hot_deal = ?, free_delivery = ? WHERE id = ?");
            $stmt->execute([$title, $second_title, $title_en, $slug, $sub_title, $author, $author_en, $description, $description_2, $price, $discount_price, $release_date, $status, $is_hot_deal, $free_delivery, $id]);
        }
        echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার আপডেট করা হয়েছে।']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>