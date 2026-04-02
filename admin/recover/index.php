<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন পাসওয়ার্ড পুনরুদ্ধার | অন্ত্যমিল</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/tailwind-config.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="antialiased selection:bg-brand-gold selection:text-white bg-brand-900 min-h-screen flex items-center justify-center relative overflow-x-hidden py-10 md:py-20 px-4 sm:px-6">

    <!-- Background Elements -->
    <div class="mesh-gradient absolute inset-0 opacity-30"></div>
    <div class="absolute top-[-10%] left-[-10%] w-[100%] sm:w-[40%] h-[100%] sm:h-[40%] bg-brand-gold/10 blur-[100px] sm:blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[100%] sm:w-[40%] h-[100%] sm:h-[40%] bg-brand-gold/5 blur-[100px] sm:blur-[120px] rounded-full"></div>

    <div class="max-w-[500px] w-full flex flex-col items-center relative z-10 transition-all duration-300">
        
        <!-- Recovery Card -->
        <div class="w-full">
            <div class="glass-dark p-8 sm:p-12 rounded-[32px] md:rounded-[40px] shadow-2xl border border-white/10">
                
                <!-- Header -->
                <div class="flex flex-col items-center mb-10 text-center">
                    <img src="../../assets/img/logo.webp" alt="logo" class="w-12 h-auto mb-5 drop-shadow-2xl">
                    <h2 id="heading" class="text-2xl sm:text-3xl font-anek font-extrabold text-white">অ্যাডমিন পাসওয়ার্ড পুনরুদ্ধার</h2>
                    <p id="subheading" class="text-gray-400 text-xs mt-3 font-anek">আপনার অফিসিয়াল ইমেইল প্রদান করুন</p>
                    <div class="w-12 h-1 bg-brand-gold rounded-full mt-4"></div>
                </div>

                <div id="recovery-steps">
                    <!-- Step 1: Email Input -->
                    <div id="step-1" class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">অফিসিয়াল ইমেইল</label>
                            <input type="email" id="email" required placeholder="admin@ontomeel.com" class="w-full bg-white/5 border border-white/10 rounded-2xl px-8 py-5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-base">
                        </div>
                        <button onclick="sendOTP()" id="btn-1" class="w-full py-5 sm:py-6 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-2xl shadow-brand-gold/20 flex items-center justify-center gap-4 group">
                            ওটিপি পাঠান
                            <svg class="w-6 h-6 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: OTP Verification -->
                    <div id="step-2" class="hidden space-y-8">
                        <div class="space-y-4 text-center">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-anek">৬-সংখ্যার ওটিপি</label>
                            <input type="text" id="otp" maxlength="6" placeholder="000000" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 sm:py-6 text-center text-3xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all text-white">
                        </div>
                        <button onclick="verifyOTP()" id="btn-2" class="w-full py-5 sm:py-6 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-2xl shadow-brand-gold/20">
                            ভেরিফাই করুন
                        </button>
                        <button onclick="resendOTP()" class="w-full text-[10px] font-bold text-gray-400 uppercase tracking-widest hover:text-white transition-colors">ওটিপি পাননি? পুনরায় পাঠান</button>
                    </div>

                    <!-- Step 3: New Password -->
                    <div id="step-3" class="hidden space-y-8">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">নতুন পাসওয়ার্ড</label>
                                <input type="password" id="new_pass" required placeholder="••••••••" class="w-full bg-white/5 border border-white/10 rounded-2xl px-8 py-5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-base">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] ml-2">পাসওয়ার্ড নিশ্চিত করুন</label>
                                <input type="password" id="confirm_pass" required placeholder="••••••••" class="w-full bg-white/5 border border-white/10 rounded-2xl px-8 py-5 focus:outline-none focus:ring-2 focus:ring-brand-gold focus:bg-white/10 transition-all font-anek text-white text-base">
                            </div>
                        </div>
                        <button onclick="resetPassword()" id="btn-3" class="w-full py-5 sm:py-6 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-2xl shadow-brand-gold/20">
                            পাসওয়ার্ড সেভ করুন
                        </button>
                    </div>

                    <!-- Success Message -->
                    <div id="step-final" class="hidden text-center space-y-8 py-6">
                        <div class="w-20 h-20 bg-green-500/10 text-green-500 rounded-full flex items-center justify-center mx-auto animate-bounce">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold font-anek text-white">পাসওয়ার্ড পরিবর্তন সফল!</h3>
                            <p class="text-gray-400 text-sm mt-2 font-anek">এখন আপনি নতুন পাসওয়ার্ড দিয়ে লগইন করতে পারেন।</p>
                        </div>
                        <a href="../login/" class="inline-block w-full py-5 sm:py-6 bg-brand-gold text-brand-900 font-anek font-bold text-lg rounded-2xl hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all shadow-2xl shadow-brand-gold/20">লগইন পেজে ফিরে যান</a>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-white/10 text-center">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-3 font-anek">সহায়তা প্রয়োজন?</p>
                    <p id="admin-notice" class="text-[11px] text-brand-gold font-bold font-anek leading-relaxed opacity-80">
                        অননুমোদিত পাসওয়ার্ড পুনরুদ্ধারের চেষ্টা কঠোরভাবে পর্যবেক্ষণ করা হয়।
                    </p>
                </div>
            </div>
            
            <div class="flex flex-col items-center gap-6 mt-12 mb-10">
                <a href="../../" class="group flex items-center gap-2 text-[11px] text-gray-500 hover:text-brand-gold transition-all font-bold uppercase tracking-widest font-anek">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    মূল ওয়েবসাইটে ফিরে যান
                </a>
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
