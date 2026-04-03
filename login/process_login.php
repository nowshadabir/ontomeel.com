<?php
require_once '../includes/db_connect.php';

// Include security helper
require_once '../includes/security_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rate limiting check
    if (!check_rate_limit('login', 5, 300)) {
        // Log the event
        log_security_event('rate_limit_exceeded', ['identifier' => 'login']);
        header("Location: index.php?error=rate_limit");
        exit();
    }

    $login_id = trim($_POST['login_id']); // This can be email or phone
    $password = $_POST['password'];

    if (empty($login_id) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Check member in database (by email or phone)
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? OR phone = ?");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch();

        if ($user) {
            $password_valid = false;

            // Verify password using bcrypt (secure hashing)
            if (password_verify($password, $user['password'])) {
                $password_valid = true;

                // Upgrade to new hash if needed (argon2 preferred over bcrypt)
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
            }

            if ($password_valid) {
                // Login success - regenerate session ID to prevent fixation
                session_regenerate_id(true);

                // Login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['membership_id'] = $user['membership_id'];
                $_SESSION['membership_plan'] = $user['membership_plan'];

                header("Location: ../dashboard/");
                exit();
            }
        }

        // Login failed
        header("Location: index.php?error=invalid");
        exit();

    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        header("Location: index.php?error=db");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>