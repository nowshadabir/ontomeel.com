<?php
/**
 * Ontomeel Bookshop - Sample CSV Template Generator
 * Generates a clean UTF-8 BOM CSV template with sample data covering ALL book fields.
 */
require_once __DIR__ . '/../../includes/db_connect.php';

// Auth check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die("Unauthorized access.");
}

$filename = 'ontomeel_books_bulk_template_' . date('Y-m-d') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Output UTF-8 BOM so Excel and Google Sheets render Bengali characters flawlessly
fputs($output, "\xEF\xBB\xBF");

// Define ALL Columns
$headers = [
    'title',              // [REQUIRED] বইয়ের বাংলা নাম
    'title_en',           // বইয়ের ইংরেজি নাম
    'subtitle',           // উপশিরোনাম
    'author',             // [REQUIRED] লেখকের বাংলা নাম
    'author_en',          // লেখকের ইংরেজি নাম
    'co_author',          // সহ-লেখক / সম্পাদক / অনুবাদক
    'category',           // ক্যাটাগরির নাম (যেমন: Books, উপন্যাস) অথবা আইডি (যেমন: 33)
    'genre',              // বিষয় / জেনার (যেমন: উপন্যাস, মুক্তিযুদ্ধ, থ্রিলার)
    'publisher',          // প্রকাশনী
    'publish_year',       // প্রকাশকাল (যেমন: 2024)
    'edition',            // সংস্করণ (যেমন: ১ম প্রকাশ)
    'language',           // ভাষা (ডিফল্ট: Bengali)
    'format',             // ফরম্যাট (Paperback / Hardcover / E-book)
    'book_condition',     // অবস্থা (New / Used / Damaged)
    'isbn',               // ISBN নম্বর (যেমন: 9789849128456)
    'page_count',         // পৃষ্ঠা সংখ্যা (যেমন: 184)
    'sell_price',         // [REQUIRED] বিক্রয় মূল্য (যেমন: 450)
    'original_price',     // মুদ্রিত গায়ের মূল্য (যেমন: 480)
    'purchase_price',     // কেনা দাম / ক্রয়মূল্য (যেমন: 350)
    'stock_qty',          // মজুদ কপি সংখ্যা (যেমন: 15)
    'min_stock_level',    // সতর্কতা স্টক সীমা (যেমন: 2)
    'is_borrowable',      // লাইব্রেরিতে ধার দেওয়ার সুযোগ (1 / yes / 0 / no)
    'shelf_location',     // তাক / শেলফ নম্বর (যেমন: A-12)
    'rack_number',        // র‍্যাক নম্বর (যেমন: R-03)
    'supplier_name',      // সরবরাহকারীর নাম
    'supplier_contact',   // সরবরাহকারীর ফোন নম্বর
    'description',        // বইয়ের সারসংক্ষেপ / বিবরণ
    'cover_image_url',    // মূল প্রচ্ছদের ইমেজ URL বা ফাইলের নাম
    'photo_2_url',        // দ্বিতীয় ছবির URL বা ফাইলের নাম
    'photo_3_url'         // তৃতীয় ছবির URL বা ফাইলের নাম
];

fputcsv($output, $headers, ',', '"', '\\');

// Sample Row 1: চাঁদের পাহাড় (Classic Bengali Novel)
$sample_row_1 = [
    'চাঁদের পাহাড়',
    'Chander Pahar',
    'রোমাঞ্চকর আফ্রিকার অরণ্য অভিযান',
    'বিভূতিভূষণ বন্দ্যোপাধ্যায়',
    'Bibhutibhushan Bandyopadhyay',
    '',
    'Books',
    'উপন্যাস ও অ্যাডভেঞ্চার',
    'মিত্র ও ঘোষ পাবলিশার্স',
    '2024',
    'বিশেষ সংস্করণ',
    'Bengali',
    'Hardcover',
    'New',
    '9789849128456',
    '184',
    '450',
    '480',
    '340',
    '15',
    '2',
    '1',
    'Shelf-A3',
    'Rack-02',
    'মিত্র ও ঘোষ ডিস্ট্রিবিউশন',
    '01711000000',
    'শঙ্কর নামক এক দুঃসাহসী বাঙালি তরুণের আফ্রিকার দুর্গম রিচার্সভেল্ট পর্বতমালা ও অরণ্যে হিরের খনির সন্ধানে রোমাঞ্চকর অভিযানের অবিস্মরণীয় কাহিনী। বিভূতিভূষণ বন্দ্যোপাধ্যায়ের রচিত বাংলা সাহিত্যের এক অনন্য কালজয়ী ক্লাসিক উপন্যাস।',
    'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=600',
    '',
    ''
];

// Sample Row 2: গীতাঞ্জলি (Poetry)
$sample_row_2 = [
    'গীতাঞ্জলি',
    'Gitanjali',
    'নোবেল বিজয়ী কাব্যগ্রন্থ',
    'রবীন্দ্রনাথ ঠাকুর',
    'Rabindranath Tagore',
    '',
    'Books',
    'কবিতা ও সাহিত্য',
    'বিশ্বভারতী গ্রন্থন বিভাগ',
    '2023',
    '১ম প্রকাশ',
    'Bengali',
    'Hardcover',
    'New',
    '9788172152437',
    '160',
    '380',
    '400',
    '280',
    '20',
    '3',
    '1',
    'Shelf-B1',
    'Rack-01',
    'বিশ্বভারতী প্রেস',
    '01811000000',
    'রবীন্দ্রনাথ ঠাকুরের নোবেল পুরস্কার বিজয়ী বিশ্বখ্যাত কাব্য সংকলন গীতাঞ্জলি। ঈশ্বর ও মানবপ্রেমের গভীর আধ্যাত্মিক ভাবধারায় রচিত ১৫৭টি অপূর্ব কবিতার অনন্য সম্ভার।',
    'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=600',
    '',
    ''
];

fputcsv($output, $sample_row_1, ',', '"', '\\');
fputcsv($output, $sample_row_2, ',', '"', '\\');

fclose($output);
exit();
