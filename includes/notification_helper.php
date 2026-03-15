<?php
require_once __DIR__ . '/smtp_client.php';

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

    // SMTP configuration
    $user = getenv('SMTP_USER') ?: 'auth@ontomeel.com';
    $pass = getenv('SMTP_PASS');
    $host = getenv('SMTP_HOST') ?: 'ontomeel.com';
    $port = getenv('SMTP_PORT') ?: 465;

    $config = [
        'host' => $host,
        'port' => $port,
        'user' => $user,
        'pass' => $pass
    ];

    $subject = "";
    $title = "";
    $content = "";
    $color = "#2563eb"; // Professional blue

    switch ($type) {
        case 'order_placed':
            $subject = "Order Confirmed - #" . $data['invoice_no'];
            $title = "Thank You for Your Order!";
            $color = "#16a34a";

            $book_info_html = '';
            if (isset($data['is_preorder']) && $data['is_preorder']) {
                $book_info_html = "
                <div style='background: #fef3c7; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #fcd34d;'>
                    <p style='margin: 0; color: #92400e; font-size: 12px; text-transform: uppercase; font-weight: bold;'>Pre-Order Book</p>
                    <p style='margin: 5px 0 0 0; font-size: 15px; font-weight: bold; color: #78350f;'>" . htmlspecialchars($data['book_title']) . "</p>
                </div>";
            }

            $content = "
                <p>Dear <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> has been successfully placed and is being processed.</p>
                " . $book_info_html . "
                <div style='background: #f8fafc; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #e2e8f0;'>
                    <p style='margin: 0 0 10px 0; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: bold;'>Order Details</p>
                    <p style='margin: 5px 0;'><strong>Amount:</strong> BDT " . number_format($data['amount'], 2) . "</p>
                    <p style='margin: 5px 0;'><strong>Shipping Address:</strong> " . htmlspecialchars($data['address']) . "</p>
                </div>
                <p>We will notify you once your order is confirmed and shipped.</p>
            ";
            if (isset($data['guest']) && $data['guest']) {
                $content .= "<p style='color: #ef4444; font-weight: bold;'>As a guest customer, please save your order number <strong>#" . $data['invoice_no'] . "</strong> for tracking.</p>";
            }
            break;

        case 'order_cancelled':
            $subject = "Order Cancelled - #" . $data['invoice_no'];
            $title = "Order Cancellation Notice";
            $color = "#dc2626";
            $content = "
                <p>Dear " . htmlspecialchars($data['name']) . ",</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> has been cancelled.</p>
                <p>If you have made any payment, it will be refunded to your account balance within 3-5 business days.</p>
                <p>If you did not request this cancellation, please contact our support team immediately.</p>
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

            $book_info_html = '';
            if (isset($data['is_preorder']) && $data['is_preorder']) {
                $book_info_html = "
                <div style='background: #fef3c7; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #fcd34d;'>
                    <p style='margin: 0; color: #92400e; font-size: 12px; text-transform: uppercase; font-weight: bold;'>Pre-Order Book</p>
                    <p style='margin: 5px 0 0 0; font-size: 15px; font-weight: bold; color: #78350f;'>" . htmlspecialchars($data['book_title']) . "</p>
                </div>";
            }

            $content = "
                <p>Dear " . htmlspecialchars($data['name']) . ",</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> status has been updated.</p>
                " . $book_info_html . "
                <p style='margin: 20px 0;'><span style='background: #dbeafe; color: #1e40af; padding: 8px 16px; border-radius: 5px; font-weight: bold; font-size: 14px;'>Current Status: " . $display_status . "</span></p>
            ";
            break;

        case 'borrow_active':
            $subject = "Book Borrowed - #" . $data['invoice_no'];
            $title = "Book Borrowing Confirmed";
            $color = "#7c3aed";
            $content = "
                <p>Dear " . htmlspecialchars($data['name']) . ",</p>
                <p>You have successfully borrowed the book <strong>'" . htmlspecialchars($data['book_title']) . "'</strong>.</p>
                <div style='background: #f5f3ff; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #ddd6fe;'>
                    <p style='margin: 0; color: #7c3aed; font-weight: bold; font-size: 16px;'>Return Due Date: " . htmlspecialchars($data['due_date']) . "</p>
                    <p style='margin: 10px 0 0 0; font-size: 13px; color: #6b7280;'>Please return the book on time so others can enjoy it too.</p>
                </div>
            ";
            break;

        case 'borrow_returned':
            $subject = "Book Returned - #" . $data['invoice_no'];
            $title = "Book Return Confirmed";
            $color = "#059669";
            $content = "
                <p>Dear " . htmlspecialchars($data['name']) . ",</p>
                <p>Thank you for returning the book <strong>'" . htmlspecialchars($data['book_title']) . "'</strong> on time.</p>
                <p>We hope you enjoyed reading it!</p>
            ";
            break;

        default:
            $subject = "Ontomeel Bookshop - Notification";
            $title = "Account Update";
            $content = "<p>You have a new update on your Ontomeel Bookshop account. Please log in to your dashboard to view details.</p>";
            break;
    }

    // Professional From Name
    if (strpos($type, 'order') !== false) {
        $from_name = "Ontomeel Orders";
    }
    elseif (strpos($type, 'otp') !== false || strpos($type, 'auth') !== false) {
        $from_name = "Ontomeel";
    }
    else {
        $from_name = "Ontomeel Bookshop";
    }

    $config['from_name'] = $from_name;
    $config['reply_to'] = $user;

    // Clean, professional HTML template in English
    $html_message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333333;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden;'>
                        <!-- Header -->
                        <tr>
                            <td style='background: " . $color . "; color: #ffffff; padding: 30px; text-align: center;'>
                                <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>" . $title . "</h1>
                            </td>
                        </tr>
                        <!-- Content -->
                        <tr>
                            <td style='padding: 30px;'>
                                " . $content . "
                                <div style='text-align: center; margin-top: 25px;'>
                                    <a href='https://ontomeel.com/dashboard' style='display: inline-block; padding: 12px 30px; background: " . $color . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Dashboard</a>
                                </div>
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td style='background: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #e9ecef;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #6c757d;'>&copy; " . date('Y') . " Ontomeel Bookshop. All rights reserved.</p>
                                <p style='margin: 0 0 10px 0; font-size: 12px; color: #adb5bd;'>This is an automated message. Please do not reply to this email.</p>
                                <p style='margin: 0 0 15px 0; font-size: 12px; color: #adb5bd;'>
                                    Need help? <a href='mailto:support@ontomeel.com' style='color: " . $color . ";'>Contact Support</a>
                                </p>
                                <div style='border-top: 1px solid #e9ecef; padding-top: 15px; margin-top: 15px;'>
                                    <a href='https://ontomeel.com/unsubscribe' style='color: #adb5bd; font-size: 11px; text-decoration: underline;'>Unsubscribe</a>
                                    <span style='color: #adb5bd; font-size: 11px;'> | </span>
                                    <a href='https://ontomeel.com/privacy' style='color: #adb5bd; font-size: 11px; text-decoration: underline;'>Privacy Policy</a>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p style='margin: 20px 0 0 0; font-size: 11px; color: #999999;'>Ontomeel Bookshop | Online Book Store | Bangladesh</p>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";

    return send_smtp_email($to, $subject, $html_message, $config, true);
}

/**
 * Queue-based notification (not used for critical emails)
 */
function send_notification($to, $type, $data)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO email_queue (recipient, type, payload) VALUES (?, ?, ?)");
        $stmt->execute([$to, $type, json_encode($data)]);
    }
    catch (Exception $e) {
        error_log("Queue Failed, falling back to instant send: " . $e->getMessage());
        return send_notification_instantly($to, $type, $data);
    }

    trigger_worker();
    return ['success' => true, 'message' => 'Email queued'];
}

function trigger_worker()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $url = $protocol . "://" . $host . "/bookshop/includes/email_worker.php";

    $parts = parse_url($url);
    $port = isset($parts['port']) ? $parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80);
    $host_conn = ($parts['scheme'] === 'https' ? "ssl://" : "") . $parts['host'];

    $fp = @fsockopen($host_conn, $port, $errno, $errstr, 2);
    if ($fp) {
        $out = "GET " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);
    }
}
