<!-- Footer -->
<footer class="bg-brand-900 text-white pt-20 pb-10 border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <div class="md:col-span-1">
                <a href="<?php echo $path_prefix ?? ''; ?>index.php" class="flex items-center gap-2 mb-6">
                    <img src="<?php echo $path_prefix ?? ''; ?>assets/img/logo.png" alt="logo of ontomeel"
                        class="w-12 h-auto">
                    <span class="font-serif text-3xl font-bold tracking-wide mt-1">অন্ত্যমিল<span
                            class="text-brand-gold">.</span></span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                    একটি প্রিমিয়াম বুকস্টোর এবং লাইব্রেরির অপূর্ব মেলবন্ধন। আপনার পছন্দের গল্পগুলো খুঁজে নিন আমাদের
                    কাছে।
                </p>
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
                    <li><a href="#" class="hover:text-brand-gold transition-colors">সাধারণ জিজ্ঞাসা (FAQ)</a></li>
                    <li><a href="#" class="hover:text-brand-gold transition-colors">ডেলিভারি ও রিটার্ন</a></li>
                    <li><a href="#" class="hover:text-brand-gold transition-colors">যোগাযোগ</a></li>
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

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">© Developed by: <a href="https://www.facebook.com/vivagodigital/"
                    target="_blank" class="hover:text-brand-gold transition-colors">Vivago Digital</a></p>
            <div class="flex space-x-4 text-gray-400">
                <a href="#" class="hover:text-brand-gold transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                    </svg>
                </a>
                <a href="#" class="hover:text-brand-gold transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.708-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                    </svg>
                </a>
            </div>
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