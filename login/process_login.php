<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

            // Check if password is properly hashed (bcrypt format starts with $2y$ or $2a$)
            if (preg_match('/^\$2[ayb]\$.{56}$/', $user['password'])) {
                // Use password_verify for hashed passwords
                $password_valid = password_verify($password, $user['password']);

                // If login successful, upgrade to new hash on next successful login
                if ($password_valid && password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
            } else {
                // Fallback: Check plain text password (legacy support)
                // This handles cases where password was directly stored in database
                if ($password === $user['password']) {
                    $password_valid = true;

                    // Upgrade to hashed password
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
            }

            if ($password_valid) {
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