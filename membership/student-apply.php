<?php
// membership/student-apply.php
// Dedicated Student Membership Application Page for "বইয়ের আনন্দ-পাঠ"

$page_title = 'বইয়ের আনন্দ-পাঠ (স্টুডেন্ট মেম্বারশিপ) | অন্ত্যমিল';
$path_prefix = '../';
require_once __DIR__ . '/../includes/db_connect.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$member = null;
$pending_request = null;
$latest_request = null;
$is_student_active = false;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $student_expire_date = null;
        if (!empty($member['student_plan_expire_date']) && strtotime($member['student_plan_expire_date']) > time()) {
            $is_student_active = true;
            $student_expire_date = $member['student_plan_expire_date'];
        } elseif ($member['membership_plan'] === 'Student' && !empty($member['plan_expire_date']) && strtotime($member['plan_expire_date']) > time()) {
            $is_student_active = true;
            $student_expire_date = $member['plan_expire_date'];
        }
    }

    // Check existing student requests
    $req_stmt = $pdo->prepare("SELECT * FROM student_membership_requests WHERE member_id = ? ORDER BY id DESC LIMIT 1");
    $req_stmt->execute([$user_id]);
    $latest_request = $req_stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest_request && $latest_request['status'] === 'Pending') {
        $pending_request = $latest_request;
    }
}

$error_msg = $_SESSION['student_apply_error'] ?? '';
$success_msg = $_SESSION['student_apply_success'] ?? '';
unset($_SESSION['student_apply_error'], $_SESSION['student_apply_success']);

include __DIR__ . '/../includes/header.php';
?>

