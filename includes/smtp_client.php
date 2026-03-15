<?php
/**
 * SMTP Client for Secure Email Sending
 * Sends email via SMTP with SSL/TLS and Authentication
 */

function get_smtp_response($socket, $debug = false)
{
    $res = "";
    while ($str = fgets($socket, 1024)) {
        $res .= $str;
        if (substr($str, 3, 1) == " " || substr($str, 3, 1) == "") {
            break;
        }
    }
    if ($debug) {
        echo "S <- " . htmlspecialchars($res) . "<br>";
    }
    return $res;
}

function send_smtp_email($to, $subject, $message, $config, $is_html = false)
{
    if (empty($to)) {
        return ["success" => false, "message" => "Recipient empty"];
    }

    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $from_name = $config['from_name'] ?? "Ontomeel Bookshop";

    $domain = "ontomeel.com";
    if (strpos($user, '@') !== false) {
        $domain = substr(strrchr($user, "@"), 1);
    }
    
    $reply_to = $config['reply_to'] ?? $user;

    $context = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ]);

    $socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return ["success" => false, "message" => "Connection failed: $errstr"];
    }

    // SMTP Handshake
    get_smtp_response($socket);
    fwrite($socket, "EHLO $domain\r\n");
    get_smtp_response($socket);

    fwrite($socket, "AUTH LOGIN\r\n");
    get_smtp_response($socket);
    fwrite($socket, base64_encode($user) . "\r\n");
    get_smtp_response($socket);
    fwrite($socket, base64_encode($pass) . "\r\n");
    $res = get_smtp_response($socket);

    if (strpos($res, "235") === false) {
        fclose($socket);
        return ["success" => false, "message" => "Auth Failed: " . $res];
    }

    // Envelope
    fwrite($socket, "MAIL FROM: <$user>\r\n");
    get_smtp_response($socket);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    get_smtp_response($socket);
    fwrite($socket, "DATA\r\n");
    get_smtp_response($socket);

    // Header Encoding
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $msg_id = "<" . time() . "." . bin2hex(random_bytes(8)) . "@" . $domain . ">";
    $date = date('r');

    // Only encode name if it contains non-ASCII characters
    $from_header = (preg_match('/[^\x00-\x7F]/', $from_name)) 
        ? "=?UTF-8?B?" . base64_encode($from_name) . "?= <$user>"
        : "$from_name <$user>";

    // Headers
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
        $boundary = "=_part_" . md5(time());
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

    // SMTP Data Termination
    fwrite($socket, $headers . $body . "\r\n.\r\n");

    $data_res = get_smtp_response($socket);
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($data_res, "250") !== false)
        ? ["success" => true, "debug" => $data_res]
        : ["success" => false, "message" => $data_res];
}

