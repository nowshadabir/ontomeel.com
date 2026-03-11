<?php
require_once 'includes/db_connect.php';

try {
    // Add reading_progress to borrows if it doesn't exist
    $pdo->exec("ALTER TABLE borrows ADD COLUMN IF NOT EXISTS reading_progress INT DEFAULT 0");
    echo "Migration successful: reading_progress column ensured in borrows table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column reading_progress already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>