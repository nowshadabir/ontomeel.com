<?php
$page_title = 'লাইব্রেরি | সব ধরনের বইয়ের বিশাল সংগ্রহ - অন্ত্যমিল';
$page_description = 'অন্ত্যমিল লাইব্রেরি - গল্প, উপন্যাস, কবিতা এবং আরও অনেক বিষয়ের বইয়ের এক অফুরন্ত ভান্ডার। অনলাইনে বই খুঁজুন এবং আপনার পছন্দের বইটি অর্ডার করুন।';
$page_keywords = 'লাইব্রেরি, বইয়ের তালিকা, গল্পের বই, উপন্যাস, অন্ত্যমিল, Vivago Digital, Online Library, Book Collection';
$path_prefix = '../';
include '../includes/db_connect.php';
include '../includes/header.php';

// Get category from URL
$category = isset($_GET['category']) ? $_GET['category'] : '';

$where = "WHERE b.is_active = 1";
$params = [];
if (!empty($category)) {
    $where .= " AND c.name = ?";
    $params[] = $category;
}

// Initial load: 50 books per page
$limit = 50;
$stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                    FROM books b 
                    LEFT JOIN categories c ON b.category_id = c.id 
                    $where
                    ORDER BY (b.stock_qty > 0) DESC, b.created_at DESC
                    LIMIT $limit");
$stmt->execute($params);
$initial_books = $stmt->fetchAll();

// Get total count
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM books b LEFT JOIN categories c ON b.category_id = c.id $where");
$total_stmt->execute($params);
$total_books_count = (int)$total_stmt->fetchColumn();

function getBookImage($image)
{
    if (!empty($image)) {
        return '../admin/assets/book-images/' . $image;
    }
    return 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400';
}

function bn_num($num)
{
    if ($num === null)
        return '০';
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace(range(0, 9), $bn_digits, $num);
}
?>

<!-- Library Header -->
<div class="pt-32 pb-16 bg-brand-900 relative overflow-hidden">
    <div class="mesh-gradient absolute inset-0 opacity-40"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-anek font-extrabold text-white mb-6 animate-slide-up">আমাদের লাইব্রেরি</h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg mb-10 animate-slide-up" style="animation-delay: 0.2s;">আপনার পছন্দের বই খুঁজে নিন আমাদের বিশাল সংগ্রহ
            থেকে। কেনা বা ধার নেওয়ার জন্য হাজারো বইয়ের তালিকা।</p>

        <!-- Dedicated Search Bar -->
        <div class="max-w-3xl mx-auto relative group animate-slide-up" style="animation-delay: 0.4s;">
            <input type="text" id="librarySearchInput" onkeyup="debounceSearch(event)"
                placeholder="বইয়ের নাম অথবা লেখকের নাম দিয়ে খুঁজুন..."
                class="w-full bg-white/80 border border-gray-200 rounded-2xl px-8 py-5 text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-lg">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-brand-gold">
                <svg id="search-spinner" class="w-6 h-6 hidden animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg id="search-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Books Collection Section -->
