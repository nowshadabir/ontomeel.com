<?php
require_once 'includes/db_connect.php';

// Admin Data
$username = 'admin';
$email = 'admin@ontomeel.com';
$password = 'admin123'; // Plain text password for now, hashed below
$full_name = 'Ontomeel Admin';
$role = 'SuperAdmin';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password, $full_name, $role]);

    echo "<h1>Admin Account Created Successfully!</h1>";
    echo "<p>Username: <strong>$username</strong></p>";
    echo "<p>Password: <strong>$password</strong></p>";
    echo "<hr><p style='color:red;'><strong>Security Note:</strong> Please delete this file (admin.php) after running it.</p>";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Error: Admin account already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>