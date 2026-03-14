<?php
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_charges') {
        $inside = $_POST['inside'] ?? '60';
        $outside = $_POST['outside'] ?? '120';

        // Insert or Update
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_charge_inside', ?), ('delivery_charge_outside', ?) 
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$inside, $inside]); // First pair
        
        // Actually we need two separate queries for Clarity or one unified with key matching
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_charge_inside', ?) 
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$inside]);

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_charge_outside', ?) 
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$outside]);

        echo json_encode(['success' => true, 'message' => 'ডেলিভারি চার্জ আপডেট করা হয়েছে।']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
?>
