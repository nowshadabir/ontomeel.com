<?php
/**
 * Password Reset Script
 * Use this to fix passwords that are stored as plain text in the database
 * 
 * HOW TO USE:
 * 1. Access this file in browser: fix_password.php?email=your@email.com&password=your_password
 * 2. OR run from command line: php fix_password.php your@email.com your_password
 */

require_once 'includes/db_connect.php';

$email = $_GET['email'] ?? ($argv[1] ?? '');
$new_password = $_GET['password'] ?? ($argv[2] ?? '');

if (empty($email) || empty($new_password)) {
    echo "=== Password Fix Tool ===\n\n";
    echo "Usage:\n";
    echo "  Browser:  fix_password.php?email=user@example.com&password=yourpassword\n";
    echo "  CLI:      php fix_password.php user@example.com yourpassword\n\n";
    echo "This will hash the password properly and update the database.\n";
    exit(1);
}

echo "=== Fixing Password for: $email ===\n\n";

try {
    // Hash the password properly
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update in database
    $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashed_password, $email]);

    if ($result && $stmt->rowCount() > 0) {
        echo "✅ SUCCESS!\n";
        echo "Password has been properly hashed and updated.\n";
        echo "New hash: " . substr($hashed_password, 0, 30) . "...\n\n";
        echo "You can now login with password: $new_password\n";

        // Verify it works
        $verify_stmt = $pdo->prepare("SELECT password FROM members WHERE email = ?");
        $verify_stmt->execute([$email]);
        $stored_hash = $verify_stmt->fetch()['password'];

        if (password_verify($new_password, $stored_hash)) {
            echo "\n✅ Verification passed! Login should work now.\n";
        } else {
            echo "\n❌ Verification failed! Something went wrong.\n";
        }
    } else {
        echo "❌ FAILED: No user found with email: $email\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}