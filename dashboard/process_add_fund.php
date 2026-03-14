<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    // header("Location: index.php?error=disabled");
    // die("Fund adding is temporarily disabled.");
    
    // For now, just redirect back to index.php
    $_SESSION['error_message'] = "তহবিল যোগ করা সাময়িকভাবে বন্ধ আছে।";
    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>