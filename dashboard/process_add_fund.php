<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $amount = (float) $_POST['amount'];

    if ($amount <= 0) {
        die("Invalid amount.");
    }

    try {
        // In a real application, you'd integrate a payment gateway here.
        // For now, we'll just increment the balance.
        $stmt = $pdo->prepare("UPDATE members SET acc_balance = acc_balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        header("Location: index.php?update=success");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}
?>