<?php
include '../includes/db_connect.php';

if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = $_GET['slug'];
    $stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE slug = ?");
    $stmt->execute([$slug]);
    $book = $stmt->fetch();
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM pre_orders WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
} else {
    header('Location: index.php');
    exit();
}

if (!$book) {
    header('Location: index.php');
    exit();
}

$page_title = $book['title'] . ' - প্রি-বুকিং | অন্ত্যমিল';
$path_prefix = (strpos($_SERVER['REQUEST_URI'], '/book/') !== false) ? '../../' : '../';
$nav_class = 'glass';

function bn_date($date)
{
    if (!$date)
        return '';
    $months = [
        'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
        'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
        'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
        'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর'
    ];
    $month_en = date('F', strtotime($date));
    $year_en = date('Y', strtotime($date));

    $month_bn = $months[$month_en] ?? $month_en;
    $year_bn = bn_num($year_en);

    return $month_bn . ' ' . $year_bn;
}

function bn_num($num)
{
    if ($num === null || $num === '')
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}

$additional_head = '
<style>
    :root {
        --brand-gold: #cda873;
        --brand-900: #0f172a;
    }
    body {
        background-color: #f8fafc;
        font-family: "Anek Bangla", sans-serif;
    }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;700;800&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
    <style>
    .main-container {
        padding-top: 120px;
    }
    .book-showcase {
        position: sticky;
        top: 120px;
    }
    .image-container {
        position: relative;
    }
    .prime-cover {
        width: 100%;
        max-width: 400px;
        aspect-ratio: 2/3;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 20px 40px 60px rgba(0,0,0,0.15);
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        z-index: 20;
    }
    .second-cover {
        position: absolute;
        bottom: -120px;
        right: -60px;
        width: 150px;
        aspect-ratio: 2/3;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 10px 20px 30px rgba(0,0,0,0.2);
        z-index: 10;
        border: 4px solid white;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .coming-soon-tag {
        position: absolute;
        top: 20px;
        left: -15px;
        background: #ef4444;
        color: white;
        padding: 5px 15px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 4px;
        box-shadow: 5px 5px 15px rgba(239, 68, 68, 0.3);
        z-index: 30;
        transform: rotate(-2deg);
    }
    .award-seal {
        position: absolute;
        top: -20px;
        right: -25px;
        width: 110px;
        height: 110px;
        background: radial-gradient(circle at center, #059669, #065f46);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 10px;
        font-size: 10px;
        font-weight: 500;
        line-height: 1.2;
        box-shadow: 0 15px 30px rgba(5, 150, 105, 0.3), inset 0 0 0 4px rgba(255, 255, 255, 0.1);
        z-index: 40;
        transform: rotate(15deg);
        border: 2px dashed rgba(255, 255, 255, 0.2);
        font-family: "Tiro Bangla", serif;
    }
    .book-wrapper {
        position: relative;
        display: inline-block;
    }
    .image-container:hover .prime-cover {
        transform: scale(1.02);
    }
    .image-container:hover .second-cover {
        transform: translateX(30px) translateY(20px);
    }

    .badge-label {
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }
    .badge-primary { background: #fee2e2; color: #991b1b; }
    .badge-gold { background: #fef3c7; color: #92400e; }
    
    .price-card {
        background: white;
        border-radius: 32px;
        padding: 32px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.02);
    }

    .author-pill {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        background: #f1f5f9;
        border-radius: 16px;
        transition: background 0.3s;
    }
    .author-pill:hover { background: #e2e8f0; }

    @media (min-width: 1025px) {
        .status-grid {
            margin-top: 180px !important;
        }
        .award-seal {
            right: -5px !important;
        }
    }

    @media (max-width: 1024px) {
        .book-showcase { 
            position: static; 
            margin-bottom: 40px; 
            padding: 0 15px;
        }
        .image-container {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 15px;
            perspective: 2000px;
            padding: 40px 0;
        }
        .prime-cover { 
            max-width: 200px; 
            margin: 0; 
            transform: rotateY(-10deg);
            z-index: 20;
        }
        .second-cover { 
            width: 110px; 
            position: relative !important;
            right: auto !important;
            bottom: auto !important;
            margin: 0;
            z-index: 10;
            transform: rotateY(-15deg);
            box-shadow: -10px 10px 20px rgba(0,0,0,0.2);
        }
        .image-container:hover .prime-cover {
            transform: rotateY(-5deg) scale(1.05);
        }
        .image-container:hover .second-cover {
            transform: rotateY(-10deg) scale(1.05);
        }
        .coming-soon-tag {
            left: -10px;
            top: 15px;
            font-size: 8px;
            padding: 4px 10px;
        }
        .award-seal { 
            width: 75px; 
            height: 75px; 
            font-size: 7px; 
            right: -10px; 
            top: -15px; 
        }
        .main-container { padding-top: 100px; }
    }
    .combo-suffix {
        display: block;
        margin-top: 10px;
        font-size: 14px;
        color: var(--brand-gold);
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 700;
    }
</style>
';

include '../includes/header.php';
?>

<main class="main-container pb-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20">
            
            <!-- Left: Visual Sticky -->
            <div class="lg:col-span-5">
                <div class="book-showcase">
                    <div class="image-container flex justify-center lg:justify-start">
                        <div class="book-wrapper">
                            <div class="coming-soon-tag">Coming Soon</div>
                            <div class="award-seal">ইন্টারন্যাশনাল বুকার প্রাইজ ২০২৫ প্রাপ্ত</div>
                            <img src="<?php echo strpos($book['cover_image'], 'http') === 0 ? $book['cover_image'] : $path_prefix . 'assets/img/preorders/' . $book['cover_image']; ?>" 
                                 class="prime-cover" alt="Main Book">
                        </div>
                        
                        <?php if (!empty($book['second_cover_image'])): ?>
                            <img src="<?php echo strpos($book['second_cover_image'], 'http') === 0 ? $book['second_cover_image'] : $path_prefix . 'assets/img/preorders/' . $book['second_cover_image']; ?>" 
                                 class="second-cover" alt="Combo Book">
                        <?php
endif; ?>
                    </div>

                    <div class="mt-12 grid grid-cols-2 gap-4 status-grid">
                        <div class="p-6 bg-white rounded-3xl text-center border border-slate-100">
                            <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">প্রকাশের মাস</span>
                            <span class="text-sm font-bold text-slate-700"><?php echo bn_date($book['release_date']); ?></span>
                        </div>
                        <div class="p-6 bg-white rounded-3xl text-center border border-slate-100">
                            <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">বুকিং স্ট্যাটাস</span>
                            <span class="text-sm font-bold text-emerald-600"><?php echo $book['status'] == 'Open' ? 'চলছে' : 'আসন্ন'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Content -->
            <div class="lg:col-span-7 space-y-10">
                <!-- Badges & Status -->
                <div class="flex flex-wrap gap-3">
                    <span class="badge-label badge-primary">নতুন প্রকাশনী অফার</span>
                    <?php if (!empty($book['second_cover_image'])): ?>
                        <span class="badge-label badge-gold">কম্বো বই সেট</span>
                    <?php
endif; ?>
                </div>

                <!-- Titles and Authors -->
                <div class="space-y-8">
                    <?php
$titles = preg_split('/\s*(?:,|এবং|ও)\s*/u', $book['title']);
?>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-red-500 mb-2">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[11px] font-black uppercase tracking-widest">খুব শীঘ্রই আসছে</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-tight">
                            <?php echo trim($titles[0]); ?>
                        </h1>
                    </div>

                    <?php if (isset($titles[1])): ?>
                        <div class="relative py-4">
                            <div class="absolute inset-y-0 left-0 w-1 bg-slate-200 rounded-full"></div>
                            <div class="pl-8 space-y-3">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">সাথে থাকছে</span>
                                <h3 class="text-2xl md:text-3xl font-bold text-slate-500">
                                    <?php echo str_replace('(কম্বো)', '', trim($titles[1])); ?>
                                </h3>
                                <p class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full inline-block">ইতিমধ্যে প্রকাশিত</p>
                            </div>
                        </div>
                    <?php
endif; ?>
                </div>

                <!-- Price Section -->
                <div class="price-card flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">অফার মূল্য</span>
                        <div class="flex items-baseline gap-4">
                            <span class="text-5xl font-black text-slate-900">৳<?php echo bn_num((int)$book['discount_price']); ?></span>
                            <span class="text-xl text-slate-300 line-through">৳<?php echo bn_num((int)$book['price']); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <?php if ($book['status'] == 'Open'): ?>
                            <a href="<?php echo $path_prefix; ?>pre-order-checkout/index.php?id=<?php echo $book['id']; ?>" 
                               class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-gold transition-all shadow-2xl flex items-center justify-center gap-4">
                                <span>বুকিং করুন</span>
                                <svg class="w-6 h-6 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        <?php
else: ?>
                            <button disabled class="bg-slate-200 text-slate-400 px-10 py-5 rounded-2xl font-black uppercase tracking-widest cursor-not-allowed">বুকিং আসছে</button>
                        <?php
endif; ?>
                        <p class="text-center text-[10px] text-slate-400 font-bold tracking-widest italic">সীমিত সময়ের অফার!</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-6 pt-10">
                    <h4 class="text-xl font-black text-slate-900 border-l-4 border-brand-gold pl-4">বিস্তারিত সারসংক্ষেপ</h4>
                    <div class="text-slate-600 leading-relaxed font-anek text-lg whitespace-pre-line">
                        <?php echo $book['description']; ?>
                    </div>
                </div>

                <div class="pt-20 text-center lg:text-left">
                    <a href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/book/') !== false) ? '../' : './'; ?>" class="text-slate-400 hover:text-brand-gold flex items-center justify-center lg:justify-start gap-2 font-bold uppercase text-[10px] tracking-widest transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        ফিরে যান
                    </a>
                </div>
            </div>
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
