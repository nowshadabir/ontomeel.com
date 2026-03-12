<?php
require_once __DIR__ . '/smtp_client.php';

/**
 * Original instant send function - now renamed to avoided conflict with queue
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
    } catch (Exception $e) {
        // Continue if check fails
    }

    // Use single SMTP account for all purposes as requested
    $user = getenv('SMTP_USER') ?: 'info@ontomeel.com';
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
    $color = "#0f172a";

    // ... (templates logic - same as before)
    switch ($type) {
        case 'order_placed':
            $subject = "অর্ডার কনফার্মেশন - #{$data['invoice_no']}";
            $title = "অর্ডার সফলভাবে গ্রহণ করা হয়েছে!";
            $color = "#16a34a";
            $content = "
                <p>প্রিয় <strong>{$data['name']}</strong>,</p>
                <p>আপনার অর্ডারটি (#{$data['invoice_no']}) সফলভাবে আমাদের সিস্টেমে যুক্ত হয়েছে। আমরা দ্রুততম সময়ের মধ্যে আপনার কাছে বই পৌঁছে দিতে কাজ করছি।</p>
                <div style='background: #f8fafc; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                    <p style='margin: 0; color: #64748b; font-size: 12px; text-transform: uppercase;'>অর্ডার বিবরণ:</p>
                    <p style='margin: 5px 0;'><strong>পরিমাণ:</strong> ৳{$data['amount']}</p>
                    <p style='margin: 5px 0;'><strong>ঠিকানা:</strong> {$data['address']}</p>
                </div>
            ";
            if (isset($data['guest']) && $data['guest']) {
                $content .= "<p style='color: #ef4444; font-weight: bold;'>যেহেতু আপনি গেস্ট হিসেবে অর্ডার করেছেন, দয়া করে অর্ডার নম্বরটি লিখে রাখুন বা এই ইমেইলটি সংরক্ষণ করুন।</p>";
            }
            break;

        case 'order_cancelled':
            $subject = "অর্ডার বাতিল - #{$data['invoice_no']}";
            $title = "অর্ডার বাতিল করা হয়েছে";
            $color = "#ef4444";
            $content = "
                <p>প্রিয় {$data['name']},</p>
                <p>আপনার অনুরোধ বা প্রশাসনিক কারণে অর্ডার নম্বর <strong>#{$data['invoice_no']}</strong> বাতিল করা হয়েছে।</p>
                <p>যদি পেমেন্ট করা হয়ে থাকে, তবে তা আপনার একাউন্ট ব্যালেন্সে রিফান্ড করে দেওয়া হবে।</p>
            ";
            break;

        case 'order_status_update':
            $status_labels = [
                'Confirmed' => 'নিশ্চিত করা হয়েছে',
                'Shipped' => 'পাঠানো হয়েছে (Shipped)',
                'Delivered' => 'ডেলিভারি সম্পন্ন',
                'Processing' => 'প্রসেসিং হচ্ছে'
            ];
            $display_status = $status_labels[$data['status']] ?? $data['status'];
            $subject = "অর্ডার আপডেট - #{$data['invoice_no']}";
            $title = "আপনার অর্ডারের নতুন আপডেট";
            $color = "#3b82f6";
            $content = "
                <p>প্রিয় {$data['name']},</p>
                <p>আপনার অর্ডার <strong>#{$data['invoice_no']}</strong> এর বর্তমান স্ট্যাটাস: <span style='background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 5px; font-weight: bold;'>{$display_status}</span></p>
            ";
            break;

        case 'borrow_active':
            $subject = "বই ধার শুরু - #{$data['invoice_no']}";
            $title = "বই ধার নেওয়া সফল হয়েছে";
            $color = "#8b5cf6";
            $content = "
                <p>প্রিয় {$data['name']},</p>
                <p>আপনার <strong>'{$data['book_title']}'</strong> বইটি ধার নেওয়ার অনুরোধটি সক্রিয় করা হয়েছে।</p>
                <div style='background: #f5f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                    <p style='margin: 0; color: #7c3aed; font-weight: bold;'>ফেরত দেওয়ার শেষ তারিখ: {$data['due_date']}</p>
                    <p style='margin: 5px 0; font-size: 13px; color: #6b7280;'>দয়া করে সময়ের মধ্যে বইটি ফেরত দিয়ে অন্যদের সুযোগ করে দিন।</p>
                </div>
            ";
            break;

        case 'borrow_returned':
            $subject = "বই ফেরত পাওয়া গেছে - #{$data['invoice_no']}";
            $title = "বই ফেরত কনফার্মেশন";
            $color = "#059669";
            $content = "
                <p>প্রিয় {$data['name']},</p>
                <p>আপনার ধার নেওয়া <strong>'{$data['book_title']}'</strong> বইটি আমাদের হাতে পৌঁছেছে। সময়মতো ফেরত দেওয়ার জন্য ধন্যবাদ।</p>
            ";
            break;

        default:
            $subject = "Antyamil - নতুন নোটিফিকেশন";
            $title = "সিস্টেম আপডেট";
            $content = "<p>আপনার একাউন্টে একটি নতুন আপডেট আছে। বিস্তারিত জানতে ড্যাশবোর্ড ভিজিট করুন।</p>";
            break;
    }

    // Determine professional From Name based on type
    if (strpos($type, 'order') !== false) {
        $from_name = "Antyamil Billing";
    } elseif (strpos($type, 'otp') !== false || strpos($type, 'auth') !== false) {
        $from_name = "Antyamil Auth";
    } else {
        $from_name = "Antyamil";
    }

    // Pass custom from_name to config
    $config['from_name'] = $from_name;
    $config['reply_to'] = $user; // Reply to the account that sent it

    // Wrap in professional HTML template
    $html_message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
            .header { background: {$color}; color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; background: #ffffff; }
            .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
            .btn { display: inline-block; padding: 12px 24px; background: {$color}; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 24px;'>{$title}</h1>
            </div>
            <div class='content'>
                {$content}
                <div style='text-align: center;'>
                    <a href='https://ontomeel.com/dashboard' class='btn'>ড্যাশবোর্ড দেখুন</a>
                </div>
            </div>
            <div class='footer'>
                <p style='margin: 5px 0;'>&copy; " . date('Y') . " Antyamil Bookshop. All rights reserved.</p>
                <p style='margin: 5px 0;'>এটি একটি অটোমেটিক জেনারেটেড ইমেইল। কোনো সাহায্যের প্রয়োজন হলে আমাদের <a href='mailto:support@ontomeel.com' style='color: #3b82f6;'>সাপোর্ট টিমের</a> সাথে যোগাযোগ করুন।</p>
                <p style='margin: 5px 0; font-size: 10px;'>Antomeel | Online Book Shop | Bangladesh</p>
                <div style='margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;'>
                    <a href='https://ontomeel.com/unsubscribe' style='color: #64748b; font-size: 11px; text-decoration: underline;'>Unsubscribe</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return send_smtp_email($to, $subject, $html_message, $config, true);
}

/**
 * Public function to queue an email and trigger worker
 */
