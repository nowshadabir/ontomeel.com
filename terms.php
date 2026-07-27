<?php
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn']) ? $_GET['lang'] : 'bn';

if ($lang === 'en') {
    $page_title = 'Terms & Conditions | Ontomeel';
    $page_description = 'Welcome to Ontomeel. By accessing and using this website, you agree to comply with and be bound by the following Terms & Conditions.';
    $page_keywords = 'Terms & Conditions, Ontomeel, Rules, Policies';
} else {
    $page_title = 'শর্তাবলী | অন্ত্যমিল';
    $page_description = 'অন্ত্যমিল-এ স্বাগতম। এই ওয়েবসাইট অ্যাক্সেস এবং ব্যবহার করার মাধ্যমে, আপনি নিচের শর্তাবলীর সাথে সম্মত হচ্ছেন এবং তা মেনে চলতে বাধ্য থাকবেন।';
    $page_keywords = 'শর্তাবলী, Terms & Conditions, Ontomeel, Rules, Policies';
}

$path_prefix = '';
include 'includes/db_connect.php';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 overflow-hidden bg-brand-900">
    <div class="mesh-gradient absolute inset-0"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-brand-900/50 via-brand-900 to-brand-900"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <span class="inline-block px-4 py-1.5 mb-6 text-xs font-bold tracking-[0.2em] text-brand-gold uppercase border border-brand-gold/30 rounded-full bg-brand-gold/5 animate-slide-up">
            <?php echo $lang === 'en' ? 'Our Policies' : 'আমাদের নীতিমালা'; ?>
        </span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 animate-slide-up" style="animation-delay: 0.2s">
            <?php if ($lang === 'en'): ?>
                Terms & <span class="text-gradient-gold">Conditions</span>
            <?php else: ?>
                শর্তাবলী <span class="text-gradient-gold"></span>
            <?php endif; ?>
        </h1>
        <p class="max-w-2xl mx-auto text-gray-400 text-lg font-light animate-slide-up" style="animation-delay: 0.4s">
            <?php echo $lang === 'en' ? 'Last Updated: 26 July 2026' : 'সর্বশেষ আপডেট: ২৬ জুলাই ২০২৬'; ?>
        </p>
    </div>
</section>

