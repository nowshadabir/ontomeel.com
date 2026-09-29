<?php
// admin/dashboard/includes/admin_helpers.php
// Common utilities and authentication for Admin Panel

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db_connect.php';

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (!function_exists('bn_num')) {
    function bn_num($num)
    {
        if ($num === null || $num === '') {
            return '০';
        }
        $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace(range(0, 9), $bn_digits, (string)$num);
    }
}

if (!function_exists('format_bn_datetime')) {
    function format_bn_datetime($datetime_str)
    {
        if (!$datetime_str || $datetime_str === '0000-00-00 00:00:00') {
            return '—';
        }
        $time = strtotime($datetime_str);
        if (!$time) {
            return $datetime_str;
        }
        $months = [
            'Jan' => 'জানু', 'Feb' => 'ফেব্রু', 'Mar' => 'মার্চ', 'Apr' => 'এপ্রিল',
            'May' => 'মে', 'Jun' => 'জুন', 'Jul' => 'জুলাই', 'Aug' => 'আগস্ট',
            'Sep' => 'সেপ্টে', 'Oct' => 'অক্টো', 'Nov' => 'নভে', 'Dec' => 'ডিসে'
        ];
        $d = date('d', $time);
        $m = $months[date('M', $time)] ?? date('M', $time);
        $y = date('Y', $time);
        $hour = date('h:i', $time);
        $ampm = date('A', $time) === 'AM' ? 'AM' : 'PM';
        return bn_num($d) . ' ' . $m . ' ' . bn_num($y) . ', ' . bn_num($hour) . ' ' . $ampm;
    }
}

if (!function_exists('getSetting')) {
    function getSetting($pdo, $key, $default = '')
    {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}