<div class="pt-32 sm:pt-36 pb-20 bg-[#f8f7f4] min-h-screen font-anek">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back Navigation -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-500 hover:text-brand-900 transition-colors">
                <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>সকল মেম্বারশিপ প্ল্যান দেখুন</span>
            </a>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3 text-sm animate-shake">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?php echo htmlspecialchars($success_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Main Card Container -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            
            <!-- Hero Banner Section -->
            <div class="relative bg-brand-900 text-white p-6 sm:p-10 overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-gold/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 md:gap-8">
                    <div class="w-28 h-28 sm:w-36 sm:h-36 shrink-0 rounded-2xl overflow-hidden shadow-2xl border-2 border-brand-gold/40 relative">
                        <img src="../assets/img/boi-er-ananda.jpeg" alt="বইয়ের আনন্দ-পাঠ" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-brand-gold/20 border border-brand-gold/40 rounded-full text-brand-gold text-xs font-bold uppercase tracking-widest mb-2">
                            <span>🎓 শিক্ষার্থীদের জন্য বিশেষ পাঠ-উদ্যোগ</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold font-anek text-white tracking-tight mb-1">
                            বইয়ের আনন্দ-পাঠ
                        </h1>
                        <p class="text-brand-gold text-base sm:text-lg font-bold font-anek mb-2">
                            বইয়ের ভেতর নিজের পথ
                        </p>
                        <p class="text-gray-300 text-xs sm:text-sm font-light leading-relaxed max-w-xl">
                            বইয়ের সঙ্গে একান্ত ও স্বতন্ত্র সম্পর্ক গড়ে তোলার পাঠ-উদ্যোগ। ১২ থেকে ১৯ বছর বয়সী তরুণ পাঠকদের বইপড়ার সুঅভ্যাস গড়ে তুলতে এই মেম্বারশিপটি সম্পূর্ণ বিনামূল্যে প্রদান করা হয়।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6 sm:p-10">
                
                <?php if (!$user_id): ?>
                    <!-- Not Logged In Notice -->
                    <div class="text-center py-8">
                        <div class="w-16 h-16 rounded-2xl bg-brand-gold/15 text-brand-gold flex items-center justify-center mx-auto mb-4 text-3xl">
                            🔐
                        </div>
                        <h2 class="text-2xl font-bold text-brand-900 mb-2">ইউজার অ্যাকাউন্ট প্রয়োজন</h2>
                        <p class="text-sm text-gray-500 max-w-md mx-auto mb-8 leading-relaxed">
                            ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপে আবেদন করতে একটি অন্ত্যমিল অ্যাকাউন্ট থাকা বাধ্যতামূলক। অনুগ্রহ করে লগইন করুন অথবা নতুন অ্যাকাউন্ট তৈরি করুন।
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center max-w-xs mx-auto">
                            <a href="../login/index.php?redirect=<?php echo urlencode("../membership/student-apply.php"); ?>" 
                               class="w-full py-3.5 bg-brand-900 text-white font-bold rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all text-sm text-center shadow-lg">
                                লগইন করুন
                            </a>
                            <a href="../signup/index.php?redirect=<?php echo urlencode("../membership/student-apply.php"); ?>" 
                               class="w-full py-3.5 bg-gray-100 text-brand-900 font-bold rounded-xl hover:bg-gray-200 transition-all text-sm text-center">
                                নতুন অ্যাকাউন্ট তৈরি
                            </a>
                        </div>
                    </div>

                <?php elseif ($is_student_active): ?>
                    <!-- Already Active Student Plan -->
                    <div class="text-center py-8 bg-emerald-50/50 rounded-2xl p-8 border border-emerald-100">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-3xl">
                            ✓
                        </div>
                        <span class="text-emerald-700 text-xs font-bold uppercase tracking-widest block mb-1">অভিনন্দন!</span>
                        <h2 class="text-2xl font-extrabold text-brand-900 mb-2">আপনার ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপ সক্রিয় আছে</h2>
                        <p class="text-sm text-gray-600 max-w-lg mx-auto mb-6">
                            আপনার মেম্বারশিপের মেয়াদ <strong><?php echo date('d M, Y', strtotime($student_expire_date)); ?></strong> পর্যন্ত বহাল রয়েছে। আপনি অন্ত্যমিল লাইব্রেরি থেকে বই ধার নিতে ও পাঠ কার্যক্রমে অংশ নিতে পারেন।
                        </p>
                        <div class="inline-flex gap-4">
                            <a href="../dashboard/index.php" class="px-6 py-3 bg-brand-900 text-white font-bold rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all text-sm">
                                ড্যাশবোর্ডে যান
                            </a>
                            <a href="../index.php" class="px-6 py-3 bg-gray-100 text-brand-900 font-bold rounded-xl hover:bg-gray-200 transition-all text-sm">
                                বই দেখুন
                            </a>
                        </div>
                    </div>

                <?php elseif ($pending_request): ?>
                    <!-- Pending Verification Screen -->
                    <div class="text-center py-6">
                        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 text-3xl animate-pulse">
                            ⏳
                        </div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold mb-3">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            রিকোয়েস্ট স্ট্যাটাস: পেন্ডিং (অপেক্ষমান)
                        </div>
                        <h2 class="text-2xl font-extrabold text-brand-900 mb-2">আপনার আবেদনটি পর্যালোচনায় রয়েছে</h2>
                        <p class="text-sm text-gray-500 max-w-lg mx-auto mb-6 leading-relaxed">
                            ধন্যবাদ! ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপের জন্য আপনার আবেদন ও তথ্য গ্রহণ করা হয়েছে। আমাদের অ্যাডমিন প্যানেল থেকে আপনার বয়স ও স্টুডেন্ট আইডি ভেরিফাই করে দ্রুত মেম্বারশিপ সক্রিয় করা হবে।
                        </p>

                        <!-- Submitted Info Summary Box -->
                        <div class="max-w-md mx-auto bg-gray-50 rounded-2xl p-5 border border-gray-100 text-left text-xs sm:text-sm space-y-2 mb-6">
                            <div class="flex justify-between pb-2 border-b border-gray-200/60">
                                <span class="text-gray-500">আবেদনকারীর নাম:</span>
                                <strong class="text-brand-900"><?php echo htmlspecialchars($member['full_name']); ?></strong>
                            </div>
                            <div class="flex justify-between pb-2 border-b border-gray-200/60">
                                <span class="text-gray-500">শিক্ষা প্রতিষ্ঠান:</span>
                                <strong class="text-brand-900"><?php echo htmlspecialchars($pending_request['institution_name']); ?></strong>
                            </div>
                            <div class="flex justify-between pb-2 border-b border-gray-200/60">
                                <span class="text-gray-500">স্টুডেন্ট আইডি / রোল:</span>
                                <strong class="text-brand-900 font-mono"><?php echo htmlspecialchars($pending_request['student_id_number']); ?></strong>
                            </div>
                            <div class="flex justify-between pb-2 border-b border-gray-200/60">
                                <span class="text-gray-500">জন্ম তারিখ ও বয়স:</span>
                                <strong class="text-brand-900"><?php echo date('d M, Y', strtotime($pending_request['dob'])); ?> (<?php echo $pending_request['age']; ?> বছর)</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">আবেদনের তারিখ:</span>
                                <strong class="text-brand-900"><?php echo date('d M, Y, h:i A', strtotime($pending_request['created_at'])); ?></strong>
                            </div>
                        </div>

                        <a href="../dashboard/index.php" class="inline-block px-8 py-3.5 bg-brand-900 text-white font-bold rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all text-sm shadow-md">
                            ড্যাশবোর্ডে ফিরে যান
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Application Form -->
                    <div>
                        <!-- Plan Key Highlights -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
                            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-center">
                                <span class="text-xs text-amber-800 font-bold block">মেম্বারশিপ ফি</span>
                                <span class="text-lg font-black text-amber-900">৳০ (সম্পূর্ণ ফ্রি)</span>
                            </div>
                            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/60 text-center">
                                <span class="text-xs text-blue-800 font-bold block">বয়সসীমা</span>
                                <span class="text-lg font-black text-blue-900">১২ — ১৯ বছর</span>
                            </div>
                            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/60 text-center">
                                <span class="text-xs text-emerald-800 font-bold block">মেয়াদ</span>
                                <span class="text-lg font-black text-emerald-900">১ বছর (নবায়নযোগ্য)</span>
                            </div>
                        </div>

                        <?php if ($latest_request && $latest_request['status'] === 'Rejected'): ?>
                            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <span>⚠️ আপনার পূর্ববর্তী আবেদনটি গৃহীত হয়নি:</span>
                                </div>
                                <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($latest_request['rejection_reason'] ?? 'তথ্য সঠিক ছিল না।'); ?></p>
                                <p class="text-gray-500 font-light">অনুগ্রহ করে সঠিক তথ্য ও স্টুডেন্ট আইডি কার্ডের ছবি দিয়ে পুনরায় আবেদন করুন।</p>
                            </div>
                        <?php endif; ?>

                        <form action="process_student_apply.php" method="POST" enctype="multipart/form-data" id="studentApplyForm" class="space-y-6">
                            
                            <!-- Member Info Display (Readonly context) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                <div>
                                    <label class="text-xs text-gray-500 block mb-1">আবেদনকারীর নাম (অ্যাকাউন্ট থেকে)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($member['full_name']); ?>" readonly 
                                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-800 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block mb-1">মোবাইল নম্বর / ইমেইল</label>
                                    <input type="text" value="<?php echo htmlspecialchars($member['phone'] ?: $member['email']); ?>" readonly 
                                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono text-gray-800 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Date of Birth Section -->
                            <div>
                                <label for="dob" class="block text-sm font-bold text-brand-900 mb-1.5">
                                    জন্ম তারিখ (Date of Birth) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" id="dob" name="dob" required 
                                           onchange="validateStudentAge(this.value)"
                                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-gold focus:border-brand-gold text-sm font-mono transition-all">
                                </div>
                                <div id="ageFeedback" class="mt-2 text-xs font-medium"></div>
                                <p class="text-[11px] text-gray-400 mt-1">
                                    * শুধুমাত্র ১২ থেকে ১৯ বছর বয়সী শিক্ষার্থীরা এই প্ল্যানের জন্য যোগ্য।
                                </p>
                            </div>

                            <!-- Institution Name -->
                            <div>
                                <label for="institution_name" class="block text-sm font-bold text-brand-900 mb-1.5">
                                    শিক্ষা প্রতিষ্ঠানের নাম <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="institution_name" name="institution_name" required 
                                       placeholder="উদাঃ মতিঝিল সরকারি বালক উচ্চ বিদ্যালয় / নটর ডেম কলেজ"
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-gold focus:border-brand-gold text-sm transition-all">
                            </div>

                            <!-- Student ID Number -->
                            <div>
                                <label for="student_id_number" class="block text-sm font-bold text-brand-900 mb-1.5">
                                    স্টুডেন্ট আইডি / রোল / রেজিস্ট্রেশন নম্বর <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="student_id_number" name="student_id_number" required 
                                       placeholder="উদাঃ ID-2024890 বা Roll-12"
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-gold focus:border-brand-gold text-sm font-mono transition-all">
                            </div>

                            <!-- ID Card Photo Upload -->
                            <div>
                                <label class="block text-sm font-bold text-brand-900 mb-1.5">
                                    স্টুডেন্ট আইডি কার্ড / ফি রসিদ / পরিচয়পত্রের ছবি <span class="text-red-500">*</span>
                                </label>
                                <div class="relative border-2 border-dashed border-gray-300 hover:border-brand-gold rounded-2xl p-6 text-center transition-all bg-gray-50/50 group cursor-pointer" onclick="document.getElementById('student_id_image').click()">
                                    <input type="file" id="student_id_image" name="student_id_image" accept="image/*,.pdf" required class="hidden" onchange="previewIdImage(event)">
                                    
                                    <div id="uploadPlaceholder">
                                        <div class="w-12 h-12 rounded-full bg-brand-gold/15 text-brand-gold flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-brand-900 mb-1">আইডি কার্ডের ছবি সিলেক্ট করুন বা ড্র্যাগ করে ছাড়ুন</p>
                                        <p class="text-xs text-gray-400">JPG, PNG, WEBP বা PDF (সর্বোচ্চ ৫ মেগাবাইট)</p>
                                    </div>

                                    <!-- Image Preview -->
                                    <div id="imagePreviewContainer" class="hidden flex flex-col items-center">
                                        <img id="imagePreview" src="#" alt="আইডি প্রিভিউ" class="max-h-48 rounded-xl object-contain border border-gray-200 shadow-md mb-2">
                                        <span id="fileNameDisplay" class="text-xs font-mono text-gray-600 font-bold"></span>
                                        <span class="text-xs text-brand-gold mt-1 underline">অন্য ছবি সিলেক্ট করতে ক্লিক করুন</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms & Declaration Checkbox -->
                            <div class="p-4 rounded-xl bg-brand-light border border-gray-100 flex items-start gap-3">
                                <input type="checkbox" id="declaration" required class="mt-1 w-4 h-4 text-brand-gold rounded border-gray-300 focus:ring-brand-gold cursor-pointer">
                                <label for="declaration" class="text-xs text-gray-600 leading-relaxed cursor-pointer select-none">
                                    আমি নিশ্চিত করছি যে আমি একজন নিয়মিত শিক্ষার্থী এবং আমার বয়স ১২ থেকে ১৯ বছরের মধ্যে। অন্ত্যমিলের নীতি ও শর্তাবলী মেনে চলব।
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button type="submit" id="submitBtn" 
                                        class="w-full py-4 rounded-xl bg-brand-900 text-white font-bold font-anek hover:bg-brand-gold hover:text-brand-900 transition-all text-base shadow-xl flex items-center justify-center gap-2">
                                    <span>বিনামূল্যে আবেদন জমা দিন</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<script>
