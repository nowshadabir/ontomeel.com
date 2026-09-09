<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/security_helper.php';

// Determine context-safe redirect locations
$is_in_login_dir = (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/login') !== false);
$login_page = $is_in_login_dir ? 'index.php' : 'login/index.php';
$dashboard_page = $is_in_login_dir ? '../dashboard/index.php' : 'dashboard/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check: 10 attempts per 5 minutes
    if (!check_rate_limit('admin_login', 10, 300)) {
        log_security_event('rate_limit_exceeded', ['identifier' => 'admin_login']);
        header("Location: " . $login_page . "?error=rate_limit");
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: " . $login_page . "?error=empty");
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
                // Clear any rate limits on success
                clear_rate_limit('admin_login');

                // Authentication successful - regenerate session ID
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_full_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['last_activity'] = time();
                $_SESSION['created_at'] = time();

                // Update last login timestamp
                $update_stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $update_stmt->execute([$admin['id']]);

                header("Location: " . $dashboard_page);
                exit();
            }
        }

        // Authentication failed
        header("Location: " . $login_page . "?error=invalid");
        exit();
    } catch (PDOException $e) {
        error_log("Admin Login Error: " . $e->getMessage());
        header("Location: " . $login_page . "?error=db");
        exit();
    }
} else {
    header("Location: " . $login_page);
    exit();
}
?>