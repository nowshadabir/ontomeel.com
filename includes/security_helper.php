<?php
/**
 * Security Helper Functions
 * Provides CSRF protection, rate limiting, input sanitization, and security headers
 */

// Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevent XSS in browsers
header('X-XSS-Protection: 1; mode=block');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Referrer policy for privacy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy - reduce XSS attack surface
// Note: Adjust directives based on your needs
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://fonts.googleapis.com https://go.screenpal.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https:; frame-src 'self' https://go.screenpal.com https://www.youtube.com;");

// Force secure session parameters globally
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    // Secure flag only if HTTPS is on
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
}

// Force HTTPS (uncomment in production)
// header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

/**
 * Generate CSRF token
 */
function csrf_token()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input - prevent XSS
 */
function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }

    // Remove leading/trailing whitespace
    $data = trim($data);

    // Convert special HTML characters to entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

/**
 * Sanitize for HTML output (when you need to allow some HTML)
 */
function sanitize_html($data)
{
    // Basic sanitization - remove script tags and event handlers
    $data = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data);
    $data = preg_replace('/\s+on\w+\s*=/i', ' data-removed=', $data);
    $data = preg_replace('/javascript:/i', 'removed:', $data);

    return $data;
}

/**
 * Rate limiting - Simple file-based rate limiter
 * Returns true if request is allowed, false if rate limited
 */
function check_rate_limit($identifier, $max_attempts = 5, $time_window = 300)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $identifier . '_' . $ip;
    $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.txt';

    $now = time();
    $attempts = [];

    // Read existing attempts
    if (file_exists($cache_file)) {
        $data = file_get_contents($cache_file);
        $attempts = json_decode($data, true) ?: [];

        // Clean old attempts
        $attempts = array_filter($attempts, function ($timestamp) use ($now, $time_window) {
            return ($now - $timestamp) < $time_window;
        });
    }

    // Check if rate limited
    if (count($attempts) >= $max_attempts) {
        return false;
    }

    // Add new attempt
    $attempts[] = $now;

    // Save attempts
    file_put_contents($cache_file, json_encode($attempts));

    return true;
}

/**
 * Get current rate limit remaining attempts
 */
function get_rate_limit_remaining($identifier, $max_attempts = 5)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $identifier . '_' . $ip;
    $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.txt';

    $now = time();
    $attempts = [];

    if (file_exists($cache_file)) {
        $data = file_get_contents($cache_file);
        $attempts = json_decode($data, true) ?: [];

        // Clean old attempts
        $attempts = array_filter($attempts, function ($timestamp) use ($now) {
            return ($now - $timestamp) < 300;
        });
    }

    return max(0, $max_attempts - count($attempts));
}

/**
 * Secure session configuration
 */
function init_secure_session()
{
    if (session_status() == PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');

        session_start();

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['created_at'])) {
            $_SESSION['created_at'] = time();
        } else {
            // Regenerate every 30 minutes
            if (time() - $_SESSION['created_at'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['created_at'] = time();
            }
        }

        // Set session timeout (30 minutes of inactivity)
        $timeout = 1800; // 30 minutes
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            // Session expired
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
    }

    return true;
}

/**
 * Generate a random secure token
 */
function generate_token($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate email format
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Bangladeshi format)
 */
function is_valid_phone($phone)
{
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Check for valid Bangladeshi phone formats
    // 01X followed by 9 digits (11 total)
    return preg_match('/^01[3-9][0-9]{8}$/', $phone) === 1;
}

/**
 * Log security events
 */
function log_security_event($event_type, $details = [])
{
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event_type' => $event_type,
        'details' => $details
    ];

    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    error_log(json_encode($log_data) . "\n", 3, $log_file);
}
?>