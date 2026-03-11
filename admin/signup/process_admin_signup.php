<?php
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Basic validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        die("Please fill all required fields.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $full_name, $role]);

        // Redirect to admin login
        header("Location: ../login/index.php?signup=success");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            die("Error: Username or Email already exists.");
        } else {
            die("Error: " . $e->getMessage());
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>