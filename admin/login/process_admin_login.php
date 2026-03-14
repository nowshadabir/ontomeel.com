<?php
session_start();
require_once '../../includes/db_connect.php';

// Include security helper
require_once '../../includes/security_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rate limiting check
    if (!check_rate_limit('admin_login', 5, 300)) {
        log_security_event('rate_limit_exceeded', ['identifier' => 'admin_login']);
        header("Location: index.php?error=rate_limit");
        exit();
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Find admin by username OR email
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin) {
            $password_valid = false;

            // Verify password using bcrypt (secure hashing)
            if (password_verify($password, $admin['password'])) {
                $password_valid = true;

                // Upgrade hash if needed
                if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $admin['id']]);
                }
            }

            if ($password_valid) {
                // Authentication successful - regenerate session ID
                session_regenerate_id(true);

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