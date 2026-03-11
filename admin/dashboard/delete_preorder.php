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
        $stmt = $pdo->prepare("DELETE FROM pre_orders WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'প্রি-অর্ডার সফলভাবে মুছে ফেলা হয়েছে।']);
    } else {
        echo json_encode(['success' => false, 'message' => 'আইডি পাওয়া যায়নি।']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>