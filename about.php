<?php
$page_title = 'আমাদের সম্পর্কে | অন্ত্যমিল';
$page_description = 'অন্ত্যমিল একটি অনলাইন বুকস্টোর, যা সর্বত্র পাঠকদের জন্য মানসম্মত বই সহজলভ্য করার উদ্দেশ্যে নিবেদিত।';
$page_keywords = 'অন্ত্যমিল, About Us, Ontomeel, বুকস্টোর, বইয়ের দোকান';
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
            আমাদের সম্পর্কে
        </span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 animate-slide-up" style="animation-delay: 0.2s">
            অন্ত্যমিল - অনলাইন <span class="text-gradient-gold">বুকস্টোর</span>
        </h1>
    </div>
</section>

<!-- About Content -->
<section class="relative z-20 mt-10 pb-24 px-6">
    <div class="max-w-4xl mx-auto space-y-12">
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 leading-relaxed text-gray-700 space-y-6">
            <p class="text-lg font-light">
                অন্ত্যমিল একটি অনলাইন বুকস্টোর, যা সর্বত্র পাঠকদের জন্য মানসম্মত বই সহজলভ্য করার উদ্দেশ্যে নিবেদিত। আমরা ফিকশন, নন-ফিকশন, একাডেমিক, শিশু সাহিত্য, আত্ম-উন্নয়নসহ বিভিন্ন ঘরানার বইয়ের এক বিশাল সংগ্রহ অফার করি।
            </p>
            <p class="text-lg font-light">
                আমাদের লক্ষ্য হলো বইপ্রেমীদের জন্য একটি সুবিধাজনক, নির্ভরযোগ্য এবং গ্রাহক-বান্ধব প্ল্যাটফর্ম প্রদানের মাধ্যমে পড়ার সংস্কৃতিকে উৎসাহিত করা। আমরা পাঠকদের এমন জ্ঞান, অনুপ্রেরণা এবং গল্পের সাথে যুক্ত করার চেষ্টা করি, যা জীবনকে সমৃদ্ধ করে।
            </p>
            <p class="text-lg font-light">
                অন্ত্যমিল-এ, আমরা একটি সতর্কতার সাথে বাছাইকৃত ক্যাটালগ, নিরাপদ অনলাইন লেনদেন, সময়মতো ডেলিভারি এবং প্রতিক্রিয়াশীল গ্রাহক সহায়তার মাধ্যমে একটি চমৎকার কেনাকাটার অভিজ্ঞতা প্রদানের জন্য প্রতিশ্রুতিবদ্ধ।
            </p>
        </div>

        <!-- Company Information -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">কোম্পানির তথ্যাবলী</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">ব্যবসার নাম:</strong> অন্ত্যমিল</li>
                <li><strong class="font-medium">ব্যবসার ধরন:</strong> বুকস্টোর (অফলাইন এবং অনলাইন)</li>
                <li><strong class="font-medium">ওয়েবসাইট:</strong> <a href="http://www.ontomeel.com" class="text-brand-gold hover:underline">www.ontomeel.com</a></li>
                <li><strong class="font-medium">ইমেইল:</strong> <a href="mailto:info@ontomeel.com" class="text-brand-gold hover:underline">info@ontomeel.com</a></li>
                <li><strong class="font-medium">ফোন:</strong> <a href="tel:+8801330975787" class="text-brand-gold hover:underline">+৮৮০১৩৩০৯৭৫৭৮৭</a></li>
                <li><strong class="font-medium">ঠিকানা:</strong> শপ নং ৬, চেইঞ্জিং ক্লোজেট বিল্ডিং, মোটেল লাবণী রোড, কক্সবাজার</li>
                <li><strong class="font-medium">নিবন্ধিত ঠিকানা:</strong> আজিজুল হক রোড, পশ্চিম জয়দেবপুর, গাজীপুর সদর, গাজীপুর-১৭০০</li>
                <li><strong class="font-medium">ট্রেড লাইসেন্স নম্বর:</strong> ০০৩৮২-০৯</li>
            </ul>
        </div>

        <!-- Management Details -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">ব্যবস্থাপনা বিবরণী</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">প্রতিষ্ঠাতা/ব্যবস্থাপনা পরিচালক:</strong> মুহাম্মদ ফিরোজ হায়াত খান</li>
                <li><strong class="font-medium">অপারেশনস ম্যানেজার:</strong> মিছবাহুর রহমান</li>
                <li><strong class="font-medium">কাস্টমার সাপোর্ট লিড:</strong> রাউল নুন পার বম</li>
            </ul>
            <p class="mt-8 text-gray-600 font-light">
                ব্যবসায়িক অনুসন্ধান, অংশীদারিত্ব বা গ্রাহক সহায়তার জন্য অনুগ্রহ করে উপরে দেওয়া তথ্য ব্যবহার করে আমাদের সাথে যোগাযোগ করুন।
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
