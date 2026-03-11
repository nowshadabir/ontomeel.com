<?php
require_once 'includes/db_connect.php';

// User/Member Data
$membership_id = 'OM-2026-BH81';
$full_name = 'সায়েম আহমেদ';
$email = 'sayem@mail.com';
$phone = '01700000001';
$password = 'user123';
$membership_plan = 'BookLover';
$acc_balance = 1250.00;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$membership_id, $full_name, $email, $phone, $hashed_password, $membership_plan, $acc_balance]);

    echo "<h1>User Account Created Successfully!</h1>";
    echo "<p>Membership ID: <strong>$membership_id</strong></p>";
    echo "<p>Email: <strong>$email</strong></p>";
    echo "<p>Password: <strong>$password</strong></p>";
    echo "<hr><p style='color:red;'><strong>Security Note:</strong> Please delete this file (user.php) after running it.</p>";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Error: User account already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>