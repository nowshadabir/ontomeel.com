<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড রিকভারি | অন্ত্যমিল</title>

    <!-- Google Fonts -->
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

    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 flex flex-col items-center relative z-10">
        
        <!-- Recovery Card -->
        <div class="w-full max-w-[450px]">
            <div class="bg-white p-6 sm:p-10 md:p-12 rounded-[32px] md:rounded-[40px] shadow-2xl border border-gray-100">
                
                <!-- Header -->
                <div class="flex flex-col items-center mb-8 text-center">
                    <img src="../assets/img/logo.webp" alt="logo" class="w-10 h-auto mb-3">
                    <h2 id="heading" class="text-2xl font-anek font-extrabold text-brand-900">পাসওয়ার্ড পুনরুদ্ধার</h2>
                    <p id="subheading" class="text-gray-400 text-xs mt-2 font-anek">আপনার অ্যাকাউন্টের ইমেইল প্রদান করুন</p>
                    <div class="w-12 h-1 bg-brand-gold rounded-full mt-3"></div>
                </div>

                <div id="recovery-steps">
                    <!-- Step 1: Email Input -->
                    <div id="step-1" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">আপনার ইমেইল এড্রেস</label>
                            <input type="email" id="email" required placeholder="example@mail.com" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>
                        <button onclick="sendOTP()" id="btn-1" class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10 flex items-center justify-center gap-3 group">
                            ওটিপি পাঠান
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: OTP Verification -->
                    <div id="step-2" class="hidden space-y-6">
                        <div class="space-y-4 text-center">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek">৬-সংখ্যার ওটিপি</label>
                            <input type="text" id="otp" maxlength="6" placeholder="000000" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-5 text-center text-3xl font-bold tracking-[0.3em] focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all text-brand-900">
                        </div>
                        <button onclick="verifyOTP()" id="btn-2" class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10">
                            ভেরিফাই করুন
                        </button>
                        <button onclick="resendOTP()" class="w-full text-[10px] font-bold text-gray-400 uppercase tracking-widest hover:text-brand-900 transition-colors">ওটিপি পাননি? পুনরায় পাঠান</button>
                    </div>

                    <!-- Step 3: New Password -->
                    <div id="step-3" class="hidden space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">নতুন পাসওয়ার্ড</label>
                            <input type="password" id="new_pass" required placeholder="••••••••" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" id="confirm_pass" required placeholder="••••••••" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white transition-all font-anek text-brand-900">
                        </div>
                        <button onclick="resetPassword()" id="btn-3" class="w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all duration-300 shadow-xl shadow-brand-900/10">
                            পাসওয়ার্ড সেভ করুন
                        </button>
                    </div>

                    <!-- Success Message -->
                    <div id="step-final" class="hidden text-center space-y-6 py-4">
                        <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto animate-bounce">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold font-anek text-brand-900">পাসওয়ার্ড পরিবর্তন সফল!</h3>
                        <p class="text-gray-400 text-sm font-anek">এখন আপনি নতুন পাসওয়ার্ড দিয়ে লগইন করতে পারেন।</p>
                        <a href="../login/" class="inline-block w-full py-5 bg-brand-900 text-white font-anek font-bold text-lg rounded-2xl hover:bg-brand-gold hover:text-brand-900 transition-all">লগইন পেজে ফিরে যান</a>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-50 text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2 font-anek">সমস্যা হচ্ছে?</p>
                    <p id="admin-notice" class="text-[11px] text-brand-gold font-bold font-anek leading-relaxed">
                        পাসওয়ার্ড পুনরুদ্ধারে কোনো সমস্যা হলে সরাসরি <br>
                        <span class="text-brand-900">এডমিনের সাথে যোগাযোগ করুন:</span> <br>
                        <a href="mailto:admin@ontomeel.com" class="hover:underline">admin@ontomeel.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentEmail = "";

        function showStep(step) {
            ['step-1', 'step-2', 'step-3', 'step-final'].forEach(s => {
                document.getElementById(s).classList.add('hidden');
            });
            document.getElementById(step).classList.remove('hidden');
            
            // Adjust headings
            const head = document.getElementById('heading');
            const sub = document.getElementById('subheading');
            
            if(step === 'step-2') {
                head.innerText = "ভেরিফিকেশন";
                sub.innerText = "আপনার ইমেইলে পাঠানো ওটিপি দিন";
            } else if(step === 'step-3') {
                head.innerText = "নতুন পাসওয়ার্ড";
                sub.innerText = "আপনার জন্য একটি শক্তিশালী পাসওয়ার্ড সেট করুন";
            } else if(step === 'step-final') {
                head.innerText = "অভিনন্দন!";
                sub.innerText = "কাজটি সফলভাবে সম্পন্ন হয়েছে";
            }
        }

        function sendOTP() {
            const email = document.getElementById('email').value;
            if(!email) { alert("দয়া করে সঠিক ইমেইল দিন।"); return; }
            
            currentEmail = email;
            const btn = document.getElementById('btn-1');
            btn.disabled = true;
            btn.innerText = "ওটিপি পাঠানো হচ্ছে...";

            const formData = new FormData();
            formData.append('email', email);

            fetch('send_recovery_otp.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        showStep('step-2');
                    } else {
                        alert("ত্রুটি: " + data.message);
                        btn.disabled = false;
                        btn.innerText = "ওটিপি পাঠান";
                    }
                }).catch(err => {
                    alert("সার্ভার সমস্যা। আবার চেষ্টা করুন।");
                    btn.disabled = false;
                    btn.innerText = "ওটিপি পাঠান";
                });
        }

        function resendOTP() {
             const formData = new FormData();
             formData.append('email', currentEmail);
             fetch('send_recovery_otp.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) alert("নতুন ওটিপি পাঠানো হয়েছে।");
                    else alert(data.message);
                });
        }

        function verifyOTP() {
            const otp = document.getElementById('otp').value;
            if(!otp || otp.length < 6) { alert("সঠিক ওটিপি দিন।"); return; }

            const btn = document.getElementById('btn-2');
            btn.disabled = true;
            btn.innerText = "যাচাই করা হচ্ছে...";

            const formData = new FormData();
            formData.append('email', currentEmail);
            formData.append('otp', otp);

            fetch('verify_otp.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        showStep('step-3');
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                        btn.innerText = "ভেরিফাই করুন";
                    }
                });
        }

        function resetPassword() {
            const p1 = document.getElementById('new_pass').value;
            const p2 = document.getElementById('confirm_pass').value;
            
            if(!p1 || p1.length < 6) { alert("পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।"); return; }
            if(p1 !== p2) { alert("পাসওয়ার্ড দুটি মেলেনি।"); return; }

            const btn = document.getElementById('btn-3');
            btn.disabled = true;
            btn.innerText = "সেভ করা হচ্ছে...";

            const formData = new FormData();
            formData.append('email', currentEmail);
            formData.append('password', p1);

            fetch('reset_password.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        showStep('step-final');
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                        btn.innerText = "পাসওয়ার্ড সেভ করুন";
                    }
                });
        }
    </script>
</body>
</html>
