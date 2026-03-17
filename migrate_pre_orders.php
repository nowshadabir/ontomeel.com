<?php
include 'includes/db_connect.php';

try {
    // Add second_title if not exists
    $pdo->exec("ALTER TABLE pre_orders ADD COLUMN second_title VARCHAR(255) DEFAULT NULL AFTER title_en");
    echo "Column 'second_title' added successfully.\n";
} catch (PDOException $e) {
    echo "Error adding 'second_title' or it already exists: " . $e->getMessage() . "\n";
}

try {
    // Add description_2 if not exists
    $pdo->exec("ALTER TABLE pre_orders ADD COLUMN description_2 TEXT DEFAULT NULL AFTER description");
    echo "Column 'description_2' added successfully.\n";
} catch (PDOException $e) {
    echo "Error adding 'description_2' or it already exists: " . $e->getMessage() . "\n";
}
?>
