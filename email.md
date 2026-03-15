# Email Delivery System Documentation

This document describes the methods and functions used to send emails within the **Ontomeel Bookshop** platform.

## Core SMTP Function

The primary function responsible for communicating with the SMTP server is `send_smtp_email()`.

### Location
`includes/smtp_client.php`

### Function Signature
```php
function send_smtp_email($to, $subject, $message, $config, $is_html = false)
```

### Parameters
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `$to` | String | Recipient email address |
| `$subject` | String | Subject line (auto-encoded to UTF-8 Base64) |
| `$message` | String | The content of the email (HTML or Plain Text) |
| `$config` | Array | SMTP settings (host, port, user, pass, from_name) |
| `$is_html` | Boolean | Whether the message should be treated as HTML |

---

## Technical Implementation Method

The system uses a custom **Direct Socket** implementation rather than standard `mail()` or PHPMailer to ensure maximum control over headers and reduce dependencies.

### 1. Connection Method
- **Socket**: Uses `stream_socket_client` for a secure connection.
- **Security**: Connects via `ssl://` (typically on port 465).
- **Authentication**: Uses `AUTH LOGIN` with Base64 encoded credentials.

### 2. Spam Reduction Techniques (Multipart/Alternative)
To ensure high deliverability to providers like Gmail, the system implements a **Multipart/Alternative** structure:
- **Plain Text Part**: Automatically generated using `strip_tags()` to provide a fallback for basic mail clients.
- **HTML Part**: The full-styled message for modern clients.
- **Headers**: Includes crucial delivery headers:
    - `Message-ID`: Unique identifier to prevent duplication.
    - `Auto-Submitted: auto-generated`: Identifies the email as transactional.
    - `MIME-Version` & `Content-Type`: Defines the boundary-separated format.
    - `Quoted-Printable Encoding`: Ensures special characters (like Bengali digits or currency symbols) don't break the message.

---

## Notification Wrapper

For application-level use, a helper function simplifies the process by handling templates and configurations.

### Location
`includes/notification_helper.php`

### Function
```php
function send_notification($to, $type, $data)
```

This function:
1.  Checks if the user has **unsubscribed** from the `members` table.
2.  Loads credentials from `smtp_config.php`.
3.  Selects the appropriate **HTML Template** based on the event type (e.g., `order_placed`, `order_cancelled`).
4.  Calls `send_smtp_email()` to perform the actual delivery.

---

## Configuration
Credentials are managed via `includes/smtp_config.php`, which prioritizes environment variables from the `.env` file for security.

---

## Related Code Snippets

### 1. Core SMTP Sending Logic (`includes/smtp_client.php`)
This snippet shows the crucial header construction and multipart body generation that ensures high deliverability.

```php
// ... Header Construction ...
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Date: " . date('r') . "\r\n";
$headers .= "Message-ID: <" . time() . "." . uniqid() . "@ontomeel.com>\r\n";
$headers .= "From: $encoded_from_name <$user>\r\n";
$headers .= "Subject: $encoded_subject\r\n";
$headers .= "Auto-Submitted: auto-generated\r\n";
$headers .= "Content-Language: en-US\r\n";

if ($is_html) {
    $boundary = "----=_Part_" . md5(uniqid(time()));
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
    
    // Plain Text Version
    $txt_body = strip_tags($message);
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
    $body .= quoted_printable_encode(trim($txt_body)) . "\r\n\r\n";
    
    // HTML Version
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
    $body .= quoted_printable_encode($message) . "\r\n\r\n";
    $body .= "--$boundary--";
}
```

### 2. Notification Wrapper with Templates (`includes/notification_helper.php`)
The application uses this function to dispatch emails for specific triggers.

```php
function send_notification_instantly($to, $type, $data) {
    // ... config loading ...
    switch ($type) {
        case 'order_placed':
            $subject = "Order Confirmed - #" . $data['invoice_no'];
            $title = "Thank You for Your Order!";
            $content = "<p>Hello <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>...";
            break;
        // ... other cases (order_cancelled, status_update, etc.) ...
    }

    $html_message = "
    <!DOCTYPE html>
    <html>
    <head><meta charset=\"UTF-8\"></head>
    <body style=\"font-family: Arial; line-height: 1.5; color: #333;\">
        <div style=\"max-width: 600px; margin: 0 auto;\">
            <div style=\"background: $color; color: #fff; padding: 20px;\">
                <h1>$title</h1>
            </div>
            <div style=\"padding: 20px; background: #f9f9f9;\">
                $content
            </div>
        </div>
    </body>
    </html>";

    return send_smtp_email($to, $subject, $html_message, $config, true);
}
```

### 3. SMTP Configuration (`includes/smtp_config.php`)
Centralized settings that pull from the `.env` file.

```php
return [
    'host'      => getenv('SMTP_HOST') ?: 'ontomeel.com',
    'port'      => getenv('SMTP_PORT') ?: 465,
    'user'      => getenv('SMTP_USER') ?: 'auth@ontomeel.com',
    'pass'      => getenv('SMTP_PASS'),
    'from_name' => 'Ontomeel Bookshop'
];
```

