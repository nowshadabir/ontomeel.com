<?php
// Load .env file first
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . "=" . trim($value));
        }
    }
}

require_once 'includes/smtp_client.php';

header('Content-Type: text/html; charset=UTF-8');

$message = '';
$error = '';
$debug = '';
$success = false;
$smtp_debug = '';

// Test configuration
$test_config = [
    'host' => getenv('SMTP_HOST') ?: 'ontomeel.com',
    'port' => getenv('SMTP_PORT') ?: 465,
    'user' => getenv('SMTP_USER') ?: 'info@ontomeel.com',
    'pass' => getenv('SMTP_PASS'),
    'from_name' => 'Ontomeel Test'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $test_email = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);

    if (!$test_email) {
        $error = 'Please enter a valid email address.';
    } else {
        $subject = 'Test Email from Ontomeel Bookshop';
        $html_message = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
            <div style="background: #2563eb; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0;">Test Email Successful!</h1>
            </div>
            <div style="padding: 20px; background: #f9f9f9;">
                <p>This is a test email from <strong>Ontomeel Bookshop</strong>.</p>
                <p>If you received this email, your email configuration is working correctly.</p>
            </div>
            <div style="padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd;">
                <p>Ontomeel Bookshop | Online Book Store</p>
            </div>
        </body>
        </html>';

        $result = send_smtp_email($test_email, $subject, $html_message, $test_config, true);

        if ($result['success']) {
            $success = true;
            $message = 'Test email sent successfully to: ' . htmlspecialchars($test_email);
            $smtp_debug = $result['debug'] ?? '';
        } else {
            $error = 'Failed to send email: ' . htmlspecialchars($result['message']);
            $smtp_debug = $result['debug'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - Ontomeel Bookshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Email Test Tool</h1>

            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-green-700 font-semibold"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                    <p class="text-red-700 font-semibold"><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($smtp_debug): ?>
                <div class="bg-gray-900 text-green-400 p-4 rounded mb-6 font-mono text-sm overflow-x-auto">
                    <pre><?php echo htmlspecialchars($smtp_debug); ?></pre>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="test_email" class="block text-gray-700 font-bold mb-2">
                        Enter your email address to test:
                    </label>
                    <input type="email" id="test_email" name="test_email" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="your@email.com">
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    Send Test Email
                </button>
            </form>

            <div class="mt-8 p-4 bg-blue-50 rounded border border-blue-200">
                <h3 class="font-bold text-blue-800 mb-2">Current Configuration:</h3>
                <p class="text-sm text-blue-700">
                    <strong>SMTP Host:</strong> <?php echo htmlspecialchars($test_config['host']); ?><br>
                    <strong>SMTP Port:</strong> <?php echo htmlspecialchars($test_config['port']); ?><br>
                    <strong>Username:</strong> <?php echo htmlspecialchars($test_config['user']); ?><br>
                    <strong>Password:</strong>
                    <?php echo empty($test_config['pass']) ? '<span class="text-red-600">NOT SET!</span>' : '********'; ?>
                </p>
            </div>
        </div>
    </div>
</body>

</html>