<?php
// مسار الملف: includes/footer.php
// النسخة المُصلحة — نظام الدخول والتسجيل يعمل بالكامل
?>
    </main>

    <footer class="mt-auto relative z-20 overflow-hidden bg-pri-900 text-white/70 border-t-[6px] border-gld-500">
        <div class="max-w-7xl mx-auto px-4 py-16 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 border-b border-white/10 pb-12">
                <div class="col-span-1 lg:col-span-1">
                    <a href="index.php?page=home" class="flex items-center gap-3 text-white mb-6 no-underline">
                        <div class="text-2xl font-black font-amiri tracking-wide"><img width="100px" style="border-radius: 25px;" src="../assets/images/logo.jpg" alt="تشافي"></div>
                    </a>
                    <p class="text-sm leading-relaxed mb-6"> تشافي للرقية الشرعية تقدم لكم أفضل المنتجات الطبيعية المقروء عليها.</p>
                    <div class="flex items-center gap-3 text-white/40">
                        <a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-gld-500 hover:text-white transition-all text-sm"><i class="fab fa-tiktok"></i></a>
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
                        <li><a href="index.php?page=products&category_id=6" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">العسل المقروء</a></li>
                        <li><a href="index.php?page=products&category_id=7" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">الزيوت المقروءة</a></li>
                        <li><a href="index.php?page=products&category_id=9" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">المسك والبخور</a></li>
                        <li><a href="index.php?page=products&category_id=8" class="hover:text-gld-400 transition no-underline text-white/70 hover:text-gld-400">المياه المقروءة</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-6 flex items-center gap-2"><i class="fas fa-headset text-gld-500"></i> تواصل معنا</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-2 text-white/70"><i class="fas fa-phone-alt text-gld-500 text-xs"></i> +966 50 000 0000</li>
                        <li class="flex items-center gap-2 text-white/70"><i class="fas fa-envelope text-gld-500 text-xs"></i> info@tashafi.net</li>
                        <li class="flex items-center gap-2 text-white/70"><i class="fab fa-whatsapp text-gld-500 text-xs"></i> +966 50 000 0000</li>
                    </ul>
                </div>
            </div>
            <div class="text-center text-sm text-white/40">
                جميع الحقوق محفوظة لمنصة تشافي &copy; <?= date('Y') ?>
            </div>
        </div>
    </footer>

    <!-- ═══════════════════════════════════════════════════════════
         مودال تسجيل الدخول وإنشاء الحساب — النسخة المُصلحة
         ═══════════════════════════════════════════════════════════ -->
    <div id="authMdl" class="modal-backdrop">
        <div class="modal-dialog" style="max-width:440px;">
            <button onclick="closeMdl('authMdl')" class="modal-close"><i class="fas fa-times"></i></button>

            <!-- التبويبات -->
            <div class="flex border-b border-gray-100" id="authTabs">
                <button type="button" onclick="switchAuthTab('login')" id="tabLogin" class="flex-1 py-4 text-center font-bold text-sm transition-all border-b-[3px] border-pri-600 text-pri-700 bg-pri-50/50">
                    <i class="fas fa-sign-in-alt ml-1"></i> تسجيل الدخول
                </button>
                <button type="button" onclick="switchAuthTab('register')" id="tabRegister" class="flex-1 py-4 text-center font-bold text-sm transition-all border-b-[3px] border-transparent text-brk-400 hover:text-brk-600">
                    <i class="fas fa-user-plus ml-1"></i> إنشاء حساب
                </button>
            </div>

            <!-- رسالة خطأ/نجاح داخل المودال -->
            <div id="authMsg" class="hidden mx-6 mt-4 p-3.5 rounded-xl text-sm font-bold"></div>

            <!-- ═══ فورم تسجيل الدخول ═══ -->
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

                    <!-- حالة التحميل -->
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

            <!-- ═══ فورم إنشاء حساب ═══ -->
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
                        <!-- مؤشر قوة كلمة المرور -->
                        <div class="mt-2 flex gap-1" id="passStrength">
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str1"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str2"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str3"></div>
                            <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="str4"></div>
                        </div>
                        <p class="text-[10px] text-brk-400 mt-1" id="strText"></p>
                    </div>

                    <!-- حالة التحميل -->
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

            <!-- فاصل -->
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

    <script>
    /* ═══════════════════════════════════════════════════════════
       1. التوست (إشعارات)
       ═══════════════════════════════════════════════════════════ */
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

    /* ═══════════════════════════════════════════════════════════
       2. المودال
       ═══════════════════════════════════════════════════════════ */
    function openMdl(id) {
        document.getElementById(id).classList.add('is-active');
        document.body.style.overflow = 'hidden';
        // إخفاء أي رسالة سابقة عند الفتح
        const msg = document.getElementById('authMsg');
        if (msg) { msg.classList.add('hidden'); }
    }

    function closeMdl(id) {
        document.getElementById(id).classList.remove('is-active');
        document.body.style.overflow = '';
    }

    // إغلاق بالضغط خارج المودال
    document.getElementById('authMdl').addEventListener('click', function(e) {
        if (e.target === this) closeMdl('authMdl');
    });

    // إغلاق بزر Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMdl('authMdl');
    });

    /* ═══════════════════════════════════════════════════════════
       3. التبديل بين تسجيل الدخول وإنشاء حساب
       ═══════════════════════════════════════════════════════════ */
    function switchAuthTab(tab) {
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        const msg = document.getElementById('authMsg');

        // إخفاء الرسالة عند التبديل
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

    /* ═══════════════════════════════════════════════════════════
       4. إظهار/إخفاء كلمة المرور
       ═══════════════════════════════════════════════════════════ */
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

    /* ═══════════════════════════════════════════════════════════
       5. مؤشر قوة كلمة المرور
       ═══════════════════════════════════════════════════════════ */
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

    /* ═══════════════════════════════════════════════════════════
       6. عرض رسالة داخل المودال
       ═══════════════════════════════════════════════════════════ */
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
        // سكرول لأعلى المودال لرؤية الرسالة
        msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ═══════════════════════════════════════════════════════════
       7. تسجيل الدخول — AJAX
       ═══════════════════════════════════════════════════════════ */
    document.getElementById('loginF').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        // تحقق بسيط
        if (!email || !password) {
            showAuthMsg('يرجى ملء جميع الحقول المطلوبة.', 'err');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAuthMsg('صيغة البريد الإلكتروني غير صحيحة.', 'err');
            return;
        }

        // إظهار التحميل وتعطيل الزر
        const btn = document.getElementById('loginBtn');
        const loading = document.getElementById('loginLoading');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        loading.classList.remove('hidden');

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
                // إغلاق المودال وإعادة تحميل الصفحة
                setTimeout(() => {
                    closeMdl('authMdl');
                    window.location.reload();
                }, 800);
            } else {
                showAuthMsg(result.message, 'err');
                // اهتزاز الفورم
                this.classList.add('animate-shake');
                setTimeout(() => this.classList.remove('animate-shake'), 500);
            }
        } catch (err) {
            showAuthMsg('حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.', 'err');
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            loading.classList.add('hidden');
        }
    });

    /* ═══════════════════════════════════════════════════════════
       8. إنشاء حساب — AJAX
       ═══════════════════════════════════════════════════════════ */
    document.getElementById('registerF').addEventListener('submit', async function(e) {
        e.preventDefault();

        const fullName = document.getElementById('regName').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const phone = document.getElementById('regPhone').value.trim();
        const password = document.getElementById('regPassword').value;

        // تحقق من الحقول
        if (!fullName || !email || !password) {
            showAuthMsg('يرجى ملء الحقول المطلوبة (الاسم، البريد، كلمة المرور).', 'err');
            return;
        }

        if (fullName.length < 3) {
            showAuthMsg('الاسم يجب أن يكون 3 أحرف على الأقل.', 'err');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAuthMsg('صيغة البريد الإلكتروني غير صحيحة.', 'err');
            return;
        }

        if (password.length < 6) {
            showAuthMsg('كلمة المرور يجب أن تكون 6 أحرف على الأقل.', 'err');
            return;
        }

        // إظهار التحميل
        const btn = document.getElementById('registerBtn');
        const loading = document.getElementById('registerLoading');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        loading.classList.remove('hidden');

        try {
            const res = await fetch('ajax/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    FullName: fullName,
                    Email: email,
                    PhoneNumber: phone,
                    Password: password
                })
            });

            const result = await res.json();

            if (result.success) {
                showAuthMsg(result.message, 'ok');
                showToast('مرحباً بك! تم إنشاء حسابك بنجاح.', 'ok');
                setTimeout(() => {
                    closeMdl('authMdl');
                    window.location.reload();
                }, 800);
            } else {
                showAuthMsg(result.message, 'err');
                this.classList.add('animate-shake');
                setTimeout(() => this.classList.remove('animate-shake'), 500);
            }
        } catch (err) {
            showAuthMsg('حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.', 'err');
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            loading.classList.add('hidden');
        }
    });

    /* ═══════════════════════════════════════════════════════════
       9. إضافة للسلة
       ═══════════════════════════════════════════════════════════ */
    function addToCart(productId, quantity, packageId, audioId, videoId) {
        const formData = new FormData();
        formData.append('action', 'add');
        if (productId > 0) formData.append('product_id', productId);
        if (packageId > 0) formData.append('package_id', packageId);
        if (audioId > 0) formData.append('audio_id', audioId);
        if (videoId > 0) formData.append('video_id', videoId);
        formData.append('quantity', quantity || 1);

        fetch('ajax/cart_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'ok');
                    const counter = document.getElementById('cCount');
                    if (counter) counter.innerText = data.total_items;
                } else {
                    showToast(data.message || 'حدث خطأ', 'err');
                }
            })
            .catch(() => showToast('حدث خطأ في الاتصال', 'err'));
    }
    </script>

    <style>
        /* اهتزاز الفورم عند الخطأ */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 50%, 90% { transform: translateX(-6px); }
            30%, 70% { transform: translateX(6px); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</body>
</html>