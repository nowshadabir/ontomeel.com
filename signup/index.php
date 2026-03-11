<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রেজিস্ট্রেশন | অন্ত্যমিল</title>

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
    class="antialiased selection:bg-brand-gold selection:text-white bg-brand-light min-h-screen flex items-center justify-center relative overflow-hidden py-10 md:py-20">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-20"></div>
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-gold/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-brand-900/10 blur-[120px] rounded-full"></div>

    <div
        class="max-w-7xl mx-auto w-full px-6 flex flex-col md:flex-row items-center justify-between gap-20 relative z-10">

        <!-- Branding Side -->
        <div class="hidden md:block w-1/2">
            <a href="../index.php" class="flex items-center gap-3 mb-10 reveal">
                <img src="../assets/img/logo.png" alt="logo" class="w-16 h-auto">
                <span class="font-serif text-4xl font-bold tracking-wide text-brand-900 mt-1">অন্ত্যমিল<span
                        class="text-brand-gold">.</span></span>
            </a>
            <h1 class="text-5xl lg:text-6xl font-anek font-extrabold text-brand-900 leading-tight mb-8 reveal"
                style="animation-delay: 100ms;">গল্পের নতুন <br> অধ্যায় শুরু হোক আপনার</h1>
            <p class="text-gray-500 text-lg font-light max-w-md reveal" style="animation-delay: 200ms;">অন্ত্যমিল
                মেম্বারশিপ নিয়ে আমাদের বিশাল লাইব্রেরি ও এক্সক্লুসিভ কালেকশনে অ্যাক্সেস পান মুহূর্তেই।</p>
        </div>

        <!-- Signup Card -->
        <div class="w-full md:w-[500px] reveal" style="animation-delay: 300ms;">
            <div
                class="glass-dark md:bg-white p-8 md:p-12 rounded-[40px] shadow-2xl border border-white/20 md:border-gray-100 backdrop-blur-3xl">
                <!-- Mobile Logo -->
                <div class="md:hidden flex flex-col items-center mb-10">
                    <img src="../assets/img/logo.png" alt="logo" class="w-12 h-auto mb-4">
                    <h2 class="text-3xl font-anek font-bold text-white">নতুন অ্যাকাউন্ট</h2>
                </div>

                <div class="hidden md:block mb-10 text-center">
                    <h2 class="text-3xl font-anek font-bold text-brand-900">রেজিস্ট্রেশন</h2>
                    <p class="text-gray-400 text-sm mt-2">আপনার সঠিক তথ্য দিয়ে ফরমটি পূরণ করুন</p>
                </div>

                <form action="process_signup.php" method="POST" class="space-y-5">
                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">সম্পূর্ণ
                            নাম (English)</label>
                        <input type="text" name="full_name" required placeholder="Ex: Sayeam Ahmed"
                            class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">ইমেইল</label>
                            <input type="email" name="email" required placeholder="name@mail.com"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">মোবাইল</label>
                            <input type="tel" name="phone" required placeholder="017XXXXXXXX"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                    </div>

                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-anek ml-2">প্ল্যান
                        নির্বাচন করুন (ঐচ্ছিক)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="cursor-pointer group">
                            <input type="radio" name="plan" value="None" class="peer hidden" checked>
                            <div
                                class="px-2 py-3 bg-gray-50 border border-transparent peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 rounded-xl text-center transition-all">
                                <span
                                    class="block text-[10px] font-bold text-gray-400 group-hover:text-brand-900 peer-checked:text-brand-900 font-anek">বিনা
                                    প্ল্যান</span>
                                <span class="block text-xs font-bold text-brand-900">৳০</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="plan" value="General" class="peer hidden">
                            <div
                                class="px-2 py-3 bg-gray-50 border border-transparent peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 rounded-xl text-center transition-all">
                                <span
                                    class="block text-[10px] font-bold text-gray-400 group-hover:text-brand-900 peer-checked:text-brand-900 font-anek">সাধারণ</span>
                                <span class="block text-xs font-bold text-brand-900">৳২০০</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="plan" value="BookLover" class="peer hidden">
                            <div
                                class="px-2 py-3 bg-gray-50 border border-transparent peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 rounded-xl text-center transition-all">
                                <span
                                    class="block text-[10px] font-bold text-gray-400 group-hover:text-brand-900 peer-checked:text-brand-900 font-anek">বইপ্রেমী</span>
                                <span class="block text-xs font-bold text-brand-900">৳৫০০</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="plan" value="Collector" class="peer hidden">
                            <div
                                class="px-2 py-3 bg-gray-50 border border-transparent peer-checked:border-brand-gold peer-checked:bg-brand-gold/5 rounded-xl text-center transition-all">
                                <span
                                    class="block text-[10px] font-bold text-gray-400 group-hover:text-brand-900 peer-checked:text-brand-900 font-anek">সংগ্রাহক</span>
                                <span class="block text-xs font-bold text-brand-900">৳১০০০</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center gap-3 ml-2 py-2">
                        <input type="checkbox" required id="terms"
                            class="w-4 h-4 rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
                        <label for="terms"
                            class="text-[10px] text-gray-400 md:text-gray-500 font-medium font-anek uppercase tracking-wider">আমি
                            সকল শর্তাবলির সাথে একমত</label>
                    </div>

                    <button type="submit"
                        class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10 flex items-center justify-center gap-3 group">
                        একাউন্ট তৈরি করুন
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <div class="pt-6 text-center border-t border-gray-50 mt-6 font-anek">
                        <p class="text-gray-400 text-sm">ইতিমধ্যে একাউন্ট আছে? <a href="../login/"
                                class="text-brand-gold font-bold hover:underline">লগইন করুন</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>