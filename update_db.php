<?php
require_once 'includes/db_connect.php';

try {
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_status ENUM('Processing', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing'");
    echo "<h1>Database updated successfully!</h1><p>Added 'Confirmed' to order_status ENUM.</p>";
} catch (PDOException $e) {
    echo "<h1>Error updating database:</h1><p>" . $e->getMessage() . "</p>";
}
?>
