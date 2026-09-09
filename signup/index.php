<?php
require_once __DIR__ . '/../includes/db_connect.php';

// If already logged in, redirect to dashboard or redirect destination
if (isset($_SESSION['user_id'])) {
    $redirect = trim($_GET['redirect'] ?? '');
    if (!empty($redirect) && !preg_match('/^https?:\/\/|^\/\//i', $redirect)) {
        header("Location: " . $redirect);
    } else {
        header("Location: ../dashboard/");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রেজিস্ট্রেশন | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-brand-light min-h-screen flex items-center justify-center relative overflow-x-hidden py-6 md:py-20">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-20"></div>
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-gold/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-brand-900/10 blur-[120px] rounded-full"></div>

    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-10 md:gap-20 relative z-10">

        <!-- Branding Side -->
        <div class="hidden md:block w-1/2">
            <a href="../index.php" class="flex items-center gap-3 mb-10">
                <img src="../assets/img/logo.webp" alt="logo" class="w-16 h-auto">
                <span class="font-serif text-4xl font-bold tracking-wide text-brand-900 mt-1">অন্ত্যমিল<span class="text-brand-gold">.</span></span>
            </a>
            <h1 class="text-5xl lg:text-6xl font-anek font-extrabold text-brand-900 leading-tight mb-8">
                গল্পের নতুন <br> অধ্যায় শুরু হোক আপনার
            </h1>
            <p class="text-gray-500 text-lg font-light max-w-md">
                অন্ত্যমিল মেম্বারশিপ নিয়ে আমাদের বিশাল লাইব্রেরি ও এক্সক্লুসিভ কালেকশনে অ্যাক্সেস পান মুহূর্তেই।
            </p>
        </div>

        <!-- Signup Card -->
        <div class="w-full max-w-[500px] md:w-[500px]">
            <div class="bg-white p-6 sm:p-10 md:p-12 rounded-3xl md:rounded-[40px] shadow-2xl border border-gray-100">
                <!-- Mobile Logo -->
                <div class="md:hidden flex flex-col items-center mb-8 text-center">
                    <img src="../assets/img/logo.webp" alt="logo" class="w-10 h-auto mb-3">
                    <h2 class="text-2xl font-anek font-extrabold text-brand-900 leading-tight">নতুন অ্যাকাউন্ট তৈরি করি</h2>
                    <div class="w-12 h-1 bg-brand-gold rounded-full mt-2"></div>
                </div>

                <div class="hidden md:block mb-8 text-center">
                    <h2 class="text-3xl font-anek font-bold text-brand-900">রেজিস্ট্রেশন</h2>
                    <p class="text-gray-400 text-sm mt-2 font-anek">আপনার সঠিক তথ্য দিয়ে ফরমটি পূরণ করুন</p>
                </div>

                <!-- Inline Alert Container -->
                <div id="alert-box" class="hidden mb-5 p-3.5 rounded-2xl text-xs sm:text-sm font-anek font-semibold flex items-center gap-2.5 animate-slide-up" role="alert">
                    <span id="alert-icon" class="text-base"></span>
                    <span id="alert-text"></span>
                </div>

                <form id="signup-form" onsubmit="handleSignup(event)" class="space-y-5">
                    <!-- Step 1: Basic Info -->
                    <div id="step-1" class="space-y-4 transition-all duration-300">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">
                                সম্পূর্ণ নাম (English)
                            </label>
                            <input type="text" name="full_name" id="full_name" required placeholder="Ex: Sayeam Ahmed"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">ইমেইল</label>
                                <input type="email" name="email" id="email" required placeholder="name@mail.com"
                                    class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">মোবাইল</label>
                                <input type="tel" name="phone" id="phone" required placeholder="017XXXXXXXX"
                                    class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 md:text-gray-500 uppercase tracking-widest font-anek ml-2">পাসওয়ার্ড</label>
                            <input type="password" name="password" id="password" required placeholder="কমপক্ষে ৬ অক্ষর"
                                class="w-full bg-brand-light md:bg-gray-50 border border-transparent md:border-gray-100 rounded-2xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>

                        <div class="flex items-center gap-3 ml-2 py-1">
                            <input type="checkbox" required id="terms"
                                class="w-4 h-4 rounded border-gray-300 text-brand-gold focus:ring-brand-gold cursor-pointer">
                            <label for="terms" class="text-[11px] text-gray-500 font-medium font-anek uppercase tracking-wider cursor-pointer select-none">
                                আমি সকল শর্তাবলির সাথে একমত
                            </label>
                        </div>

                        <button type="button" onclick="sendOTP()" id="otp-btn"
                            class="w-full py-4 bg-brand-900 text-white font-anek font-bold text-base md:text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10 flex items-center justify-center gap-3 group cursor-pointer">
                            <span>ওটিপি পাঠান</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: OTP Verification -->
                    <div id="step-2" class="space-y-5 hidden transition-all duration-300">
                        <div class="text-center">
                            <div class="w-14 h-14 bg-brand-gold/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-brand-900 font-anek">ইমেইল ভেরিফিকেশন</h3>
                            <p class="text-gray-500 text-xs mt-1 font-anek">
                                আপনার ইমেইল <strong id="sent-email-display" class="text-brand-900 font-bold font-mono"></strong>-এ ওটিপি পাঠানো হয়েছে।
                            </p>
                        </div>

                        <div class="space-y-1 text-center">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek">৬-সংখ্যার ওটিপি কোড</label>
                            <input type="text" name="otp" id="otp" maxlength="6" placeholder="000000"
                                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-3.5 text-center text-3xl font-bold tracking-[0.3em] font-mono focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all text-brand-900 placeholder:text-gray-200">
                        </div>

                        <button type="submit" id="submit-btn"
                            class="w-full py-4 bg-brand-900 text-white font-anek font-bold text-base md:text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/20 cursor-pointer">
                            ভেরিফাই ও সম্পূর্ণ করুন
                        </button>

                        <div class="flex items-center justify-between pt-1">
                            <button type="button" onclick="backToStep1()"
                                class="text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-brand-900 transition-colors font-anek">
                                ← তথ্য পরিবর্তন
                            </button>

                            <button type="button" id="resend-btn" onclick="resendOTP()"
                                class="text-xs font-bold text-brand-gold hover:text-brand-900 transition-colors font-anek hover:underline">
                                পুনরায় ওটিপি পাঠান
                            </button>
                        </div>
                    </div>

                    <div class="pt-5 text-center border-t border-gray-50 mt-5 font-anek">
                        <p class="text-gray-400 text-sm">
                            ইতিমধ্যে একাউন্ট আছে? 
                            <a href="../login/" class="text-brand-gold font-bold hover:underline ml-1">লগইন করুন</a>
                        </p>
                    </div>
                </form>

                <script>
                    const API_DIR = window.location.pathname.replace(/\/index\.php$/, '').replace(/\/$/, '') + '/';
                    const SEND_OTP_URL = API_DIR + 'send_otp.php';
                    const PROCESS_SIGNUP_URL = API_DIR + 'process_signup.php';

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

                    function startResendCountdown() {
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
                        const fullName = document.getElementById('full_name').value.trim();
                        const email = document.getElementById('email').value.trim();
                        const phone = document.getElementById('phone').value.trim();
                        const password = document.getElementById('password').value;
                        const terms = document.getElementById('terms').checked;

                        if (!fullName || !email || !phone || !password) {
                            showAlert("সবগুলো তথ্য সঠিকভাবে পূরণ করুন।");
                            return;
                        }

                        if (!terms) {
                            showAlert("শর্তাবলির সাথে একমত হতে হবে।");
                            return;
                        }

                        hideAlert();
                        const btn = document.getElementById('otp-btn');
                        btn.disabled = true;
                        btn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>ওটিপি পাঠানো হচ্ছে...</span>
                        `;

                        const formData = new FormData();
                        formData.append('full_name', fullName);
                        formData.append('email', email);
                        formData.append('phone', phone);
                        formData.append('password', password);

                        fetch(SEND_OTP_URL, {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.json())
                            .then(data => {
                                btn.disabled = false;
                                btn.innerHTML = `<span>ওটিপি পাঠান</span><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>`;

                                if (data.success) {
                                    document.getElementById('sent-email-display').innerText = email;
                                    document.getElementById('step-1').classList.add('hidden');
                                    document.getElementById('step-2').classList.remove('hidden');
                                    startResendCountdown();
                                    showAlert("আপনার ইমেইলে ওটিপি পাঠানো হয়েছে।", true);
                                } else {
                                    showAlert(data.message || "ওটিপি পাঠাতে সমস্যা হয়েছে।");
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                btn.disabled = false;
                                btn.innerHTML = `<span>ওটিপি পাঠান</span>`;
                                showAlert("সার্ভার সমস্যা হয়েছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।");
                            });
                    }

                    function resendOTP() {
                        const fullName = document.getElementById('full_name').value.trim();
                        const email = document.getElementById('email').value.trim();
                        const phone = document.getElementById('phone').value.trim();
                        const password = document.getElementById('password').value;

                        hideAlert();
                        const resendBtn = document.getElementById('resend-btn');
                        resendBtn.innerText = "পাঠানো হচ্ছে...";

                        const formData = new FormData();
                        formData.append('full_name', fullName);
                        formData.append('email', email);
                        formData.append('phone', phone);
                        formData.append('password', password);

                        fetch(SEND_OTP_URL, {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    showAlert("নতুন ওটিপি কোড পাঠানো হয়েছে।", true);
                                    startResendCountdown();
                                } else {
                                    showAlert(data.message || "ওটিপি পাঠাতে সমস্যা হয়েছে।");
                                    resendBtn.innerText = "পুনরায় ওটিপি পাঠান";
                                }
                            })
                            .catch(err => {
                                showAlert("সার্ভার সমস্যা হয়েছে।");
                                resendBtn.innerText = "পুনরায় ওটিপি পাঠান";
                            });
                    }

                    function backToStep1() {
                        hideAlert();
                        document.getElementById('step-2').classList.add('hidden');
                        document.getElementById('step-1').classList.remove('hidden');
                        const otpBtn = document.getElementById('otp-btn');
                        otpBtn.disabled = false;
                        otpBtn.innerHTML = `<span>ওটিপি পাঠান</span><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>`;
                    }

                    function handleSignup(e) {
                        e.preventDefault();
                        const otp = document.getElementById('otp').value.trim();
                        if (!otp || otp.length < 6) {
                            showAlert("সঠিক ৬-সংখ্যার ওটিপি কোড লিখুন।");
                            return;
                        }

                        hideAlert();
                        const btn = document.getElementById('submit-btn');
                        btn.disabled = true;
                        btn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>ভেরিফাই হচ্ছে...</span>
                        `;

                        const formData = new FormData(e.target);

                        fetch(PROCESS_SIGNUP_URL, {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const redirectUrl = urlParams.get('redirect');
                                    if (redirectUrl && !/^https?:\/\/|^\/\//i.test(redirectUrl)) {
                                        window.location.href = redirectUrl;
                                    } else {
                                        window.location.href = "../dashboard/?welcome=1";
                                    }
                                } else {
                                    showAlert(data.message || "ভেরিফিকেশন সম্পন্ন হয়নি।");
                                    btn.disabled = false;
                                    btn.innerText = "ভেরিফাই ও সম্পূর্ণ করুন";
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                showAlert("ভেরিফিকেশন করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                                btn.disabled = false;
                                btn.innerText = "ভেরিফাই ও সম্পূর্ণ করুন";
                            });
                    }
                </script>
            </div>
        </div>
    </div>

</body>

</html>