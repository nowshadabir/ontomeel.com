<?php
/**
 * Minimal SMTP Client for Secure Email Sending
 * Sends email via SMTP with SSL/TLS and Authentication without external libraries.
 */

function send_smtp_email($to, $subject, $message, $config) {
    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $from = $config['user'];
    $from_name = "Antyamil";

    // Create Socket
    $socket = fsockopen("ssl://$host", $port, $errno, $errstr, 30);
    if (!$socket) return ["success" => false, "message" => "Connection failed: $errstr ($errno)"];

    function get_response($socket) {
        $res = "";
        while ($str = fgets($socket, 515)) {
            $res .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $res;
    }

    // Server Greeting
    get_response($socket);

    // HELO/EHLO
    fputs($socket, "EHLO $host\r\n");
    get_response($socket);

    // Login
    fputs($socket, "AUTH LOGIN\r\n");
    get_response($socket);

    fputs($socket, base64_encode($user) . "\r\n");
    get_response($socket);

    fputs($socket, base64_encode($pass) . "\r\n");
    $res = get_response($socket);
    if (strpos($res, "235") === false) return ["success" => false, "message" => "Authentication failed: $res"];

    // MAIL FROM
    fputs($socket, "MAIL FROM: <$user>\r\n");
    get_response($socket);

    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    get_response($socket);

    // DATA
    fputs($socket, "DATA\r\n");
    get_response($socket);

    // Header & Content
    $headers = "To: <$to>\r\n";
    $headers .= "From: $from_name <$user>\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Reply-To: <$user>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n\r\n";

    fputs($socket, $headers . $message . "\r\n.\r\n");
    $res = get_response($socket);

    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return strpos($res, "250") !== false ? ["success" => true] : ["success" => false, "message" => $res];
}
