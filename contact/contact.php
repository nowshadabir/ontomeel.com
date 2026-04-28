<?php
$page_title = 'যোগাযোগ | অন্ত্যমিল - আমাদের সাথে যুক্ত হোন';
$page_description = 'অন্ত্যমিলের সাথে যোগাযোগ করুন। আমাদের ঠিকানা: শপ নং ৬, চেইঞ্জিং ক্লোজেট বিল্ডিং, মোটেল লাবণী রোড, কক্সবাজার। ফোন: +৮৮০১৩৩০৯৭৫৭৮৭';
$page_keywords = 'যোগাযোগ, অন্ত্যমিল ঠিকানা, Ontomeel Contact, কক্সবাজার বুকশপ, বইয়ের দোকান';
$path_prefix = '../';
include '../includes/db_connect.php';
include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 overflow-hidden bg-brand-900">
    <div class="mesh-gradient absolute inset-0"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-brand-900/50 via-brand-900 to-brand-900"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <span class="inline-block px-4 py-1.5 mb-6 text-xs font-bold tracking-[0.2em] text-brand-gold uppercase border border-brand-gold/30 rounded-full bg-brand-gold/5 animate-slide-up">
            আমাদের সাথে যোগাযোগ করুন
        </span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 animate-slide-up" style="animation-delay: 0.2s">
            আপনার জিজ্ঞাসা, আমাদের <br> <span class="text-gradient-gold">অনুপ্রেরণা</span>
        </h1>
        <p class="max-w-2xl mx-auto text-gray-400 text-lg font-light animate-slide-up" style="animation-delay: 0.4s">
            বই নিয়ে যেকোনো পরামর্শ, অভিযোগ বা জিজ্ঞাসার জন্য আমাদের সাথে সরাসরি যোগাযোগ করুন। আমরা সব সময় আপনার পাশে আছি।
        </p>
    </div>
</section>

