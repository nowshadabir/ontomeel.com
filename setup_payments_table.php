<?php
include 'includes/db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS payment_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        method_key VARCHAR(50) UNIQUE,
        method_name VARCHAR(100),
        is_active TINYINT DEFAULT 1,
        config_json TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    $methods = [
        ['bkash', 'bKash Payment', 1],
        ['nagad', 'Nagad Payment', 1],
        ['cod', 'Cash on Delivery', 1],
        ['fund', 'Account Fund', 1]
    ];
    
    foreach ($methods as $method) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO payment_methods (method_key, method_name, is_active) VALUES (?, ?, ?)");
        $stmt->execute($method);
    }
    
    echo "Success";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
