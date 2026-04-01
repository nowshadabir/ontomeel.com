<?php
include 'includes/db_connect.php';

try {
    $stmt = $pdo->query("DESCRIBE books");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in books: " . implode(', ', $cols) . "\n";
    
    $stmt = $pdo->query("SELECT * FROM books LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Sample row keys: " . implode(', ', array_keys($row)) . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
