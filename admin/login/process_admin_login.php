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

        if ($admin) {
            $password_valid = false;

            // Check if password is properly hashed (bcrypt format)
            if (preg_match('/^\$2[ayb]\$.{56}$/', $admin['password'])) {
                $password_valid = password_verify($password, $admin['password']);

                // Upgrade hash if needed
                if ($password_valid && password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $admin['id']]);
                }
            } else {
                // Fallback: Check plain text password (legacy support)
                if ($password === $admin['password']) {
                    $password_valid = true;

                    // Upgrade to hashed password
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $admin['id']]);
                }
            }

            if ($password_valid) {
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
            }
        }

        // Authentication failed
        header("Location: index.php?error=invalid");
        exit();
    } catch (PDOException $e) {
        error_log("Admin Login Error: " . $e->getMessage());
        header("Location: index.php?error=db");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>