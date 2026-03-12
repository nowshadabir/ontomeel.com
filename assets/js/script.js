// --- 1. Navbar Scroll Effect ---
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('shadow-md');
        navbar.classList.replace('py-4', 'py-2');
    } else {
        navbar.classList.remove('shadow-md');
        navbar.classList.replace('py-2', 'py-4');
    }
});

// --- 2. Mobile Menu Toggle ---
const mobileMenu = document.getElementById('mobile-menu');
const mobileOverlay = document.getElementById('mobile-menu-overlay');

function toggleMobileMenu() {
    if (mobileMenu.classList.contains('translate-x-full')) {
        mobileMenu.classList.remove('translate-x-full');
        if (mobileOverlay) {
            mobileOverlay.classList.remove('hidden');
            setTimeout(() => mobileOverlay.classList.remove('opacity-0'), 10);
        }
        document.body.style.overflow = 'hidden';
    } else {
        mobileMenu.classList.add('translate-x-full');
        if (mobileOverlay) {
            mobileOverlay.classList.add('opacity-0');
            setTimeout(() => {
                mobileOverlay.classList.add('hidden');
            }, 500);
        }
        document.body.style.overflow = '';
    }
}

// --- 3. Books Data (Populated from DB) ---
var allBooks = window.allBooks || [];

// Number converter helper
const convertToBengaliNumber = (num) => {
    const bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return num.toString().split('').map(digit => bengaliDigits[digit] || digit).join('');
};

// DOM Elements will be queried inside functions for robustness

// Show Skeletons
function showSkeletons() {
    const bookGrid = document.getElementById('book-grid') || document.getElementById('library-book-grid');
    if (!bookGrid) return;

    bookGrid.innerHTML = '';
    for (let i = 0; i < 8; i++) {
        bookGrid.innerHTML += `
            <div class="book-card reveal active">
                <div class="skeleton aspect-[2/3] rounded-md mb-4"></div>
                <div class="px-1 flex flex-col items-center">
                    <div class="skeleton skeleton-text w-1/4 mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-title mb-2"></div>
                    <div class="skeleton skeleton-text skeleton-author"></div>
                </div>
            </div>
        `;
    }
}

