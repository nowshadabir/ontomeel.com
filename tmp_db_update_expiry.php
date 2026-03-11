<?php
require_once 'includes/db_connect.php';
try {
    $pdo->exec("ALTER TABLE members ADD COLUMN plan_expire_date DATETIME DEFAULT NULL");
    echo "Successfully added plan_expire_date column.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>