<?php
require_once __DIR__ . '/includes/db_connect.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?), (?, ?)");
    $stmt->execute([
        'delivery_charge_inside', '60',
        'delivery_charge_outside', '120'
    ]);

    echo "Migration successful - Settings table created and delivery charges initialized.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
