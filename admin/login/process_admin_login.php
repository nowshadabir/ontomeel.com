<?php
session_start();
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Authentication successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];

            // Update last login
            $update_stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $update_stmt->execute([$admin['id']]);

            header("Location: ../dashboard/index.php");
            exit();
        } else {
            // Authentication failed
            header("Location: index.php?error=invalid");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: index.php?error=db");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>