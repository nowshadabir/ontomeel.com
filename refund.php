<?php
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn']) ? $_GET['lang'] : 'bn';

if ($lang === 'en') {
    $page_title = 'Return & Refund Policy | Ontomeel';
    $page_description = 'At Ontomeel, customer satisfaction is our priority. If you are not completely satisfied with your purchase, please review our return and refund policy.';
    $page_keywords = 'Return, Refund, Policy, Ontomeel, Bookstore Returns';
} else {
    $page_title = 'রিটার্ন ও রিফান্ড নীতি | অন্ত্যমিল';
    $page_description = 'অন্ত্যমিল-এ, গ্রাহক সন্তুষ্টি আমাদের অগ্রাধিকার। আপনি যদি আপনার কেনাকাটায় পুরোপুরি সন্তুষ্ট না হন, তবে অনুগ্রহ করে আমাদের রিটার্ন এবং রিফান্ড নীতি পর্যালোচনা করুন।';
    $page_keywords = 'রিটার্ন, রিফান্ড, Return, Refund, Policy, Ontomeel, Bookstore Returns';
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
                Return & <span class="text-gradient-gold">Refund</span>
            <?php else: ?>
                রিটার্ন ও <span class="text-gradient-gold">রিফান্ড</span>
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
                At Ontomeel, customer satisfaction is our priority. If you are not completely satisfied with your purchase, please review our return and refund policy below.
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <p><strong class="font-medium text-brand-900">1. Eligibility for Returns:</strong> Customers may request a return if the book received is damaged, defective, or incorrect, provided that the item remains unused and in its original condition. To be eligible for a return, the request must be submitted to Ontomeel within 7 days of receiving the order. Requests received after this period may not qualify for return or refund consideration.</p>
                <p><strong class="font-medium text-brand-900">2. Non-Returnable Items:</strong> The following items are generally not eligible for return or refund: books that show signs of use, damage, marking, or alteration by the customer; items returned without their original packaging or accompanying materials where applicable; downloaded, accessed, or activated digital products, including eBooks and audiobooks; and clearance, promotional, sale, or specially discounted items, unless they are found to be damaged, defective, or incorrectly supplied by Ontomeel.</p>
                <p><strong class="font-medium text-brand-900">3. Damaged or Incorrect Orders:</strong> If you receive a damaged, defective, or incorrect item, please contact Ontomeel within 48 hours of delivery and provide your order number along with clear photographs of the item and its packaging. Our customer support team will review your request and, if the claim is verified, arrange for a replacement, exchange, or refund as appropriate in accordance with this Return & Refund Policy.</p>
                <p><strong class="font-medium text-brand-900">4. Refund Process:</strong> Once your returned item is received and inspected by Ontomeel, eligible refunds will be approved and processed to the original payment method used for the purchase. Refund processing times may vary depending on your bank, card issuer, or payment service provider and typically take 5-10 business days to be completed. Please note that shipping and delivery charges are generally non-refundable unless the return is required due to an error on the part of Ontomeel, such as the shipment of a defective, damaged, or incorrect item.</p>
                <p><strong class="font-medium text-brand-900">5. Return Shipping:</strong> Customers are responsible for the cost of returning items unless the product received is damaged, defective, or incorrect due to an error by Ontomeel. In such cases, Ontomeel may cover the return shipping costs and provide appropriate return instructions or shipping assistance to facilitate the return process. Customers are advised to follow the return instructions provided by our support team to ensure timely processing of their request.</p>
                <p><strong class="font-medium text-brand-900">6. Order Cancellation:</strong> Orders may be cancelled before they are shipped. Once an order has been dispatched, it cannot be cancelled and must follow the return process outlined above.</p>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                We are committed to providing a smooth and fair resolution process for all our customers.
            </p>
            <?php else: ?>
            <p class="text-lg font-light mb-8">
                অন্ত্যমিলে গ্রাহক সন্তুষ্টিই আমাদের সর্বোচ্চ অগ্রাধিকার। আমরা সর্বদা সঠিক ও মানসম্মত পণ্য সরবরাহের চেষ্টা করি। তবুও, কোনো কারণে আপনি যদি প্রাপ্ত পণ্য নিয়ে সন্তুষ্ট না হন, তাহলে নিচের রিটার্ন ও রিফান্ড নীতিমালা প্রযোজ্য হবে।
            </p>
            
            <div class="space-y-6 text-gray-700 font-light">
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">১. রিটার্নের যোগ্যতা</h3>
                    <p>নিম্নোক্ত ক্ষেত্রে আপনি রিটার্নের জন্য আবেদন করতে পারবেন—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>প্রাপ্ত বইটি ক্ষতিগ্রস্ত হলে</li>
                        <li>বইটিতে উৎপাদনগত ত্রুটি থাকলে</li>
                        <li>ভুল বই বা ভুল সংস্করণ সরবরাহ করা হলে</li>
                    </ul>
                    <p class="mt-4">রিটার্নের জন্য আবেদন করতে হলে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>পণ্যটি অব্যবহৃত এবং মূল অবস্থায় থাকতে হবে।</li>
                        <li>অর্ডার গ্রহণের <strong>৭ দিনের মধ্যে</strong> রিটার্নের অনুরোধ জানাতে হবে।</li>
                    </ul>
                    <p class="mt-4">নির্ধারিত সময়সীমার পর প্রাপ্ত অনুরোধ গ্রহণযোগ্য নাও হতে পারে।</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">২. যেসব পণ্য রিটার্ন বা রিফান্ডযোগ্য নয়</h3>
                    <p>সাধারণত নিচের ক্ষেত্রে রিটার্ন বা রিফান্ড প্রযোজ্য হবে না—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>ব্যবহার করা বা ক্ষতিগ্রস্ত বই</li>
                        <li>দাগ, লেখা বা অন্য কোনো পরিবর্তন করা বই</li>
                        <li>মূল প্যাকেজিং বা আনুষঙ্গিক সামগ্রী ছাড়া ফেরত দেওয়া পণ্য</li>
                        <li>ডাউনলোড, অ্যাক্টিভেট বা ব্যবহৃত ডিজিটাল পণ্য (যেমন: ই-বুক বা অডিওবুক)</li>
                        <li>বিশেষ ছাড়, ক্লিয়ারেন্স বা প্রোমোশনাল অফারে কেনা পণ্য</li>
                    </ul>
                    <p class="mt-4">তবে, এসব পণ্য অন্ত্যমিলের ভুলে ক্ষতিগ্রস্ত, ত্রুটিপূর্ণ বা ভুলভাবে সরবরাহ করা হলে এই সীমাবদ্ধতা প্রযোজ্য হবে না।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৩. ক্ষতিগ্রস্ত বা ভুল পণ্য প্রাপ্ত হলে</h3>
                    <p>আপনি যদি ক্ষতিগ্রস্ত, ত্রুটিপূর্ণ বা ভুল পণ্য গ্রহণ করেন, তাহলে ডেলিভারির <strong>৪৮ ঘণ্টার মধ্যে</strong> আমাদের সঙ্গে যোগাযোগ করুন।</p>
                    <p class="mt-4">অনুগ্রহ করে নিম্নোক্ত তথ্য প্রদান করুন—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>অর্ডার নম্বর</li>
                        <li>পণ্যের স্পষ্ট ছবি</li>
                        <li>প্যাকেজিংয়ের স্পষ্ট ছবি</li>
                        <li>সমস্যার সংক্ষিপ্ত বিবরণ</li>
                    </ul>
                    <p class="mt-4">আমাদের কাস্টমার সাপোর্ট টিম বিষয়টি যাচাই করে প্রয়োজন অনুযায়ী রিপ্লেসমেন্ট, এক্সচেঞ্জ অথবা রিফান্ডের ব্যবস্থা করবে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৪. রিফান্ড প্রক্রিয়া</h3>
                    <p>ফেরত দেওয়া পণ্য আমাদের কাছে পৌঁছানোর পর তা পরিদর্শন করা হবে।</p>
                    <p class="mt-4">রিটার্ন অনুমোদিত হলে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>রিফান্ড আপনার ব্যবহৃত মূল পেমেন্ট মাধ্যমেই প্রদান করা হবে।</li>
                        <li>ব্যাংক, মোবাইল ফাইন্যান্সিয়াল সার্ভিস বা পেমেন্ট সেবাদাতার প্রক্রিয়ার ওপর নির্ভর করে রিফান্ড সম্পন্ন হতে সাধারণত <strong>৫–১০ কর্মদিবস</strong> সময় লাগতে পারে।</li>
                    </ul>
                    <p class="mt-4">শিপিং বা ডেলিভারি চার্জ সাধারণত ফেরতযোগ্য নয়, যদি না অন্ত্যমিলের ভুলের কারণে (যেমন ভুল বা ক্ষতিগ্রস্ত পণ্য সরবরাহ) রিটার্ন করতে হয়।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৫. রিটার্ন শিপিং</h3>
                    <p>রিটার্নের কারণ যদি অন্ত্যমিলের কোনো ভুল না হয়, তাহলে পণ্য ফেরত পাঠানোর খরচ গ্রাহককে বহন করতে হবে।</p>
                    <p class="mt-4">তবে—</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>ভুল পণ্য সরবরাহ</li>
                        <li>ক্ষতিগ্রস্ত পণ্য</li>
                        <li>ত্রুটিপূর্ণ পণ্য</li>
                    </ul>
                    <p class="mt-4">এসব ক্ষেত্রে রিটার্ন শিপিংয়ের ব্যয় অন্ত্যমিল বহন করতে পারে এবং প্রয়োজনীয় নির্দেশনা প্রদান করবে।</p>
                    <p class="mt-4">রিটার্ন দ্রুত সম্পন্ন করার জন্য আমাদের কাস্টমার সাপোর্ট টিমের নির্দেশনা অনুসরণ করার অনুরোধ করা হচ্ছে।</p>
                </div>

                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">৬. অর্ডার বাতিল</h3>
                    <p>অর্ডার <strong>শিপমেন্টের আগে</strong> বাতিল করা যাবে।</p>
                    <p class="mt-2">একবার অর্ডার কুরিয়ারে হস্তান্তর বা শিপমেন্ট হয়ে গেলে তা আর বাতিল করা সম্ভব হবে না। সেক্ষেত্রে প্রয়োজনে এই রিটার্ন নীতিমালা অনুযায়ী আবেদন করতে হবে।</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif text-brand-900 mb-2">যোগাযোগ</h3>
                    <p>রিটার্ন, রিফান্ড বা অর্ডার-সংক্রান্ত যেকোনো প্রশ্ন বা সহায়তার জন্য আমাদের কাস্টমার সাপোর্ট টিমের সঙ্গে যোগাযোগ করুন।</p>
                </div>
            </div>
            
            <p class="text-lg font-light mt-8 pt-8 border-t border-gray-100">
                অন্ত্যমিল সর্বদা গ্রাহকদের জন্য স্বচ্ছ, দ্রুত এবং ন্যায্য রিটার্ন ও রিফান্ড প্রক্রিয়া নিশ্চিত করতে প্রতিশ্রুতিবদ্ধ।
            </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
