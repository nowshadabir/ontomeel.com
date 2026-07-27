<?php
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn']) ? $_GET['lang'] : 'bn';

if ($lang === 'en') {
    $page_title = 'Privacy Policy | Ontomeel';
    $page_description = 'At Ontomeel, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your data.';
    $page_keywords = 'Privacy Policy, Data Protection, Ontomeel, Bookstore Privacy';
} else {
    $page_title = 'গোপনীয়তা নীতি | অন্ত্যমিল';
    $page_description = 'অন্ত্যমিল-এ, আমরা আপনার গোপনীয়তাকে মূল্য দিই এবং আপনার ব্যক্তিগত তথ্য সুরক্ষায় প্রতিশ্রুতিবদ্ধ। এই গোপনীয়তা নীতিতে ব্যাখ্যা করা হয়েছে যে আমরা কীভাবে আপনার তথ্য সংগ্রহ ও সুরক্ষিত করি।';
    $page_keywords = 'গোপনীয়তা নীতি, Privacy Policy, Data Protection, Ontomeel, Bookstore Privacy';
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
                Privacy <span class="text-gradient-gold">Policy</span>
            <?php else: ?>
                গোপনীয়তা <span class="text-gradient-gold">নীতি</span>
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
                At Ontomeel, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and purchase products from us.
                <br><br>
                By using our website, you agree to the practices described in this Privacy Policy.
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">1. Information We Collect</h3>
                    <p>We may collect the following types of information:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>Personal Information:</strong> Full name, Email address, Phone number, Billing address, Shipping address, Payment information (processed securely through third-party payment providers).</li>
                        <li><strong>Non-Personal Information:</strong> IP address, Browser type and version, Device information, Website usage data, Cookies and similar technologies.</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">2. How We Use Your Information</h3>
                    <p>We use your personal information to process and fulfill orders, provide customer support, send order confirmations and delivery updates, and ensure a smooth shopping experience. Your information may also be used to improve our website, products, and services, respond to inquiries and feedback, send promotional offers and marketing communications where permitted by law or with your consent, and detect, prevent, or investigate fraudulent, unauthorized, or unlawful activities that may affect our customers, website, or business operations.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">3. Cookies</h3>
                    <p>Ontomeel may use cookies and similar technologies to enhance website functionality, remember user preferences, analyze website traffic and performance, and improve the overall user experience. These technologies help us understand how visitors interact with our website and enable us to provide more personalized and efficient services. Users may choose to disable cookies through their browser settings; however, doing so may limit access to certain features and affect the functionality and performance of the website.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">4. Sharing of Information</h3>
                    <p>Ontomeel does not sell, rent, or trade your personal information to third parties. However, we may share your information with trusted service providers, including payment processors, delivery and logistics partners, website hosting providers, and technology service providers, solely for the purpose of operating our business and delivering services to you. We may also disclose information to government authorities, regulatory bodies, or law enforcement agencies when required by applicable laws or legal processes. All third-party service providers engaged by Ontomeel are expected to implement appropriate security measures and maintain the confidentiality and protection of your personal information.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">5. Data Security</h3>
                    <p>We implement reasonable technical and organizational measures to protect your personal information from unauthorized access, loss, misuse, alteration, or disclosure. While we strive to protect your information, no method of electronic transmission or storage is completely secure. Therefore, we cannot guarantee absolute security.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">6. Data Retention</h3>
                    <p>We retain personal information only for as long as necessary to fulfill and manage customer orders, provide our services, comply with applicable legal and regulatory requirements, resolve disputes, and enforce our agreements, policies, and terms of use. Once the information is no longer required for these purposes, it will be securely deleted, anonymized, or otherwise disposed of in accordance with applicable data protection laws and industry best practices.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">7. Your Rights</h3>
                    <p>Subject to applicable laws and regulations, you may have the right to access the personal information we hold about you, request corrections to inaccurate or incomplete information, request the deletion of your personal data, object to certain types of data processing, and withdraw your consent to receive marketing or promotional communications at any time. Ontomeel will make reasonable efforts to respond to such requests in accordance with applicable data protection laws. To exercise any of these rights, please contact us using the contact details provided below.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">8. Third-Party Links</h3>
                    <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices, content, or policies of those websites. We encourage users to review the privacy policies of any third-party sites they visit.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">9. Children's Privacy</h3>
                    <p>Ontomeel does not knowingly collect personal information from children under the age of 13. If we become aware that such information has been collected, we will take reasonable steps to delete it.</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">10. Changes to This Privacy Policy</h3>
                    <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page along with the updated effective date.</p>
                </div>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                By using the Ontomeel website, you acknowledge that you have read and understood this Privacy Policy and agree to its terms.
            </p>
            <?php else: ?>
            <p class="text-lg font-light mb-8">
                অন্ত্যমিল আপনার ব্যক্তিগত তথ্যের গোপনীয়তা ও নিরাপত্তাকে সর্বোচ্চ গুরুত্ব দেয়। এই গোপনীয়তা নীতিতে ব্যাখ্যা করা হয়েছে, আপনি আমাদের ওয়েবসাইট ব্যবহার বা আমাদের কাছ থেকে পণ্য ক্রয় করার সময় আমরা কী ধরনের তথ্য সংগ্রহ করি, কীভাবে তা ব্যবহার করি এবং কীভাবে সুরক্ষিত রাখি।
                <br><br>
                আমাদের ওয়েবসাইট ব্যবহার করার মাধ্যমে আপনি এই গোপনীয়তা নীতির শর্তাবলীর প্রতি সম্মতি প্রদান করছেন।
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১. আমরা কী ধরনের তথ্য সংগ্রহ করি</h3>
                    <p>আমরা প্রয়োজনে নিম্নোক্ত তথ্য সংগ্রহ করতে পারি।</p>
                    <h4 class="font-medium text-gray-800 mt-4 mb-2">ব্যক্তিগত তথ্য</h4>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>পূর্ণ নাম</li>
                        <li>ইমেইল ঠিকানা</li>
                        <li>মোবাইল নম্বর</li>
                        <li>বিলিং ঠিকানা</li>
                        <li>ডেলিভারির ঠিকানা</li>
                        <li>পেমেন্ট-সংক্রান্ত তথ্য (নিরাপদ তৃতীয় পক্ষের পেমেন্ট সেবার মাধ্যমে প্রক্রিয়াজাত করা হয়)</li>
                    </ul>
                    <h4 class="font-medium text-gray-800 mt-4 mb-2">অ-ব্যক্তিগত তথ্য</h4>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>আইপি (IP) ঠিকানা</li>
                        <li>ব্রাউজারের ধরন ও সংস্করণ</li>
                        <li>ডিভাইস সম্পর্কিত তথ্য</li>
                        <li>ওয়েবসাইট ব্যবহারের তথ্য</li>
                        <li>কুকিজ এবং অনুরূপ প্রযুক্তির মাধ্যমে সংগৃহীত তথ্য</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">২. তথ্য ব্যবহারের উদ্দেশ্য</h3>
                    <p>সংগৃহীত তথ্য আমরা নিম্নলিখিত কাজে ব্যবহার করি—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>অর্ডার গ্রহণ, প্রক্রিয়াকরণ ও সম্পন্ন করা</li>
                        <li>পণ্য ডেলিভারি নিশ্চিত করা</li>
                        <li>গ্রাহক সহায়তা প্রদান</li>
                        <li>অর্ডারের অবস্থা ও ডেলিভারি সংক্রান্ত তথ্য জানানো</li>
                        <li>ওয়েবসাইট, পণ্য ও সেবার মান উন্নয়ন</li>
                        <li>ব্যবহারকারীর মতামত ও অনুসন্ধানের উত্তর প্রদান</li>
                        <li>আপনার সম্মতি থাকলে অফার, প্রচারণা ও অন্যান্য বিপণন বার্তা পাঠানো</li>
                        <li>প্রতারণা, অননুমোদিত ব্যবহার বা বেআইনি কার্যকলাপ শনাক্ত ও প্রতিরোধ করা</li>
                        <li>প্রযোজ্য আইন ও বিধিমালা অনুসরণ করা</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৩. কুকিজ (Cookies)</h3>
                    <p>আমাদের ওয়েবসাইটে ব্যবহারকারীর অভিজ্ঞতা উন্নত করতে কুকিজ এবং অনুরূপ প্রযুক্তি ব্যবহার করা হতে পারে।</p>
                    <p class="mt-2">এর মাধ্যমে আমরা—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>আপনার পছন্দের সেটিংস সংরক্ষণ করি</li>
                        <li>ওয়েবসাইটের ব্যবহার বিশ্লেষণ করি</li>
                        <li>পারফরম্যান্স উন্নত করি</li>
                        <li>আরও ব্যক্তিগতকৃত সেবা প্রদান করি</li>
                    </ul>
                    <p class="mt-2">আপনি চাইলে ব্রাউজারের সেটিংস থেকে কুকিজ নিষ্ক্রিয় করতে পারেন। তবে এতে ওয়েবসাইটের কিছু সুবিধা সীমিত হতে পারে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৪. তথ্য শেয়ারিং</h3>
                    <p>অন্ত্যমিল আপনার ব্যক্তিগত তথ্য কোনো তৃতীয় পক্ষের কাছে বিক্রি, ভাড়া বা বাণিজ্যিকভাবে বিনিময় করে না।</p>
                    <p class="mt-2">তবে সেবা প্রদান ও ব্যবসায়িক কার্যক্রম পরিচালনার প্রয়োজনে আমরা বিশ্বস্ত অংশীদারদের সঙ্গে সীমিত তথ্য শেয়ার করতে পারি, যেমন—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>পেমেন্ট সেবা প্রদানকারী</li>
                        <li>কুরিয়ার ও লজিস্টিক প্রতিষ্ঠান</li>
                        <li>ওয়েবসাইট হোস্টিং সেবা প্রদানকারী</li>
                        <li>প্রযুক্তিগত সেবা প্রদানকারী</li>
                    </ul>
                    <p class="mt-2">এছাড়া, আইনগত বাধ্যবাধকতা বা সরকারি কর্তৃপক্ষের বৈধ অনুরোধের ক্ষেত্রে প্রয়োজনীয় তথ্য প্রদান করা হতে পারে।</p>
                    <p class="mt-2">আমাদের সকল সহযোগী প্রতিষ্ঠানের কাছ থেকে ব্যক্তিগত তথ্যের গোপনীয়তা ও নিরাপত্তা বজায় রাখার প্রত্যাশা করা হয়।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৫. তথ্যের নিরাপত্তা</h3>
                    <p>আপনার ব্যক্তিগত তথ্য অননুমোদিত প্রবেশ, অপব্যবহার, পরিবর্তন, প্রকাশ বা হারিয়ে যাওয়া থেকে সুরক্ষিত রাখতে আমরা উপযুক্ত প্রযুক্তিগত ও প্রশাসনিক নিরাপত্তা ব্যবস্থা গ্রহণ করি।</p>
                    <p class="mt-2">তবে ইন্টারনেটের মাধ্যমে তথ্য আদান-প্রদান বা ডিজিটাল সংরক্ষণের কোনো পদ্ধতিই শতভাগ নিরাপদ নয়। তাই সর্বোচ্চ চেষ্টা সত্ত্বেও আমরা সম্পূর্ণ নিরাপত্তার নিশ্চয়তা দিতে পারি না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৬. তথ্য সংরক্ষণ</h3>
                    <p>আপনার ব্যক্তিগত তথ্য শুধুমাত্র প্রয়োজনীয় সময় পর্যন্ত সংরক্ষণ করা হয়, যেমন—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>অর্ডার সম্পন্ন করা</li>
                        <li>গ্রাহক সেবা প্রদান</li>
                        <li>আইনগত ও নিয়ন্ত্রক বাধ্যবাধকতা পালন</li>
                        <li>বিরোধ নিষ্পত্তি</li>
                        <li>আমাদের নীতিমালা ও শর্তাবলী কার্যকর রাখা</li>
                    </ul>
                    <p class="mt-2">প্রয়োজন শেষ হলে তথ্য নিরাপদভাবে মুছে ফেলা, বেনামীকরণ অথবা প্রযোজ্য আইন অনুযায়ী নিষ্পত্তি করা হয়।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৭. আপনার অধিকার</h3>
                    <p>প্রযোজ্য আইন অনুযায়ী আপনার নিম্নলিখিত অধিকার থাকতে পারে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>আপনার ব্যক্তিগত তথ্য দেখার অনুরোধ করা</li>
                        <li>ভুল বা অসম্পূর্ণ তথ্য সংশোধনের অনুরোধ করা</li>
                        <li>ব্যক্তিগত তথ্য মুছে ফেলার অনুরোধ করা</li>
                        <li>নির্দিষ্ট ক্ষেত্রে তথ্য প্রক্রিয়াকরণে আপত্তি জানানো</li>
                        <li>যেকোনো সময় প্রচারণামূলক বার্তা গ্রহণের সম্মতি প্রত্যাহার করা</li>
                    </ul>
                    <p class="mt-2">এসব বিষয়ে সহায়তার জন্য আমাদের সঙ্গে যোগাযোগ করতে পারেন।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৮. তৃতীয় পক্ষের ওয়েবসাইট</h3>
                    <p>আমাদের ওয়েবসাইটে অন্যান্য প্রতিষ্ঠানের ওয়েবসাইটের লিংক থাকতে পারে। এসব ওয়েবসাইটের গোপনীয়তা নীতি বা কার্যক্রমের জন্য অন্ত্যমিল দায়ী নয়।</p>
                    <p class="mt-2">তাই অন্য কোনো ওয়েবসাইট ব্যবহার করার আগে তাদের নিজস্ব গোপনীয়তা নীতি পড়ে নেওয়ার পরামর্শ দেওয়া হয়।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৯. শিশুদের গোপনীয়তা</h3>
                    <p>অন্ত্যমিল জেনেশুনে ১৩ বছরের কম বয়সী কোনো শিশুর ব্যক্তিগত তথ্য সংগ্রহ করে না। যদি অনিচ্ছাকৃতভাবে এমন তথ্য সংগ্রহ হয়েছে বলে জানা যায়, তাহলে তা দ্রুত মুছে ফেলার জন্য প্রয়োজনীয় ব্যবস্থা নেওয়া হবে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১০. গোপনীয়তা নীতির পরিবর্তন</h3>
                    <p>প্রয়োজন অনুযায়ী আমরা সময়ে সময়ে এই গোপনীয়তা নীতি সংশোধন বা হালনাগাদ করতে পারি। যেকোনো পরিবর্তন এই পৃষ্ঠায় প্রকাশের সঙ্গে সঙ্গে কার্যকর হবে এবং সর্বশেষ হালনাগাদের তারিখ উল্লেখ থাকবে।</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">যোগাযোগ</h3>
                    <p>এই গোপনীয়তা নীতি বা আপনার ব্যক্তিগত তথ্য সম্পর্কিত কোনো প্রশ্ন, মতামত বা অনুরোধ থাকলে অনুগ্রহ করে আমাদের সঙ্গে যোগাযোগ করুন।</p>
                </div>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                অন্ত্যমিল ব্যবহার করার মাধ্যমে আপনি নিশ্চিত করছেন যে, আপনি এই গোপনীয়তা নীতি পড়েছেন, বুঝেছেন এবং এর শর্তাবলীর সঙ্গে সম্মত হয়েছেন।
            </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