<section class="py-20 px-6 lg:px-8 max-w-7xl mx-auto min-h-[600px]">
    <div class="flex justify-between items-end mb-12 border-b border-gray-200 pb-8 reveal active">
        <div>
            <span id="section-subtitle"
                class="text-brand-gold font-medium tracking-wider text-sm uppercase">সংগ্রহশালা</span>
            <h2 id="section-title" class="text-3xl md:text-4xl font-anek font-bold text-brand-900 mt-2">সব বইয়ের
                তালিকা</h2>
        </div>
        <div class="text-gray-500 text-sm font-medium">
            মোট <span id="book-count-total" class="text-brand-900 font-bold"><?php echo $total_books_count; ?></span>টি বই পাওয়া গেছে
        </div>
    </div>

    <!-- Empty State -->
    <div id="no-results" class="<?php echo ($total_books_count > 0) ? 'hidden' : ''; ?> text-center py-32 bg-white rounded-3xl shadow-sm border border-gray-100">
        <svg class="w-20 h-20 text-gray-200 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-2xl font-anek font-bold text-brand-900">দুঃখিত, কোনো বই খুঁজে পাওয়া যায়নি!</h3>
        <p class="text-gray-500 mt-3 font-light text-lg">অনুগ্রহ করে সঠিক বানান চেক করুন অথবা অন্য নাম দিয়ে চেষ্টা
            করুন।</p>
        <button onclick="clearLibrarySearch()"
            class="mt-8 text-brand-gold font-bold hover:underline flex items-center gap-2 mx-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            সব বই পুনরায় দেখান
        </button>
    </div>

    <!-- Library Books Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12" id="library-book-grid">
        <!-- Initial Books rendered by PHP for SEO and Speed -->
        <?php foreach ($initial_books as $index => $book): 
            $img = getBookImage($book['cover_image']);
            $isOutOfStock = $book['stock_qty'] <= 0;
            $canBorrow = ($book['is_borrowable'] == 1 && !$isOutOfStock);
            $delay = ($index % 4) * 80;
        ?>
            <div class="book-card group reveal active <?php echo $isOutOfStock ? 'opacity-80' : ''; ?>" style="transition-delay: <?php echo $delay; ?>ms;">
                <div class="relative book-cover-container aspect-[2/3] rounded-md overflow-hidden bg-gray-100 mb-4 shadow-sm border border-gray-100">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="object-cover w-full h-full transition-all duration-700 <?php echo $isOutOfStock ? 'grayscale' : ''; ?>" loading="lazy">
                    
                    <?php if ($isOutOfStock): ?>
                    <div class="absolute top-4 left-4 bg-red-600/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest z-20 backdrop-blur-sm shadow-lg">
                        স্টক আউট
                    </div>
                    <?php elseif ($book['stock_qty'] > 0 && $book['stock_qty'] <= 5): ?>
                    <div class="absolute top-4 left-4 bg-amber-500/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest z-20 backdrop-blur-sm shadow-lg">
                        অল্প কিছু বাকি
                    </div>
                    <?php endif; ?>

                    <div class="absolute inset-x-0 bottom-0 md:inset-0 bg-brand-900/95 md:bg-brand-900/70 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-row md:flex-col justify-center items-center gap-2 md:gap-3 backdrop-blur-md md:backdrop-blur-sm p-2 md:p-0 z-30">
                        <?php if ($isOutOfStock): ?>
                            <button disabled class="flex-1 md:flex-none md:w-3/4 bg-gray-700 text-gray-400 py-2 rounded-sm font-bold text-[10px] md:text-sm cursor-not-allowed border border-white/10">স্টকে নেই</button>
                        <?php else: ?>
                            <?php $cartData = htmlspecialchars(json_encode(['id' => $book['id'], 'title' => (string)($book['title']??''), 'price' => (float)($book['sell_price']??0), 'img' => $img, 'author' => (string)($book['author']??'')]), ENT_QUOTES, 'UTF-8'); ?>
                            <button onclick='addToCart(<?php echo $cartData; ?>)' 
                                     class="flex-1 md:flex-none md:w-3/4 bg-brand-gold text-brand-900 py-2 rounded-sm font-bold transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 hover:bg-white text-[10px] md:text-sm shadow-xl font-sans">
                                <span class="hidden md:block">কিনুন ৳<?php echo $book['sell_price']; ?></span>
                                <span class="md:hidden">কিনুন</span>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($canBorrow): ?>
                            <?php $borrowData = htmlspecialchars(json_encode(['id' => $book['id'], 'title' => (string)($book['title']??''), 'price' => 0, 'img' => $img, 'author' => (string)($book['author']??'')]), ENT_QUOTES, 'UTF-8'); ?>
                            <button onclick='borrowBook(<?php echo $borrowData; ?>)' 
                                    class="flex-1 md:flex-none md:w-3/4 bg-transparent border border-white/30 text-white py-2 rounded-sm font-medium transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 delay-75 hover:bg-white hover:text-brand-900 text-[10px] md:text-sm font-sans flex items-center justify-center gap-1 backdrop-blur-sm">
                                <span class="hidden md:block">ধার নিন</span>
                                <span class="md:hidden">ধার</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-center px-1">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="text-[10px] md:text-xs text-brand-gold font-bold uppercase tracking-wider"><?php echo $book['category_name']; ?></span>
                        <?php if ($book['is_borrowable'] == 1): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500" title="লাইব্রেরিতে রয়েছে"></span>
                        <?php endif; ?>
                    </div>
                    <a href="../book-details.php?id=<?php echo $book['id']; ?>" class="block hover:text-brand-gold">
                        <h3 class="font-serif text-base md:text-lg text-brand-900 mt-1 truncate font-bold transition-colors"><?php echo $book['title']; ?></h3>
                    </a>
                    <p class="text-gray-500 text-xs md:text-sm font-light mt-1"><?php echo $book['author']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination-controls" class="flex flex-wrap justify-center items-center gap-2 mt-12 mb-20 pb-10">
        <?php
        $total_pages = ceil($total_books_count / $limit);
        if ($total_pages > 1) {
            echo '<button disabled class="px-3 md:px-4 py-2 rounded-sm border border-brand-gold bg-brand-gold text-brand-900 font-bold font-sans">১</button>';
            if ($total_pages >= 2) echo '<button onclick="fetchPage(2)" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans">' . bn_num(2) . '</button>';
            if ($total_pages >= 3) echo '<button onclick="fetchPage(3)" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans">' . bn_num(3) . '</button>';
            if ($total_pages > 3) {
                echo '<span class="px-2 text-gray-400">...</span>';
                echo '<button onclick="fetchPage('.$total_pages.')" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans">' . bn_num($total_pages) . '</button>';
            }
            echo '<button onclick="fetchPage(2)" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-sm md:text-base">পরবর্তী &raquo;</button>';
        }
        ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
    let currentPage = 1;
    let isLoading = false;
    const itemsPerPage = 50;
    let searchQuery = '';
    const categoryQuery = '<?php echo addslashes($category); ?>';
    
    const grid = document.getElementById('library-book-grid');
    const totalCountEl = document.getElementById('book-count-total');
    const noResultsEl = document.getElementById('no-results');
    const searchSpinner = document.getElementById('search-spinner');
    const searchIcon = document.getElementById('search-icon');
    const paginationContainer = document.getElementById('pagination-controls');

    // Number converter helper for Bengali
    const bnNum = (num) => {
        const digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return num.toString().split('').map(d => digits[d] || d).join('');
    };

    function renderPagination(totalItems, currentPg) {
        if (!paginationContainer) return;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        let html = '';
        
        // Prev button
        if (currentPg > 1) {
            html += `<button onclick="fetchPage(${currentPg - 1})" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-sm md:text-base">&laquo; পূর্ববর্তী</button>`;
        }

        // Page numbers
        let startPage = Math.max(1, currentPg - 2);
        let endPage = Math.min(totalPages, currentPg + 2);

        if (startPage > 1) {
            html += `<button onclick="fetchPage(1)" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans text-sm md:text-base">${bnNum(1)}</button>`;
            if (startPage > 2) html += `<span class="px-2 text-gray-400 font-sans">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPg) {
                html += `<button disabled class="px-3 md:px-4 py-2 rounded-sm border border-brand-gold bg-brand-gold text-brand-900 font-bold font-sans text-sm md:text-base">${bnNum(i)}</button>`;
            } else {
                html += `<button onclick="fetchPage(${i})" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans text-sm md:text-base">${bnNum(i)}</button>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="px-2 text-gray-400 font-sans">...</span>`;
            html += `<button onclick="fetchPage(${totalPages})" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-sans text-sm md:text-base">${bnNum(totalPages)}</button>`;
        }

        // Next button
        if (currentPg < totalPages) {
            html += `<button onclick="fetchPage(${currentPg + 1})" class="px-3 md:px-4 py-2 rounded-sm border border-gray-200 text-gray-700 hover:bg-brand-gold hover:text-brand-900 hover:border-brand-gold transition-colors font-anek font-bold text-sm md:text-base">পরবর্তী &raquo;</button>`;
        }

        paginationContainer.innerHTML = html;
    }

    async function fetchPage(pageNum) {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = pageNum;
        
        // Beautiful fade out animation
        grid.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        grid.style.opacity = '0';
        grid.style.transform = 'translateY(15px)';
        grid.style.pointerEvents = 'none';
        
        // Scroll back to section title simultaneously for smooth feel
        const sectionTitle = document.getElementById('section-subtitle');
        if (sectionTitle) {
            const yOffset = -120;
            const y = sectionTitle.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({top: y, behavior: 'smooth'});
        }
        
        try {
            // Concurrent operations: fade-out timer + fetch AJAX request
            const [response, _] = await Promise.all([
                fetch(`fetch_books.php?page=${currentPage}&search=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(categoryQuery)}`),
                new Promise(r => setTimeout(r, 300)) // Guarantee at least 300ms for visuals
            ]);
            
            const data = await response.json();
            
            totalCountEl.innerText = bnNum(data.total);
            grid.innerHTML = '';
            grid.style.transform = 'translateY(0)'; // Reset transform position
            
            if (data.books.length === 0) {
                noResultsEl.classList.remove('hidden');
                paginationContainer.innerHTML = '';
            } else {
                noResultsEl.classList.add('hidden');
                
                data.books.forEach((book, index) => {
                    const delay = (index % 4) * 50; // Faster animation for page loads
                    const isOutOfStock = parseInt(book.stock_qty) <= 0;
                    const isLowStock = !isOutOfStock && parseInt(book.stock_qty) <= 5;
                    const canBorrow = parseInt(book.is_borrowable) === 1 && !isOutOfStock;
                    
                    const safeCategory = String(book.category || 'Uncategorized');
                    const displayTitle = String(book.title || '').replace(/"/g, '&quot;');

                    const actionData = JSON.stringify({
                        id: book.id,
                        title: String(book.title || ''),
                        price: Number(book.price || 0),
                        img: String(book.img || ''),
                        author: String(book.author || '')
                    }).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                    
                    const borrowData = JSON.stringify({
                        id: book.id,
                        title: String(book.title || ''),
                        price: 0,
                        img: String(book.img || ''),
                        author: String(book.author || '')
                    }).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                    
                    let stockBadgeHTML = '';
                    if (isOutOfStock) {
                        stockBadgeHTML = '<div class="absolute top-4 left-4 bg-red-600/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest z-20 backdrop-blur-sm shadow-lg">স্টক আউট</div>';
                    } else if (isLowStock) {
                        stockBadgeHTML = '<div class="absolute top-4 left-4 bg-amber-500/90 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest z-20 backdrop-blur-sm shadow-lg">অল্প কিছু বাকি</div>';
                    }

                    const buyButtonHTML = isOutOfStock ? 
                        '<button disabled class="flex-1 md:flex-none md:w-3/4 bg-gray-700 text-gray-400 py-2 rounded-sm font-bold text-[10px] md:text-sm cursor-not-allowed border border-white/10">স্টকে নেই</button>' : 
                        '<button onclick=\'addToCart(' + actionData + ')\' class="flex-1 md:flex-none md:w-3/4 bg-brand-gold text-brand-900 py-2 rounded-sm font-bold transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 hover:bg-white text-[10px] md:text-sm shadow-xl font-sans"><span class="hidden md:block">কিনুন ৳' + bnNum(book.price) + '</span><span class="md:hidden">কিনুন</span></button>';
                    const borrowButtonHTML = canBorrow ? 
                        '<button onclick=\'borrowBook(' + borrowData + ')\' class="flex-1 md:flex-none md:w-3/4 bg-transparent border border-white/30 text-white py-2 rounded-sm font-medium transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 delay-75 hover:bg-white hover:text-brand-900 text-[10px] md:text-sm font-sans flex items-center justify-center gap-1 backdrop-blur-sm"><span class="hidden md:block">ধার নিন</span><span class="md:hidden">ধার</span></button>' : '';

                    const borrowIndicatorHTML = parseInt(book.is_borrowable) === 1 ? '<span class="w-1.5 h-1.5 rounded-full bg-green-500" title="লাইব্রেরিতে রয়েছে"></span>' : '';

                    const html = `
                        <div class="book-card group reveal active animate-slide-up ${isOutOfStock ? 'opacity-80' : ''}" style="animation-delay: ${delay}ms;">
                            <div class="relative book-cover-container aspect-[2/3] rounded-md overflow-hidden bg-gray-100 mb-4 shadow-sm border border-gray-100">
                                <img src="${book.img}" alt="${displayTitle}" class="object-cover w-full h-full transition-all duration-700 ${isOutOfStock ? 'grayscale' : ''}" loading="lazy">
                                ${stockBadgeHTML}
                                <div class="absolute inset-x-0 bottom-0 md:inset-0 bg-brand-900/95 md:bg-brand-900/70 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-row md:flex-col justify-center items-center gap-2 md:gap-3 backdrop-blur-md md:backdrop-blur-sm p-2 md:p-0 z-30">
                                    ${buyButtonHTML}
                                    ${borrowButtonHTML}
                                </div>
                            </div>
                            <div class="text-center px-1">
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <span class="text-[10px] md:text-xs text-brand-gold font-bold uppercase tracking-wider">${safeCategory}</span>
                                    ${borrowIndicatorHTML}
                                </div>
                                <a href="../book-details.php?id=${book.id}" class="block hover:text-brand-gold">
                                    <h3 class="font-serif text-base md:text-lg text-brand-900 mt-1 truncate font-bold transition-colors">${displayTitle}</h3>
                                </a>
                                <p class="text-gray-500 text-xs md:text-sm font-light mt-1">${String(book.author || '')}</p>
                            </div>
                        </div>
                    `;
                    grid.insertAdjacentHTML('beforeend', html);
                });

                // Update pagination controls
                renderPagination(data.total, currentPage);
            }

        } catch (error) {
            console.error('Error fetching books:', error);
        } finally {
            isLoading = false;
            // Force browser reflow to guarantee CSS transition plays correctly
            void grid.offsetWidth; 
            grid.style.opacity = '1';
            grid.style.pointerEvents = 'auto';
        }
    }

    // Debounce search
    let searchTimeout;
    function debounceSearch(event) {
        searchQuery = event.target.value;
        clearTimeout(searchTimeout);
        searchIcon.classList.add('hidden');
        searchSpinner.classList.remove('hidden');
        
        searchTimeout = setTimeout(() => {
            fetchPage(1).finally(() => {
                searchSpinner.classList.add('hidden');
                searchIcon.classList.remove('hidden');
            });
        }, 500);
    }

    function clearLibrarySearch() {
        document.getElementById('librarySearchInput').value = '';
        searchQuery = '';
        fetchPage(1);
    }
</script>
</body>
</html>