function send_notification($to, $type, $data)
{
    global $pdo;

    // Add to queue
    try {
        $stmt = $pdo->prepare("INSERT INTO email_queue (recipient, type, payload) VALUES (?, ?, ?)");
        $stmt->execute([$to, $type, json_encode($data)]);
    } catch (Exception $e) {
        // Fallback to instant send if queue fails
        error_log("Queue Failed, falling back to instant send: " . $e->getMessage());
        return send_notification_instantly($to, $type, $data);
    }

    // 3. Trigger worker asynchronously (non-blocking)
    trigger_worker();

    return ['success' => true, 'message' => 'Email queued'];
}

function trigger_worker()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    // Construct a absolute path regardless of where we are
    // We assume the project is in /bookshop/ relative to document root
    $url = $protocol . "://" . $host . "/bookshop/includes/email_worker.php";

    // Simple non-blocking trigger using fsockopen
    $parts = parse_url($url);
    $port = isset($parts['port']) ? $parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80);
    $host_conn = ($parts['scheme'] === 'https' ? "ssl://" : "") . $parts['host'];

    $fp = @fsockopen($host_conn, $port, $errno, $errstr, 2);
    if ($fp) {
        $out = "GET " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        // We don't wait for response, just close and continue
        fclose($fp);
    }
}
