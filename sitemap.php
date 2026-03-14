<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db_connect.php';

// Base URL of your website
$base_url = "https://ontomeel.com/";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Pages -->
    <url>
        <loc><?php echo $base_url; ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo $base_url; ?>category/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo $base_url; ?>library/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo $base_url; ?>pre-booking/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo $base_url; ?>membership/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Dynamic Book Details Pages -->
    <?php
    $stmt = $pdo->query("SELECT id, updated_at FROM books WHERE is_active = 1");
    while ($row = $stmt->fetch()) {
        $lastmod = !empty($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : date('Y-m-d');
        ?>
        <url>
            <loc><?php echo $base_url; ?>book-details.php?id=<?php echo $row['id']; ?></loc>
            <lastmod><?php echo $lastmod; ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    <?php } ?>

    <!-- Dynamic Pre-order Details Pages (if they use the same details page) -->
    <?php
    $stmt = $pdo->query("SELECT id FROM pre_orders WHERE status != 'Closed'");
    while ($row = $stmt->fetch()) {
        ?>
        <url>
            <loc><?php echo $base_url; ?>book-details.php?id=<?php echo $row['id']; ?></loc>
            <lastmod><?php echo date('Y-m-d'); ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    <?php } ?>
</urlset>
