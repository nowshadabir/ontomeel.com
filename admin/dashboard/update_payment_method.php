<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method_key = $_POST['method_key'] ?? '';
    
    if (empty($method_key)) {
        echo json_encode(['success' => false, 'message' => 'Method key missing']);
        exit();
    }

    // Toggle logic
    if (isset($_POST['is_active'])) {
        $is_active = $_POST['is_active'];
        $stmt = $pdo->prepare("UPDATE payment_methods SET is_active = ? WHERE method_key = ?");
        if ($stmt->execute([$is_active, $method_key])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } 
    // Config save logic
    else {
        unset($_POST['method_key']); // Remove method_key from config data
        $config_json = json_encode($_POST);
        
        $stmt = $pdo->prepare("UPDATE payment_methods SET config_json = ? WHERE method_key = ?");
        if ($stmt->execute([$config_json, $method_key])) {
            
            // If it's bKash, update the bkash/config.json file as well
            if ($method_key === 'bkash') {
                $bkash_config_path = '../../bkash/config.json';
                if (file_exists($bkash_config_path)) {
                    file_put_contents($bkash_config_path, json_encode([
                        'app_key' => $_POST['app_key'] ?? '',
                        'app_secret' => $_POST['app_secret'] ?? '',
                        'username' => $_POST['username'] ?? '',
                        'password' => $_POST['password'] ?? '',
                        'base_url' => $_POST['base_url'] ?? ''
                    ], JSON_PRETTY_PRINT));
                }
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
