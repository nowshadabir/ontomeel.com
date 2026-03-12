<?php
/**
 * Minimal SMTP Client for Secure Email Sending
 * Sends email via SMTP with SSL/TLS and Authentication without external libraries.
 */

function get_smtp_response($socket) {
    $res = "";
    while ($str = fgets($socket, 515)) {
        $res .= $str;
        if (substr($str, 3, 1) == " ") break;
    }
    return $res;
}

function send_smtp_email($to, $subject, $message, $config, $is_html = false) {
    if (empty($to)) return ["success" => false, "message" => "Recipient empty"];
    
    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $from_name = "Antyamil";

    // Create Socket
    $socket = @fsockopen("ssl://$host", $port, $errno, $errstr, 30);
    if (!$socket) return ["success" => false, "message" => "Connection failed: $errstr ($errno)"];

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

    // Encode Subject for UTF-8
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    
    // Message ID and Date
    $msg_id = "<" . md5(uniqid(microtime())) . "@$host>";
    $date = date('r');

    // Header & Content
    $type = $is_html ? "text/html" : "text/plain";
    $headers = "To: <$to>\r\n";
    $headers .= "From: $from_name <$user>\r\n";
    $headers .= "Subject: $encoded_subject\r\n";
    $headers .= "Date: $date\r\n";
    $headers .= "Message-ID: $msg_id\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: $type; charset=UTF-8\r\n";
    $headers .= "Reply-To: <$user>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n\r\n";

    // Escape dots at start of lines
    $escaped_message = str_replace("\n.", "\n..", $message);

    fputs($socket, $headers . $escaped_message . "\r\n.\r\n");
    $res = get_smtp_response($socket);

    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($res, "250") !== false || strpos($res, "200") !== false) ? ["success" => true] : ["success" => false, "message" => $res];
}
