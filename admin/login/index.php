<?php
require_once '../../includes/db_connect.php';

// If already authenticated as admin, redirect to admin overview
if (isset($_SESSION['admin_id'])) {
    header("Location: /admin/overview");
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | অন্ত্যমিল</title>
    <meta name="description" content="অন্ত্যমিল অ্যাডমিনিস্ট্রেশন সিকিউর কমান্ড সেন্টার ও কন্ট্রোল পোর্টাল। শুধুমাত্র অনুমোদিত অ্যাডমিনদের জন্য।">
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts for Bengali & Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="antialiased bg-[#faf8f5] text-stone-900 min-h-screen flex flex-col justify-between selection:bg-stone-200 selection:text-stone-900">

    <!-- Top Quick Bar -->
    <nav class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2">
        <div class="flex items-center justify-between">
            <a href="../../index.php" class="inline-flex items-center gap-2 text-xs font-anek text-stone-600 hover:text-stone-900 transition-colors py-1.5 px-3 rounded-md bg-white border border-[#d8d3c7] shadow-xs group">
                <svg class="w-3.5 h-3.5 text-stone-500 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>মূল ওয়েবসাইটে ফিরে যান</span>
            </a>

            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-white border border-[#d8d3c7] text-[11px] font-mono text-stone-600 shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>SYSTEM ACTIVE</span>
            </div>
        </div>
    </nav>

    <!-- Main Workspace Container -->
    <main class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 my-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center">

            <!-- LEFT COLUMN: Operations Cockpit & Showcase (Desktop: 7 Cols) -->
            <div class="hidden lg:flex flex-col justify-center lg:col-span-7 pr-4">
                
                <!-- Brand Badge -->
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded border border-[#e7e3da] bg-[#f4f1ea] text-stone-700 text-[11px] font-mono w-fit mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-stone-900"></span>
                    <span>ONTOMEEL ADMIN HUB v2.5</span>
                </div>

                <!-- Main Branding Heading -->
                <div class="flex items-center gap-3 mb-4">
                    <img src="../../assets/img/logo.webp" alt="অন্ত্যমিল লোগো" class="w-10 h-auto">
                    <h1 class="font-serif text-3xl font-bold tracking-wide text-stone-900">
                        অন্ত্যমিল<span class="text-stone-400">.</span>
                    </h1>
                </div>

                <h2 class="text-2xl font-anek font-bold text-stone-900 leading-snug mb-3">
                    বুকস্টোর ও ডিজিটাল লাইব্রেরি পরিচালনার <br>
                    <span class="text-stone-600">প্রশাসনিক কন্ট্রোল কনসোল</span>
                </h2>

                <p class="text-stone-600 text-xs font-anek leading-relaxed mb-6 max-w-md">
                    রিয়েলটাইম অর্ডার প্রসেসিং, মেম্বারশিপ সাবস্ক্রিপশন নিয়ন্ত্রণ, বইয়ের স্টক ও পেমেন্ট অডিট—সকল কার্যক্রম নিরাপদে পরিচালনা করুন।
                </p>

                <!-- Operations Feature Row -->
                <div class="grid grid-cols-3 gap-3 max-w-lg">
                    <div class="bg-white border border-[#e7e3da] p-3.5 rounded-xl shadow-xs">
                        <div class="text-stone-500 text-xs font-mono font-bold mb-1">01. ORDERS</div>
                        <h4 class="text-xs font-semibold font-anek text-stone-900">অর্ডার অডিট</h4>
                        <p class="text-[10px] text-stone-500 font-anek">তাত্ক্ষণিক ট্র্যাকিং</p>
                    </div>
                    <div class="bg-white border border-[#e7e3da] p-3.5 rounded-xl shadow-xs">
                        <div class="text-stone-500 text-xs font-mono font-bold mb-1">02. MEMBERS</div>
                        <h4 class="text-xs font-semibold font-anek text-stone-900">মেম্বারশিপ</h4>
                        <p class="text-[10px] text-stone-500 font-anek">সাবস্ক্রিপশন নিয়ন্ত্রণ</p>
                    </div>
                    <div class="bg-white border border-[#e7e3da] p-3.5 rounded-xl shadow-xs">
                        <div class="text-stone-500 text-xs font-mono font-bold mb-1">03. AUDIT</div>
                        <h4 class="text-xs font-semibold font-anek text-stone-900">পেমেন্ট গেটওয়ে</h4>
                        <p class="text-[10px] text-stone-500 font-anek">SSL ও ম্যানুয়াল ভেরিফাই</p>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="flex items-center gap-2 text-stone-500 text-[11px] font-anek mt-6">
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>শুধুমাত্র অনুমোদিত প্রশাসনিক কর্মকর্তাদের জন্য সংরক্ষিত ওয়ার্কস্টেশন</span>
                </div>
            </div>

            <!-- RIGHT COLUMN: Login Terminal (Desktop: 5 Cols) -->
            <div class="w-full lg:col-span-5 max-w-md mx-auto">
                
                <!-- Mobile Header (< lg) -->
                <div class="lg:hidden flex flex-col items-center mb-6 text-center">
                    <img src="../../assets/img/logo.webp" alt="অন্ত্যমিল লোগো" class="w-10 h-auto mb-2">
                    <h2 class="font-serif text-2xl font-bold tracking-wide text-stone-900">অন্ত্যমিল<span class="text-stone-400">.</span></h2>
                    <p class="text-stone-500 text-[10px] font-mono uppercase tracking-wider mt-0.5">ADMIN SECURE CONSOLE</p>
                </div>

                <!-- Login Card -->
                <div class="bg-white border border-[#e7e3da] p-6 sm:p-7 rounded-2xl shadow-sm">
                    
                    <!-- Card Header -->
                    <div class="mb-5 pb-3 border-b border-[#e7e3da] flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-anek font-semibold text-stone-900">প্রশাসনিক প্রবেশ</h3>
                            <p class="text-xs text-stone-500 font-anek mt-0.5">প্রবেশ করতে ক্রেডেনশিয়াল দিন</p>
                        </div>
                        <span class="text-xs font-mono text-stone-600 bg-[#f4f1ea] px-2 py-0.5 rounded border border-[#e7e3da]">AUTH</span>
                    </div>

                    <!-- Alerts & Notifications -->
                    <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5" role="alert">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-xs text-emerald-800 font-anek">নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন।</p>
                        </div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2.5" role="alert">
                            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs text-red-700 font-anek">
                                <?php
                                if ($_GET['error'] == 'invalid')
                                    echo "ভুল ইউজারনেম বা পাসওয়ার্ড!";
                                elseif ($_GET['error'] == 'empty')
                                    echo "সবগুলো ঘর পূরণ করুন!";
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
                        
                        <!-- Username -->
                        <div class="space-y-1.5">
                            <label for="admin_username" class="block text-xs font-anek text-stone-600">
                                অ্যাডমিন ইউজারনেম
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="admin_username"
                                    name="username" 
                                    required 
                                    autocomplete="username"
                                    placeholder="admin_user"
                                    class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center">
                                <label for="admin_password" class="block text-xs font-anek text-stone-600">
                                    পাসওয়ার্ড
                                </label>
                                <a href="../recover/" class="text-[11px] font-anek text-stone-500 hover:text-stone-900 transition-colors">
                                    পাসওয়ার্ড রিকভারি
                                </a>
                            </div>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="admin_password"
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-3 pr-9 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                                <button 
                                    type="button" 
                                    id="toggle-admin-password" 
                                    aria-label="পাসওয়ার্ড দেখুন বা লুকান"
                                    onclick="toggleAdminPasswordVisibility('admin_password')"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 focus:outline-none transition-colors cursor-pointer">
                                    <svg id="eye-icon-admin" class="w-3.5 h-3.5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-off-icon-admin" class="w-3.5 h-3.5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Session -->
                        <div class="flex items-center gap-2 pt-1">
                            <input 
                                type="checkbox" 
                                id="remember_admin" 
                                name="remember_admin"
                                class="w-3.5 h-3.5 rounded border-[#d8d3c7] bg-white text-stone-900 focus:ring-0 accent-stone-900 cursor-pointer">
                            <label for="remember_admin" class="text-xs text-stone-600 font-anek cursor-pointer select-none">
                                এই ডিভাইসে সেশন মনে রাখুন
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button 
                                type="submit"
                                class="w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                <span>কন্ট্রোল প্যানেলে প্রবেশ</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Notice -->
                <div class="text-center mt-4 text-[11px] text-stone-500 font-anek flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>অননুমোদিত প্রবেশ চেষ্টা স্বয়ংক্রিয়ভাবে অডিট লগে রেকর্ড হয়</span>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-stone-500 text-[11px] font-mono border-t border-[#e7e3da]">
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
