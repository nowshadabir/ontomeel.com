<?php
/**
 * Minimal SMTP Client for Secure Email Sending
 * Sends email via SMTP with SSL/TLS and Authentication without external libraries.
 */

function get_smtp_response($socket)
{
    $res = "";
    while ($str = fgets($socket, 515)) {
        $res .= $str;
        if (substr($str, 3, 1) == " ")
            break;
    }
    return $res;
}

function send_smtp_email($to, $subject, $message, $config, $is_html = false)
{
    if (empty($to))
        return ["success" => false, "message" => "Recipient empty"];

    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $from_name = $config['from_name'] ?? "Antyamil";
    $reply_to = $config['reply_to'] ?? $user;

    // Create Socket
    $socket = @fsockopen("ssl://$host", $port, $errno, $errstr, 30);
    if (!$socket)
        return ["success" => false, "message" => "Connection failed: $errstr ($errno)"];

    // Server Greeting
    get_smtp_response($socket);

    // HELO/EHLO
    fputs($socket, "EHLO $host\r\n");
    get_smtp_response($socket);

    // Login
    fputs($socket, "AUTH LOGIN\r\n");
    $res = get_smtp_response($socket);
    if (strpos($res, "334") === false) {
        fclose($socket);
        return ["success" => false, "message" => "AUTH LOGIN command failed: $res"];
    }

    fputs($socket, base64_encode($user) . "\r\n");
    get_smtp_response($socket);

    fputs($socket, base64_encode($pass) . "\r\n");
    $res = get_smtp_response($socket);
    if (strpos($res, "235") === false) {
        fclose($socket);
        return ["success" => false, "message" => "Authentication failed: $res"];
    }

    // MAIL FROM
    fputs($socket, "MAIL FROM: <$user>\r\n");
    get_smtp_response($socket);

    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    get_smtp_response($socket);

    // DATA
    fputs($socket, "DATA\r\n");
    get_smtp_response($socket);

    // Encode Subject and From Name
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $encoded_from_name = "=?UTF-8?B?" . base64_encode($from_name) . "?=";

    // Boundary for multipart
    $boundary = "----=_Part_" . md5(time() . uniqid());
    $domain = (strpos($host, '.') !== false) ? $host : "ontomeel.com";
    $msg_id = "<" . time() . "." . uniqid() . "@$domain>";
    $date = date('r');

    // Create Plain Text version by stripping tags if it's HTML
    $text_message = $is_html ? strip_tags(str_replace(['<br>', '</p>'], "\n", $message)) : $message;
    $encoded_text = quoted_printable_encode($text_message);
    $encoded_html = $is_html ? quoted_printable_encode($message) : "";

    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Date: $date\r\n";
    $headers .= "Message-ID: $msg_id\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "From: $encoded_from_name <$user>\r\n";
    $headers .= "Reply-To: $encoded_from_name <$reply_to>\r\n";
    $headers .= "Return-Path: <$user>\r\n";
    $headers .= "Subject: $encoded_subject\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Importance: normal\r\n";
    $headers .= "Auto-Submitted: auto-generated\r\n";
    $headers .= "X-Auto-Response-Suppress: OOF, DR, RN, NRN\r\n";

    if ($is_html) {
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $headers .= "List-Unsubscribe: <mailto:unsubscribe@ontomeel.com>\r\n";
        $headers .= "Precedence: bulk\r\n\r\n";

        // Body Construction (Multipart)
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= $encoded_text . "\r\n\r\n";

        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= $encoded_html . "\r\n\r\n";
        $body .= "--$boundary--\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $headers .= "List-Unsubscribe: <mailto:unsubscribe@ontomeel.com>\r\n";
        $headers .= "Precedence: bulk\r\n\r\n";
        $body = $encoded_text;
    }

    fputs($socket, $headers . $body . "\r\n.\r\n");
    $res = get_smtp_response($socket);

    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($res, "250") !== false || strpos($res, "200") !== false) ? ["success" => true] : ["success" => false, "message" => $res];
}
