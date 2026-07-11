<?php
// مسار الملف: includes/footer.php

$stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM settings");
$sysSet = [];
while($row = $stmtSettings->fetch()) {
    $sysSet[$row['setting_key']] = $row['setting_value'];
}

// جلب الأقسام الرئيسية لعرضها كروابط سريعة في الفوتر
$footerCategories = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC LIMIT 5")->fetchAll();

// إعدادات الصوتية الخلفية
$bgAudioUrl = $sysSet['bg_audio'] ?? 'https://server11.mp3quran.net/hazza/015.mp3';
$enableBgAudio = $sysSet['enable_bg_audio'] ?? '1';

// معرفة الصفحة الحالية لكي يعمل القرآن فقط في الرئيسية
$currentPage = isset($_GET['page']) ? trim($_GET['page']) : 'home';
?>
    </main>

    <footer class="mt-auto relative z-20 overflow-hidden bg-pri-900 text-white/70 border-t-[6px] border-gld-500">
        <div class="max-w-7xl mx-auto px-4 py-16 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 border-b border-white/10 pb-12">
                <div class="col-span-1 lg:col-span-1">
                    <a href="index.php?page=home" class="flex items-center gap-3 text-white mb-6 no-underline">
                        <div class="text-2xl font-black font-amiri tracking-wide">
                                <img width="100px" style="border-radius: 25px;" src="../assets/images/logo.jpg" alt="تشافي">
                        </div>
                    </a>
                    <p class="text-sm leading-relaxed mb-6"><?= htmlspecialchars($sysSet['site_description'] ?? 'تشافي للرقية الشرعية تقدم لكم أفضل المنتجات الطبيعية المقروء عليها.') ?></p>
                    
                    <div class="flex items-center gap-3 text-white/40">
                        <?php if(!empty($sysSet['twitter'])): ?><a href="<?= htmlspecialchars($sysSet['twitter']) ?>" target="_blank" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if(!empty($sysSet['instagram'])): ?><a href="<?= htmlspecialchars($sysSet['instagram']) ?>" target="_blank" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if(!empty($sysSet['youtube'])): ?><a href="<?= htmlspecialchars($sysSet['youtube']) ?>" target="_blank" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-youtube"></i></a><?php endif; ?>
                        <?php if(!empty($sysSet['tiktok'])): ?><a href="<?= htmlspecialchars($sysSet['tiktok']) ?>" target="_blank" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-bold text-lg mb-6 flex items-center gap-2"><i class="fas fa-link text-gld-500"></i> روابط سريعة</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="index.php?page=home" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">الرئيسية</a></li>
                        <li><a href="index.php?page=products" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">المتجر والمنتجات</a></li>
                        <li><a href="index.php?page=packages" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">الباقات والعروض</a></li>
                        <li><a href="index.php?page=contact" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">تواصل معنا</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold text-lg mb-6 flex items-center gap-2"><i class="fas fa-box-open text-gld-500"></i> الأقسام</h4>
                    <ul class="space-y-3 text-sm">
                        <?php foreach($footerCategories as $fCat): ?>
                            <li><a href="index.php?page=category&category_id=<?= $fCat['id'] ?>" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400"><?= htmlspecialchars($fCat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold text-lg mb-6 flex items-center gap-2"><i class="fas fa-headset text-gld-500"></i> تواصل معنا</h4>
                    <ul class="space-y-3 text-sm">
                        <?php if(!empty($sysSet['phone'])): ?>
                        <li class="flex items-center gap-2 text-white/70"><i class="fas fa-phone-alt text-gld-500 text-xs"></i> <span dir="ltr"><?= htmlspecialchars($sysSet['phone']) ?></span></li>
                        <?php endif; ?>
                        
                        <?php if(!empty($sysSet['whatsapp'])): ?>
                        <li class="flex items-center gap-2 text-white/70"><i class="fab fa-whatsapp text-gld-500 text-xs"></i> <span dir="ltr"><?= htmlspecialchars($sysSet['whatsapp']) ?></span></li>
                        <?php endif; ?>

                        <?php if(!empty($sysSet['email'])): ?>
                        <li class="flex items-center gap-2 text-white/70"><i class="fas fa-envelope text-gld-500 text-xs"></i> <?= htmlspecialchars($sysSet['email']) ?></li>
                        <?php endif; ?>

                        <?php if(!empty($sysSet['address'])): ?>
                        <li class="flex items-start gap-2 text-white/70 mt-4 leading-relaxed"><i class="fas fa-map-marker-alt text-gld-500 text-xs mt-1"></i> <?= htmlspecialchars($sysSet['address']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="text-center text-sm text-white/40 flex flex-col items-center gap-2">
                <div>جميع الحقوق محفوظة لمنصة <?= htmlspecialchars($sysSet['site_name'] ?? 'تشافي') ?> &copy; <?= date('Y') ?></div>
                <div class="text-xs">نسأل الله أن يجعل القرآن العظيم شفاءً ورحمةً للمؤمنين.</div>
            </div>
        </div>
    </footer>

    <?php 
    // التأكد من تشغيل الصوت والمشغل فقط إذا كنا في الصفحة الرئيسية
    if($enableBgAudio == '1' && !empty($bgAudioUrl) && $currentPage === 'home'): 
    ?>
    <!-- مشغل القرآن الكريم العائم (في الرئيسية فقط) -->
    <div class="fixed bottom-6 left-6 z-[99999] flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 pl-4 rounded-full shadow-2xl border border-pri-100 hover:shadow-pri-500/20 transition-all group" id="quranWidget">
        <audio id="siteBgAudio" loop preload="auto">
            <source src="<?= htmlspecialchars($bgAudioUrl) ?>" type="audio/mpeg">
        </audio>
        
        <button id="quranPlayBtn" class="w-10 h-10 rounded-full bg-gradient-to-br from-pri-500 to-pri-700 text-white flex items-center justify-center shadow-md hover:scale-110 transition-transform">
            <i class="fas fa-play ml-1 text-sm"></i>
        </button>
        
        <div class="flex flex-col">
            <span class="text-[10px] text-pri-600 font-bold uppercase tracking-wider mb-0.5">تلاوة مباركة</span>
            <div class="text-xs font-black text-pri-900 w-24 overflow-hidden relative h-4">
                <div class="absolute whitespace-nowrap animate-[marquee_10s_linear_infinite]" id="quranTrackName">تلاوة هادئة للخلفية</div>
            </div>
        </div>
        
        <div class="flex items-end gap-0.5 h-4 ml-1 opacity-50" id="quranWaves">
            <div class="w-1 bg-pri-400 rounded-t-sm h-1 transition-all"></div>
            <div class="w-1 bg-pri-400 rounded-t-sm h-2 transition-all"></div>
            <div class="w-1 bg-pri-400 rounded-t-sm h-1 transition-all"></div>
            <div class="w-1 bg-pri-400 rounded-t-sm h-3 transition-all"></div>
        </div>
    </div>

    <style>
        @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
        .wave-anim div { animation: wave 1s infinite alternate ease-in-out; }
        .wave-anim div:nth-child(2) { animation-delay: 0.2s; }
        .wave-anim div:nth-child(3) { animation-delay: 0.4s; }
        .wave-anim div:nth-child(4) { animation-delay: 0.6s; }
        @keyframes wave { 0% { height: 20%; } 100% { height: 100%; } }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const audio = document.getElementById('siteBgAudio');
        if(!audio) return;
        
        const playBtn = document.getElementById('quranPlayBtn');
        const waves = document.getElementById('quranWaves');
        let isPlaying = false;

        function updatePlayerState(playing) {
            isPlaying = playing;
            if(playing) {
                playBtn.innerHTML = '<i class="fas fa-pause text-sm"></i>';
                playBtn.classList.replace('from-pri-500', 'from-gld-500');
                playBtn.classList.replace('to-pri-700', 'to-gld-700');
                waves.classList.add('wave-anim');
                waves.classList.replace('opacity-50', 'opacity-100');
            } else {
                playBtn.innerHTML = '<i class="fas fa-play ml-1 text-sm"></i>';
                playBtn.classList.replace('from-gld-500', 'from-pri-500');
                playBtn.classList.replace('to-gld-700', 'to-pri-700');
                waves.classList.remove('wave-anim');
                waves.classList.replace('opacity-100', 'opacity-50');
            }
        }

        const playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.then(_ => {
                updatePlayerState(true);
            }).catch(error => {
                document.body.addEventListener('click', function autoPlayOnClick() {
                    if (!isPlaying) {
                        audio.play().then(() => {
                            updatePlayerState(true);
                        }).catch(e => console.log('Audio play failed:', e));
                    }
                    document.body.removeEventListener('click', autoPlayOnClick);
                }, { once: true });
            });
        }

        playBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if(isPlaying) {
                audio.pause();
                updatePlayerState(false);
            } else {
                audio.play();
                updatePlayerState(true);
            }
        });
    });
    </script>
    <?php endif; ?>

    <!-- مودال تسجيل الدخول وإنشاء الحساب -->
    <div id="authMdl" class="modal-backdrop">
        <div class="modal-dialog" style="max-width:440px;">
            <button onclick="closeMdl('authMdl')" class="modal-close"><i class="fas fa-times"></i></button>

            <div class="flex border-b border-gray-100" id="authTabs">
                <button type="button" onclick="switchAuthTab('login')" id="tabLogin" class="flex-1 py-4 text-center font-bold text-sm transition-all border-b-[3px] border-pri-600 text-pri-700 bg-pri-50/50">
                    <i class="fas fa-sign-in-alt ml-1"></i> تسجيل الدخول
                </button>
                <button type="button" onclick="switchAuthTab('register')" id="tabRegister" class="flex-1 py-4 text-center font-bold text-sm transition-all border-b-[3px] border-transparent text-brk-400 hover:text-brk-600">
                    <i class="fas fa-user-plus ml-1"></i> إنشاء حساب
                </button>
            </div>

            <div id="authMsg" class="hidden mx-6 mt-4 p-3.5 rounded-xl text-sm font-bold"></div>

            <div id="loginPanel" class="p-6 pt-5">
                <form id="loginF" action="#" method="post" novalidate>
                    <div class="form-group mb-5">
                        <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="email" name="email" id="loginEmail" class="form-control !pr-11" required placeholder="example@email.com" dir="ltr" autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">كلمة المرور <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="password" name="password" id="loginPassword" class="form-control !pr-11 !pl-11" required placeholder="أدخل كلمة المرور" autocomplete="current-password">
                            <button type="button" onclick="togglePass('loginPassword', this)" class="absolute left-3 top-1/2 -translate-y-1/2 text-brk-300 hover:text-brk-600 transition text-sm" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div id="loginLoading" class="hidden mb-4">
                        <div class="flex items-center justify-center gap-3 p-3 bg-pri-50 rounded-xl">
                            <div class="dot-loader"><span></span><span></span><span></span></div>
                            <span class="text-pri-700 font-bold text-sm">جاري تسجيل الدخول...</span>
                        </div>
                    </div>

                    <button type="submit" id="loginBtn" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                    </button>
                </form>
            </div>

            <div id="registerPanel" class="p-6 pt-5 hidden">
                <form id="registerF" action="#" method="post" novalidate>
                    <div class="form-group mb-4">
                        <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="text" name="full_name" id="regName" class="form-control !pr-11" required placeholder="أدخل اسمك الكامل" autocomplete="name">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="email" name="email" id="regEmail" class="form-control !pr-11" required placeholder="example@email.com" dir="ltr" autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">رقم الجوال</label>
                        <div class="relative">
                            <i class="fas fa-phone absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="tel" name="phone_number" id="regPhone" class="form-control !pr-11" placeholder="05XXXXXXXX" dir="ltr" autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">كلمة المرور <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-brk-300 text-sm"></i>
                            <input type="password" name="password" id="regPassword" class="form-control !pr-11 !pl-11" required placeholder="أدخل كلمة مرور قوية" minlength="6" autocomplete="new-password">
                            <button type="button" onclick="togglePass('regPassword', this)" class="absolute left-3 top-1/2 -translate-y-1/2 text-brk-300 hover:text-brk-600 transition text-sm" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="mt-2 flex gap-1" id="passStrength">
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str1"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str2"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str3"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str4"></div>
                        </div>
                        <p class="text-[10px] text-brk-400 mt-1" id="strText"></p>
                    </div>

                    <div id="registerLoading" class="hidden mb-4">
                        <div class="flex items-center justify-center gap-3 p-3 bg-pri-50 rounded-xl">
                            <div class="dot-loader"><span></span><span></span><span></span></div>
                            <span class="text-pri-700 font-bold text-sm">جاري إنشاء الحساب...</span>
                        </div>
                    </div>

                    <button type="submit" id="registerBtn" class="btn btn-gold btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> إنشاء حساب جديد
                    </button>
                </form>
            </div>

            <div class="px-6 pb-5">
                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                    <span class="relative bg-white px-4 text-[10px] text-brk-400 font-bold">آمن ومشفر 100%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- نظام التوست -->
    <div id="toastC" class="toast-container"></div>

    <style>
        /* التعديل لضمان ظهور المودالات والتنبيهات فوق الهيدر وأي محتوى آخر */
        .modal-backdrop { z-index: 999999 !important; }
        .toast-container { z-index: 9999999 !important; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 50%, 90% { transform: translateX(-6px); }
            30%, 70% { transform: translateX(6px); }
        }
        .animate-shake { animation: shake 0.5s ease-in-out; }
    </style>

    <script>
    function showToast(msg, type = 'ok') {
        const c = document.getElementById('toastC');
        const t = document.createElement('div');
        const ico = type === 'ok' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        t.className = `toast-msg ${type === 'ok' ? 'toast-success' : 'toast-error'}`;
        t.innerHTML = `<i class="fas ${ico}"></i> <span>${msg}</span>`;
        c.appendChild(t);
        setTimeout(() => t.classList.add('show'), 10);
        setTimeout(() => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 500);
        }, 3500);
    }

    function openMdl(id) {
        document.getElementById(id).classList.add('is-active');
        document.body.style.overflow = 'hidden';
        const msg = document.getElementById('authMsg');
        if (msg) { msg.classList.add('hidden'); }
    }

    function closeMdl(id) {
        document.getElementById(id).classList.remove('is-active');
        document.body.style.overflow = '';
    }

    document.getElementById('authMdl').addEventListener('click', function(e) {
        if (e.target === this) closeMdl('authMdl');
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMdl('authMdl');
    });

    function switchAuthTab(tab) {
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        const msg = document.getElementById('authMsg');

        msg.classList.add('hidden');

        if (tab === 'login') {
            tabLogin.classList.add('border-pri-600', 'text-pri-700', 'bg-pri-50/50');
            tabLogin.classList.remove('border-transparent', 'text-brk-400');
            tabRegister.classList.remove('border-pri-600', 'text-pri-700', 'bg-pri-50/50');
            tabRegister.classList.add('border-transparent', 'text-brk-400');
            loginPanel.classList.remove('hidden');
            registerPanel.classList.add('hidden');
        } else {
            tabRegister.classList.add('border-gld-500', 'text-gld-700', 'bg-gld-50/50');
            tabRegister.classList.remove('border-transparent', 'text-brk-400');
            tabLogin.classList.remove('border-pri-600', 'text-pri-700', 'bg-pri-50/50');
            tabLogin.classList.add('border-transparent', 'text-brk-400');
            registerPanel.classList.remove('hidden');
            loginPanel.classList.add('hidden');
        }
    }

    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    const regPassInput = document.getElementById('regPassword');
    if (regPassInput) {
        regPassInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

            const colors = ['', '#ef4444', '#f59e0b', '#22c55e', '#16a34a'];
            const texts = ['', 'ضعيفة', 'متوسطة', 'جيدة', 'قوية جداً'];
            const textColors = ['', 'text-red-500', 'text-yellow-600', 'text-green-600', 'text-green-700'];

            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById('str' + i);
                bar.style.backgroundColor = i <= score ? colors[score] : '#e5e7eb';
            }

            const strText = document.getElementById('strText');
            if (val.length === 0) {
                strText.textContent = '';
            } else {
                strText.textContent = 'قوة كلمة المرور: ' + (texts[score] || 'ضعيفة جداً');
                strText.className = 'text-[10px] mt-1 ' + (textColors[score] || 'text-red-500');
            }
        });
    }

    function showAuthMsg(text, type) {
        const msg = document.getElementById('authMsg');
        msg.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border-r-4', 'border-green-500',
                                  'bg-red-50', 'text-red-700', 'border-r-4', 'border-red-500');
        if (type === 'ok') {
            msg.classList.add('bg-green-50', 'text-green-700', 'border-r-4', 'border-green-500');
        } else {
            msg.classList.add('bg-red-50', 'text-red-700', 'border-r-4', 'border-red-500');
        }
        msg.innerHTML = `<i class="fas ${type === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle'} ml-2"></i>${text}`;
        msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.getElementById('loginF').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        if (!email || !password) { showAuthMsg('يرجى ملء جميع الحقول المطلوبة.', 'err'); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAuthMsg('صيغة البريد الإلكتروني غير صحيحة.', 'err'); return; }

        const btn = document.getElementById('loginBtn');
        const loading = document.getElementById('loginLoading');
        btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); loading.classList.remove('hidden');

        try {
            const res = await fetch('ajax/auth.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            const result = await res.json();
            if (result.success) {
                showAuthMsg(result.message, 'ok');
                showToast(result.message, 'ok');
                setTimeout(() => { closeMdl('authMdl'); window.location.reload(); }, 800);
            } else {
                showAuthMsg(result.message, 'err');
                this.classList.add('animate-shake'); setTimeout(() => this.classList.remove('animate-shake'), 500);
            }
        } catch (err) {
            showAuthMsg('حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.', 'err');
        } finally {
            btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-not-allowed'); loading.classList.add('hidden');
        }
    });

    document.getElementById('registerF').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fullName = document.getElementById('regName').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const phone = document.getElementById('regPhone').value.trim();
        const password = document.getElementById('regPassword').value;

        if (!fullName || !email || !password) { showAuthMsg('يرجى ملء الحقول المطلوبة.', 'err'); return; }
        if (fullName.length < 3) { showAuthMsg('الاسم يجب أن يكون 3 أحرف على الأقل.', 'err'); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAuthMsg('صيغة البريد الإلكتروني غير صحيحة.', 'err'); return; }
        if (password.length < 6) { showAuthMsg('كلمة المرور يجب أن تكون 6 أحرف على الأقل.', 'err'); return; }

        const btn = document.getElementById('registerBtn');
        const loading = document.getElementById('registerLoading');
        btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); loading.classList.remove('hidden');

        try {
            const res = await fetch('ajax/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ FullName: fullName, Email: email, PhoneNumber: phone, Password: password })
            });
            const result = await res.json();
            if (result.success) {
                showAuthMsg(result.message, 'ok');
                showToast('مرحباً بك! تم إنشاء حسابك بنجاح.', 'ok');
                setTimeout(() => { closeMdl('authMdl'); window.location.reload(); }, 800);
            } else {
                showAuthMsg(result.message, 'err');
                this.classList.add('animate-shake'); setTimeout(() => this.classList.remove('animate-shake'), 500);
            }
        } catch (err) {
            showAuthMsg('حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.', 'err');
        } finally {
            btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-not-allowed'); loading.classList.add('hidden');
        }
    });

    /* دالة السلة الشاملة والذكية (Smart AddToCart) */
    function addToCart(arg1, arg2, arg3, arg4, arg5) {
        const formData = new FormData();
        formData.append('action', 'add');

        let itemType = 'product';
        let itemId = 0;
        let qty = 1;

        if (typeof arg1 === 'string') {
            itemType = arg1;
            itemId = parseInt(arg2) || 0;
            qty = parseInt(arg3) || 1;
        } else {
            qty = parseInt(arg2) || 1;
            if (arg1 > 0) { itemType = 'product'; itemId = parseInt(arg1); }
            else if (arg3 > 0) { itemType = 'package'; itemId = parseInt(arg3); }
            else if (arg4 > 0) { itemType = 'audio'; itemId = parseInt(arg4); }
            else if (arg5 > 0) { itemType = 'video'; itemId = parseInt(arg5); }
        }

        formData.append('item_type', itemType);
        formData.append('item_id', itemId);
        formData.append('quantity', qty);

        fetch('ajax/cart_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'ok');
                    const counter = document.getElementById('cCount');
                    if (counter && data.totals && data.totals.count !== undefined) {
                        counter.innerText = data.totals.count;
                    }
                } else {
                    showToast(data.message || 'حدث خطأ', 'err');
                }
            })
            .catch(() => showToast('حدث خطأ في الاتصال بالسيرفر', 'err'));
    }
    </script>
</body>
</html>