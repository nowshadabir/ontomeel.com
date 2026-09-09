<?php
/**
 * SMTP Client for Secure Email Sending
 * Robust implementation using cURL (with stream socket fallback)
 * Works flawlessly on macOS, Linux, Windows, PHP-FPM, Apache, XAMPP, and cPanel.
 */

function send_smtp_email($to, $subject, $message, $config, $is_html = false)
{
    if (empty($to)) {
        return ["success" => false, "message" => "Recipient is empty"];
    }

    $host = $config['host'] ?? 'ontomeel.com';
    $port = (int)($config['port'] ?? 465);
    $user = $config['user'] ?? 'info@ontomeel.com';
    $pass = $config['pass'] ?? '';
    $from_name = $config['from_name'] ?? "Ontomeel Bookshop";
    $reply_to = $config['reply_to'] ?? $user;

    $domain = "ontomeel.com";
    if (strpos($user, '@') !== false) {
        $domain = substr(strrchr($user, "@"), 1);
    }

    // Header Encoding
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $msg_id = "<" . time() . "." . bin2hex(random_bytes(8)) . "@" . $domain . ">";
    $date = date('r');

    $from_header = (preg_match('/[^\x00-\x7F]/', $from_name)) 
        ? "=?UTF-8?B?" . base64_encode($from_name) . "?= <$user>"
        : "$from_name <$user>";

    // Build standard MIME headers
    $headers = "Date: $date\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "From: $from_header\r\n";
    $headers .= "Reply-To: <$reply_to>\r\n";
    $headers .= "Return-Path: <$user>\r\n";
    $headers .= "Subject: $encoded_subject\r\n";
    $headers .= "Message-ID: $msg_id\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Priority: 3 (Normal)\r\n";
    $headers .= "Content-Language: en-US, bn\r\n";
    $headers .= "Auto-Submitted: auto-generated\r\n";
    $headers .= "List-Unsubscribe: <https://$domain/unsubscribe.php?email=" . urlencode($to) . ">, <mailto:unsubscribe@$domain?subject=unsubscribe>\r\n";

    if ($is_html) {
        $boundary = "=_part_" . md5(time() . uniqid());
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
        
        $txt_body = strip_tags($message);
        $txt_body = html_entity_decode($txt_body, ENT_QUOTES, 'UTF-8');

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($txt_body) . "\r\n\r\n";

        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($message) . "\r\n\r\n";
        $body .= "--$boundary--\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body = quoted_printable_encode($message) . "\r\n";
    }

    $raw_email = $headers . $body;

    // Primary: cURL-based SMTP (high performance, non-blocking DNS, thread-safe on all OS)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        
        // Use smtps:// for SSL port 465, smtp:// for 587/25
        $scheme = ($port == 465) ? "smtps" : "smtp";
        curl_setopt($ch, CURLOPT_URL, "$scheme://$host:$port");
        curl_setopt($ch, CURLOPT_USERNAME, $user);
        curl_setopt($ch, CURLOPT_PASSWORD, $pass);
        curl_setopt($ch, CURLOPT_MAIL_FROM, "<$user>");
        curl_setopt($ch, CURLOPT_MAIL_RCPT, ["<$to>"]);
        
        if ($port == 465) {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
        } else {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_TRY);
        }
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $temp_stream = fopen('php://memory', 'r+');
        fwrite($temp_stream, $raw_email);
        rewind($temp_stream);
        curl_setopt($ch, CURLOPT_READDATA, $temp_stream);
        curl_setopt($ch, CURLOPT_UPLOAD, true);

        $result = curl_exec($ch);
        $curl_err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($temp_stream);

        if ($result && ($resp_code === 250 || $resp_code === 0)) {
            return ["success" => true, "debug" => "250 OK (cURL)"];
        } else {
            $err_msg = !empty($curl_err) ? $curl_err : "SMTP error (code $resp_code)";
            return ["success" => false, "message" => $err_msg];
        }
    }

    // Fallback: Raw stream socket
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return ["success" => false, "message" => "Socket connection failed: $errstr"];
    }

    stream_set_timeout($socket, 10);
    $res = "";
    while ($str = fgets($socket, 1024)) {
        $res .= $str;
        if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r" || $str[3] === "\n")) break;
    }

    fwrite($socket, "EHLO $domain\r\n");
    while ($str = fgets($socket, 1024)) { if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, "AUTH LOGIN\r\n");
    while ($str = fgets($socket, 1024)) { if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, base64_encode($user) . "\r\n");
    while ($str = fgets($socket, 1024)) { if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, base64_encode($pass) . "\r\n");
    $auth_res = "";
    while ($str = fgets($socket, 1024)) { $auth_res .= $str; if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    if (strpos($auth_res, "235") === false) {
        fclose($socket);
        return ["success" => false, "message" => "Auth failed: " . trim($auth_res)];
    }

    fwrite($socket, "MAIL FROM: <$user>\r\n");
    while ($str = fgets($socket, 1024)) { if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, "RCPT TO: <$to>\r\n");
    $rcpt_res = "";
    while ($str = fgets($socket, 1024)) { $rcpt_res .= $str; if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    if (strpos($rcpt_res, "250") === false && strpos($rcpt_res, "251") === false) {
        fclose($socket);
        return ["success" => false, "message" => "Recipient rejected: " . trim($rcpt_res)];
    }

    fwrite($socket, "DATA\r\n");
    while ($str = fgets($socket, 1024)) { if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, $raw_email . "\r\n.\r\n");
    $data_res = "";
    while ($str = fgets($socket, 1024)) { $data_res .= $str; if (strlen($str) >= 4 && ($str[3] === ' ' || $str[3] === "\r")) break; }

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($data_res, "250") !== false)
        ? ["success" => true, "debug" => trim($data_res)]
        : ["success" => false, "message" => trim($data_res)];
}
