<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../assets/js/tailwind-config.js"></script>

    <!-- Custom JS -->
    <script src="../assets/js/script.js" defer></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body
    class="antialiased selection:bg-brand-gold selection:text-white bg-brand-light min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-20"></div>
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-gold/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-brand-900/10 blur-[120px] rounded-full"></div>

    <div
        class="max-w-7xl mx-auto w-full px-6 flex flex-col md:flex-row items-center justify-between gap-20 relative z-10 py-20">

        <!-- Branding Side -->
        <div class="hidden md:block w-1/2">
            <a href="../index.php" class="flex items-center gap-3 mb-10 reveal">
                <img src="../assets/img/logo.png" alt="logo" class="w-16 h-auto">
                <span class="font-serif text-4xl font-bold tracking-wide text-brand-900 mt-1">অন্ত্যমিল<span
                        class="text-brand-gold">.</span></span>
            </a>
            <h1 class="text-5xl lg:text-6xl font-anek font-extrabold text-brand-900 leading-tight mb-8 reveal"
                style="animation-delay: 100ms;">আপনার পছন্দের বইয়ের <br> জগতে ফিরুন</h1>
            <p class="text-gray-500 text-lg font-light max-w-md reveal" style="animation-delay: 200ms;">অন্ত্যমিল
                মেম্বার হিসেবে লগইন করুন এবং আপনার বুকশেলফ ম্যানেজ করুন সহজেই।</p>
        </div>

        <!-- Login Card -->
        <div class="w-full md:w-[450px] reveal" style="animation-delay: 300ms;">
            <div
                class="glass-dark md:bg-white p-8 md:p-12 rounded-[40px] shadow-2xl border border-white/20 md:border-gray-100 backdrop-blur-3xl">
                <!-- Mobile Logo -->
                <div class="md:hidden flex flex-col items-center mb-10">
                    <img src="../assets/img/logo.png" alt="logo" class="w-12 h-auto mb-4">
                    <h2 class="text-3xl font-anek font-bold text-white">লগইন করুন</h2>
                </div>

                <div class="hidden md:block mb-10 text-center">
                    <h2 class="text-3xl font-anek font-bold text-brand-900">লগইন</h2>
                    <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                        <p class="text-green-600 text-sm font-bold mt-2 bg-green-50 py-2 rounded-xl">নিবন্ধন সফল হয়েছে! এখন
                            লগইন করুন।</p>
                    <?php elseif (isset($_GET['error'])): ?>
                        <?php if ($_GET['error'] == 'invalid'): ?>
                            <p class="text-red-600 text-sm font-bold mt-2 bg-red-50 py-2 rounded-xl">ভুল ইমেইল বা পাসওয়ার্ড!</p>
                        <?php elseif ($_GET['error'] == 'empty'): ?>
                            <p class="text-red-600 text-sm font-bold mt-2 bg-red-50 py-2 rounded-xl">সবগুলো ঘর পূরণ করুন।</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-400 text-sm mt-2">আপনার অ্যাকাউন্টে প্রবেশ করুন</p>
                    <?php endif; ?>
                </div>

                <form action="process_login.php" method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="text-xs font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">ইমেইল
                            বা মোবাইল</label>
                        <div class="relative group">
                            <input type="text" name="login_id" required placeholder="example@mail.com"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                            <svg class="w-5 h-5 absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-brand-gold transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-2">
                            <label
                                class="text-xs font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek">পাসওয়ার্ড</label>
                            <a href="#"
                                class="text-[10px] text-brand-gold font-bold uppercase hover:underline">পাসওয়ার্ড ভুলে
                                গেছেন?</a>
                        </div>
                        <div class="relative group">
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                            <svg class="w-5 h-5 absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-brand-gold transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 ml-2">
                        <input type="checkbox" id="remember"
                            class="w-4 h-4 rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
                        <label for="remember"
                            class="text-xs text-gray-400 md:text-gray-500 font-medium font-anek uppercase tracking-wider">তথ্য
                            মনে রাখুন</label>
                    </div>

                    <button type="submit"
                        class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10 flex items-center justify-center gap-3 group">
                        প্রবেশ করুন
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <div class="pt-6 text-center border-t border-gray-50 mt-6 font-anek">
                        <p class="text-gray-400 text-sm">নতুন ইউজার? <a href="../signup/"
                                class="text-brand-gold font-bold hover:underline">রেজিস্ট্রেশন করুন</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>