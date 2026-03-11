<?php
/**
 * Common helper functions for Ontomeel Bookshop
 */

// Convert numbers to Bengali digits
function bn_num($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}

// Get book image with fallback
function getBookImage($image, $path_prefix = '')
{
    if (!empty($image)) {
        return $path_prefix . 'admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

// Get correct image path for JavaScript
function getCorrectImagePath($img, $path_prefix = '')
{
    if (!$img || strpos($img, 'http') === 0)
        return $img;
    $stripped = preg_replace('/^(\.\.\/)+/', '', $img);
    return $path_prefix . $stripped;
}

// Format date in Bengali
function formatBanglaDate($date)
{
    if (!$date)
        return "N/A";
    return date('d M, Y', strtotime($date));
}

// Get status CSS class
function getStatusClass($status)
{
    $s = strtolower($status);
    if (in_array($s, ['active', 'paid', 'delivered', 'returned']))
        return 'bg-green-100 text-green-700';
    if (in_array($s, ['processing', 'shipped']))
        return 'bg-blue-100 text-blue-700';
    if (in_array($s, ['cancelled', 'overdue', 'failed']))
        return 'bg-red-100 text-red-600';
    return 'bg-gray-100 text-gray-500';
}

// Get days remaining until due date
function getDaysRemaining($due_date)
{
    return round((strtotime($due_date) - time()) / 86400);
}

// Convert number to Bengali (for admin dashboard)
function bn_num_admin($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}
?>