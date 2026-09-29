<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন রেজিস্ট্রেশন | অন্ত্যমিল</title>

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

<body class="antialiased bg-[#faf8f5] text-stone-900 min-h-screen flex items-center justify-center relative py-10 px-4 sm:px-6 selection:bg-stone-200 selection:text-stone-900">

    <div class="max-w-lg w-full relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-6 text-center">
            <img src="../../assets/img/logo.webp" alt="logo" class="w-10 h-auto mb-2">
            <h1 class="font-serif text-2xl font-bold tracking-wide text-stone-900">অন্ত্যমিল<span class="text-stone-400">.</span></h1>
            <p class="text-stone-500 text-[10px] font-mono uppercase tracking-wider mt-1">ADMIN ENROLLMENT</p>
        </div>

        <!-- Signup Card -->
        <div class="bg-white border border-[#e7e3da] p-6 sm:p-8 rounded-2xl shadow-sm">
            <div class="mb-6 pb-3 border-b border-[#e7e3da] text-center">
                <h2 class="text-base font-anek font-semibold text-stone-900">নতুন অ্যাডমিন অ্যাকাউন্ট</h2>
                <p class="text-stone-500 text-xs mt-0.5 font-anek">সতর্কতার সাথে ফর্মটি পূরণ করুন</p>
            </div>

            <form action="process_admin_signup.php" method="POST" class="space-y-4">
                <!-- Full Name -->
                <div class="space-y-1.5">
                    <label class="text-xs font-anek text-stone-600">সম্পূর্ণ নাম</label>
                    <input type="text" name="full_name" required placeholder="আপনার পূর্ণ নাম লিখুন"
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-anek text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Username -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-anek text-stone-600">ইউজারনেম</label>
                        <input type="text" name="username" required placeholder="admin_one"
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                    </div>
                    <!-- Role -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-anek text-stone-600">পদবী (Role)</label>
                        <div class="relative">
                            <select name="role"
                                class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 appearance-none focus:outline-none focus:border-stone-800 transition-colors">
                                <option value="Editor" class="bg-white text-stone-900">Editor</option>
                                <option value="Manager" class="bg-white text-stone-900">Manager</option>
                                <option value="SuperAdmin" class="bg-white text-stone-900">Super Admin</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-stone-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="text-xs font-anek text-stone-600">অফিসিয়াল ইমেইল</label>
                    <input type="email" name="email" required placeholder="info@ontomeel.com"
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label class="text-xs font-anek text-stone-600">পাসওয়ার্ড</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                        <span>অ্যাডমিন যুক্ত করুন</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <div class="pt-4 text-center border-t border-[#e7e3da] mt-4 font-anek">
                    <p class="text-stone-500 text-xs">ইতিমধ্যে অ্যাকাউন্ট আছে? 
                        <a href="../login/" class="text-stone-900 font-semibold hover:underline ml-1">লগইন করুন</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>