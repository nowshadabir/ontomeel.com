<?php
require_once __DIR__ . '/smtp_client.php';
require_once __DIR__ . '/smtp_config.php';

/**
 * Send notifications instantly - all content in English to avoid spam
 */
function send_notification_instantly($to, $type, $data)
{
    if (empty($to))
        return ['success' => false, 'message' => 'No recipient email'];

    // Check if user has unsubscribed from emails
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT email_unsubscribed FROM members WHERE email = ?");
        $stmt->execute([$to]);
        $member = $stmt->fetch();
        if ($member && $member['email_unsubscribed']) {
            return ['success' => false, 'message' => 'User has unsubscribed from emails'];
        }
    }
    catch (Exception $e) {
    // Continue if check fails
    }

    // Load SMTP configuration
    $smtp_config = require __DIR__ . '/smtp_config.php';

    $config = [
        'host' => $smtp_config['host'],
        'port' => $smtp_config['port'],
        'user' => $smtp_config['user'],
        'pass' => $smtp_config['pass'],
        'from_name' => $smtp_config['from_name'],
        'reply_to' => $smtp_config['reply_to']
    ];

    $subject = "";
    $title = "";
    $content = "";
    $color = "#2563eb"; // Professional blue

    // Use English title/author if provided, otherwise fallback to generic English to avoid spam
    $book_display_title = (!empty($data['book_title_en'])) ? $data['book_title_en'] : 'a book from our collection';
    $book_display_author = (!empty($data['book_author_en'])) ? $data['book_author_en'] : '';

    switch ($type) {
        case 'order_placed':
            $subject = "Order Confirmed - #" . $data['invoice_no'];
            $title = "Thank You for Your Order!";
            $color = "#16a34a";

            $book_info_html = '';
            if (isset($data['book_title']) || isset($data['book_title_en'])) {
                $book_info_html = "
                <div style=\"background: #f0f0f0; padding: 15px; margin: 15px 0;\">
                    <p style=\"margin: 0; font-weight: bold;\">" . (isset($data['is_preorder']) ? 'Pre-Order' : 'Book') . " Details</p>
                    <p style=\"margin: 5px 0; font-weight: bold;\">" . htmlspecialchars($book_display_title) . "</p>
                    " . ($book_display_author ? "<p style=\"margin: 0;\">by " . htmlspecialchars($book_display_author) . "</p>" : "") . "
                </div>";
            }

            $content = "
                <p>Hello <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> has been received and is being processed.</p>
                " . $book_info_html . "
                <p><strong>Total Amount:</strong> BDT " . number_format($data['amount'], 2) . "</p>
                <p><strong>Shipping Address:</strong> " . htmlspecialchars($data['address']) . "</p>
                <p>We will notify you once your order has been shipped!</p>
            ";
            if (isset($data['guest']) && $data['guest']) {
                $content .= "<p><strong>Note:</strong> Please keep your order number <strong>#" . $data['invoice_no'] . "</strong> for tracking.</p>";
            }
            break;

        case 'order_cancelled':
            $subject = "Order Cancelled - #" . $data['invoice_no'];
            $title = "Order Cancellation Notice";
            $color = "#dc2626";
            $content = "
                <p>Hello " . htmlspecialchars($data['name']) . ",</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> has been cancelled.</p>
                <p>If any payment was made, it will be credited back within 3-5 business days.</p>
            ";
            break;

        case 'order_status_update':
            $status_labels = [
                'Confirmed' => 'Confirmed',
                'Shipped' => 'Shipped',
                'Delivered' => 'Delivered',
                'Processing' => 'Processing'
            ];
            $display_status = $status_labels[$data['status']] ?? $data['status'];
            $subject = "Order Update - #" . $data['invoice_no'];
            $title = "Your Order Status Has Been Updated";
            $color = "#2563eb";

            $content = "
                <p>Hello " . htmlspecialchars($data['name']) . ",</p>
                <p>The status of your order <strong>#" . $data['invoice_no'] . "</strong> has changed.</p>
                <p><strong>New Status:</strong> " . $display_status . "</p>
            ";
            break;

        case 'borrow_active':
            $subject = "Book Borrowed - #" . $data['invoice_no'];
            $title = "Borrow Confirmation";
            $color = "#7c3aed";
            $content = "
                <p>Hello " . htmlspecialchars($data['name']) . ",</p>
                <p>You have borrowed <strong>\"" . htmlspecialchars($book_display_title) . "\"</strong>.</p>
                <p><strong>Return Due Date:</strong> " . htmlspecialchars($data['due_date']) . "</p>
                <p>Please return the book by the due date to avoid late fees.</p>
            ";
            break;

        case 'borrow_returned':
            $subject = "Book Returned - #" . $data['invoice_no'];
            $title = "Return Confirmation";
            $color = "#059669";
            $content = "
                <p>Hello " . htmlspecialchars($data['name']) . ",</p>
                <p>Thank you for returning <strong>\"" . htmlspecialchars($book_display_title) . "\"</strong>.</p>
                <p>We hope you enjoyed the read!</p>
            ";
            break;

        default:
            $subject = "Ontomeel Bookshop Notification";
            $title = "Account Notification";
            $content = "<p>There is a new update regarding your Ontomeel Bookshop account.</p>";
            break;
    }

    // Professional From Name
    $from_name = "Ontomeel Bookshop";

    $config['from_name'] = $from_name;
    $config['reply_to'] = $config['user'];

    // Minimalist HTML template - avoids 'high-probability spam' by mimicking simple OTP emails
    $html_message = "
    <!DOCTYPE html>
    <html lang=\"bn\">
    <head><meta charset=\"UTF-8\"></head>
    <body style=\"font-family: sans-serif; line-height: 1.5; color: #333333; margin: 0; padding: 20px;\">
        <div style=\"max-width: 600px; border: 1px solid #eeeeee; padding: 20px;\">
            <h2 style=\"color: $color; margin-top: 0;\">$title</h2>
            <hr style=\"border: none; border-top: 1px solid #eeeeee; margin: 20px 0;\">
            <div style=\"font-size: 16px;\">
                $content
            </div>
            <p style=\"margin-top: 30px; text-align: center;\">
                <a href=\"https://ontomeel.com/dashboard\" style=\"background-color: $color; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;\">View Your Order</a>
            </p>
            <div style=\"margin-top: 40px; border-top: 1px solid #eeeeee; padding-top: 15px; font-size: 12px; color: #888888; text-align: center;\">
                <p><strong>Ontomeel Bookshop</strong><br>Online Store</p>
                <p>&copy; " . date('Y') . " All Rights Reserved.</p>
                <p><a href=\"https://ontomeel.com/unsubscribe.php?email=" . urlencode($to) . "\" style=\"color: #888888;\">Unsubscribe</a></p>
            </div>
        </div>
    </body>
    </html>
    ";

    return send_smtp_email($to, $subject, $html_message, $config, true);
}

