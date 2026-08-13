<!-- Footer -->
<footer class="bg-brand-900 text-white pt-20 pb-10 border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <div class="md:col-span-1">
                <a href="<?php echo $path_prefix ?? ''; ?>index.php" class="flex items-center gap-2 mb-6">
                    <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.webp" alt="logo of ontomeel"
                        class="w-12 h-auto">
                    <span class="font-serif text-3xl font-bold tracking-wide mt-1">অন্ত্যমিল<span
                            class="text-brand-gold">.</span></span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">
                    একটি প্রিমিয়াম বুকস্টোর এবং লাইব্রেরির অপূর্ব মেলবন্ধন। আপনার পছন্দের গল্পগুলো খুঁজে নিন আমাদের কাছে।
                </p>
                <div class="text-xs text-gray-400 space-y-2 border-t border-gray-800 pt-4 mb-6">
                    <p><strong class="text-gray-300 font-medium">নিবন্ধিত ঠিকানা:</strong> আজিজুল হক রোড, পশ্চিম জয়দেবপুর, গাজীপুর সদর, গাজীপুর–১৭০০।</p>
                    <p><strong class="text-gray-300 font-medium">ডেলিভারি সময়:</strong> ঢাকার ভেতরে ৫ দিন | ঢাকার বাইরে ১০ দিন</p>
                </div>
            </div>

            <div>
                <h4 class="font-serif text-xl font-bold mb-6 text-brand-gold_light">এক্সপ্লোর</h4>
                <ul class="space-y-3 text-sm text-gray-400 font-anek">
                    <li><a href="<?php echo $path_prefix ?? ''; ?>category/index.php"
                            class="hover:text-brand-gold transition-colors">ক্যাটাগরি</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>library/index.php"
                            class="hover:text-brand-gold transition-colors">লাইব্রেরি</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>membership/index.php"
                            class="hover:text-brand-gold transition-colors">মেম্বারশিপ</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>pre-booking/index.php"
                            class="hover:text-brand-gold transition-colors">প্রি-বুকিং</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-serif text-xl font-bold mb-6 text-brand-gold_light">সাপোর্ট</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="<?php echo $path_prefix ?? ''; ?>about.php" class="hover:text-brand-gold transition-colors">আমাদের সম্পর্কে</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>terms.php" class="hover:text-brand-gold transition-colors">শর্তাবলী</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>privacy.php" class="hover:text-brand-gold transition-colors">প্রাইভেসি পলিসি</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>refund.php" class="hover:text-brand-gold transition-colors">রিটার্ন ও রিফান্ড</a></li>
                    <li><a href="<?php echo $path_prefix ?? ''; ?>contact/contact.php" class="hover:text-brand-gold transition-colors">যোগাযোগ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-serif text-xl font-bold mb-6 text-brand-gold_light">নিউজলেটার</h4>
                <p class="text-gray-400 text-sm mb-4">নতুন বই ও বিশেষ অফারের খবর পেতে সাবস্ক্রাইব করুন।</p>
                <div class="flex">
                    <input type="email" placeholder="আপনার ইমেইল"
                        class="bg-gray-800 text-white px-4 py-2 w-full focus:outline-none focus:ring-1 focus:ring-brand-gold rounded-l-sm text-sm border border-gray-700 font-sans">
                    <button
                        class="bg-brand-gold text-brand-900 px-4 py-2 rounded-r-sm hover:bg-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Banner -->
        <div class="mb-12 flex justify-center">
            <img src="<?php echo $path_prefix ?? ''; ?>assets/img/footer_banner.png" alt="Payment Banner" class="w-full h-auto object-contain rounded-lg">
        </div>

        <div class="border-t border-gray-800 pt-8 flex justify-center items-center">
            <p class="text-gray-500 text-sm text-center">© Developed by: <a href="https://vivagotechnologies.com"
                    target="_blank" class="hover:text-brand-gold transition-colors">VIVAGO TECHNOLOGIES</a></p>
        </div>
    </div>
</footer>

<!-- UI Message Toast -->
<div id="toast"
    class="fixed bottom-5 right-5 bg-brand-900 text-white px-6 py-3 rounded shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-[70] flex items-center gap-3 border border-gray-700">
    <svg class="w-5 h-5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span id="toast-msg" class="text-sm font-medium font-sans"></span>
</div>
</body>

</html>