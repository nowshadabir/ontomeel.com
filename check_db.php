<?php
include 'includes/db_connect.php';

try {
    $count_all = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $count_active = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 1")->fetchColumn();
    $count_inactive = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 0")->fetchColumn();
    $count_null = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active IS NULL")->fetchColumn();
    
    echo "Total books: $count_all\n";
    echo "Active books (is_active=1): $count_active\n";
    echo "Inactive books (is_active=0): $count_inactive\n";
    echo "NULL is_active books: $count_null\n";

    $sample = $pdo->query("SELECT id, title, is_active FROM books LIMIT 5")->fetchAll();
    echo "Samples:\n";
    print_r($sample);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
