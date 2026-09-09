<?php
require_once __DIR__ . '/../includes/db_connect.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড পুনরুদ্ধার | অন্ত্যমিল</title>
    <meta name="description" content="অন্ত্যমিল অ্যাকাউন্টের পাসওয়ার্ড ওটিপি ভেরিফিকেশনের মাধ্যমে পুনরুদ্ধার করুন।">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700;800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-brand-light min-h-screen flex flex-col justify-between relative overflow-x-hidden text-brand-900">

    <!-- Ambient Glow Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
        <div class="mesh-gradient absolute inset-0 opacity-20"></div>
        <div class="absolute -top-24 -left-24 w-72 sm:w-96 md:w-[450px] h-72 sm:h-96 md:h-[450px] bg-brand-gold/15 blur-[90px] sm:blur-[130px] rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-72 sm:w-96 md:w-[450px] h-72 sm:h-96 md:h-[450px] bg-brand-900/10 blur-[90px] sm:blur-[130px] rounded-full"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2 relative z-20">
        <div class="flex items-center justify-between">
            <a href="../index.php" class="inline-flex items-center gap-2 group focus:outline-none p-1 transition-transform active:scale-95">
                <img src="../assets/img/logo.webp" alt="লোগো" class="w-9 sm:w-11 h-auto drop-shadow-sm transition-transform group-hover:scale-105">
                <span class="font-serif text-2xl sm:text-3xl font-bold tracking-wide text-brand-900">অন্ত্যমিল<span class="text-brand-gold">.</span></span>
            </a>

            <a href="../login/" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-anek font-semibold text-gray-600 hover:text-brand-900 bg-white/80 hover:bg-white border border-gray-200/80 shadow-sm hover:shadow px-3.5 py-1.5 rounded-full transition-all duration-200">
                <svg class="w-3.5 h-3.5 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>লগইন পেজে ফিরুন</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 my-auto flex flex-col items-center justify-center relative z-10">
        
        <!-- Recovery Card -->
        <div class="w-full max-w-[460px]">
            <div class="bg-white/95 backdrop-blur-md p-6 sm:p-9 md:p-10 rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100/90 relative">
                
                <!-- Dynamic Header -->
                <div class="flex flex-col items-center mb-6 text-center">
                    <div id="header-icon" class="w-12 h-12 rounded-2xl bg-brand-gold/10 border border-brand-gold/20 flex items-center justify-center mb-3 text-2xl text-brand-900">
                        🔑
                    </div>
                    <h2 id="heading" class="text-xl sm:text-2xl font-anek font-extrabold text-brand-900">পাসওয়ার্ড পুনরুদ্ধার</h2>
                    <p id="subheading" class="text-gray-500 text-xs sm:text-sm mt-1 font-anek">আপনার অ্যাকাউন্টের ইমেইল বা মোবাইল নম্বর দিন</p>
                    <div class="w-10 h-0.5 bg-brand-gold rounded-full mt-2.5"></div>
                </div>

                <!-- Alert Message Box -->
                <div id="alert-box" class="hidden mb-4 p-3 rounded-xl text-xs sm:text-sm font-anek font-semibold flex items-center gap-2.5 animate-slide-up" role="alert">
                    <span id="alert-icon"></span>
                    <span id="alert-text"></span>
                </div>

                <div id="recovery-steps">
                    <!-- Step 1: Email / Phone Input -->
                    <div id="step-1" class="space-y-4">
                        <div class="space-y-1">
                            <label for="login_input" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek ml-1">
                                ইমেইল বা মোবাইল নম্বর
                            </label>
                            <input 
                                type="text" 
                                id="login_input" 
                                required 
                                autofocus
                                placeholder="017XXXXXXXX বা mail@example.com" 
                                class="w-full bg-gray-50/80 border border-gray-200 rounded-xl px-4 py-3 text-sm font-anek text-brand-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                        </div>

                        <button 
                            type="button"
                            onclick="sendOTP()" 
                            id="btn-1" 
                            class="w-full py-3 bg-brand-900 text-white font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-brand-gold hover:text-brand-900 active:scale-[0.99] transition-all duration-200 shadow-md shadow-brand-900/15 flex items-center justify-center gap-2 group cursor-pointer">
                            <span>ওটিপি পাঠান</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: OTP Verification -->
                    <div id="step-2" class="hidden space-y-4">
                        <div class="text-center pb-1">
                            <p class="text-xs text-gray-600 font-anek">
                                আপনার ইমেইল <strong id="display-masked-email" class="text-brand-900 font-bold font-mono"></strong> এ একটি ৬-সংখ্যার কোড পাঠানো হয়েছে।
                            </p>
                        </div>

                        <div class="space-y-1 text-center">
                            <label for="otp_input" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek">
                                ৬-সংখ্যার ওটিপি কোড
                            </label>
                            <input 
                                type="text" 
                                id="otp_input" 
                                maxlength="6" 
                                placeholder="000000" 
                                class="w-full bg-gray-50/80 border border-gray-200 rounded-xl px-4 py-3 text-center text-2xl sm:text-3xl font-bold tracking-[0.3em] font-mono text-brand-900 placeholder:text-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                        </div>

                        <button 
                            type="button"
                            onclick="verifyOTP()" 
                            id="btn-2" 
                            class="w-full py-3 bg-brand-900 text-white font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-brand-gold hover:text-brand-900 active:scale-[0.99] transition-all duration-200 shadow-md shadow-brand-900/15 cursor-pointer">
                            ওটিপি যাচাই করুন
                        </button>

                        <div class="flex items-center justify-between pt-1">
                            <button 
                                type="button" 
                                onclick="showStep('step-1')" 
                                class="text-xs font-semibold text-gray-500 hover:text-brand-900 font-anek transition-colors">
                                ← তথ্য পরিবর্তন
                            </button>
                            
                            <button 
                                type="button" 
                                id="resend-btn"
                                onclick="resendOTP()" 
                                class="text-xs font-bold text-brand-gold hover:text-brand-900 font-anek transition-colors hover:underline">
                                পুনরায় ওটিপি পাঠান
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: New Password -->
                    <div id="step-3" class="hidden space-y-4">
                        <div class="space-y-1">
                            <label for="new_pass" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek ml-1">
                                নতুন পাসওয়ার্ড
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_pass" 
                                    required 
                                    placeholder="কমপক্ষে ৬ অক্ষর" 
                                    class="w-full bg-gray-50/80 border border-gray-200 rounded-xl pl-4 pr-10 py-3 text-sm font-anek text-brand-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                                <button 
                                    type="button" 
                                    onclick="togglePass('new_pass')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-900 p-1">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="confirm_pass" class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider font-anek ml-1">
                                পাসওয়ার্ড নিশ্চিত করুন
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="confirm_pass" 
                                    required 
                                    placeholder="একই পাসওয়ার্ড পুনরায় লিখুন" 
                                    class="w-full bg-gray-50/80 border border-gray-200 rounded-xl pl-4 pr-10 py-3 text-sm font-anek text-brand-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:border-brand-gold focus:bg-white transition-all">
                                <button 
                                    type="button" 
                                    onclick="togglePass('confirm_pass')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-900 p-1">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button 
                            type="button"
                            onclick="resetPassword()" 
                            id="btn-3" 
                            class="w-full py-3 bg-brand-900 text-white font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-brand-gold hover:text-brand-900 active:scale-[0.99] transition-all duration-200 shadow-md shadow-brand-900/15 cursor-pointer">
                            পাসওয়ার্ড পরিবর্তন সম্পন্ন করুন
                        </button>
                    </div>

                    <!-- Success Message -->
                    <div id="step-final" class="hidden text-center space-y-4 py-2">
                        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto border border-emerald-200 text-3xl">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-xl font-bold font-anek text-brand-900">পাসওয়ার্ড পরিবর্তন সফল!</h3>
                            <p class="text-gray-500 text-xs sm:text-sm font-anek mt-1">আপনার অ্যাকাউন্টের পাসওয়ার্ড সফলভাবে আপডেট করা হয়েছে।</p>
                        </div>
                        <a href="../login/" class="inline-flex items-center justify-center w-full py-3 bg-brand-900 text-white font-anek font-bold text-sm sm:text-base rounded-xl hover:bg-brand-gold hover:text-brand-900 transition-all shadow-md shadow-brand-900/15">
                            লগইন করুন →
                        </a>
                    </div>
                </div>

                <!-- Footer Support Note -->
                <div class="mt-6 pt-4 border-t border-gray-100 text-center font-anek">
                    <p class="text-[11px] text-gray-500">
                        কোনো সমস্যা হচ্ছে? 
                        <a href="mailto:info@ontomeel.com" class="text-brand-gold font-bold hover:underline">info@ontomeel.com</a>-এ যোগাযোগ করুন।
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-center text-gray-400 text-[11px] font-anek relative z-10">
        <p>&copy; <?php echo date('Y'); ?> অন্ত্যমিল (ontomeel.com) — সর্বস্বত্ব সংরক্ষিত।</p>
    </footer>

    <script>
        const API_DIR = window.location.pathname.replace(/\/index\.php$/, '').replace(/\/$/, '') + '/';
        const SEND_RECOVERY_URL = API_DIR + 'send_recovery_otp.php';
        const VERIFY_OTP_URL = API_DIR + 'verify_otp.php';
        const RESET_PASS_URL = API_DIR + 'reset_password.php';

        let currentEmail = "";
        let resendTimer = null;

        function showAlert(msg, isSuccess = false) {
            const box = document.getElementById('alert-box');
            const icon = document.getElementById('alert-icon');
            const text = document.getElementById('alert-text');
            
            box.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
            
            if (isSuccess) {
                box.classList.add('bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-200');
                icon.innerHTML = "✓";
            } else {
                box.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
                icon.innerHTML = "⚠️";
            }
            text.innerText = msg;
        }

        function hideAlert() {
            document.getElementById('alert-box').classList.add('hidden');
        }

        function togglePass(id) {
            const el = document.getElementById(id);
            if (el) el.type = el.type === 'password' ? 'text' : 'password';
        }

        function showStep(step) {
            hideAlert();
            ['step-1', 'step-2', 'step-3', 'step-final'].forEach(s => {
                const el = document.getElementById(s);
                if (el) el.classList.add('hidden');
            });
            const target = document.getElementById(step);
            if (target) target.classList.remove('hidden');
            
            const head = document.getElementById('heading');
            const sub = document.getElementById('subheading');
            const icon = document.getElementById('header-icon');
            
            if (step === 'step-1') {
                head.innerText = "পাসওয়ার্ড পুনরুদ্ধার";
                sub.innerText = "আপনার অ্যাকাউন্টের ইমেইল বা মোবাইল নম্বর দিন";
                icon.innerText = "🔑";
            } else if (step === 'step-2') {
                head.innerText = "ওটিপি যাচাই";
                sub.innerText = "আপনার ইমেইলে পাঠানো ৬-সংখ্যার ওটিপি দিন";
                icon.innerText = "📩";
                startResendTimer();
            } else if (step === 'step-3') {
                head.innerText = "নতুন পাসওয়ার্ড সেট করুন";
                sub.innerText = "কমপক্ষে ৬ অক্ষরের একটি শক্তিশালী পাসওয়ার্ড দিন";
                icon.innerText = "🔒";
            } else if (step === 'step-final') {
                head.innerText = "অভিনন্দন!";
                sub.innerText = "পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে";
                icon.innerText = "🎉";
            }
        }

        function startResendTimer() {
            let timeLeft = 60;
            const resendBtn = document.getElementById('resend-btn');
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-50', 'pointer-events-none');
            
            if (resendTimer) clearInterval(resendTimer);
            
            resendTimer = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(resendTimer);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('opacity-50', 'pointer-events-none');
                    resendBtn.innerText = "পুনরায় ওটিপি পাঠান";
                } else {
                    resendBtn.innerText = `পুনরায় পাঠান (${timeLeft}s)`;
                    timeLeft--;
                }
            }, 1000);
        }

        function sendOTP() {
            const inputVal = document.getElementById('login_input').value.trim();
            if (!inputVal) {
                showAlert("দয়া করে আপনার ইমেইল বা মোবাইল নম্বর লিখুন।");
                return;
            }
            
            hideAlert();
            const btn = document.getElementById('btn-1');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>ওটিপি পাঠানো হচ্ছে...</span>
            `;

            const formData = new FormData();
            formData.append('email', inputVal);

            fetch(SEND_RECOVERY_URL, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<span>ওটিপি পাঠান</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>`;
                    
                    if (data.success) {
                        currentEmail = data.email || inputVal;
                        if (data.masked_email) {
                            document.getElementById('display-masked-email').innerText = data.masked_email;
                        } else {
                            document.getElementById('display-masked-email').innerText = currentEmail;
                        }
                        showStep('step-2');
                    } else {
                        showAlert(data.message || "ওটিপি পাঠাতে সমস্যা হয়েছে।");
                    }
                }).catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = `<span>ওটিপি পাঠান</span>`;
                    showAlert("সার্ভার সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                });
        }

        function resendOTP() {
            if (!currentEmail) return;
            hideAlert();
            const resendBtn = document.getElementById('resend-btn');
            resendBtn.innerText = "পাঠানো হচ্ছে...";

            const formData = new FormData();
            formData.append('email', currentEmail);

            fetch(SEND_RECOVERY_URL, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("নতুন ওটিপি কোড পাঠানো হয়েছে।", true);
                        startResendTimer();
                    } else {
                        showAlert(data.message || "ওটিপি পুনরায় পাঠাতে সমস্যা হয়েছে।");
                        resendBtn.innerText = "পুনরায় ওটিপি পাঠান";
                    }
                }).catch(() => {
                    showAlert("সার্ভার সংযোগ সমস্যা।");
                    resendBtn.innerText = "পুনরায় ওটিপি পাঠান";
                });
        }

        function verifyOTP() {
            const otp = document.getElementById('otp_input').value.trim();
            if (!otp || otp.length < 6) {
                showAlert("সঠিক ৬-সংখ্যার ওটিপি কোড লিখুন।");
                return;
            }

            hideAlert();
            const btn = document.getElementById('btn-2');
            btn.disabled = true;
            btn.innerText = "যাচাই করা হচ্ছে...";

            const formData = new FormData();
            formData.append('email', currentEmail);
            formData.append('otp', otp);

            fetch(VERIFY_OTP_URL, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerText = "ওটিপি যাচাই করুন";
                    
                    if (data.success) {
                        showStep('step-3');
                    } else {
                        showAlert(data.message || "ভুল ওটিপি কোড!");
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.innerText = "ওটিপি যাচাই করুন";
                    showAlert("যাচাই করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                });
        }

        function resetPassword() {
            const p1 = document.getElementById('new_pass').value;
            const p2 = document.getElementById('confirm_pass').value;
            
            if (!p1 || p1.length < 6) {
                showAlert("পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।");
                return;
            }
            if (p1 !== p2) {
                showAlert("উভয় পাসওয়ার্ড হুবহু এক হতে হবে।");
                return;
            }

            hideAlert();
            const btn = document.getElementById('btn-3');
            btn.disabled = true;
            btn.innerText = "সংরক্ষণ করা হচ্ছে...";

            const formData = new FormData();
            formData.append('email', currentEmail);
            formData.append('password', p1);

            fetch(RESET_PASS_URL, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerText = "পাসওয়ার্ড পরিবর্তন সম্পন্ন করুন";
                    
                    if (data.success) {
                        showStep('step-final');
                    } else {
                        showAlert(data.message || "পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে।");
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.innerText = "পাসওয়ার্ড পরিবর্তন সম্পন্ন করুন";
                    showAlert("সার্ভার সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                });
        }
    </script>
</body>
</html>
