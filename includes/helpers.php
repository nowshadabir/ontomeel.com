<?php
/**
 * Common helper functions for Ontomeel Bookshop
 */

// Convert numbers to Bengali digits
if (!function_exists('bn_num')) {
    function bn_num($num)
    {
        if ($num === null || $num === '')
            return '০';
        $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace(range(0, 9), $bn_digits, $num);
    }
}

// Get book image with fallback
if (!function_exists('getBookImage')) {
    function getBookImage($image, $path_prefix = '')
    {
        if (!empty($image)) {
            if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
                return $image;
            }
            return $path_prefix . 'admin/assets/book-images/' . $image;
        }
        return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
    }
}

// Get correct image path for JavaScript
if (!function_exists('getCorrectImagePath')) {
    function getCorrectImagePath($img, $path_prefix = '')
    {
        if (!$img || strpos($img, 'http') === 0)
            return $img;
        $stripped = preg_replace('/^(\.\.\/)+/', '', $img);
        return $path_prefix . $stripped;
    }
}

// Format date in Bengali
if (!function_exists('formatBanglaDate')) {
    function formatBanglaDate($date)
    {
        if (!$date)
            return "N/A";
        return date('d M, Y', strtotime($date));
    }
}

// Get status CSS class
if (!function_exists('getStatusClass')) {
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
}

// Get days remaining until due date
if (!function_exists('getDaysRemaining')) {
    function getDaysRemaining($due_date)
    {
        return round((strtotime($due_date) - time()) / 86400);
    }
}

// Convert number to Bengali (for admin dashboard)
if (!function_exists('bn_num_admin')) {
    function bn_num_admin($num)
    {
        if ($num === null || $num === '')
            return '০';
        $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace(range(0, 9), $bn_digits, $num);
    }
}

// Generate URL slug from a title/text
if (!function_exists('create_book_slug')) {
    function create_book_slug($string)
    {
        if (empty($string)) return '';
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', (string)$string);
        $slug = preg_replace('/-+/', '-', $slug);
        return strtolower(trim($slug, '-'));
    }
}

// Generate a guaranteed unique slug for books in database
if (!function_exists('get_unique_book_slug')) {
    function get_unique_book_slug($pdo, $title_en, $title = '', $book_id = null)
    {
        $base_title = !empty(trim((string)$title_en)) ? trim((string)$title_en) : trim((string)$title);
        $base_slug = create_book_slug($base_title);
        if (empty($base_slug)) {
            $base_slug = 'book-' . ($book_id ?: time());
        }

        $slug = $base_slug;
        $counter = 1;
        while (true) {
            if ($book_id) {
                $stmt = $pdo->prepare("SELECT id FROM books WHERE slug = ? AND id != ? LIMIT 1");
                $stmt->execute([$slug, $book_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id FROM books WHERE slug = ? LIMIT 1");
                $stmt->execute([$slug]);
            }
            if (!$stmt->fetch()) {
                break;
            }
            $counter++;
            $slug = $base_slug . '-' . $counter;
        }
        return $slug;
    }
}

// Generate clean book detail URL (e.g. domain/books/slug)
if (!function_exists('get_book_url')) {
    function get_book_url($book, $path_prefix = '')
    {
        if (is_array($book)) {
            if (!empty($book['slug'])) {
                return $path_prefix . 'books/' . urlencode($book['slug']);
            }
            if (!empty($book['id'])) {
                return $path_prefix . 'book-details.php?id=' . (int)$book['id'];
            }
        } elseif (!empty($book)) {
            return $path_prefix . 'books/' . urlencode((string)$book);
        }
        return $path_prefix . 'library/';
    }
}
?>