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

        if ($user && password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['membership_id'] = $user['membership_id'];
            $_SESSION['membership_plan'] = $user['membership_plan'];

            header("Location: ../dashboard/");
            exit();
        } else {
            // Login failed
            header("Location: index.php?error=invalid");
            exit();
        }

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}
?>