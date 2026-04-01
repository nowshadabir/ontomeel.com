<?php
include 'includes/db_connect.php';
$stmt = $pdo->query('DESCRIBE books');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo $row['Field'] . "\n";
}
?>