function calculateAge(birthday) {
    const dob = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}

function validateStudentAge(dobValue) {
    const feedback = document.getElementById('ageFeedback');
    const submitBtn = document.getElementById('submitBtn');
    if (!dobValue) {
        feedback.innerHTML = '';
        return;
    }

    const age = calculateAge(dobValue);
    if (isNaN(age)) {
        feedback.innerHTML = '<span class="text-red-600">⚠️ সঠিক জন্ম তারিখ প্রদান করুন।</span>';
        return;
    }

    if (age < 12) {
        feedback.innerHTML = `<span class="text-red-600">⚠️ আপনার বর্তমান বয়স ${age} বছর। এই মেম্বারশিপটি ন্যূনতম ১২ বছর বয়সীদের জন্য।</span>`;
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else if (age > 19) {
        feedback.innerHTML = `<span class="text-red-600">⚠️ আপনার বর্তমান বয়স ${age} বছর। ‘বইয়ের আনন্দ-পাঠ’ শুধুমাত্র ১২ থেকে ১৯ বছর বয়সী তরুণ শিক্ষার্থীদের জন্য। আপনি আমাদের নিয়মিত মেম্বারশিপ প্ল্যানগুলো বেছে নিতে পারেন।</span>`;
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        feedback.innerHTML = `<span class="text-emerald-700 font-bold">✓ বয়স: ${age} বছর (যোগ্য)। আবেদন করতে পারেন।</span>`;
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

function previewIdImage(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('ফাইলের সাইজ ৫ মেগাবাইটের বেশি হতে পারবে না।');
            event.target.value = '';
            return;
        }

        const placeholder = document.getElementById('uploadPlaceholder');
        const container = document.getElementById('imagePreviewContainer');
        const preview = document.getElementById('imagePreview');
        const nameDisplay = document.getElementById('fileNameDisplay');

        nameDisplay.textContent = file.name + ' (' + (file.size / (1024*1024)).toFixed(2) + ' MB)';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                container.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
            placeholder.classList.add('hidden');
            container.classList.remove('hidden');
        }
    }
}

document.getElementById('studentApplyForm')?.addEventListener('submit', function(e) {
    const dobValue = document.getElementById('dob').value;
    const age = calculateAge(dobValue);
    if (age < 12 || age > 19) {
        e.preventDefault();
        alert('দুঃখিত! শুধুমাত্র ১২ থেকে ১৯ বছর বয়সী শিক্ষার্থীরা এই প্ল্যানের জন্য আবেদন করতে পারবেন।');
        return false;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> সাবমিট করা হচ্ছে...';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
