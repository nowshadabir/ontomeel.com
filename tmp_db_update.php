<?php
require 'includes/db_connect.php';

try {
    // Modify ENUM column
    $pdo->exec("ALTER TABLE borrows MODIFY COLUMN status ENUM('Processing', 'Active', 'Returned', 'Overdue') DEFAULT 'Active'");

    // Update any rows with an empty status mapping caused by insertion failure on ENUM
    $stmt = $pdo->query("UPDATE borrows SET status='Processing' WHERE status='' OR status IS NULL OR status='Active'");

    echo "<h1>Database updated successfully!</h1>";
    echo "<p>Rows fixed/updated: " . $stmt->rowCount() . "</p>";
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>