/**
 * Fallback: Try alternative SMTP credentials if primary fails
 */
function send_notification_with_fallback($to, $type, $data)
{
    // Try primary credentials first
    $result = send_notification_instantly($to, $type, $data);

    if (!$result['success']) {
        error_log("Primary SMTP failed, trying fallback: " . $result['message']);

        // Try with info@ontomeel.com as fallback
        $fallback_config = [
            'host' => getenv('SMTP_HOST') ?: 'ontomeel.com',
            'port' => getenv('SMTP_PORT') ?: 465,
            'user' => getenv('SMTP_USER') ?: 'info@ontomeel.com',
            'pass' => getenv('SMTP_PASS'),
            'from_name' => 'Ontomeel Bookshop',
            'reply_to' => getenv('SMTP_USER') ?: 'info@ontomeel.com'
        ];

        // Get the email content (reconstruct from type and data)
        $subject = "Ontomeel Bookshop Notification";
        $title = "Account Update";
        $content = "<p>You have a new update on your Ontomeel Bookshop account.</p>";
        $color = "#2563eb";

        // Build the HTML message (simplified version for fallback)
        $html_message = "
        <!DOCTYPE html>
        <html><head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; padding: 20px;'>
            <div style='background: $color; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>$title</h1>
            </div>
            <div style='padding: 20px; background: #f9f9f9;'>
                $content
            </div>
        </body></html>";

        return send_smtp_email($to, $subject, $html_message, $fallback_config, true);
    }

    return $result;
}

/**
 * Send notification - sends immediately for reliability
 */
function send_notification($to, $type, $data)
{
    // Send immediately instead of queuing for reliable delivery
    return send_notification_instantly($to, $type, $data);
}

function trigger_worker()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Determine the correct path - works for both localhost and production
    $script_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    if ($script_path === '\\' || $script_path === '/') {
        $script_path = '';
    }
    $worker_path = $script_path . '/includes/email_worker.php';
    $url = $protocol . "://" . $host . $worker_path;

    // Use cURL for a more robust non-blocking trigger
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Only wait 1 second
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

    // SSL Verification bypass for local/dev
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_exec($ch);
    curl_close($ch);
    return true; // Assume triggered successfully
}
