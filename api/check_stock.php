<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// Disable error reporting output for clean JSON response
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
    exit;
}

$ids = explode(',', $_GET['ids']);
$ids = array_filter($ids, 'is_numeric');

if (empty($ids)) {
    echo json_encode(['success' => true, 'stocks' => []]);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, stock_qty, title FROM books WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stocks = [];
    foreach ($results as $row) {
        $stocks[$row['id']] = (int)$row['stock_qty'];
    }

    echo json_encode(['success' => true, 'stocks' => $stocks]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
