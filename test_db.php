<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';

try {
    echo "<h1>Database Test</h1>";
    echo "Host: " . getenv('DB_HOST') . "<br>";
    echo "DB: " . getenv('DB_NAME') . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $count = $stmt->fetchColumn();
    echo "Total Books in table: $count<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 1");
    $active = $stmt->fetchColumn();
    echo "Active Books (is_active=1): $active<br>";
    
    $stmt = $pdo->query("SELECT b.* FROM books b LIMIT 1");
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Sample data exists: " . ($sample ? "Yes" : "No") . "<br>";
    if ($sample) {
        echo "Sample ID: " . $sample['id'] . " Title: " . $sample['title'] . " Active: " . $sample['is_active'] . "<br>";
    }
    
    // Check with JOIN
    $stmt = $pdo->query("SELECT b.id, b.title, c.name as cat_name FROM books b LEFT JOIN categories c ON b.category_id = c.id LIMIT 1");
    $sample_join = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Sample with JOIN exists: " . ($sample_join ? "Yes" : "No") . "<br>";
    if ($sample_join) {
        echo "Join ID: " . $sample_join['id'] . " Cat: " . ($sample_join['cat_name'] ?? 'NULL') . "<br>";
    }

} catch (PDOException $e) {
    echo "<h2>PDO Error</h2>";
    echo $e->getMessage();
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo $e->getMessage();
}
?>
