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
    class="antialiased selection:bg-brand-gold selection:text-white bg-brand-900 min-h-screen flex items-center justify-center relative overflow-hidden py-20">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-30"></div>
    <div class="absolute top-[-20%] right-[-10%] w-[60%] h-[60%] bg-brand-gold/10 blur-[150px] rounded-full"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[60%] h-[60%] bg-brand-gold/5 blur-[150px] rounded-full"></div>

    <div class="max-w-xl w-full px-6 relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-12 reveal">
            <img src="../../assets/img/logo.png" alt="logo" class="w-16 h-auto mb-4 drop-shadow-2xl">
            <h1 class="font-serif text-3xl font-bold tracking-wide text-white">অন্ত্যমিল<span
                    class="text-brand-gold">.</span></h1>
            <p class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.4em] mt-2">অ্যাডমিন এনরোলমেন্ট</p>
        </div>

        <!-- Signup Card -->
        <div class="glass-dark p-8 md:p-12 rounded-[40px] border border-white/10 shadow-2xl reveal"
            style="animation-delay: 100ms;">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-anek font-bold text-white">নতুন অ্যাডমিন অ্যাকাউন্ট</h2>
                <p class="text-gray-400 text-xs mt-2 font-anek">সতর্কতার সাথে ফর্মটি পূরণ করুন</p>
            </div>

            <form action="process_admin_signup.php" method="POST" class="space-y-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">সম্পূর্ণ
                        নাম</label>
                    <input type="text" name="full_name" required placeholder="Ex: Admin User"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">ইউজারনেম</label>
                        <input type="text" name="username" required placeholder="admin_one"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">পদবী
                            (Role)</label>
                        <select name="role"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white appearance-none">
                            <option value="Editor" class="bg-brand-900">Editor</option>
                            <option value="Manager" class="bg-brand-900">Manager</option>
                            <option value="SuperAdmin" class="bg-brand-900">Super Admin</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">অফিসিয়াল
                        ইমেইল</label>
                    <input type="email" name="email" required placeholder="admin@ontomeel.com"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white">
                </div>

                <div class="space-y-1">
                    <label
                        class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white">
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-5 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white transition-all duration-300 shadow-xl shadow-brand-gold/20 flex items-center justify-center gap-3 group">
                        অ্যাডমিন হিসেবে যুক্ত হোন
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <div class="pt-6 text-center border-t border-white/10 mt-6 font-anek">
                    <p class="text-gray-500 text-sm">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="../login/"
                            class="text-brand-gold font-bold hover:underline">লগইন করুন</a></p>
                </div>
            </form>
        </div>

        <p class="text-center text-gray-500 text-[10px] mt-10 uppercase tracking-widest font-anek">সুরক্ষিত এলাকা:
            নিবন্ধন শুধুমাত্র অনুমোদিত স্টাফদের জন্য</p>
    </div>

</body>

</html>