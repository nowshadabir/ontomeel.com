<?php
require_once 'includes/db_connect.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = 'Invalid email address.';
    } else {
        try {
            // Check if member exists
            $stmt = $pdo->prepare("SELECT id, email_unsubscribed FROM members WHERE email = ?");
            $stmt->execute([$email]);
            $member = $stmt->fetch();

            if ($member) {
                // Update unsubscribe status
                $stmt = $pdo->prepare("UPDATE members SET email_unsubscribed = 1, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$member['id']]);
                $message = 'You have been unsubscribed successfully. You will no longer receive promotional emails.';
            } else {
                $message = 'This email is not registered in our system.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Unsubscribe error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Ontomeel Bookshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 m-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Unsubscribe</h1>
            <p class="text-gray-600 mt-2">Ontomeel Bookshop</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$message || $error): ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                        Enter your email address
                    </label>
                    <input type="email" id="email" name="email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
                        placeholder="your@email.com">
                </div>
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Unsubscribe
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>You will no longer receive promotional emails from us.</p>
            <p class="mt-2">For support: <a href="mailto:support@ontomeel.com"
                    class="text-blue-500 hover:underline">support@ontomeel.com</a></p>
        </div>
    </div>
</body>

</html>