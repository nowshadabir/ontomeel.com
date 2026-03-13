<?php
include '../includes/db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: index.php');
    exit();
}

$page_title = $book['title'] . ' - প্রি-বুকিং | অন্ত্যমিল';
$path_prefix = '../';
$nav_class = 'glass';

function bn_date($date)
{
    if (!$date) return '';
    $months = [
        'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
        'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
        'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
        'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর'
    ];
    $date_str = date('d F, Y', strtotime($date));
    foreach ($months as $en => $bn) {
        $date_str = str_replace($en, $bn, $date_str);
    }
    return $date_str;
}

function bn_num($num)
{
    if ($num === null || $num === '') return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}

$additional_head = '
<style>
    :root {
        --brand-gold: #cda873;
        --brand-gold-light: #e6c89b;
    }
    body {
        background-color: #fafafe;
        color: #1a1a1a;
    }
    .primary-info-card {
        background: #ffffff;
        border-radius: 40px;
        padding: 40px;
        box-shadow: 0 40px 80px -20px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .secondary-info-card {
        background: #ffffff;
        border-radius: 40px;
        padding: 60px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.01);
    }
    @media (max-width: 768px) {
        .primary-info-card {
            border-radius: 24px;
            padding: 24px;
        }
        .secondary-info-card {
            border-radius: 24px;
            padding: 30px;
        }
        .book-visual {
            transform: none !important;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        }
        .text-5xl {
            font-size: 2.5rem;
            line-height: 1.2;
        }
        .md\:text-7xl {
            font-size: 2.5rem;
        }
        .text-6xl {
            font-size: 3rem;
        }
    }
    .book-visual {
        transform: perspective(1000px) rotateY(-15deg) rotateX(5deg);
        filter: drop-shadow(20px 30px 50px rgba(0,0,0,0.1));
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: rgba(205, 168, 115, 0.1);
        color: var(--brand-gold);
        border: 1px solid rgba(205, 168, 115, 0.2);
    }
    .btn-preorder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: var(--brand-gold);
        color: #ffffff;
        padding: 18px 40px;
        border-radius: 16px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 15px 35px -5px rgba(205, 168, 115, 0.4);
        width: 100%;
    }
    @media (min-width: 640px) {
        .btn-preorder {
            width: auto;
        }
    }
    .btn-preorder:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 45px -5px rgba(205, 168, 115, 0.6);
        background: #1a1a1a;
    }
    .desc-wrapper {
        line-height: 2;
        font-size: 1.15rem;
        color: #374151;
        white-space: pre-line;
        position: relative;
    }
    .desc-content.clamped {
        display: -webkit-box;
        -webkit-line-clamp: 6;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .read-more-btn {
        margin-top: 20px;
        color: var(--brand-gold);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: 1px solid rgba(205, 168, 115, 0.2);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .read-more-btn:hover {
        background: rgba(205, 168, 115, 0.05);
        border-color: var(--brand-gold);
    }
    .spec-item {
        padding: 24px;
        background: #f9f9fb;
        border-radius: 20px;
        border: 1px solid rgba(0,0,0,0.01);
    }
</style>
';

include '../includes/header.php';
?>

<main class="pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-12">
        
        <!-- Primary Details Section -->
        <div class="primary-info-card grid grid-cols-1 lg:grid-cols-12 gap-16 items-center mt-12">
            
            <!-- Book Visual Sidebar -->
            <div class="lg:col-span-5 space-y-8">
                <div class="book-visual rounded-2xl overflow-hidden aspect-[4/6]">
                    <img src="<?php echo strpos($book['cover_image'], 'http') === 0 ? $book['cover_image'] : $path_prefix . 'assets/img/preorders/' . $book['cover_image']; ?>" 
                         class="w-full h-full object-cover" 
                         alt="<?php echo $book['title']; ?>">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="spec-item text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">প্রকাশের তারিখ</p>
                        <p class="text-sm font-bold text-gray-900"><?php echo bn_date($book['release_date']); ?></p>
                    </div>
                    <div class="spec-item text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">বুকিং স্ট্যাটাস</p>
                        <p class="text-sm font-bold text-gray-900"><?php echo $book['status'] == 'Open' ? 'ওপেন' : ($book['status'] == 'Upcoming' ? 'আসন্ন' : 'বন্ধ'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Book Info Main Content (Right Side Primary) -->
            <div class="lg:col-span-7 space-y-10">
                <div class="space-y-4">
                    <div class="badge-status">
                        <span class="w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse"></span>
                        ১ম মুদ্রণ প্রি-বুকিং
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl font-sans font-black text-gray-900 leading-tight">
                        <?php echo $book['title']; ?>
                    </h1>
                    <h2 class="text-2xl font-serif italic text-brand-gold opacity-90">
                        <?php echo $book['sub_title']; ?>
                    </h2>
                    <p class="text-2xl font-anek text-gray-400">লেখক: <span class="text-gray-900 font-bold"><?php echo $book['author']; ?></span></p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-8 sm:gap-10 py-8 sm:py-10 border-y border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2 sm:mb-3">विशेष প্রি-অর্ডার মূল্য</p>
                        <div class="flex items-baseline gap-4">
                            <span class="text-4xl sm:text-5xl md:text-6xl font-sans font-black text-gray-900">৳<?php echo bn_num((int) $book['discount_price']); ?></span>
                            <span class="text-lg sm:text-xl text-gray-400 line-through">৳<?php echo bn_num((int) $book['price']); ?></span>
                        </div>
                    </div>
                    <div class="h-16 sm:h-20 w-full sm:w-[1px] bg-gray-100 hidden sm:block"></div>
                    <div>
                        <p class="text-[10px] text-brand-gold font-bold uppercase tracking-widest mb-2 sm:mb-3">আপনি সাশ্রয় করছেন</p>
                        <p class="text-2xl sm:text-3xl md:text-4xl font-bold font-anek text-brand-gold">৳<?php echo bn_num((int)($book['price'] - $book['discount_price'])); ?></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 pt-6">
                    <?php if ($book['status'] == 'Open'): ?>
                        <a href="../pre-order-checkout/index.php?id=<?php echo $book['id']; ?>" class="btn-preorder">
                            <span>বুকিং নিশ্চিত করুন</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    <?php else: ?>
                        <button class="btn-preorder opacity-50 cursor-not-allowed grayscale" disabled>
                            <span>বুকিং শীঘ্রই আসবে</span>
                        </button>
                    <?php endif; ?>
                    
                    <!-- Quick Stats Badges -->
                    <div class="flex items-center gap-6 px-6 py-4 bg-gray-50 rounded-2xl border border-gray-100">
                         <div class="flex items-center gap-2">
                             <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                             <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">হোম ডেলিভারি</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                             <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">অটোগ্রাফ কার্ড</span>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Detailed Description Section (Full Width) -->
        <div class="secondary-info-card space-y-12">
            <div class="flex items-center gap-6 mb-12">
                <h3 class="text-3xl font-sans font-black text-gray-900">বইটির বিস্তারিত বর্ণনা</h3>
                <div class="h-[2px] flex-1 bg-gray-100"></div>
            </div>
            
            <div class="desc-wrapper font-anek max-w-4xl mx-auto">
                <div id="desc-content" class="desc-content clamped">
                    <?php echo $book['description']; ?>
                </div>
                <div class="flex justify-center md:justify-start">
                    <button id="read-more-toggle" class="read-more-btn hidden">
                        <span>বিস্তারিত পড়ুন</span>
                        <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
            </div>
            
            <!-- Trust Benefits -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pt-16 border-t border-gray-50">
                <div class="space-y-3">
                    <div class="w-12 h-12 bg-gold-50 text-brand-gold rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h5 class="font-bold text-sm">১০০% অরিজিনাল কপি</h5>
                    <p class="text-xs text-gray-400">সরাসরি প্রকাশনী থেকে সংগৃহীত।</p>
                </div>
                <!-- Add more benefits if needed -->
            </div>
        </div>

        <!-- Back Link -->
        <div class="pt-10 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-brand-gold transition-colors font-bold uppercase tracking-widest text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                ফিরে যান প্রি-বুকিং তালিকায়
            </a>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const descContent = document.getElementById('desc-content');
        const toggleBtn = document.getElementById('read-more-toggle');
        
        // Show button only if content is taller than clamped height
        if (descContent.scrollHeight > descContent.clientHeight) {
            toggleBtn.classList.remove('hidden');
        }
        
        toggleBtn.addEventListener('click', () => {
            const isClamped = descContent.classList.contains('clamped');
            const btnText = toggleBtn.querySelector('span');
            const btnIcon = toggleBtn.querySelector('svg');
            
            if (isClamped) {
                descContent.classList.remove('clamped');
                btnText.innerText = 'সংক্ষেপ করুন';
                btnIcon.classList.add('rotate-180');
            } else {
                descContent.classList.add('clamped');
                btnText.innerText = 'বিস্তারিত পড়ুন';
                btnIcon.classList.remove('rotate-180');
                // Scroll back to title for better UX
                const title = document.querySelector('.secondary-info-card h3');
                title.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
