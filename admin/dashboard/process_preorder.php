<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $id = $_POST['po_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $sub_title = $_POST['sub_title'] ?? '';
    $author = $_POST['author'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $discount_price = $_POST['discount_price'] ?? 0;
    $release_date = $_POST['release_date'] ?? '';
    $status = $_POST['status'] ?? 'Upcoming';
    $is_hot_deal = isset($_POST['is_hot_deal']) ? 1 : 0;

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
        $stmt = $pdo->prepare("INSERT INTO pre_orders (title, sub_title, author, description, price, discount_price, release_date, cover_image, second_cover_image, status, is_hot_deal) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $sub_title, $author, $description, $price, $discount_price, $release_date, $cover_image, $second_cover_image, $status, $is_hot_deal]);
        echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার সফলভাবে যোগ করা হয়েছে।']);
    } else {
        // Update
        if ($cover_image && $second_cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, sub_title = ?, author = ?, description = ?, price = ?, discount_price = ?, release_date = ?, cover_image = ?, second_cover_image = ?, status = ?, is_hot_deal = ? WHERE id = ?");
            $stmt->execute([$title, $sub_title, $author, $description, $price, $discount_price, $release_date, $cover_image, $second_cover_image, $status, $is_hot_deal, $id]);
        } elseif ($cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, sub_title = ?, author = ?, description = ?, price = ?, discount_price = ?, release_date = ?, cover_image = ?, status = ?, is_hot_deal = ? WHERE id = ?");
            $stmt->execute([$title, $sub_title, $author, $description, $price, $discount_price, $release_date, $cover_image, $status, $is_hot_deal, $id]);
        } elseif ($second_cover_image) {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, sub_title = ?, author = ?, description = ?, price = ?, discount_price = ?, release_date = ?, second_cover_image = ?, status = ?, is_hot_deal = ? WHERE id = ?");
            $stmt->execute([$title, $sub_title, $author, $description, $price, $discount_price, $release_date, $second_cover_image, $status, $is_hot_deal, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE pre_orders SET title = ?, sub_title = ?, author = ?, description = ?, price = ?, discount_price = ?, release_date = ?, status = ?, is_hot_deal = ? WHERE id = ?");
            $stmt->execute([$title, $sub_title, $author, $description, $price, $discount_price, $release_date, $status, $is_hot_deal, $id]);
        }
        echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার আপডেট করা হয়েছে।']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>