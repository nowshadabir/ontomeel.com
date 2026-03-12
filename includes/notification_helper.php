<?php
require_once __DIR__ . '/smtp_client.php';

/**
 * Original instant send function - now renamed to avoided conflict with queue
 */
function send_notification_instantly($to, $type, $data) {
    if (empty($to)) return ['success' => false, 'message' => 'No recipient email'];

    $host = getenv('SMTP_HOST') ?: 'ontomeel.com';
    $port = getenv('SMTP_PORT') ?: 465;
    $user = getenv('SMTP_AUTH_USER') ?: getenv('SMTP_USER');
    $pass = getenv('SMTP_PASS');

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

    $template = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f1f5f9; }
            .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            .header { background: {$color}; padding: 40px 20px; text-align: center; color: white; }
            .body { padding: 40px; }
            .footer { background: #f8fafc; padding: 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
            .brand { font-size: 24px; font-weight: 800; letter-spacing: -0.025em; margin-bottom: 5px; display: block; text-decoration: none; color: white !important; }
            .btn { display: inline-block; padding: 12px 25px; background: #0f172a; color: white !important; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='header'>
                <a href='https://ontomeel.com' class='brand'>Antyamil</a>
                <h1 style='margin: 10px 0 0; font-size: 20px; font-weight: 600;'>{$title}</h1>
            </div>
            <div class='body'>
                {$content}
                <a href='https://ontomeel.com/dashboard' class='btn'>আমার একাউন্ট</a>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Antyamil Bookshop. All Rights Reserved.</p>
                <p>House #00, Road #00, Dhaka, Bangladesh</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return send_smtp_email($to, $subject, $template, $config, true);
}

/**
 * Public function to queue an email and trigger worker
 */
function send_notification($to, $type, $data) {
    global $pdo;
    
    // 1. Ensure queue table exists (one-time check)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipient VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            payload TEXT NOT NULL,
            status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_at TIMESTAMP NULL
        )");
    } catch (Exception $e) {}

    // 2. Add to queue
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

function trigger_worker() {
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
