<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন কমান্ড সেন্টার | অন্ত্যমিল</title>
    <meta name="description" content="অন্ত্যমিল অ্যাডমিনিস্ট্রেশন সিকিউর কমান্ড সেন্টার ও কন্ট্রোল পোর্টাল। শুধুমাত্র অনুমোদিত অ্যাডমিনদের জন্য।">
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700;800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        /* Tech terminal subtle dot grid */
        .bg-tech-grid {
            background-image: radial-gradient(rgba(205, 168, 115, 0.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* Ambient glowing orb animation */
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.15; transform: scale(1); }
            50% { opacity: 0.25; transform: scale(1.08); }
        }
        .animate-glow {
            animation: pulse-slow 8s ease-in-out infinite;
        }
    </style>
</head>

<body class="antialiased selection:bg-brand-gold selection:text-brand-900 bg-[#08080a] text-white min-h-screen relative overflow-x-hidden flex flex-col justify-between">

    <!-- Ambient Glow Background Canvas (Zero layout interference) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
        <div class="bg-tech-grid absolute inset-0 opacity-40"></div>
        <div class="absolute -top-36 -left-36 w-96 sm:w-[540px] h-96 sm:h-[540px] bg-brand-gold/15 blur-[120px] rounded-full animate-glow"></div>
        <div class="absolute -bottom-36 -right-36 w-96 sm:w-[540px] h-96 sm:h-[540px] bg-brand-gold/10 blur-[140px] rounded-full animate-glow" style="animation-delay: 4s;"></div>
    </div>

    <!-- Top Mobile / Universal Quick Bar -->
    <nav class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2 relative z-20">
        <div class="flex items-center justify-between">
            <a href="../../" class="inline-flex items-center gap-2 text-xs font-anek font-semibold text-gray-400 hover:text-brand-gold transition-colors py-1.5 px-3 rounded-full bg-white/[0.03] hover:bg-white/[0.08] border border-white/10 group">
                <svg class="w-3.5 h-3.5 text-brand-gold transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>মূল ওয়েবসাইটে ফিরে যান</span>
            </a>

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[11px] font-mono text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>SYSTEM ONLINE</span>
            </div>
        </div>
    </nav>

    <!-- Main Workspace Container -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 my-auto relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <!-- LEFT COLUMN: Operations Cockpit & Showcase (Desktop: 7 Cols) -->
            <div class="hidden lg:flex flex-col justify-center lg:col-span-7 xl:col-span-7 pr-6">
                
                <!-- Brand Badge -->
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1 rounded-full bg-brand-gold/10 border border-brand-gold/30 text-brand-gold text-xs font-mono w-fit mb-5">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-gold"></span>
                    <span>ONTOMEEL ADMIN HUB v2.5</span>
                </div>

                <!-- Main Branding Heading -->
                <div class="flex items-center gap-3.5 mb-3">
                    <img src="../../assets/img/logo.webp" alt="অন্ত্যমিল লোগো" class="w-12 h-auto drop-shadow-[0_8px_16px_rgba(205,168,115,0.3)]">
                    <h1 class="font-serif text-3xl xl:text-4xl font-bold tracking-wide text-white">
                        অন্ত্যমিল<span class="text-brand-gold">.</span>
                    </h1>
                </div>

                <h2 class="text-2xl xl:text-3xl font-anek font-extrabold text-white leading-snug mb-3">
                    বুকস্টোর ও ডিজিটাল লাইব্রেরি পরিচালনার <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-gold via-amber-200 to-brand-gold">কেন্দ্রীয় প্রশাসনিক কনসোল</span>
                </h2>

                <p class="text-gray-400 text-sm font-light leading-relaxed mb-6 max-w-lg">
                    রিয়েলটাইম অর্ডার প্রসেসিং, মেম্বারশিপ সাবস্ক্রিপশন নিয়ন্ত্রণ, বইয়ের স্টক ও পেমেন্ট অডিট—সকল কার্যক্রম নিরাপদে পরিচালনা করুন।
                </p>

                <!-- Live Operations Glass Widget -->
                <div class="bg-white/[0.035] backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-2xl max-w-lg space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 text-xs font-mono">
                        <div class="flex items-center gap-2 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Secure SSL Session</span>
                        </div>
                        <span class="text-gray-500">TLS 1.3 • AES-256</span>
                    </div>

                    <!-- 3 Feature Highlights -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white/[0.02] border border-white/5 p-3 rounded-xl">
                            <div class="text-lg mb-1">📦</div>
                            <h4 class="text-xs font-bold font-anek text-white">অর্ডার অডিট</h4>
                            <p class="text-[10px] text-gray-500 font-anek">তাত্ক্ষণিক ট্র্যাকিং</p>
                        </div>
                        <div class="bg-white/[0.02] border border-white/5 p-3 rounded-xl">
                            <div class="text-lg mb-1">👥</div>
                            <h4 class="text-xs font-bold font-anek text-white">মেম্বারশিপ</h4>
                            <p class="text-[10px] text-gray-500 font-anek">স্মার্ট রিনিউয়াল</p>
                        </div>
                        <div class="bg-white/[0.02] border border-white/5 p-3 rounded-xl">
                            <div class="text-lg mb-1">💳</div>
                            <h4 class="text-xs font-bold font-anek text-white">পেমেন্ট গেটওয়ে</h4>
                            <p class="text-[10px] text-gray-500 font-anek">SSLCommerz লাইভ</p>
                        </div>
                    </div>
                </div>

                <!-- Security Watermark -->
                <div class="flex items-center gap-2 text-gray-500 text-[11px] font-anek mt-5">
                    <svg class="w-3.5 h-3.5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>অনুমোদিত প্রশাসনিক কর্মকর্তাদের জন্য সংরক্ষিত ওয়ার্কস্টেশন</span>
                </div>
            </div>

            <!-- RIGHT COLUMN: Login Terminal (Desktop: 5 Cols, Mobile: 12 Cols Full Width) -->
            <div class="w-full lg:col-span-5 xl:col-span-5 max-w-md mx-auto">
                
                <!-- Mobile Logo Header (< lg) -->
                <div class="lg:hidden flex flex-col items-center mb-5 text-center">
                    <img src="../../assets/img/logo.webp" alt="অন্ত্যমিল লোগো" class="w-12 h-auto drop-shadow-md mb-2">
                    <h2 class="font-serif text-2xl font-bold tracking-wide text-white">অন্ত্যমিল<span class="text-brand-gold">.</span></h2>
                    <p class="text-brand-gold text-[10px] font-mono uppercase tracking-widest mt-0.5">ADMIN SECURE CONSOLE</p>
                </div>

                <!-- Admin Terminal Card -->
                <div class="bg-[#121216]/90 backdrop-blur-2xl border border-white/15 p-6 sm:p-7 rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.8)] relative">
                    
                    <!-- Card Header -->
                    <div class="mb-5 pb-3.5 border-b border-white/10 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg sm:text-xl font-anek font-bold text-white leading-tight">প্রশাসনিক প্রবেশ</h3>
                            <p class="text-xs text-gray-400 font-anek mt-0.5">প্রবেশ করতে ক্রেডেনশিয়াল দিন</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-brand-gold/15 border border-brand-gold/30 flex items-center justify-center text-lg text-brand-gold shrink-0">
                            🔐
                        </div>
                    </div>

                    <!-- Alerts & Notifications -->
                    <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                        <div class="mb-4 p-3 bg-brand-gold/10 border border-brand-gold/30 rounded-xl flex items-center gap-2.5 animate-slide-up" role="alert">
                            <div class="w-6 h-6 rounded-lg bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs text-brand-gold font-anek font-semibold">নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন।</p>
                        </div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl flex items-center gap-2.5 animate-slide-up" role="alert">
                            <div class="w-6 h-6 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <p class="text-xs text-red-300 font-anek font-semibold">
                                <?php
                                if ($_GET['error'] == 'invalid')
                                    echo "ভুল ইউজারনেম বা সিকিউরিটি কী!";
                                elseif ($_GET['error'] == 'empty')
                                    echo "সবগুলো ঘর সঠিকভাবে পূরণ করুন!";
                                elseif ($_GET['error'] == 'rate_limit')
                                    echo "অতিরিক্ত চেষ্টা করা হয়েছে! কিছুক্ষণ পর চেষ্টা করুন।";
                                else
                                    echo "লগইন করতে সমস্যা হচ্ছে। আবার চেষ্টা করুন।";
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="process_admin_login.php" method="POST" class="space-y-4" novalidate>
                        
                        <!-- Username / Admin ID -->
                        <div class="space-y-1">
                            <label for="admin_username" class="block text-[11px] font-bold text-gray-300 uppercase tracking-wider font-anek ml-0.5">
                                অ্যাডমিন ইউজারনেম / আইডি
                            </label>
                            <div class="relative group">
                                <input 
                                    type="text" 
                                    id="admin_username"
                                    name="username" 
                                    required 
                                    autocomplete="username"
                                    placeholder="admin_user"
                                    class="w-full bg-white/[0.04] border border-white/10 rounded-xl pl-3.5 pr-10 py-2.5 sm:py-3 text-sm font-anek text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white/[0.07] transition-all">
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-focus-within:text-brand-gold transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Security Key / Password -->
                        <div class="space-y-1">
                            <div class="flex justify-between items-center ml-0.5">
                                <label for="admin_password" class="block text-[11px] font-bold text-gray-300 uppercase tracking-wider font-anek">
                                    সিকিউরিটি কী / পাসওয়ার্ড
                                </label>
                                <a href="../recover/" class="text-[11px] font-bold font-anek text-brand-gold/80 hover:text-brand-gold transition-colors hover:underline">
                                    পাসওয়ার্ড রিকভারি
                                </a>
                            </div>
                            <div class="relative group">
                                <input 
                                    type="password" 
                                    id="admin_password"
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full bg-white/[0.04] border border-white/10 rounded-xl pl-3.5 pr-10 py-2.5 sm:py-3 text-sm font-anek text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white/[0.07] transition-all">
                                <button 
                                    type="button" 
                                    id="toggle-admin-password" 
                                    aria-label="পাসওয়ার্ড দেখুন বা লুকান"
                                    onclick="toggleAdminPasswordVisibility('admin_password')"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-brand-gold focus:outline-none p-1 rounded-lg transition-colors cursor-pointer">
                                    <svg id="eye-icon-admin" class="w-4 h-4 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-off-icon-admin" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Session -->
                        <div class="flex items-center gap-2 ml-0.5 pt-0.5">
                            <input 
                                type="checkbox" 
                                id="remember_admin" 
                                name="remember_admin"
                                class="w-3.5 h-3.5 rounded border-gray-600 bg-white/5 text-brand-gold focus:ring-brand-gold accent-brand-gold cursor-pointer">
                            <label for="remember_admin" class="text-xs text-gray-400 font-anek cursor-pointer select-none">
                                এই ডিভাইসে সেশন মনে রাখুন
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button 
                                type="submit"
                                class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-brand-gold via-[#dfba85] to-brand-gold text-brand-900 font-anek font-bold text-sm sm:text-base rounded-xl hover:brightness-110 active:scale-[0.99] transition-all duration-200 shadow-[0_4px_20px_rgba(205,168,115,0.25)] flex items-center justify-center gap-2 group cursor-pointer">
                                <span>কন্ট্রোল প্যানেলে প্রবেশ</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>

                        <!-- Request Registration -->
                        <div class="pt-2 text-center">
                            <p class="text-gray-500 text-xs font-anek">
                                অ্যাডমিন অনুমোদন প্রয়োজন? 
                                <a href="../signup/" class="text-brand-gold font-bold hover:underline ml-1">
                                    নিবন্ধন রিকোয়েস্ট পাঠান →
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Micro Security Notice -->
                <div class="text-center mt-3 sm:mt-4 text-[11px] text-gray-500 font-anek flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>অননুমোদিত প্রবেশ চেষ্টা স্বয়ংক্রিয়ভাবে অডিট লগে রেকর্ড হয়</span>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-center text-gray-500 text-[11px] font-mono relative z-10 border-t border-white/5">
        <p>&copy; <?php echo date('Y'); ?> ONTOMEEL OPERATIONS • ALL RIGHTS RESERVED</p>
    </footer>

    <!-- Password Toggle Script -->
    <script>
        function toggleAdminPasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById('eye-icon-admin');
            const eyeOffIcon = document.getElementById('eye-off-icon-admin');
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
