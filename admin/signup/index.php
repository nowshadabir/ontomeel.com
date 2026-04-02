<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন রেজিস্ট্রেশন | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration -->
    <script src="../../assets/js/tailwind-config.js"></script>

    <!-- Custom JS -->
    <script src="../../assets/js/script.js" defer></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body
    class="antialiased selection:bg-brand-gold selection:text-white bg-brand-900 min-h-screen flex items-center justify-center relative overflow-x-hidden py-8 md:py-12 px-4 sm:px-6">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-30"></div>
    <div class="absolute top-[-10%] right-[-10%] w-[100%] sm:w-[60%] h-[100%] sm:h-[60%] bg-brand-gold/10 blur-[100px] rounded-full"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[100%] sm:w-[60%] h-[100%] sm:h-[60%] bg-brand-gold/5 blur-[100px] rounded-full"></div>

    <div class="max-w-xl w-full relative z-10 transition-all duration-300">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8 md:mb-10 reveal">
            <img src="../../assets/img/logo.webp" alt="logo" class="w-12 sm:w-16 h-auto mb-4 drop-shadow-2xl">
            <h1 class="font-serif text-2xl sm:text-3xl font-bold tracking-wide text-white">অন্ত্যমিল<span
                    class="text-brand-gold">.</span></h1>
            <p class="text-brand-gold text-[8px] sm:text-[10px] font-bold uppercase tracking-[0.4em] mt-2">অ্যাডমিন এনরোলমেন্ট</p>
        </div>

        <!-- Signup Card -->
        <div class="glass-dark p-6 sm:p-10 rounded-[35px] sm:rounded-[40px] border border-white/10 shadow-2xl reveal"
            style="animation-delay: 100ms;">
            <div class="mb-8 md:mb-10 text-center">
                <h2 class="text-xl sm:text-2xl font-anek font-bold text-white">নতুন অ্যাডমিন অ্যাকাউন্ট</h2>
                <p class="text-gray-400 text-[10px] sm:text-xs mt-2 font-anek">সতর্কতার সাথে ফর্মটি পূরণ করুন</p>
            </div>

            <form action="process_admin_signup.php" method="POST" class="space-y-5 sm:space-y-6">
                <!-- Group 1: General Info -->
                <div class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">সম্পূর্ণ নাম</label>
                        <input type="text" name="full_name" required placeholder="আপনার পূর্ণ নাম লিখুন"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-sm sm:text-base placeholder:text-gray-800">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">ইউজারনেম</label>
                            <input type="text" name="username" required placeholder="admin_one"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-sm sm:text-base placeholder:text-gray-800">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">পদবী (Role)</label>
                            <div class="relative group">
                                <select name="role"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white appearance-none text-sm sm:text-base">
                                    <option value="Editor" class="bg-brand-900 border-none">Editor</option>
                                    <option value="Manager" class="bg-brand-900 border-none">Manager</option>
                                    <option value="SuperAdmin" class="bg-brand-900 border-none">Super Admin</option>
                                </select>
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-brand-gold transition-transform group-focus-within:rotate-180">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Account Security -->
                <div class="space-y-5 pt-2 sm:pt-4 border-t border-white/5">
                    <div class="space-y-1.5">
                        <label class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">অফিসিয়াল ইমেইল</label>
                        <input type="email" name="email" required placeholder="admin@ontomeel.com"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-sm sm:text-base placeholder:text-gray-800">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-sm sm:text-base placeholder:text-gray-800">
                    </div>
                </div>

                <div class="pt-4 sm:pt-6">
                    <button type="submit"
                        class="w-full py-4 sm:py-5 bg-brand-gold text-brand-900 font-anek font-bold text-base sm:text-lg rounded-2xl hover:bg-white transition-all duration-300 shadow-xl shadow-brand-gold/20 flex items-center justify-center gap-3 group">
                        অ্যাডমিন যুক্ত হোন
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <div class="pt-6 text-center border-t border-white/10 mt-6 font-anek">
                    <p class="text-gray-500 text-xs sm:text-sm">ইতিমধ্যে অ্যাকাউন্ট আছে? 
                        <a href="../login/" class="text-brand-gold font-bold hover:underline underline-offset-4 transition-all">লগইন করুন</a>
                    </p>
                </div>
            </form>
        </div>

        <div class="flex flex-col items-center gap-4 mt-6 md:mt-8 mb-6">
            <p class="text-center text-gray-600 text-[9px] sm:text-[10px] uppercase tracking-widest font-anek">নিবন্ধন শুধুমাত্র অনুমোদিত স্টাফদের জন্য</p>
            <a href="../../" class="group flex items-center gap-2 text-[10px] text-gray-500 hover:text-brand-gold transition-all font-bold uppercase tracking-widest font-anek">
                <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                মূল ওয়েবসাইট
            </a>
        </div>
    </div>

</body>

</html>