<?php
/**
 * Diagnostic script to check member login issues
 * Run this to see what's stored in the database for a member
 */

require_once 'includes/db_connect.php';

// Get email from command line or GET parameter
$email = $_GET['email'] ?? ($argv[1] ?? '');

if (empty($email)) {
    echo "Usage: php debug_login.php <email>\n";
    echo "Example: php debug_login.php user@example.com\n";
    exit(1);
}

echo "=== Login Diagnostic Tool ===\n\n";

try {
    // Get user by email
    $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, password, membership_plan FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ User not found with email: $email\n";
        exit(1);
    }

    echo "=== User Found ===\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['full_name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Phone: " . $user['phone'] . "\n";
    echo "Membership: " . $user['membership_plan'] . "\n\n";

    echo "=== Password Info ===\n";
    $password = $user['password'];

    // Check if it's a valid bcrypt hash
    if (preg_match('/^\$2[ayb]\$.{56}$/', $password)) {
        echo "✅ Password is properly hashed (bcrypt)\n";

        // Test password verification
        echo "\n=== Testing Password Verification ===\n";

        // Try common test passwords
        $test_passwords = ['password', '123456', '12345678', 'password123', 'admin', $email];

        foreach ($test_passwords as $test) {
            if (password_verify($test, $password)) {
                echo "✅ PASSWORD FOUND: '$test'\n";
                echo "   The user can login with password: $test\n";
            }
        }

    } else {
        echo "⚠️  WARNING: Password does NOT appear to be a valid bcrypt hash!\n";
        echo "Stored value: " . substr($password, 0, 20) . "...\n\n";
        echo "This is likely why login is failing - the password is stored as plain text\n";
        echo "or was incorrectly hashed.\n";

        echo "\n=== Fix Options ===\n";
        echo "Option 1: Update password using proper hashing:\n";
        echo "   New hash: " . password_hash('your_new_password', PASSWORD_DEFAULT) . "\n";
    }

    echo "\n=== Database Column Check ===\n";
    echo "Password column length: " . strlen($password) . " characters\n";
    echo "Expected for bcrypt: 60 characters\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}