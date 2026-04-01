<?php
include 'includes/db_connect.php';

try {
    $pdo->exec("ALTER TABLE books ADD INDEX idx_is_active (is_active)");
    $pdo->exec("ALTER TABLE books ADD INDEX idx_title (title)");
    $pdo->exec("ALTER TABLE books ADD INDEX idx_author (author)");
    $pdo->exec("ALTER TABLE books ADD INDEX idx_category_id (category_id)");
    $pdo->exec("ALTER TABLE books ADD INDEX idx_suggested_active (is_suggested, is_active)");
    $pdo->exec("ALTER TABLE books ADD INDEX idx_stock (stock_qty)");
    
    echo "Indexes added successfully!\n";
} catch (PDOException $e) {
    echo "Error adding indexes: " . $e->getMessage() . "\n";
}
?>
