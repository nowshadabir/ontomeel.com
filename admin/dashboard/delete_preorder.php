<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $id = $_POST['id'] ?? '';

    if (!empty($id)) {
        // First, fetch image filenames
        $stmt = $pdo->prepare("SELECT cover_image, second_cover_image FROM pre_orders WHERE id = ?");
        $stmt->execute([$id]);
        $po = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM pre_orders WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Delete files from storage
            $upload_dir = '../../assets/img/preorders/';
            if ($po) {
                if (!empty($po['cover_image']) && strpos($po['cover_image'], 'http') !== 0) {
                    @unlink($upload_dir . trim($po['cover_image']));
                }
                if (!empty($po['second_cover_image']) && strpos($po['second_cover_image'], 'http') !== 0) {
                    @unlink($upload_dir . trim($po['second_cover_image']));
                }
            }
            echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার এবং সংশ্লিষ্ট ছবিগুলো সফলভাবে মুছে ফেলা হয়েছে।']);
        } else {
            echo json_encode(['success' => false, 'message' => 'মুছে ফেলতে সমস্যা হয়েছে।']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'আইডি পাওয়া যায়নি।']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>