<!-- Contact Content -->
<section class="relative z-20 mt-10 pb-24 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Contact Info Cards -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Location -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-500 reveal active">
                    <div class="w-12 h-12 bg-brand-gold/10 rounded-2xl flex items-center justify-center text-brand-gold mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">আমাদের অবস্থান</h3>
                    <p class="text-gray-600 font-light leading-relaxed">
                        শপ নং ৬, চেইঞ্জিং ক্লোজেট বিল্ডিং,<br>
                        মোটেল লাবণী রোড, কক্সবাজার।
                    </p>
                </div>

                <!-- Phone & Email -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-500 reveal active" style="transition-delay: 100ms">
                    <div class="w-12 h-12 bg-brand-gold/10 rounded-2xl flex items-center justify-center text-brand-gold mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">ফোন ও ইমেইল</h3>
                    <div class="space-y-2">
                        <a href="tel:+8801330975787" class="block text-gray-600 hover:text-brand-gold transition-colors font-sans">+৮৮০১৩৩০৯৭৫৭৮৭</a>
                        <a href="mailto:info@ontomeel.com" class="block text-gray-600 hover:text-brand-gold transition-colors font-sans">info@ontomeel.com</a>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="bg-brand-900 p-8 rounded-3xl shadow-2xl reveal active" style="transition-delay: 200ms">
                    <h3 class="text-xl font-serif text-white mb-6">সোশ্যাল মিডিয়ায় আমরা</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="https://facebook.com/ontomeel" target="_blank" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-white hover:bg-brand-gold hover:text-brand-900 transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://instagram.com/ontomeel" target="_blank" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-white hover:bg-brand-gold hover:text-brand-900 transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.012 3.845.06 1.157.052 1.93.242 2.37.412.585.226 1.082.528 1.57.92.54.43.91.82 1.25 1.43.23.41.4 1.05.5 2.22.1 1.08.11 1.41.11 3.55s-.01 2.47-.11 3.55c-.1 1.17-.27 1.81-.5 2.22-.34.61-.71 1-1.25 1.43-.49.39-.98.69-1.57.92-.44.17-1.213.36-2.37.41-1.06.05-1.415.06-3.845.06s-2.784-.01-3.845-.06c-1.157-.05-1.93-.24-2.37-.41-.585-.226-1.082-.528-1.57-.92-.54-.43-.91-.82-1.25-1.43-.23-.41-.4-1.05-.5-2.22-.1-1.08-.11-1.41-.11-3.55s.01-2.47.11-3.55c.1-1.17.27-1.81.5-2.22.34-.61.71-1 1.25-1.43.49-.39.98-.69 1.57-.92.44-.17-1.213-.36 2.37-.41 1.06-.05 1.415-.06 3.845-.06zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8a3 3 0 110-6 3 3 0 010 6zm5.885-9.35a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z"/></svg>
                        </a>
                        <a href="https://wa.me/8801330975787" target="_blank" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-white hover:bg-brand-gold hover:text-brand-900 transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.659 1.437 5.634 1.437h.005c6.558 0 11.894-5.335 11.897-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 reveal active">
                    <h2 class="text-3xl font-serif text-brand-900 mb-8">আমাদের একটি বার্তা পাঠান</h2>
                    <form id="contactForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name" class="text-sm font-bold text-gray-400 uppercase tracking-wider">আপনার নাম</label>
                                <input type="text" id="name" name="name" required placeholder="নাম লিখুন"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all">
                            </div>
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-bold text-gray-400 uppercase tracking-wider">ইমেইল ঠিকানা</label>
                                <input type="email" id="email" name="email" required placeholder="example@email.com"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="subject" class="text-sm font-bold text-gray-400 uppercase tracking-wider">বিষয়</label>
                            <input type="text" id="subject" name="subject" required placeholder="বার্তার বিষয়"
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all">
                        </div>
                        <div class="space-y-2">
                            <label for="message" class="text-sm font-bold text-gray-400 uppercase tracking-wider">আপনার বার্তা</label>
                            <textarea id="message" name="message" rows="5" required placeholder="বিস্তারিত লিখুন..."
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all resize-none"></textarea>
                        </div>
                        <button type="submit" class="w-full py-5 bg-brand-gold text-brand-900 font-bold text-lg rounded-2xl hover:bg-brand-900 hover:text-white transition-all duration-500 shadow-lg shadow-brand-gold/20 transform hover:-translate-y-1">
                            বার্তা পাঠান
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-20 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-serif text-brand-900 mb-4">আমাদের ঠিকানা খুঁজুন</h2>
            <div class="w-20 h-1.5 bg-brand-gold mx-auto rounded-full"></div>
        </div>
        
        <div class="rounded-3xl overflow-hidden shadow-2xl border-8 border-white h-[450px] relative">
            <!-- Embedded Google Map -->
           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3713.9854656636735!2d91.97195407498602!3d21.429816273883525!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30adc941d9a7dff9%3A0xb1735e9ebbddf782!2sontomeel!5e0!3m2!1sen!2sbd!4v1777375639280!5m2!1sen!2sbd" style="border:0; width: 100%; height: 100%;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const btn = form.querySelector('button');
    const originalBtnText = btn.innerText;
    
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3 inline-block" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> পাঠানো হচ্ছে...';
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('../api/send-message.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            form.innerHTML = `
                <div class="text-center py-12 animate-slide-up">
                    <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-serif text-brand-900 mb-2">${result.message}</h3>
                    <p class="text-gray-500">আমরা সাধারণত ২৪ ঘণ্টার মধ্যে উত্তর দিয়ে থাকি।</p>
                    <button onclick="location.reload()" class="mt-8 text-brand-gold font-bold hover:underline">আরেকটি বার্তা পাঠান</button>
                </div>
            `;
        } else {
            alert(result.message);
            btn.disabled = false;
            btn.innerText = originalBtnText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('দুঃখিত, কোনো একটি সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        btn.disabled = false;
        btn.innerText = originalBtnText;
    }
});
</script>