// Render Books
function renderBooks(booksToRender) {
    const bookGrid = document.getElementById('book-grid');
    const libraryBookGrid = document.getElementById('library-book-grid');
    const noResults = document.getElementById('no-results');
    const bookCountTotal = document.getElementById('book-count-total');

    let prefix = '/';
    if (typeof PROJECT_ROOT !== 'undefined') {
        prefix = PROJECT_ROOT;
    } else if (window.location.pathname.includes('/bookshop/')) {
        prefix = window.location.pathname.substring(0, window.location.pathname.indexOf('/bookshop/') + 10);
    }

    const targetGrid = libraryBookGrid || bookGrid;
    if (!targetGrid) return;

    targetGrid.innerHTML = '';

    if (booksToRender.length === 0) {
        if (noResults) noResults.classList.remove('hidden');
        targetGrid.classList.add('hidden');
        if (bookCountTotal) bookCountTotal.innerText = convertToBengaliNumber(0);
        return;
    } else {
        if (noResults) noResults.classList.add('hidden');
        targetGrid.classList.remove('hidden');
        if (bookCountTotal) bookCountTotal.innerText = convertToBengaliNumber(booksToRender.length);
    }

    booksToRender.forEach((book, index) => {
        const isSubscribed = localStorage.getItem('is_subscribed') === 'true';
        const delay = (index % 4) * 100;

        // Check stock and borrow status
        const isOutOfStock = parseInt(book.stock_qty) <= 0;
        const canBorrow = parseInt(book.is_borrowable) === 1 && !isOutOfStock;

        const bookHTML = `
                    <div class="book-card group reveal active ${isOutOfStock ? 'opacity-80' : ''}" style="transition-delay: ${delay}ms;">
                        <!-- Cover -->
                        <div class="relative book-cover-container aspect-[2/3] rounded-md overflow-hidden bg-gray-200 mb-4 shadow-sm border border-gray-100">
                            <img src="${book.img}" alt="${book.title}" loading="lazy" class="object-cover w-full h-full ${isOutOfStock ? 'grayscale' : ''}">
                            
                            ${isOutOfStock ? `
                            <div class="absolute top-2 left-2 bg-red-600 text-white text-[9px] font-bold px-2 py-1 rounded-sm uppercase tracking-wider z-20">
                                স্টক আউট
                            </div>` : ''}

                        <!-- Overlay Actions -->
                        <div class="absolute inset-x-0 bottom-0 md:inset-0 bg-brand-900/90 md:bg-brand-900/70 md:opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-row md:flex-col justify-center items-center gap-2 md:gap-3 backdrop-blur-md md:backdrop-blur-sm p-2 md:p-0">
                            ${isOutOfStock ? `
                            <button disabled class="flex-1 md:flex-none md:w-3/4 bg-gray-600 text-gray-300 py-2 rounded-sm font-bold text-[10px] md:text-sm cursor-not-allowed">
                                স্টকে নেই
                            </button>` : `
                            <button onclick="addToCart({id: ${book.id}, title: '${book.title.replace(/'/g, "\\'")}', price: ${book.price}, img: '${book.img}', author: '${book.author.replace(/'/g, "\\'")}'})" 
                                    class="flex-1 md:flex-none md:w-3/4 bg-brand-gold text-brand-900 py-2 rounded-sm font-bold transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 hover:bg-white text-[10px] md:text-sm shadow-lg font-sans">
                                <span class="md:hidden">কিনুন</span>
                                <span class="hidden md:block">কিনুন ৳${convertToBengaliNumber(book.price)}</span>
                            </button>`}
                            
                            ${canBorrow ? `
                            <button onclick="borrowBook({id: ${book.id}, title: '${book.title.replace(/'/g, "\\'")}', price: 0, img: '${book.img}', author: '${book.author.replace(/'/g, "\\'")}'})" 
                                    class="flex-1 md:flex-none md:w-3/4 bg-transparent border border-white text-white py-2 rounded-sm font-medium transform md:translate-y-4 md:group-hover:translate-y-0 transition-all duration-400 delay-75 hover:bg-white hover:text-brand-900 text-[10px] md:text-sm font-sans flex items-center justify-center gap-1">
                                <span class="borrow-icon">
                                    <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </span>
                                <span class="md:hidden">ধার</span>
                                <span class="hidden md:block">ধার নিন</span>
                            </button>` : ''}
                        </div>
                        
                        <!-- Premium Shine Effect on hover -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transform -translate-x-full group-hover:translate-x-full transition-all duration-1000 ease-in-out"></div>
                    </div>
                        
                        <!-- Details -->
                        <div class="text-center px-1">
                            <span class="text-[10px] md:text-xs text-brand-gold font-bold uppercase tracking-wider">${book.category}</span>
                            <a href="${prefix}book-details.php?id=${book.id}" class="block hover:text-brand-gold">
                                <h3 class="font-serif text-base md:text-lg text-brand-900 mt-1 truncate font-bold">${book.title}</h3>
                            </a>
                            <p class="text-gray-500 text-xs md:text-sm font-light mt-1">${book.author}</p>
                        </div>
                    </div>
                `;
        targetGrid.innerHTML += bookHTML;
    });
}

// Initialize with all books
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryFilter = urlParams.get('category');
    const libGrid = document.getElementById('library-book-grid');
    const bookGrid = document.getElementById('book-grid');
    const isLibraryPage = window.location.pathname.includes('library') || libGrid;

    if (categoryFilter && (libGrid || bookGrid)) {
        showSkeletons();
        setTimeout(() => filterByCategory(categoryFilter), 300);
    } else if (isLibraryPage && libGrid) {
        showSkeletons();
        setTimeout(() => renderBooks(allBooks), 300);
    } else if (bookGrid) {
        // If homepage has suggested books pre-rendered by PHP, we might still want to "refresh" 
        // them or just let them stay. The user asked for skeleton loading animation.
        // Let's hide the PHP rendered ones, show skeletons, then show them again.
        
        // Check if we have suggested books in the data
        const suggestedBooks = allBooks.filter(book => parseInt(book.is_suggested) === 1);
        if (suggestedBooks.length > 0) {
            showSkeletons();
            setTimeout(() => renderBooks(suggestedBooks), 300);
        }
    }
});

