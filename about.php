<?php
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn']) ? $_GET['lang'] : 'bn';

if ($lang === 'en') {
    $page_title = 'About Us | Ontomeel';
    $page_description = 'Ontomeel is an A Premium bookstore dedicated to making quality books easily accessible to readers everywhere.';
    $page_keywords = 'About Us, Ontomeel, Bookstore, Bookshop';
} else {
    $page_title = 'আমাদের সম্পর্কে | অন্ত্যমিল';
    $page_description = 'অন্ত্যমিল একটি বুকস্টোর (অনলাইন ও অফলাইন), যা সর্বত্র পাঠকদের জন্য মানসম্মত বই সহজলভ্য করার উদ্দেশ্যে নিবেদিত।';
    $page_keywords = 'অন্ত্যমিল, About Us, Ontomeel, বুকস্টোর, বইয়ের দোকান';
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
            <?php echo $lang === 'en' ? 'About Us' : 'আমাদের সম্পর্কে'; ?>
        </span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 animate-slide-up" style="animation-delay: 0.2s">
            <?php if ($lang === 'en'): ?>
                Ontomeel - A Premium <span class="text-gradient-gold">Bookstore</span>
            <?php else: ?>
                অন্ত্যমিল - <span class="text-gradient-gold">বুকস্টোর</span> (অনলাইন ও অফলাইন)
            <?php endif; ?>
        </h1>
    </div>
</section>

<!-- About Content -->
<section class="relative z-20 mt-10 pb-24 px-6">
    <div class="max-w-4xl mx-auto space-y-12">
        <!-- Language Toggle -->
        <div class="flex justify-end -mb-6 relative z-30">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <a href="?lang=bn" class="px-4 py-2 text-sm font-medium border rounded-l-lg transition-colors <?php echo $lang == 'bn' ? 'bg-brand-900 text-white border-brand-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:text-brand-900'; ?>">বাংলা</a>
                <a href="?lang=en" class="px-4 py-2 text-sm font-medium border border-l-0 rounded-r-lg transition-colors <?php echo $lang == 'en' ? 'bg-brand-900 text-white border-brand-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:text-brand-900'; ?>">English</a>
            </div>
        </div>

        <?php if ($lang === 'en'): ?>
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 leading-relaxed text-gray-700 space-y-6">
            <p class="text-lg font-light">
                Ontomeel is an A Premium bookstore dedicated to making quality books easily accessible to readers everywhere. We offer a vast collection of books across various genres, including fiction, non-fiction, academic, children's literature, and self-development.
            </p>
            <p class="text-lg font-light">
                Our mission is to foster a reading culture by providing a convenient, reliable, and customer-friendly platform for book lovers. We strive to connect readers with knowledge, inspiration, and stories that enrich their lives.
            </p>
            <p class="text-lg font-light">
                At Ontomeel, we are committed to delivering an excellent shopping experience through a carefully curated catalog, secure A Online transactions, timely delivery, and responsive customer support.
            </p>
        </div>

        <!-- Company Information -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">Company Information</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">Business Name:</strong> Ontomeel</li>
                <li><strong class="font-medium">Business Type:</strong> Bookstore (Offline and Online)</li>
                <li><strong class="font-medium">Website:</strong> <a href="http://www.ontomeel.com" class="text-brand-gold hover:underline">www.ontomeel.com</a></li>
                <li><strong class="font-medium">Email:</strong> <a href="mailto:info@ontomeel.com" class="text-brand-gold hover:underline">info@ontomeel.com</a></li>
                <li><strong class="font-medium">Phone:</strong> <a href="tel:+8801330975787" class="text-brand-gold hover:underline">+8801330975787</a></li>
                <li><strong class="font-medium">Address:</strong> Shop No 6, Changing Closet Building, Motel Laboni Road, Cox's Bazar</li>
                <li><strong class="font-medium">Registered Address:</strong> Azizul Haque Road, Paschim Joydebpur, Gazipur Sadar, Gazipur-1700</li>
                <li><strong class="font-medium">Trade License Number:</strong> 00382-09</li>
            </ul>
        </div>

        <!-- Management Details -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">Management Details</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">Founder/Managing Director:</strong> Muhammad Firoz Hayat Khan</li>
                <li><strong class="font-medium">Operations Manager:</strong> Misbahur Rahman</li>
                <li><strong class="font-medium">Customer Support Lead:</strong> Raul Nun Par Bom</li>
            </ul>
            <p class="mt-8 text-gray-600 font-light">
                For business inquiries, partnerships, or customer support, please contact us using the information provided above.
            </p>
        </div>
        <?php else: ?>
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 leading-relaxed text-gray-700 space-y-6">
            <h2 class="text-3xl font-serif text-brand-900 mb-4">অন্ত্যমিল – আপনার বিশ্বস্ত বুকস্টোর (অনলাইন ও অফলাইন)</h2>
            <p class="text-lg font-light">
                অন্ত্যমিল একটি আধুনিক বুকস্টোর (অনলাইন ও অফলাইন), যার লক্ষ্য দেশের প্রতিটি বইপ্রেমীর কাছে মানসম্মত বই সহজে পৌঁছে দেওয়া। আমরা বিশ্বাস করি, একটি ভালো বই মানুষের জ্ঞান, চিন্তাশক্তি ও কল্পনাশক্তিকে সমৃদ্ধ করে। সেই বিশ্বাস থেকেই আমরা পাঠকদের জন্য নির্ভরযোগ্য ও সহজ একটি বই কেনার প্ল্যাটফর্ম গড়ে তুলেছি।
            </p>
            <p class="text-lg font-light">
                আমাদের সংগ্রহে রয়েছে বাংলা ও বিদেশি লেখকদের ফিকশন, নন-ফিকশন, একাডেমিক বই, শিশু-কিশোর সাহিত্য, আত্ম-উন্নয়নমূলক বই, ধর্মীয় গ্রন্থসহ বিভিন্ন বিষয় ও ঘরানার বই। পাঠকের প্রয়োজন ও আগ্রহকে গুরুত্ব দিয়ে আমরা নিয়মিত নতুন বই যুক্ত করি এবং মানসম্পন্ন প্রকাশনীর বই সংগ্রহে রাখি।
            </p>
            <p class="text-lg font-light">
                আমাদের লক্ষ্য শুধু বই বিক্রি নয়; বরং পাঠাভ্যাসকে উৎসাহিত করা এবং জ্ঞান ও সৃজনশীলতার প্রসারে একটি ইতিবাচক ভূমিকা রাখা। তাই আমরা সর্বোচ্চ মানের সেবা নিশ্চিত করতে নিরাপদ অনলাইন পেমেন্ট, দ্রুত ও নির্ভরযোগ্য ডেলিভারি এবং আন্তরিক গ্রাহক সহায়তা প্রদান করে থাকি।
            </p>
            <p class="text-lg font-light">
                অন্ত্যমিল সবসময় এমন একটি কেনাকাটার অভিজ্ঞতা নিশ্চিত করতে প্রতিশ্রুতিবদ্ধ, যেখানে প্রতিটি পাঠক সহজেই তাঁর পছন্দের বই খুঁজে পাবেন এবং নির্ভরতার সঙ্গে অর্ডার করতে পারবেন।
            </p>
        </div>

        <!-- Company Information -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">কোম্পানির তথ্য</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">ব্যবসার নাম:</strong> অন্ত্যমিল</li>
                <li><strong>ব্যবসার ধরন:</strong> বুকস্টোর (অনলাইন ও অফলাইন)</li>
                <li><strong class="font-medium">ওয়েবসাইট:</strong> <a href="http://www.ontomeel.com" class="text-brand-gold hover:underline">www.ontomeel.com</a></li>
                <li><strong class="font-medium">ইমেইল:</strong> <a href="mailto:info@ontomeel.com" class="text-brand-gold hover:underline">info@ontomeel.com</a></li>
                <li><strong class="font-medium">ফোন:</strong> <a href="tel:+8801330975787" class="text-brand-gold hover:underline">+৮৮০১৩৩০৯৭৫৭৮৭</a></li>
                <li><strong class="font-medium">শোরুমের ঠিকানা:</strong> শপ নং ৬, চেইঞ্জিং ক্লোজেট বিল্ডিং, মোটেল লাবণী রোড, কক্সবাজার।</li>
                <li><strong class="font-medium">নিবন্ধিত ঠিকানা:</strong> আজিজুল হক রোড, পশ্চিম জয়দেবপুর, গাজীপুর সদর, গাজীপুর–১৭০০।</li>
                <li><strong class="font-medium">ট্রেড লাইসেন্স নম্বর:</strong> ০০৩৮২-০৯</li>
            </ul>
        </div>

        <!-- Management Details -->
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-3xl font-serif text-brand-900 mb-6">ব্যবস্থাপনা</h2>
            <ul class="space-y-4 text-gray-700 font-light">
                <li><strong class="font-medium">প্রতিষ্ঠাতা ও ব্যবস্থাপনা পরিচালক:</strong> মুহাম্মদ ফিরোজ হায়াত খান</li>
                <li><strong class="font-medium">অপারেশনস ম্যানেজার:</strong> মিছবাহুর রহমান</li>
                <li><strong class="font-medium">কাস্টমার সাপোর্ট লিড:</strong> রাউল নুন পার বম</li>
            </ul>
            <p class="mt-8 text-gray-600 font-light">
                ব্যবসায়িক সহযোগিতা, অংশীদারিত্ব, পাইকারি বিক্রয় বা যেকোনো গ্রাহক সহায়তার জন্য আমাদের সঙ্গে যোগাযোগ করতে পারেন। অন্ত্যমিল সবসময় আপনার সেবায় প্রতিশ্রুতিবদ্ধ।
            </p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
