<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$borrow_id = $_POST['borrow_id'] ?? null;
$progress = $_POST['progress'] ?? null;

if (!$borrow_id || $progress === null) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

$progress = (int) $progress;
if ($progress < 0)
    $progress = 0;
if ($progress > 100)
    $progress = 100;

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM borrows WHERE id = ? AND member_id = ?");
    $stmt->execute([$borrow_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Borrow record not found or unauthorized']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE borrows SET reading_progress = ? WHERE id = ?");
    $stmt->execute([$progress, $borrow_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
