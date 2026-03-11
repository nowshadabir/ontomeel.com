<?php
$page_title = 'ক্যাটাগরি | অন্ত্যমিল';
$path_prefix = '../';
include '../includes/db_connect.php';

// Fetch Categories with Book Counts
$stmt = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                    FROM categories c 
                    LEFT JOIN books b ON c.id = b.category_id 
                    GROUP BY c.id");
$categories = $stmt->fetchAll();

$additional_head = '
    <style>
        .category-card:hover .category-overlay {
            opacity: 1;
        }

        .category-card:hover img {
            transform: scale(1.1);
        }
    </style>';
include '../includes/header.php';
?>

<!-- Header -->
<header class="pt-40 pb-24 bg-brand-900 relative overflow-hidden">
    <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center text-white">
        <span class="text-brand-gold text-xs font-bold uppercase tracking-[0.3em] mb-4 block animate-slide-up">আপনার
            পছন্দের বিষয়</span>
        <h1 class="text-5xl md:text-7xl font-anek font-extrabold mb-6 animate-slide-up">বইয়ের ক্যাটাগরি</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg font-light leading-relaxed animate-slide-up"
            style="animation-delay: 100ms;">
            গল্প, উপন্যাস থেকে শুরু করে বিজ্ঞান ও প্রযুক্তি—সব ধরনের বইয়ের সমাহার। নিচে আপনার প্রিয় ক্যাটাগরি বেছে
            নিন।
        </p>
    </div>
</header>

<!-- Category Grid -->
<section class="py-24 max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php foreach($categories as $index => $cat): 
            $cat_images = [
                'ফিকশন' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=800',
                'নন-ফিকশন' => 'https://images.unsplash.com/photo-1456324504439-367cee3b3c32?q=80&w=800',
                'শিল্প ও লাইফস্টাইল' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?q=80&w=800',
            ];
            $img = $cat_images[$cat['name']] ?? 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=800';
            $delay = $index * 100;
        ?>
        <a href="../library/?category=<?php echo urlencode($cat['name']); ?>"
            class="category-card group relative h-[450px] rounded-3xl overflow-hidden shadow-2xl reveal active">
            <img src="<?php echo $img; ?>"
                alt="<?php echo $cat['name']; ?>" class="w-full h-full object-cover transition-transform duration-700">
            <div
                class="absolute inset-0 bg-gradient-to-t from-brand-900 via-brand-900/20 to-transparent opacity-90 transition-opacity duration-500">
            </div>
            <div class="absolute bottom-0 left-0 p-10 z-20">
                <span class="text-brand-gold font-bold text-xs uppercase tracking-[0.2em] mb-3 block">
                    <?php echo $cat['book_count']; ?>টি বই</span>
                <h3 class="text-4xl font-anek font-bold text-white mb-4"><?php echo $cat['name']; ?></h3>
                <p class="text-gray-300 text-sm font-light line-clamp-2 mb-6">
                    <?php echo $cat['description'] ?: 'আপনার প্রিয় বিষয়ের বইগুলো খুঁজে নিন এখানে।'; ?>
                </p>
                <div class="flex items-center gap-3 text-white font-bold group-hover:text-brand-gold transition-colors">
                    বইগুলো দেখুন <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>

</html>