<?php
$page_title = 'বইয়ের বিস্তারিত | অন্ত্যমিল';
include 'includes/db_connect.php';

// Get book ID from URL
$book_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($book_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch Book Details
$stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                      FROM books b 
                      LEFT JOIN categories c ON b.category_id = c.id 
                      WHERE b.id = ? AND b.is_active = 1");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: index.php");
    exit;
}

// Helper for image paths
function getBookImage($image)
{
    if (!empty($image)) {
        return 'admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=800';
}

function bn_num($num)
{
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}

include 'includes/header.php';
?>

<div class="pt-32 pb-20 bg-brand-light min-h-screen font-anek">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex mb-12 text-sm text-gray-400 font-medium">
            <a href="index.php" class="hover:text-brand-gold transition-colors">হোম</a>
            <span class="mx-3">/</span>
            <a href="library/index.php" class="hover:text-brand-gold transition-colors">লাইব্রেরি</a>
            <span class="mx-3">/</span>
            <span class="text-brand-900"><?php echo $book['title']; ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-start">

            <!-- Left: Book Visuals -->
            <div class="lg:col-span-5 space-y-8">
                <div class="relative group">
                    <div
                        class="rounded-3xl overflow-hidden shadow-2xl bg-white border border-gray-100 aspect-[3/4] transition-transform duration-700 hover:scale-[1.02]">
                        <img id="main-image" src="<?php echo getBookImage($book['cover_image']); ?>"
                            alt="<?php echo $book['title']; ?>" class="w-full h-full object-cover">

                        <!-- Premium Badge -->
                        <?php if ($book['is_suggested']): ?>
                            <div
                                class="absolute top-6 left-6 bg-brand-gold text-brand-900 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest shadow-lg">
                                সাজেস্টেড
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Thumbnail Gallery (if multiple photos exist) -->
                <div class="grid grid-cols-3 gap-4">
                    <button onclick="changeImage('<?php echo getBookImage($book['cover_image']); ?>')"
                        class="aspect-square rounded-xl overflow-hidden border-2 border-brand-gold shadow-sm">
                        <img src="<?php echo getBookImage($book['cover_image']); ?>" class="w-full h-full object-cover">
                    </button>
                    <?php if ($book['photo_2']): ?>
                        <button onclick="changeImage('<?php echo 'admin/assets/book-images/' . $book['photo_2']; ?>')"
                            class="aspect-square rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-gold transition-all opacity-70 hover:opacity-100">
                            <img src="admin/assets/book-images/<?php echo $book['photo_2']; ?>"
                                class="w-full h-full object-cover">
                        </button>
                    <?php endif; ?>
                    <?php if ($book['photo_3']): ?>
                        <button onclick="changeImage('<?php echo 'admin/assets/book-images/' . $book['photo_3']; ?>')"
                            class="aspect-square rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-gold transition-all opacity-70 hover:opacity-100">
                            <img src="admin/assets/book-images/<?php echo $book['photo_3']; ?>"
                                class="w-full h-full object-cover">
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Book Details & Actions -->
            <div class="lg:col-span-7">
                <div class="space-y-8">
                    <div>
                        <span
                            class="text-brand-gold font-bold uppercase tracking-[0.2em] text-xs"><?php echo $book['category_name']; ?></span>
                        <h1 class="text-4xl md:text-5xl font-serif text-brand-900 mt-2 font-bold">
                            <?php echo $book['title']; ?></h1>
                        <?php if ($book['subtitle']): ?>
                            <p class="text-xl text-gray-500 mt-2 italic"><?php echo $book['subtitle']; ?></p>
                        <?php endif; ?>

                        <div class="flex items-center gap-6 mt-6 pb-6 border-b border-gray-200">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-10 h-10 rounded-full bg-brand-light flex items-center justify-center text-brand-900 font-bold">লে</span>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">লেখক</p>
                                    <p class="text-brand-900 font-bold"><?php echo $book['author']; ?></p>
                                </div>
                            </div>
                            <?php if ($book['is_borrowable']): ?>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">লাইব্রেরিতে
                                        রয়েছে</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Price & Stock -->
                    <div class="flex items-end gap-10">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">বিক্রয় মূল্য
                            </p>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-4xl font-bold text-brand-900">৳<?php echo bn_num($book['sell_price']); ?></span>
                                <?php if ($book['discount_price'] > 0): ?>
                                    <span
                                        class="text-lg text-gray-400 line-through">৳<?php echo bn_num($book['sell_price'] + $book['discount_price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">স্টক স্ট্যাটাস
                            </p>
                            <?php if ($book['stock_qty'] > 0): ?>
                                <span
                                    class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold"><?php echo bn_num($book['stock_qty']); ?>
                                    টি অবশিষ্ট</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">আউট অফ
                                    স্টক</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="prose prose-brand max-w-none">
                        <h4 class="text-brand-900 font-bold mb-3">বইয়ের সারসংক্ষেপ</h4>
                        <p class="text-gray-600 leading-relaxed font-light">
                            <?php echo $book['description'] ?: 'এই বইটির কোনো বিবরণ এখনো যোগ করা হয়নি।'; ?>
                        </p>
                    </div>

                    <!-- Specifications Grid -->
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 gap-6 p-8 bg-white rounded-3xl border border-gray-100 shadow-sm">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">ভাষা</p>
                            <p class="text-brand-900 font-bold"><?php echo $book['language']; ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">ফরম্যাট</p>
                            <p class="text-brand-900 font-bold"><?php echo $book['format']; ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">অবস্থা</p>
                            <p class="text-brand-900 font-bold"><?php echo $book['book_condition']; ?></p>
                        </div>
                        <?php if ($book['publisher']): ?>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">প্রকাশনী</p>
                                <p class="text-brand-900 font-bold"><?php echo $book['publisher']; ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($book['publish_year']): ?>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">প্রকাশকাল</p>
                                <p class="text-brand-900 font-bold"><?php echo $book['publish_year']; ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($book['isbn']): ?>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">ISBN</p>
                                <p class="text-brand-900 font-bold font-sans"><?php echo $book['isbn']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button
                            onclick="addToCart(<?php echo htmlspecialchars(json_encode([
                                'id' => $book['id'], 
                                'title' => $book['title'],
                                'price' => (float)($book['sell_price'] ?? 0),
                                'img' => getBookImage($book['cover_image']),
                                'author' => $book['author']
                            ]), ENT_QUOTES, 'UTF-8'); ?>)"
                            class="flex-1 px-10 py-5 bg-brand-gold text-brand-900 font-bold text-lg rounded-xl shadow-xl shadow-brand-gold/20 hover:bg-brand-900 hover:text-white transition-all duration-500 transform hover:-translate-y-1">
                            কার্টে যোগ করুন
                        </button>

                        <?php if ($book['is_borrowable']): ?>
                            <button
                                onclick="borrowBook(<?php echo htmlspecialchars(json_encode([
                                    'id' => $book['id'], 
                                    'title' => $book['title'],
                                    'price' => 0,
                                    'img' => getBookImage($book['cover_image']),
                                    'author' => $book['author']
                                ]), ENT_QUOTES, 'UTF-8'); ?>)"
                                class="flex-1 px-10 py-5 bg-white border-2 border-brand-900 text-brand-900 font-bold text-lg rounded-xl hover:bg-brand-900 hover:text-white transition-all duration-500 transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                <span class="borrow-icon-main">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                </span>
                                ধার নিন
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Additional Help -->
                    <p class="text-center text-xs text-gray-400 mt-6">
                        যেকোনো সমস্যায় কল করুন: <span class="text-brand-gold font-bold">+৮৮০ ১৭১২-৩৪৫৬৭৮</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function changeImage(src) {
        const mainImg = document.getElementById('main-image');
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Handle subscription check for detail page lock icon
        const isSubscribed = localStorage.getItem('is_subscribed') === 'true';
        if (isSubscribed) {
            const icon = document.querySelector('.borrow-icon-main');
            if (icon) icon.classList.add('hidden');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>