<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন পাসওয়ার্ড পুনরুদ্ধার | অন্ত্যমিল</title>

    <!-- Google Fonts for Bengali & Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="antialiased bg-[#faf8f5] text-stone-900 min-h-screen flex items-center justify-center relative py-10 px-4 sm:px-6 selection:bg-stone-200 selection:text-stone-900">

    <div class="max-w-md w-full relative z-10">
        
        <!-- Recovery Card -->
        <div class="bg-white border border-[#e7e3da] p-6 sm:p-8 rounded-2xl shadow-sm">
            
            <!-- Header -->
            <div class="flex flex-col items-center mb-6 text-center">
                <img src="../../assets/img/logo.webp" alt="logo" class="w-10 h-auto mb-3">
                <h2 id="heading" class="text-lg font-anek font-semibold text-stone-900">অ্যাডমিন পাসওয়ার্ড পুনরুদ্ধার</h2>
                <p id="subheading" class="text-stone-500 text-xs mt-1 font-anek">আপনার অফিসিয়াল ইমেইল প্রদান করুন</p>
            </div>

            <div id="recovery-steps">
                <!-- Step 1: Email Input -->
                <div id="step-1" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-anek text-stone-600">অফিসিয়াল ইমেইল</label>
                        <input type="email" id="email" required placeholder="info@ontomeel.com" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                    </div>
                    <button onclick="sendOTP()" id="btn-1" class="w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                        <span>ওটিপি পাঠান</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="step-2" class="hidden space-y-4">
                    <div class="space-y-2 text-center">
                        <label class="text-xs font-anek text-stone-600">৬-সংখ্যার ওটিপি</label>
                        <input type="text" id="otp" maxlength="6" placeholder="000000" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-4 py-3 text-center text-xl font-mono font-bold tracking-[0.3em] text-stone-900 focus:outline-none focus:border-stone-800 transition-colors">
                    </div>
                    <button onclick="verifyOTP()" id="btn-2" class="w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 active:scale-[0.99] transition-all cursor-pointer shadow-xs">
                        ভেরিফাই করুন
                    </button>
                    <button onclick="resendOTP()" class="w-full text-center text-[11px] font-anek text-stone-500 hover:text-stone-900 transition-colors cursor-pointer">ওটিপি পাননি? পুনরায় পাঠান</button>
                </div>

                <!-- Step 3: New Password -->
                <div id="step-3" class="hidden space-y-4">
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-anek text-stone-600">নতুন পাসওয়ার্ড</label>
                            <input type="password" id="new_pass" required placeholder="••••••••" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-anek text-stone-600">পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" id="confirm_pass" required placeholder="••••••••" class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 transition-colors">
                        </div>
                    </div>
                    <button onclick="resetPassword()" id="btn-3" class="w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 active:scale-[0.99] transition-all cursor-pointer shadow-xs">
                        পাসওয়ার্ড সেভ করুন
                    </button>
                </div>

                <!-- Success Message -->
                <div id="step-final" class="hidden text-center space-y-4 py-4">
                    <div class="w-12 h-12 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold font-anek text-stone-900">পাসওয়ার্ড পরিবর্তন সফল!</h3>
                        <p class="text-stone-500 text-xs mt-1 font-anek">এখন আপনি নতুন পাসওয়ার্ড দিয়ে লগইন করতে পারেন।</p>
                    </div>
                    <a href="../login/" class="inline-block w-full py-2.5 bg-stone-900 text-white font-anek font-semibold text-xs rounded-lg hover:bg-stone-800 transition-colors shadow-xs">লগইন পেজে ফিরে যান</a>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-[#e7e3da] text-center">
                <a href="../login/" class="inline-flex items-center gap-1.5 text-xs text-stone-500 hover:text-stone-900 transition-colors font-anek">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>লগইন পেজে ফিরে যান</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Modal / Toast for Ajax feedback -->
    <div id="toast" class="fixed top-5 right-5 z-50 transform translate-y-[-150%] transition-transform duration-300">
        <div id="toast-body" class="bg-stone-900 text-white border border-stone-800 px-4 py-2.5 rounded-lg text-xs font-anek flex items-center gap-2 shadow-2xl">
            <span id="toast-icon"></span>
            <span id="toast-text">বার্তা</span>
        </div>
    </div>

    <script>
        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('toast');
            const toastBody = document.getElementById('toast-body');
            const toastText = document.getElementById('toast-text');
            const toastIcon = document.getElementById('toast-icon');

            toastText.innerText = msg;
            if (isSuccess) {
                toastBody.className = "bg-stone-900 text-white border border-emerald-500 px-4 py-2.5 rounded-lg text-xs font-anek flex items-center gap-2 shadow-2xl";
                toastIcon.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
            } else {
                toastBody.className = "bg-stone-900 text-white border border-red-500 px-4 py-2.5 rounded-lg text-xs font-anek flex items-center gap-2 shadow-2xl";
                toastIcon.innerHTML = `<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }

            toast.classList.remove('translate-y-[-150%]');
            setTimeout(() => {
                toast.classList.add('translate-y-[-150%]');
            }, 3500);
        }

        let userEmail = "";

        function sendOTP() {
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();
            const btn = document.getElementById('btn-1');

            if (!email) {
                showToast("অনুগ্রহ করে আপনার ইমেইল প্রদান করুন", false);
                return;
            }

            btn.innerHTML = `<span>পাঠানো হচ্ছে...</span>`;
            btn.disabled = true;

            const fd = new FormData();
            fd.append('action', 'send_otp');
            fd.append('email', email);

            fetch('recover_action.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = `<span>ওটিপি পাঠান</span><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>`;
                
                if (data.status === 'success') {
                    userEmail = email;
                    showToast(data.message, true);
                    document.getElementById('step-1').classList.add('hidden');
                    document.getElementById('step-2').classList.remove('hidden');
                    document.getElementById('heading').innerText = "ওটিপি ভেরিফিকেশন";
                    document.getElementById('subheading').innerText = "আপনার ইমেইলে পাঠানো ৬ সংখ্যার ওটিপি কোডটি লিখুন";
                } else {
                    showToast(data.message, false);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<span>ওটিপি পাঠান</span>`;
                showToast("সার্ভারে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।", false);
            });
        }

        function resendOTP() {
            if (!userEmail) return;
            const fd = new FormData();
            fd.append('action', 'send_otp');
            fd.append('email', userEmail);

            fetch('recover_action.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast("নতুন ওটিপি পাঠানো হয়েছে", true);
                } else {
                    showToast(data.message, false);
                }
            });
        }

        function verifyOTP() {
            const otpInput = document.getElementById('otp');
            const otp = otpInput.value.trim();
            const btn = document.getElementById('btn-2');

            if (otp.length !== 6) {
                showToast("সঠিক ৬-সংখ্যার ওটিপি লিখুন", false);
                return;
            }

            btn.innerText = "যাচাই হচ্ছে...";
            btn.disabled = true;

            const fd = new FormData();
            fd.append('action', 'verify_otp');
            fd.append('otp', otp);

            fetch('recover_action.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = "ভেরিফাই করুন";

                if (data.status === 'success') {
                    showToast(data.message, true);
                    document.getElementById('step-2').classList.add('hidden');
                    document.getElementById('step-3').classList.remove('hidden');
                    document.getElementById('heading').innerText = "নতুন পাসওয়ার্ড দিন";
                    document.getElementById('subheading').innerText = "একটি শক্তিশালী নতুন পাসওয়ার্ড সেট করুন";
                } else {
                    showToast(data.message, false);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "ভেরিফাই করুন";
                showToast("ভেরিফিকেশনে ত্রুটি হয়েছে", false);
            });
        }

        function resetPassword() {
            const newPass = document.getElementById('new_pass').value;
            const confirmPass = document.getElementById('confirm_pass').value;
            const btn = document.getElementById('btn-3');

            if (!newPass || newPass.length < 6) {
                showToast("পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে", false);
                return;
            }

            if (newPass !== confirmPass) {
                showToast("উভয় পাসওয়ার্ড মিলছে না", false);
                return;
            }

            btn.innerText = "সংরক্ষণ হচ্ছে...";
            btn.disabled = true;

            const fd = new FormData();
            fd.append('action', 'reset_password');
            fd.append('new_password', newPass);

            fetch('recover_action.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = "পাসওয়ার্ড সেভ করুন";

                if (data.status === 'success') {
                    document.getElementById('step-3').classList.add('hidden');
                    document.getElementById('step-final').classList.remove('hidden');
                    document.getElementById('heading').innerText = "পাসওয়ার্ড রিসেট সম্পূর্ণ";
                    document.getElementById('subheading').innerText = "";
                } else {
                    showToast(data.message, false);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "পাসওয়ার্ড সেভ করুন";
                showToast("পাসওয়ার্ড আপডেটে ত্রুটি হয়েছে", false);
            });
        }
    </script>
</body>

</html>
