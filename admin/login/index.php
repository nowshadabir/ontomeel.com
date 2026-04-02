<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | অন্ত্যমিল</title>

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

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">

    <!-- Custom JS -->
    <script src="../../assets/js/script.js" defer></script>
</head>

<body
    class="antialiased selection:bg-brand-gold selection:text-white bg-brand-900 min-h-screen flex items-center justify-center relative overflow-hidden py-10 md:py-20 px-4 sm:px-6">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-30"></div>
    <div class="absolute top-[-20%] right-[-10%] w-[100%] sm:w-[60%] h-[100%] sm:h-[60%] bg-brand-gold/10 blur-[100px] sm:blur-[150px] rounded-full"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[100%] sm:w-[60%] h-[100%] sm:h-[60%] bg-brand-gold/5 blur-[100px] sm:blur-[150px] rounded-full"></div>

    <div class="max-w-[480px] w-full relative z-10 transition-all duration-300">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-10 md:mb-12 reveal">
            <img src="../../assets/img/logo.webp" alt="logo" class="w-16 sm:w-20 h-auto mb-6 drop-shadow-2xl">
            <h1 class="font-serif text-3xl sm:text-4xl font-bold tracking-wide text-white">অন্ত্যমিল<span
                    class="text-brand-gold">.</span></h1>
            <p class="text-brand-gold text-[10px] font-bold uppercase tracking-[0.4em] mt-3">অ্যাডমিনিস্ট্রেশন</p>
        </div>

        <!-- Login Card -->
        <div class="glass-dark p-8 sm:p-12 rounded-[40px] border border-white/10 shadow-2xl reveal"
            style="animation-delay: 100ms;">
            <div class="mb-10 text-center">
                <h2 class="text-2xl sm:text-3xl font-anek font-bold text-white">অ্যাডমিন পোর্টাল</h2>
                <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                    <p class="text-brand-gold text-sm font-bold mt-3 bg-white/5 py-3 rounded-2xl">নিবন্ধন সফল! এখন লগইন করুন।
                    </p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400 text-sm font-bold mt-3 bg-red-500/10 py-3 rounded-2xl">
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
                <?php else: ?>
                    <p class="text-gray-400 text-xs mt-3 font-anek">আপনার ক্রেডেনশিয়াল ব্যবহার করে প্রবেশ করুন</p>
                <?php endif; ?>
            </div>

            <form action="process_admin_login.php" method="POST" class="space-y-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek ml-2">অ্যাডমিন
                        আইডি / ইমেইল</label>
                    <input type="text" name="username" required placeholder="admin_user"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-8 py-5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-base">
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-2">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek">সিকিউরিটি
                            কী</label>
                        <a href="../recover/" class="text-[10px] font-bold text-brand-gold uppercase tracking-widest font-anek hover:underline underline-offset-4 decoration-1">পাসওয়ার্ড পুনরুদ্ধার</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-8 py-5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-base">
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-5 sm:py-6 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-2xl shadow-brand-gold/20 flex items-center justify-center gap-4 group">
                        ড্যাশবোর্ডে প্রবেশ
                        <svg class="w-6 h-6 group-hover:translate-x-2 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
                
                <div class="pt-2 text-center">
                    <p class="text-gray-500 text-sm font-anek">অ্যাকাউন্ট নেই? <a href="../signup/" class="text-brand-gold font-bold hover:underline">নিবন্ধন করুন</a></p>
                </div>
            </form>
        </div>

        <div class="flex flex-col items-center gap-6 mt-12 mb-10">
            <p class="text-center text-gray-600 text-[10px] uppercase tracking-widest font-anek">সুরক্ষিত এলাকা: অননুমোদিত প্রবেশ নিষিদ্ধ</p>
            <a href="../../" class="group flex items-center gap-2 text-[11px] text-gray-500 hover:text-brand-gold transition-all font-bold uppercase tracking-widest font-anek">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                মূল ওয়েবসাইটে ফিরে যান
            </a>
        </div>
    </div>

</body>

</html>
