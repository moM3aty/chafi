// مسار الملف: wwwroot/js/site.js

// وظائف التوست (الإشعارات)
function showToast(msg, type = 'ok') {
    const c = document.getElementById('toastC');
    if (!c) return;
    const t = document.createElement('div');
    const ico = type === 'ok' ? 'fa-check-circle' : type === 'err' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';
    const clr = type === 'ok' ? '#2e7d32' : type === 'err' ? '#c62828' : 'var(--gld)';
    t.className = 'toast ' + type;
    t.innerHTML = `<i class="fas ${ico}" style="color:${clr};font-size:18px;flex-shrink:0"></i><span class="text-sm">${msg}</span>`;
    c.appendChild(t);
    requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3500);
}

// التحكم في المودال (النافذة المنبثقة)
function openMdl(id) {
    document.getElementById(id).classList.add('on');
    document.body.style.overflow = 'hidden';
}
function closeMdl(id) {
    document.getElementById(id).classList.remove('on');
    document.body.style.overflow = '';
}

// إظهار/إخفاء كلمة المرور
function togPw(id, btn) {
    const inp = document.getElementById(id);
    const isP = inp.type === 'password';
    inp.type = isP ? 'text' : 'password';
    btn.innerHTML = isP ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
}

// التحويل بين تسجيل الدخول وإنشاء حساب
let isLog = true;
function togAuth() {
    isLog = !isLog;
    document.getElementById('loginF').classList.toggle('hidden', !isLog);
    document.getElementById('regF').classList.toggle('hidden', isLog);
    document.getElementById('authTitle').textContent = isLog ? 'تسجيل الدخول' : 'إنشاء حساب جديد';
    document.getElementById('authSub').textContent = isLog ? 'مرحباً بك في تشافي' : 'أنشئ حسابك واستمتع بالمميزات';
    document.getElementById('authSwTxt').textContent = isLog ? 'ليس لديك حساب؟' : 'لديك حساب بالفعل؟';
    document.getElementById('authSwBtn').textContent = isLog ? 'إنشاء حساب' : 'تسجيل الدخول';
}

// إرسال طلب تسجيل الدخول (AJAX)
async function doLogin(e) {
    e.preventDefault();
    const email = document.querySelector('#loginF input[type="email"]').value;
    const password = document.getElementById('loginPw').value;

    try {
        const response = await fetch('/Account/LoginAjax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ Email: email, Password: password, RememberMe: true })
        });

        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'ok');
            setTimeout(() => window.location.reload(), 1000); // تحديث الصفحة بعد الدخول
        } else {
            showToast(data.message, 'err');
        }
    } catch (error) {
        showToast('حدث خطأ في الاتصال بالخادم', 'err');
    }
}

// إرسال طلب إنشاء الحساب (AJAX)
async function doReg(e) {
    e.preventDefault();
    const fullName = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const phone = document.getElementById('regPhone').value;
    const password = document.getElementById('regPw').value;
    const confirmPassword = document.getElementById('regPw2').value;
    const terms = document.getElementById('regTerms').checked;

    if (password !== confirmPassword) {
        showToast('كلمتا المرور غير متطابقتين', 'err'); return;
    }
    if (!terms) {
        showToast('يجب الموافقة على الشروط', 'err'); return;
    }

    try {
        const response = await fetch('/Account/RegisterAjax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ FullName: fullName, Email: email, PhoneNumber: phone, Password: password, ConfirmPassword: confirmPassword })
        });

        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'ok');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.errors ? data.errors[0] : data.message, 'err');
        }
    } catch (error) {
        showToast('حدث خطأ في الاتصال بالخادم', 'err');
    }
}