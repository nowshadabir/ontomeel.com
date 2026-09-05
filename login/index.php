<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন | অন্ত্যমিল - আপনার বুকশেলফে ফিরে যান</title>
    <meta name="description" content="অন্ত্যমিল অ্যাকাউন্টে লগইন করুন এবং আপনার কেনা ও ধার নেওয়া বই ম্যানেজ করুন। আপনার রিডিং প্রগ্রেস সেভ করুন খুব সহজেই।">
    <meta name="keywords" content="লগইন, অন্ত্যমিল, VIVAGO TECHNOLOGIES, অনলাইন বুকস্টোর, মেম্বার লগইন">
    <meta name="author" content="VIVAGO TECHNOLOGIES">

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700;800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Custom JS -->
    <script src="../assets/js/script.js" defer></script>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-brand-light min-h-screen flex flex-col justify-between relative overflow-x-hidden text-brand-900">

    <!-- Fixed Ambient Glow Background (No layout shifting or scrollbars) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
        <div class="mesh-gradient absolute inset-0 opacity-20"></div>
        <div class="absolute -top-24 -left-24 w-72 sm:w-96 md:w-[450px] h-72 sm:h-96 md:h-[450px] bg-brand-gold/15 blur-[90px] sm:blur-[130px] rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-72 sm:w-96 md:w-[450px] h-72 sm:h-96 md:h-[450px] bg-brand-900/10 blur-[90px] sm:blur-[130px] rounded-full"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 sm:pt-5 pb-1 relative z-20">
        <div class="flex items-center justify-between">
            <a href="../index.php" class="inline-flex items-center gap-2 group focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold rounded-xl p-1 transition-transform active:scale-95">
                <img src="../assets/img/logo.webp" alt="অন্ত্যমিল লোগো" class="w-9 sm:w-11 h-auto drop-shadow-sm transition-transform group-hover:scale-105">
                <span class="font-serif text-2xl sm:text-3xl font-bold tracking-wide text-brand-900">অন্ত্যমিল<span class="text-brand-gold">.</span></span>
            </a>

            <a href="../index.php" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-anek font-semibold text-gray-600 hover:text-brand-900 bg-white/80 hover:bg-white border border-gray-200/80 shadow-sm hover:shadow px-3 sm:px-4 py-1.5 sm:py-2 rounded-full transition-all duration-200">
                <svg class="w-3.5 h-3.5 text-brand-gold transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>হোমপেজে ফিরুন</span>
            </a>
        </div>
    </header>

    <!-- Main Content Container: Perfectly fitted for both mobile and desktop viewports without vertical overflow -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-6 my-auto relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-center lg:justify-between gap-6 sm:gap-8 lg:gap-14">

            <!-- Left Branding Side (Desktop: lg+) -->
            <div class="hidden lg:flex flex-col justify-center flex-1 max-w-lg pr-2">
                <div class="inline-flex items-center gap-2 bg-brand-gold/15 text-brand-900 text-xs font-bold font-anek px-3.5 py-1 rounded-full w-fit mb-4 border border-brand-gold/30">
                    <span class="w-2 h-2 rounded-full bg-brand-gold animate-pulse"></span>
                    <span>অন্ত্যমিল রিডার্স কমিউনিটি</span>
                </div>

                <h1 class="text-3xl xl:text-4xl font-anek font-extrabold text-brand-900 leading-[1.25] mb-4">
                    আপনার পছন্দের বইয়ের <br>
                    <span class="text-brand-gold">অনন্য ভুবনে ফিরুন</span>
                </h1>

                <p class="text-gray-600 text-sm xl:text-base font-light leading-relaxed mb-6 max-w-md">
                    অন্ত্যমিল মেম্বার হিসেবে লগইন করে আপনার বুকশেলফ পরিচালনা করুন, নতুন বই অর্ডার করুন এবং পড়ার অনন্য অভিজ্ঞতা উপভোগ করুন।
                </p>

                <!-- Value Highlights Grid -->
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-200/60">
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-200 flex items-center justify-center shrink-0 text-sm">📚</div>
                        <div>
                            <h4 class="text-xs font-bold font-anek text-brand-900">বিশাল সংগ্রহ</h4>
                            <p class="text-[11px] text-gray-500 font-anek">হাজারো সেরা বই</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-200 flex items-center justify-center shrink-0 text-sm">⚡</div>
                        <div>
                            <h4 class="text-xs font-bold font-anek text-brand-900">সহজ ধার নেওয়া</h4>
                            <p class="text-[11px] text-gray-500 font-anek">মেম্বারশিপ সুবিধা</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-200 flex items-center justify-center shrink-0 text-sm">🚚</div>
                        <div>
                            <h4 class="text-xs font-bold font-anek text-brand-900">দ্রুত ডেলিভারি</h4>
                            <p class="text-[11px] text-gray-500 font-anek">নিরাপদ ট্র্যাকিং</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Login Card -->
            <div class="w-full max-w-md mx-auto">
                <div class="bg-white/95 backdrop-blur-md p-5 sm:p-7 md:p-8 rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100/90 relative">
                    
                    <!-- Card Header -->
                    <div class="text-center mb-4 sm:mb-5">
                        <div class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-gold/10 border border-brand-gold/20 mb-2 text-xl">
                            📖
                        </div>
                        <h2 class="text-xl sm:text-2xl font-anek font-bold text-brand-900 leading-tight">লগইন করুন</h2>
                        <p class="text-xs text-gray-500 font-anek mt-0.5">আপনার অন্ত্যমিল অ্যাকাউন্টে প্রবেশ করুন</p>
                    </div>

                    <!-- Status / Error Notification (Always visible on all screen sizes) -->
                    <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2.5 animate-slide-up" role="alert">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs sm:text-sm text-emerald-800 font-anek font-semibold">নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন।</p>
                        </div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2.5 animate-slide-up" role="alert">
                            <div class="w-7 h-7 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <p class="text-xs sm:text-sm text-red-700 font-anek font-semibold">
                                <?php
                                if ($_GET['error'] == 'invalid') {
                                    echo "ভুল ইমেইল বা পাসওয়ার্ড! অনুগ্রহ করে আবার চেষ্টা করুন।";
                                } elseif ($_GET['error'] == 'empty') {
                                    echo "দয়া করে সবগুলো তথ্য সঠিকভাবে পূরণ করুন।";
                                } elseif ($_GET['error'] == 'rate_limit') {
                                    echo "অতিরিক্ত ভুল চেষ্টা করা হয়েছে! কিছুক্ষণ পর চেষ্টা করুন।";
                                } elseif ($_GET['error'] == 'db') {
                                    echo "সার্ভার সংযোগ সমস্যা। একটু পরে আবার চেষ্টা করুন।";
                                } else {
                                    echo "লগইন ব্যর্থ হয়েছে। তথ্য যাচাই করে আবার চেষ্টা করুন।";
                                }
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="process_login.php" method="POST" class="space-y-3.5 sm:space-y-4" novalidate>
                        
                        <!-- Login ID (Email or Phone) -->
                        <div class="space-y-1">
                            <label for="login_id" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek ml-0.5">
                                ইমেইল বা মোবাইল নম্বর
                            </label>
                            <div class="relative group">
                                <input 
                                    type="text" 
                                    id="login_id" 
                                    name="login_id" 
                                    required 
                                    autocomplete="username"
                                    placeholder="আপনার ইমেইল বা ফোন (017...)"
                                    class="w-full bg-gray-50/80 border border-gray-200 rounded-xl pl-3.5 pr-10 py-2.5 sm:py-3 text-sm font-anek text-brand-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 group-focus-within:text-brand-gold transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Password Field with Show/Hide Toggle -->
                        <div class="space-y-1">
                            <div class="flex justify-between items-center ml-0.5">
                                <label for="login_password" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek">
                                    পাসওয়ার্ড
                                </label>
                                <a href="../recover/" class="text-[11px] font-bold font-anek text-brand-gold hover:text-brand-900 transition-colors hover:underline">
                                    পাসওয়ার্ড ভুলে গেছেন?
                                </a>
                            </div>
                            <div class="relative group">
                                <input 
                                    type="password" 
                                    id="login_password" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full bg-gray-50/80 border border-gray-200 rounded-xl pl-3.5 pr-10 py-2.5 sm:py-3 text-sm font-anek text-brand-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                                <button 
                                    type="button" 
                                    id="toggle-user-password" 
                                    aria-label="পাসওয়ার্ড দেখুন"
                                    onclick="togglePasswordVisibility('login_password')"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-900 focus:outline-none p-1 rounded-lg transition-colors cursor-pointer">
                                    <!-- Eye Icon (Default) -->
                                    <svg id="eye-icon-login" class="w-4 h-4 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <!-- Eye Off Icon (Hidden) -->
                                    <svg id="eye-off-icon-login" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center gap-2 ml-0.5 pt-0.5">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="w-3.5 h-3.5 rounded border-gray-300 text-brand-900 focus:ring-brand-gold accent-brand-900 cursor-pointer">
                            <label for="remember" class="text-xs text-gray-600 font-anek cursor-pointer select-none">
                                এই ডিভাইসে তথ্য মনে রাখুন
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-1">
                            <button 
                                type="submit"
                                class="w-full py-2.5 sm:py-3 bg-brand-900 text-white font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-brand-gold hover:text-brand-900 active:scale-[0.99] transition-all duration-200 shadow-md shadow-brand-900/15 flex items-center justify-center gap-2 group cursor-pointer">
                                <span>প্রবেশ করুন</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sign Up Footer -->
                        <div class="pt-3 text-center border-t border-gray-100 font-anek">
                            <p class="text-gray-500 text-xs">
                                অন্ত্যমিলে নতুন পাঠক? 
                                <a href="../signup/" class="text-brand-gold font-bold hover:text-brand-900 transition-colors hover:underline ml-1">
                                    রেজিস্ট্রেশন করুন →
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 text-center text-gray-400 text-[11px] font-anek relative z-10">
        <p>&copy; <?php echo date('Y'); ?> অন্ত্যমিল (ontomeel.com) — সর্বস্বত্ব সংরক্ষিত।</p>
    </footer>

    <!-- Password Toggle Script -->
    <script>
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById('eye-icon-login');
            const eyeOffIcon = document.getElementById('eye-off-icon-login');
            if (!input || !eyeIcon || !eyeOffIcon) return;

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>

</html>