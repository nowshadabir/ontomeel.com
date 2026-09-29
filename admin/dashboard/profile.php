<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'profile';
$page_title = 'প্রোফাইল সেটিংস';

$admin_id = (int)($_SESSION['admin_id'] ?? 0);

// Fetch fresh admin info from DB
$admin_row = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$admin_row->execute([$admin_id]);
$current_admin = $admin_row->fetch(PDO::FETCH_ASSOC) ?: [];

$admin_fullname = $current_admin['full_name'] ?? ($_SESSION['admin_full_name'] ?? $_SESSION['admin_name'] ?? '');
$admin_username = $current_admin['username'] ?? ($_SESSION['admin_username'] ?? 'admin');
$admin_email = $current_admin['email'] ?? '';
$admin_role = $current_admin['role'] ?? ($_SESSION['admin_role'] ?? 'admin');
$admin_lastlogin = $current_admin['last_login'] ?? '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title><?php echo $page_title; ?> - অন্তমীল এডমিন</title>
</head>
<body class="bg-[#faf8f5] text-stone-900 font-anek antialiased min-h-screen flex selection:bg-stone-200 selection:text-stone-900">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#faf8f5] lg:pl-64">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <main class="flex-1 p-6 lg:p-10 space-y-8 max-w-7xl w-full mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e7e3da] pb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                    <p class="text-xs text-stone-500 font-light mt-1">আপনার অ্যাকাউন্ট তথ্য, নিরাপত্তা সেটিংস ও পাসওয়ার্ড পরিবর্তন করুন।</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Profile Summary Card -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white border border-[#e7e3da] rounded-xl p-6 space-y-6 shadow-xs">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-stone-900 flex items-center justify-center text-2xl font-mono font-bold text-white shadow-xs">
                                <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-semibold text-stone-900 truncate">
                                    <?php echo htmlspecialchars($admin_fullname ?: $admin_username); ?>
                                </h3>
                                <p class="text-xs font-mono text-stone-500 mt-0.5">@<?php echo htmlspecialchars($admin_username); ?></p>
                                <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7]">
                                    <?php echo htmlspecialchars($admin_role); ?>
                                </span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-[#e7e3da] space-y-3.5 text-xs font-mono">
                            <div>
                                <span class="text-stone-400 block text-[11px] uppercase">ইমেইল ঠিকানা</span>
                                <span class="text-stone-800 mt-0.5 block truncate font-medium">
                                    <?php echo !empty($admin_email) ? htmlspecialchars($admin_email) : '<span class="text-amber-600">যুক্ত করা হয়নি</span>'; ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[11px] uppercase">সর্বশেষ লগইন</span>
                                <span class="text-stone-700 mt-0.5 block font-medium">
                                    <?php echo !empty($admin_lastlogin) ? date('d M Y, h:i A', strtotime($admin_lastlogin)) : '—'; ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-[11px] uppercase">অ্যাকাউন্ট স্ট্যাটাস</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-stone-800 text-xs font-sans font-medium">সক্রিয়</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Profile Settings Forms -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Form 1: Personal Info -->
                    <div class="bg-white border border-[#e7e3da] rounded-xl p-6 space-y-6 shadow-xs">
                        <div class="border-b border-[#e7e3da] pb-4">
                            <h3 class="text-sm font-semibold text-stone-900 font-mono uppercase tracking-wider">ব্যক্তিগত তথ্য</h3>
                            <p class="text-xs text-stone-500 mt-0.5">আপনার নাম ও যাচাইকৃত ইমেইল আপডেট করুন</p>
                        </div>

                        <form id="profile-info-form" onsubmit="updateProfileInfo(event)" class="space-y-4">
                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">পূর্ণ নাম</label>
                                <input type="text" name="full_name" id="profile-fullname"
                                       value="<?php echo htmlspecialchars($admin_fullname); ?>"
                                       placeholder="আপনার পূর্ণ নাম"
                                       class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                            </div>

                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">ইউজারনেম (অপরিবর্তনযোগ্য)</label>
                                <input type="text" value="<?php echo htmlspecialchars($admin_username); ?>" readonly
                                       class="w-full bg-[#f4f1ea] border border-[#e7e3da] rounded-lg px-3.5 py-2 text-xs font-mono text-stone-500 cursor-not-allowed">
                            </div>

                            <!-- Email with OTP verification -->
                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">ইমেইল ঠিকানা</label>
                                <div class="flex gap-2">
                                    <input type="email" name="email" id="profile-email-input"
                                           value="<?php echo htmlspecialchars($admin_email); ?>"
                                           placeholder="example@email.com"
                                           class="flex-1 bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs font-mono text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800">
                                    <button type="button" onclick="sendProfileEmailOtp()" id="profile-send-otp-btn"
                                            class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-700 border border-[#d8d3c7] rounded-lg text-xs font-mono font-medium transition-colors whitespace-nowrap">
                                        OTP পাঠান
                                    </button>
                                </div>

                                <!-- OTP Input Row (hidden until OTP sent) -->
                                <div id="profile-otp-row" class="hidden mt-3 p-3 bg-[#faf8f5] border border-[#e7e3da] rounded-lg space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text" id="profile-otp-input" maxlength="6" placeholder="৬-সংখ্যার কোড"
                                               class="flex-1 bg-white border border-[#d8d3c7] rounded-lg px-3 py-1.5 text-xs font-mono text-center tracking-[0.3em] text-stone-900 focus:outline-none focus:border-stone-800">
                                        <button type="button" onclick="verifyProfileEmailOtp()" id="profile-verify-otp-btn"
                                                class="px-4 py-1.5 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold shadow-xs">
                                            যাচাই
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-stone-500 font-mono">ইমেইলে পাঠানো ৬-সংখ্যার OTP কোডটি লিখুন।</p>
                                </div>
                                <div id="profile-email-msg" class="text-xs font-mono mt-1.5 hidden"></div>
                            </div>

                            <div id="profile-info-msg" class="text-xs font-mono hidden"></div>

                            <button type="submit" id="profile-info-save-btn"
                                    class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-xs">
                                নাম সংরক্ষণ করুন
                            </button>
                        </form>
                    </div>

                    <!-- Form 2: Password Change -->
                    <div class="bg-white border border-[#e7e3da] rounded-xl p-6 space-y-6 shadow-xs">
                        <div class="border-b border-[#e7e3da] pb-4">
                            <h3 class="text-sm font-semibold text-stone-900 font-mono uppercase tracking-wider">পাসওয়ার্ড পরিবর্তন</h3>
                            <p class="text-xs text-stone-500 mt-0.5">অ্যাকাউন্টের নিরাপত্তা নিশ্চিত করতে শক্তিশালী পাসওয়ার্ড ব্যবহার করুন</p>
                        </div>

                        <form id="change-password-form" onsubmit="updateAdminPassword(event)" class="space-y-4">
                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">বর্তমান পাসওয়ার্ড *</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="cp-current" required placeholder="বর্তমান পাসওয়ার্ড"
                                           class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs font-mono text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800">
                                    <button type="button" onclick="togglePw('cp-current')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 text-xs font-mono">দেখুন</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">নতুন পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর) *</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="cp-new" required minlength="8" placeholder="নতুন পাসওয়ার্ড"
                                           class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs font-mono text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800">
                                    <button type="button" onclick="togglePw('cp-new')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 text-xs font-mono">দেখুন</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-mono text-stone-600 uppercase mb-1.5">নতুন পাসওয়ার্ড নিশ্চিত করুন *</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="cp-confirm" required minlength="8" placeholder="পুনরায় নতুন পাসওয়ার্ড"
                                           class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3.5 py-2 text-xs font-mono text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800">
                                    <button type="button" onclick="togglePw('cp-confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 text-xs font-mono">দেখুন</button>
                                </div>
                            </div>

                            <div id="change-pw-msg" class="text-xs font-mono hidden"></div>

                            <button type="submit" id="change-pw-btn"
                                    class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-xs">
                                পাসওয়ার্ড পরিবর্তন করুন
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-stone-900 text-white border border-stone-800 px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 text-xs font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="toast-message">নোটিফিকেশন বার্তা</span>
        </div>
    </div>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        function togglePw(inputId) {
            const el = document.getElementById(inputId);
            if (el.type === 'password') el.type = 'text';
            else el.type = 'password';
        }

        function updateProfileInfo(e) {
            e.preventDefault();
            const fullName = document.getElementById('profile-fullname').value.trim();
            const msgEl = document.getElementById('profile-info-msg');
            const btn = document.getElementById('profile-info-save-btn');

            msgEl.classList.add('hidden');
            if (!fullName) {
                msgEl.textContent = 'নাম খালি রাখা যাবে না।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            const formData = new FormData();
            formData.append('action', 'update_profile_name');
            formData.append('full_name', fullName);

            fetch('/admin/dashboard/process_settings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                msgEl.textContent = data.message;
                msgEl.className = `text-xs font-mono ${data.success ? 'text-emerald-600' : 'text-red-600'}`;
                msgEl.classList.remove('hidden');
                if (data.success) showToast('প্রোফাইল নাম আপডেট হয়েছে।');
            })
            .catch(() => {
                msgEl.textContent = 'সার্ভার সংযোগে ত্রুটি।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'নাম সংরক্ষণ করুন';
            });
        }

        function sendProfileEmailOtp() {
            const email = document.getElementById('profile-email-input').value.trim();
            const msgEl = document.getElementById('profile-email-msg');
            const btn = document.getElementById('profile-send-otp-btn');

            msgEl.classList.add('hidden');
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                msgEl.textContent = 'সঠিক ইমেইল ঠিকানা দিন।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'পাঠানো হচ্ছে...';

            const formData = new FormData();
            formData.append('action', 'send_email_otp');
            formData.append('email', email);

            fetch('/admin/dashboard/process_settings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profile-otp-row').classList.remove('hidden');
                    msgEl.textContent = `${email} ঠিকানায় OTP কোড পাঠানো হয়েছে।`;
                    msgEl.className = 'text-xs font-mono text-emerald-600';
                    msgEl.classList.remove('hidden');
                    btn.textContent = 'পুনরায় পাঠান';
                } else {
                    msgEl.textContent = data.message || 'OTP পাঠাতে সমস্যা হয়েছে।';
                    msgEl.className = 'text-xs font-mono text-red-600';
                    msgEl.classList.remove('hidden');
                    btn.textContent = 'OTP পাঠান';
                }
            })
            .catch(() => {
                msgEl.textContent = 'সার্ভার সমস্যা।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                btn.textContent = 'OTP পাঠান';
            })
            .finally(() => { btn.disabled = false; });
        }

        function verifyProfileEmailOtp() {
            const otp = document.getElementById('profile-otp-input').value.trim();
            const msgEl = document.getElementById('profile-email-msg');
            const btn = document.getElementById('profile-verify-otp-btn');

            if (!otp || otp.length !== 6) {
                msgEl.textContent = '৬-সংখ্যার OTP কোড দিন।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'যাচাই হচ্ছে...';

            const formData = new FormData();
            formData.append('action', 'verify_email_otp');
            formData.append('otp', otp);

            fetch('/admin/dashboard/process_settings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profile-otp-row').classList.add('hidden');
                    msgEl.textContent = '✅ ইমেইল সফলভাবে আপডেট হয়েছে।';
                    msgEl.className = 'text-xs font-mono text-emerald-600';
                    msgEl.classList.remove('hidden');
                    showToast('ইমেইল যাচাই সম্পন্ন!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msgEl.textContent = data.message || 'OTP সঠিক নয়।';
                    msgEl.className = 'text-xs font-mono text-red-600';
                    msgEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'যাচাই';
                }
            })
            .catch(() => {
                msgEl.textContent = 'সার্ভার সমস্যা।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'যাচাই';
            });
        }

        function updateAdminPassword(e) {
            e.preventDefault();
            const msgEl = document.getElementById('change-pw-msg');
            const btn = document.getElementById('change-pw-btn');
            const newPw = document.getElementById('cp-new').value;
            const cfPw = document.getElementById('cp-confirm').value;

            msgEl.classList.add('hidden');

            if (newPw !== cfPw) {
                msgEl.textContent = 'নতুন পাসওয়ার্ড দুটি মিলছে না।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'পরিবর্তন হচ্ছে...';

            const formData = new FormData(document.getElementById('change-password-form'));
            formData.append('action', 'update_password');

            fetch('/admin/dashboard/process_settings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                msgEl.textContent = data.message;
                msgEl.className = `text-xs font-mono ${data.success ? 'text-emerald-600' : 'text-red-600'}`;
                msgEl.classList.remove('hidden');
                if (data.success) {
                    document.getElementById('change-password-form').reset();
                    showToast('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে!');
                }
            })
            .catch(() => {
                msgEl.textContent = 'সার্ভার সমস্যা।';
                msgEl.className = 'text-xs font-mono text-red-600';
                msgEl.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'পাসওয়ার্ড পরিবর্তন করুন';
            });
        }
    </script>
</body>
</html>