// --- 4. Search and Filter Logic ---
function searchBooks(event, isMobile = false) {
    const query = event.target.value.toLowerCase();
    const filteredBooks = allBooks.filter(book =>
        book.title.toLowerCase().includes(query) ||
        book.author.toLowerCase().includes(query)
    );

    document.getElementById('section-title').innerText = query ? "অনুসন্ধানের ফলাফল" : "সাজেস্টেড বই";
    document.getElementById('section-subtitle').innerText = query ? "সার্চ রেজাল্ট" : "আমাদের কালেকশন";
    document.getElementById('section-desc').innerText = query ? `"${event.target.value}" এর জন্য প্রাপ্ত বইসমূহ` : "আপনার জন্য আমাদের বাছাইকৃত কিছু চমৎকার বই, যা আপনি কিনতে বা লাইব্রেরি থেকে ধার নিতে পারেন।";
    document.getElementById('clearFilterBtn').classList.remove('hidden');

    renderBooks(filteredBooks);

    if (isMobile && event.key === 'Enter') {
        toggleMobileMenu();
        const discoverSection = document.getElementById('discover');
        if (discoverSection) {
            discoverSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

function filterByCategory(category) {
    const filteredBooks = allBooks.filter(book => book.category === category);
    document.getElementById('section-title').innerText = category;
    document.getElementById('section-subtitle').innerText = "ক্যাটাগরি ফিল্টার";
    document.getElementById('section-desc').innerText = `"${category}" ক্যাটাগরির সমস্ত বই।`;
    document.getElementById('clearFilterBtn').classList.remove('hidden');

    renderBooks(filteredBooks);
    document.getElementById('discover').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function clearFilters() {
    const searchInput = document.getElementById('searchInput');
    const mobileSearchInput = document.getElementById('mobileSearchInput');
    if (searchInput) searchInput.value = '';
    if (mobileSearchInput) mobileSearchInput.value = '';

    const sectionTitle = document.getElementById('section-title');
    if (sectionTitle) sectionTitle.innerText = "সাজেস্টেড বই";

    const sectionSubtitle = document.getElementById('section-subtitle');
    if (sectionSubtitle) sectionSubtitle.innerText = "আমাদের কালেকশন";

    const sectionDesc = document.getElementById('section-desc');
    if (sectionDesc) sectionDesc.innerText = "আপনার জন্য আমাদের বাছাইকৃত কিছু চমৎকার বই, যা আপনি কিনতে বা লাইব্রেরি থেকে ধার নিতে পারেন।";

    const clearBtn = document.getElementById('clearFilterBtn');
    if (clearBtn) clearBtn.classList.add('hidden');

    // If on homepage (checked by presence of PROJECT_ROOT), show only suggested
    if (typeof PROJECT_ROOT !== 'undefined' && window.location.pathname === PROJECT_ROOT) {
        const suggestedBooks = allBooks.filter(book => parseInt(book.is_suggested) === 1);
        renderBooks(suggestedBooks);
    } else {
        renderBooks(allBooks);
    }
}

// Library Specific Search
function searchBooksLibrary(event) {
    const query = event.target.value.toLowerCase();
    const filteredBooks = allBooks.filter(book =>
        book.title.toLowerCase().includes(query) ||
        book.author.toLowerCase().includes(query) ||
        book.category.toLowerCase().includes(query)
    );
    renderBooks(filteredBooks);
}

function clearLibrarySearch() {
    const librarySearchInput = document.getElementById('librarySearchInput');
    if (librarySearchInput) librarySearchInput.value = '';
    renderBooks(allBooks);
}


// --- 5. Cart Logic & Slider ---
let cart = JSON.parse(localStorage.getItem('antyam_cart') || '[]');
function saveCart() { localStorage.setItem('antyam_cart', JSON.stringify(cart)); }
const cartCountEl = document.getElementById('cart-count');
const toast = document.getElementById('toast');
const toastMsg = document.getElementById('toast-msg');

const cartDrawer = document.getElementById('cart-drawer');
const cartOverlay = document.getElementById('cart-overlay');
const cartItemsContainer = document.getElementById('cart-items-container');
const cartEmptyState = document.getElementById('cart-empty');
const cartTotalEl = document.getElementById('cart-total');

// Borrow Cart Elements
let borrowCart = JSON.parse(localStorage.getItem('antyam_borrow_cart') || '[]');
function saveBorrowCart() { localStorage.setItem('antyam_borrow_cart', JSON.stringify(borrowCart)); }
const borrowCartCountEl = document.getElementById('borrow-cart-count');
const borrowCartDrawer = document.getElementById('borrow-cart-drawer');
const borrowCartOverlay = document.getElementById('borrow-cart-overlay');
const borrowCartItemsContainer = document.getElementById('borrow-cart-items-container');
const borrowCartEmptyState = document.getElementById('borrow-cart-empty');

const getCorrectImagePath = (img) => {
    if (!img || img.startsWith('http')) return img;
    const stripped = img.replace(/^(\.\.\/)+/, '');
    let prefix = '/';
    if (typeof PROJECT_ROOT !== 'undefined') {
        prefix = PROJECT_ROOT;
    } else if (window.location.pathname.includes('/bookshop/')) {
        prefix = window.location.pathname.substring(0, window.location.pathname.indexOf('/bookshop/') + 10);
    }
    return prefix + stripped;
};

function toggleCartDrawer() {
    if (cartDrawer.classList.contains('translate-x-full')) {
        cartDrawer.classList.remove('translate-x-full');
        cartOverlay.classList.remove('hidden');
        setTimeout(() => cartOverlay.classList.remove('opacity-0'), 10);
    } else {
        cartDrawer.classList.add('translate-x-full');
        cartOverlay.classList.add('opacity-0');
        setTimeout(() => cartOverlay.classList.add('hidden'), 500);
    }
}

function toggleBorrowCartDrawer() {
    if (borrowCartDrawer.classList.contains('translate-x-full')) {
        borrowCartDrawer.classList.remove('translate-x-full');
        borrowCartOverlay.classList.remove('hidden');
        setTimeout(() => borrowCartOverlay.classList.remove('opacity-0'), 10);
    } else {
        borrowCartDrawer.classList.add('translate-x-full');
        borrowCartOverlay.classList.add('opacity-0');
        setTimeout(() => borrowCartOverlay.classList.add('hidden'), 500);
    }
}

function updateCartUI() {
    // If not on a page with cart UI, skip updating
    if (!cartCountEl || !cartItemsContainer) return;

    // Update badge
    cartCountEl.innerText = convertToBengaliNumber(cart.length);

    if (cart.length === 0) {
        cartEmptyState.style.display = 'flex';
        Array.from(cartItemsContainer.children).forEach(child => {
            if (child.id !== 'cart-empty') child.remove();
        });
        cartTotalEl.innerText = '৳০';
        saveCart();
        return;
    }

    cartEmptyState.style.display = 'none';

    // Clear existing items except empty state
    Array.from(cartItemsContainer.children).forEach(child => {
        if (child.id !== 'cart-empty') child.remove();
    });

    let total = 0;

    cart.forEach((item, index) => {
        total += item.price;
        const itemEl = document.createElement('div');
        itemEl.className = 'flex gap-4 items-center bg-gray-50 p-3 rounded-lg border border-gray-100';
        itemEl.innerHTML = `
                    <img src="${getCorrectImagePath(item.img)}" alt="${item.title}" loading="lazy" class="w-16 h-24 object-cover rounded shadow-sm">
                    <div class="flex-1">
                        <h4 class="font-serif font-bold text-brand-900 text-[15px]">${item.title}</h4>
                        <p class="text-xs text-gray-500 mb-2">${item.author}</p>
                        <p class="font-bold text-brand-gold text-sm">৳${convertToBengaliNumber(item.price)}</p>
                    </div>
                    <button onclick="removeFromCart(${index})" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                `;
        cartItemsContainer.appendChild(itemEl);
    });

    cartTotalEl.innerText = `৳${convertToBengaliNumber(total)}`;
    saveCart();
}

function goToCheckout() {
    if (cart.length === 0) {
        showToast("কার্ট খালি!");
        return;
    }
    let prefix = '/';
    if (typeof PROJECT_ROOT !== 'undefined') {
        prefix = PROJECT_ROOT;
    } else if (window.location.pathname.includes('/bookshop/')) {
        prefix = window.location.pathname.substring(0, window.location.pathname.indexOf('/bookshop/') + 10);
    }
    const baseUrl = prefix + 'checkout/index.php?type=buy';
    window.location.href = baseUrl;
}

function addToCart(bookId) {
    let book = allBooks.find(b => b.id === bookId);

    // Support for objects directly (if bookId is an object)
    if (typeof bookId === 'object' && bookId !== null) {
        book = bookId;
    }

    if (book) {
        cart.push(book);
        updateCartUI();

        // Pop animation on badge
        cartCountEl.classList.add('scale-150');
        setTimeout(() => cartCountEl.classList.remove('scale-150'), 200);

        showToast(`"${book.title}" কার্টে যোগ করা হয়েছে`);

        // Automatically open drawer on first add
        if (cart.length === 1 && cartDrawer.classList.contains('translate-x-full')) {
            toggleCartDrawer();
        }
    }
}

function removeFromCart(index) {
    const removed = cart.splice(index, 1)[0];
    updateCartUI();
    showToast(`"${removed.title}" কার্ট থেকে সরানো হয়েছে`);
}

function updateBorrowCartUI() {
    if (!borrowCartCountEl || !borrowCartItemsContainer) return;

    borrowCartCountEl.innerText = convertToBengaliNumber(borrowCart.length);

    if (borrowCart.length === 0) {
        borrowCartEmptyState.style.display = 'flex';
        Array.from(borrowCartItemsContainer.children).forEach(child => {
            if (child.id !== 'borrow-cart-empty') child.remove();
        });
        saveBorrowCart();
        return;
    }

    borrowCartEmptyState.style.display = 'none';

    Array.from(borrowCartItemsContainer.children).forEach(child => {
        if (child.id !== 'borrow-cart-empty') child.remove();
    });

    borrowCart.forEach((item, index) => {
        const itemEl = document.createElement('div');
        itemEl.className = 'flex gap-4 items-center bg-gray-50 p-3 rounded-lg border border-gray-100';
        itemEl.innerHTML = `
            <img src="${getCorrectImagePath(item.img)}" alt="${item.title}" loading="lazy" class="w-16 h-24 object-cover rounded shadow-sm">
            <div class="flex-1">
                <h4 class="font-serif font-bold text-brand-900 text-[15px]">${item.title}</h4>
                <p class="text-xs text-gray-500 mb-2">${item.author}</p>
                <p class="font-bold text-brand-900 text-xs">মূল্য: বিনামূল্যে</p>
            </div>
            <button onclick="removeFromBorrowCart(${index})" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        borrowCartItemsContainer.appendChild(itemEl);
    });

    saveBorrowCart();
}

function removeFromBorrowCart(index) {
    const removed = borrowCart.splice(index, 1)[0];
    updateBorrowCartUI();
    showToast(`"${removed.title}" ধার নেওয়ার কার্ট থেকে সরানো হয়েছে`);
}

function goToBorrowCheckout() {
    if (borrowCart.length === 0) {
        showToast("আপনার ধার নেওয়ার কার্ট খালি!");
        return;
    }
    let prefix = '/';
    if (typeof PROJECT_ROOT !== 'undefined') {
        prefix = PROJECT_ROOT;
    } else if (window.location.pathname.includes('/bookshop/')) {
        prefix = window.location.pathname.substring(0, window.location.pathname.indexOf('/bookshop/') + 10);
    }
    const baseUrl = prefix + 'checkout/index.php?type=borrow';
    window.location.href = baseUrl;
}

function showToast(message) {
    toastMsg.innerText = message;
    toast.classList.remove('translate-y-20', 'opacity-0');

    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

function borrowBook(bookData) {
    // Check if user is logged in (using global user_id from header)
    if (typeof user_id === 'undefined') {
        const loginBtn = document.querySelector('a[href*="login/"]');
        if (loginBtn) loginBtn.click();
        else window.location.href = '../login/';
        return;
    }

    // Check membership (based on localStorage or we can just let backend handle it, but UI check is good)
    const membershipPlan = localStorage.getItem('membership_plan');
    if (!membershipPlan || membershipPlan === 'None') {
        showToast("বই ধার নিতে মেম্বারশিপ প্রয়োজন!");
        setTimeout(() => {
            let prefix = '/';
            if (typeof PROJECT_ROOT !== 'undefined') {
                prefix = PROJECT_ROOT;
            } else if (window.location.pathname.includes('/bookshop/')) {
                prefix = window.location.pathname.substring(0, window.location.pathname.indexOf('/bookshop/') + 10);
            }
            window.location.href = prefix + 'membership/index.php';
        }, 1500);
        return;
    }

    // Add book to the separate borrow cart
    const exists = borrowCart.find(b => b.id === bookData.id);
    if (!exists) {
        borrowCart.push(bookData);
        saveBorrowCart();
        updateBorrowCartUI();

        if (borrowCartCountEl) {
            borrowCartCountEl.classList.add('scale-150');
            setTimeout(() => borrowCartCountEl.classList.remove('scale-150'), 200);
        }

        showToast(`"${bookData.title}" ধার নেওয়ার কার্টে যোগ করা হয়েছে`);

        if (borrowCart.length === 1 && borrowCartDrawer && borrowCartDrawer.classList.contains('translate-x-full')) {
            toggleBorrowCartDrawer();
        }
    } else {
        showToast("বইটি ইতিমধ্যে ধার নেওয়ার কার্টে আছে!");
    }
}


// --- 6. Scroll Reveal Animation ---
document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');

    const revealOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };

    const revealOnScroll = new IntersectionObserver(function (entries, observer) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('active');
            observer.unobserve(entry.target);
        });
    }, revealOptions);

    reveals.forEach(reveal => {
        revealOnScroll.observe(reveal);
    });

    // Load cart UI on init
    if (typeof updateCartUI === 'function') updateCartUI();
    if (typeof updateBorrowCartUI === 'function') updateBorrowCartUI();
});