<!-- Content -->
<section class="relative z-20 mt-10 pb-24 px-6">
    <div class="max-w-4xl mx-auto">
        <!-- Language Toggle -->
        <div class="flex justify-end mb-6 relative z-30">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <a href="?lang=bn" class="px-4 py-2 text-sm font-medium border rounded-l-lg transition-colors <?php echo $lang == 'bn' ? 'bg-brand-900 text-white border-brand-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:text-brand-900'; ?>">বাংলা</a>
                <a href="?lang=en" class="px-4 py-2 text-sm font-medium border border-l-0 rounded-r-lg transition-colors <?php echo $lang == 'en' ? 'bg-brand-900 text-white border-brand-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:text-brand-900'; ?>">English</a>
            </div>
        </div>

        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 leading-relaxed text-gray-700 space-y-6">
            <?php if ($lang === 'en'): ?>
            <p class="text-lg font-light mb-8">
                Welcome to Ontomeel. By accessing and using this website, you agree to comply with and be bound by the following Terms & Conditions. If you do not agree with any part of these terms, please do not use our website.
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <p><strong class="font-medium text-brand-900">1. General:</strong> These Terms & Conditions govern your use of the Ontomeel website and the purchase of products offered through our A Premium bookstore. We reserve the right to update, modify, or replace these terms at any time. Any changes will be effective immediately upon posting on this page.</p>
                <p><strong class="font-medium text-brand-900">2. Products and Availability:</strong> Ontomeel strives to ensure that all books and products displayed on the website are accurately described and available for purchase. However, product availability is subject to change without notice, and we reserve the right to limit quantities or discontinue products at our sole discretion.</p>
                <p><strong class="font-medium text-brand-900">3. Pricing and Payment:</strong> All prices displayed on the Ontomeel website are subject to change without notice. Payment must be completed before orders are processed and dispatched. While we strive for pricing accuracy, Ontomeel reserves the right to cancel or adjust any order affected by pricing errors and will notify customers accordingly.</p>
                <p><strong class="font-medium text-brand-900">4. Orders:</strong> By placing an order, you make an offer to purchase products from Ontomeel. We reserve the right to accept or reject any order for any reason, including product unavailability, payment issues, or suspected fraudulent activity. Once your order is confirmed, you will receive an email acknowledging receipt of your order.</p>
                <p><strong class="font-medium text-brand-900">5. Shipping and Delivery:</strong> We will make reasonable efforts to process and deliver orders within the estimated timeframe (Inside Dhaka - 5 days & Outside Dhaka - 10 days). Delivery times may vary depending on location and courier services. Ontomeel is not responsible for delays caused by circumstances beyond our control.</p>
                <p><strong class="font-medium text-brand-900">6. Returns and Refunds:</strong> Returns and refunds are subject to our Return & Refund Policy. Customers are encouraged to review the policy before making a purchase.</p>
                <p><strong class="font-medium text-brand-900">7. Intellectual Property:</strong> All content available on the Ontomeel website, including but not limited to text, graphics, logos, images, icons, website design, and software, is the property of Ontomeel or its content providers and is protected by applicable copyright and intellectual property laws. You may not copy, reproduce, distribute, modify, or exploit any content from this website without prior written permission.</p>
                <p><strong class="font-medium text-brand-900">8. User Conduct:</strong> You agree to use the Ontomeel website responsibly and lawfully. Users must not engage in any activity that violates applicable laws or regulations, provide false, inaccurate, or misleading information, interfere with the website's operation, security, or functionality, or attempt to gain unauthorized access to any part of the website, its servers, databases, or related systems. Any misuse of the website may result in the suspension or termination of access and, where applicable, legal action.</p>
                <p><strong class="font-medium text-brand-900">9. Limitation of Liability:</strong> To the fullest extent permitted by applicable law, Ontomeel shall not be liable for any indirect, incidental, consequential, special, or punitive damages arising from your use of the website or any products purchased through it.</p>
                <p><strong class="font-medium text-brand-900">10. Privacy:</strong> Your use of the website is also governed by our Privacy Policy, which explains how we collect, use, and protect your personal information.</p>
                <p><strong class="font-medium text-brand-900">11. Third-Party Links:</strong> Our website may contain links to third-party websites for your convenience. Ontomeel does not endorse and is not responsible for the content, policies, or practices of such websites.</p>
                <p><strong class="font-medium text-brand-900">12. Governing Law:</strong> These Terms & Conditions shall be governed by and construed in accordance with the applicable laws of the jurisdiction in which Ontomeel operates, without regard to conflict of law principles.</p>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                By using this website, you acknowledge that you have read, understood, and agreed to these Terms & Conditions.
            </p>
            <?php else: ?>
            <p class="text-lg font-light mb-8">
                অন্ত্যমিলে আপনাকে স্বাগতম। এই ওয়েবসাইট ব্যবহার, ব্রাউজ বা এর মাধ্যমে অর্ডার করার মাধ্যমে আপনি নিচে বর্ণিত শর্তাবলী মেনে চলতে সম্মত হচ্ছেন। যদি এই শর্তাবলীর কোনো অংশের সঙ্গে আপনার দ্বিমত থাকে, তাহলে অনুগ্রহ করে আমাদের ওয়েবসাইট ব্যবহার থেকে বিরত থাকুন।
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১. সাধারণ শর্ত</h3>
                    <p>এই শর্তাবলী অন্ত্যমিলের ওয়েবসাইট, অনলাইন সেবা এবং আমাদের মাধ্যমে পরিচালিত সকল পণ্য ক্রয়ের ক্ষেত্রে প্রযোজ্য।</p>
                    <p class="mt-2">প্রয়োজন অনুযায়ী আমরা যেকোনো সময় এই শর্তাবলী সংশোধন, পরিবর্তন বা হালনাগাদ করতে পারি। সংশোধিত সংস্করণ প্রকাশের সঙ্গে সঙ্গে তা কার্যকর হবে।</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">২. পণ্যের তথ্য ও প্রাপ্যতা</h3>
                    <p>আমরা ওয়েবসাইটে প্রদর্শিত প্রতিটি বই ও পণ্যের তথ্য যথাসম্ভব নির্ভুলভাবে উপস্থাপন করার চেষ্টা করি। তবে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>পণ্যের স্টক বা প্রাপ্যতা পূর্বঘোষণা ছাড়াই পরিবর্তিত হতে পারে।</li>
                        <li>প্রয়োজনে কোনো পণ্যের বিক্রয় সীমিত বা বন্ধ করার অধিকার অন্ত্যমিল সংরক্ষণ করে।</li>
                        <li>প্রকাশকের কারণে বইয়ের প্রচ্ছদ, সংস্করণ বা মুদ্রণে সামান্য পরিবর্তন হতে পারে।</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৩. মূল্য ও পেমেন্ট</h3>
                    <p>ওয়েবসাইটে প্রদর্শিত সকল মূল্য বাংলাদেশি টাকায় (৳) উল্লেখ করা হয় এবং প্রয়োজন অনুযায়ী পূর্ব নোটিশ ছাড়াই পরিবর্তন হতে পারে। অর্ডার নিশ্চিত করার আগে প্রযোজ্য ক্ষেত্রে সম্পূর্ণ মূল্য পরিশোধ করতে হবে।</p>
                    <p class="mt-2">যদি কোনো প্রযুক্তিগত বা মানবিক ত্রুটির কারণে ভুল মূল্য প্রদর্শিত হয়, তাহলে অন্ত্যমিল সেই অর্ডার সংশোধন বা বাতিল করার অধিকার সংরক্ষণ করে। এ ক্ষেত্রে গ্রাহককে যথাযথভাবে অবহিত করা হবে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৪. অর্ডার গ্রহণ</h3>
                    <p>ওয়েবসাইটে অর্ডার করা মানেই অর্ডারটি চূড়ান্তভাবে গ্রহণ করা হয়েছে—এমনটি নয়। নিম্নোক্ত কারণে আমরা কোনো অর্ডার বাতিল বা প্রত্যাখ্যান করতে পারি—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>পণ্য স্টকে না থাকা</li>
                        <li>পেমেন্ট ব্যর্থ হওয়া</li>
                        <li>ভুল মূল্য প্রদর্শন</li>
                        <li>অসম্পূর্ণ বা ভুল তথ্য প্রদান</li>
                        <li>প্রতারণামূলক বা সন্দেহজনক কার্যক্রমের আশঙ্কা</li>
                    </ul>
                    <p class="mt-2">অর্ডার গ্রহণের পর আপনাকে ইমেইল বা অন্য উপযুক্ত মাধ্যমে নিশ্চিতকরণ বার্তা পাঠানো হবে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৫. শিপিং ও ডেলিভারি</h3>
                    <p>আমরা যথাসম্ভব দ্রুত অর্ডার প্রক্রিয়াকরণ ও ডেলিভারির চেষ্টা করি। আনুমানিক ডেলিভারি সময়—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>ঢাকার ভেতরে:</strong> সর্বোচ্চ ৫ কার্যদিবস</li>
                        <li><strong>ঢাকার বাইরে:</strong> সর্বোচ্চ ১০ কার্যদিবস</li>
                    </ul>
                    <p class="mt-2">আবহাওয়া, কুরিয়ার সেবা, সরকারি বিধিনিষেধ বা অন্যান্য অনিবার্য কারণে ডেলিভারিতে বিলম্ব হতে পারে। এ ধরনের পরিস্থিতির জন্য অন্ত্যমিল দায়ী থাকবে না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৬. রিটার্ন ও রিফান্ড</h3>
                    <p>রিটার্ন ও রিফান্ড সংক্রান্ত সকল বিষয় আমাদের <strong>রিটার্ন ও রিফান্ড নীতিমালা</strong> অনুযায়ী পরিচালিত হবে। কেনাকাটার আগে সেই নীতিমালা পড়ে নেওয়ার জন্য অনুরোধ করা হচ্ছে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৭. মেধাস্বত্ব</h3>
                    <p>এই ওয়েবসাইটে প্রকাশিত সকল লেখা, ছবি, লোগো, গ্রাফিক্স, ডিজাইন, সফটওয়্যার এবং অন্যান্য কনটেন্ট অন্ত্যমিল অথবা সংশ্লিষ্ট স্বত্বাধিকারীর সম্পত্তি এবং প্রচলিত কপিরাইট ও মেধাস্বত্ব আইন দ্বারা সুরক্ষিত।</p>
                    <p class="mt-2">অন্ত্যমিলের লিখিত অনুমতি ছাড়া এসব কনটেন্ট কপি, পুনঃপ্রকাশ, পরিবর্তন, বিতরণ বা বাণিজ্যিকভাবে ব্যবহার করা যাবে না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৮. ব্যবহারকারীর দায়িত্ব</h3>
                    <p>ওয়েবসাইট ব্যবহার করার সময় ব্যবহারকারীকে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>সঠিক ও হালনাগাদ তথ্য প্রদান করতে হবে।</li>
                        <li>আইনবিরোধী বা প্রতারণামূলক কার্যকলাপ থেকে বিরত থাকতে হবে।</li>
                        <li>ওয়েবসাইটের নিরাপত্তা বা কার্যকারিতায় বিঘ্ন ঘটানোর চেষ্টা করা যাবে না।</li>
                        <li>সার্ভার, ডাটাবেজ বা অন্য কোনো সিস্টেমে অননুমোদিত প্রবেশের চেষ্টা করা যাবে না।</li>
                    </ul>
                    <p class="mt-2">এই শর্ত লঙ্ঘিত হলে প্রয়োজন অনুযায়ী ব্যবহারকারীর অ্যাকাউন্ট বা ওয়েবসাইট ব্যবহারের সুযোগ সীমিত বা স্থগিত করা হতে পারে এবং প্রয়োজনে আইনগত ব্যবস্থা গ্রহণ করা হতে পারে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৯. দায়বদ্ধতার সীমা</h3>
                    <p>প্রযোজ্য আইন অনুযায়ী অনুমোদিত সীমার মধ্যে অন্ত্যমিল ওয়েবসাইট ব্যবহার বা আমাদের কাছ থেকে কেনা পণ্যের কারণে সৃষ্ট কোনো পরোক্ষ, আনুষঙ্গিক বা বিশেষ ক্ষতির জন্য দায়ী থাকবে না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১০. গোপনীয়তা</h3>
                    <p>আপনার ব্যক্তিগত তথ্যের সংগ্রহ, ব্যবহার ও সংরক্ষণ আমাদের <strong>গোপনীয়তা নীতিমালা</strong> অনুযায়ী পরিচালিত হয়। ওয়েবসাইট ব্যবহার করার মাধ্যমে আপনি সেই নীতিমালার প্রতিও সম্মতি প্রদান করছেন।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১১. তৃতীয় পক্ষের ওয়েবসাইট</h3>
                    <p>আমাদের ওয়েবসাইটে তৃতীয় পক্ষের ওয়েবসাইটের লিংক থাকতে পারে। এসব ওয়েবসাইটের কনটেন্ট, নিরাপত্তা, নীতিমালা বা কার্যক্রমের জন্য অন্ত্যমিল কোনো দায় বহন করে না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১২. প্রযোজ্য আইন</h3>
                    <p>এই শর্তাবলী বাংলাদেশের প্রচলিত আইন অনুযায়ী পরিচালিত ও ব্যাখ্যা করা হবে। এই শর্তাবলী সংক্রান্ত যেকোনো বিরোধ বা আইনি বিষয়ে বাংলাদেশের সংশ্লিষ্ট আদালতের এখতিয়ার প্রযোজ্য হবে।</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">যোগাযোগ</h3>
                    <p>এই শর্তাবলী সম্পর্কে কোনো প্রশ্ন, মতামত বা সহায়তার প্রয়োজন হলে অনুগ্রহ করে আমাদের সঙ্গে যোগাযোগ করুন।</p>
                </div>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                অন্ত্যমিলের ওয়েবসাইট ব্যবহার করার মাধ্যমে আপনি নিশ্চিত করছেন যে, আপনি এই শর্তাবলী পড়েছেন, বুঝেছেন এবং এর সকল শর্ত মেনে নিতে সম্মত হয়েছেন।
            </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
