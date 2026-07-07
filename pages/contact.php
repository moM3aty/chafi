<?php
// مسار الملف: pages/contact.php
// الوظيفة: نموذج تواصل الزوار مع إمكانية الرفع للجلسة

$successMsg = '';
$errMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $errMsg = 'يرجى ملء الحقول المطلوبة (الاسم، البريد، الرسالة).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errMsg = 'البريد الإلكتروني غير صالح.';
    } else {
        try {
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message, user_id, status) VALUES (?, ?, ?, ?, ?, ?, 'New')");
            $stmt->execute([$name, $email, $phone, $subject, $message, $userId]);
            $successMsg = 'تم إرسال رسالتك بنجاح! سنتواصل معك في أقرب وقت.';
        } catch (PDOException $e) {
            $errMsg = 'حدث خطأ أثناء إرسال الرسالة، يرجى المحاولة لاحقاً.';
        }
    }
}

// جلب بيانات الاتصال من الإعدادات
$sitePhone = '';
$siteEmail = '';
$siteAddress = '';
try {
    $settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('phone','email','address')")->fetchAll();
    foreach ($settings as $s) {
        if ($s['setting_key'] === 'phone') $sitePhone = $s['setting_value'];
        if ($s['setting_key'] === 'email') $siteEmail = $s['setting_value'];
        if ($s['setting_key'] === 'address') $siteAddress = $s['setting_value'];
    }
} catch(Exception $e) {}
?>

<div class="max-w-7xl mx-auto px-4 py-12 mb-14">
    <!-- رأس القسم -->
    <div class="sec-hd mb-12 afiu">
        <div class="relative z-10 text-center">
            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center text-gld-400 text-3xl mx-auto mb-4">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white font-amiri mb-3">تواصل معنا</h1>
            <p class="text-white/70 max-w-lg mx-auto">نسعد بتواصلكم معنا في أي وقت. فريق تشافي جاهز للإجابة على جميع استفساراتكم.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 afiu" style="animation-delay:.1s">
        <!-- معلومات التواصل -->
        <div class="space-y-5">
            <div class="erp-card p-6 hover:border-pri-200 transition">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-pri-600 flex items-center justify-center text-xl mb-4"><i class="fas fa-phone-alt"></i></div>
                <h3 class="font-bold text-pri-900 mb-2">الهاتف</h3>
                <p class="text-brk-500 text-sm" dir="ltr"><?= htmlspecialchars($sitePhone ?: '+966 50 000 0000') ?></p>
            </div>
            <div class="erp-card p-6 hover:border-pri-200 transition">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 text-gld-600 flex items-center justify-center text-xl mb-4"><i class="fas fa-envelope"></i></div>
                <h3 class="font-bold text-pri-900 mb-2">البريد الإلكتروني</h3>
                <p class="text-brk-500 text-sm" dir="ltr"><?= htmlspecialchars($siteEmail ?: 'info@tashafi.net') ?></p>
            </div>
            <div class="erp-card p-6 hover:border-pri-200 transition">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4"><i class="fas fa-map-marker-alt"></i></div>
                <h3 class="font-bold text-pri-900 mb-2">العنوان</h3>
                <p class="text-brk-500 text-sm"><?= htmlspecialchars($siteAddress ?: 'المملكة العربية السعودية') ?></p>
            </div>
            <div class="erp-card p-6 hover:border-pri-200 transition">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl mb-4"><i class="fab fa-whatsapp"></i></div>
                <h3 class="font-bold text-pri-900 mb-2">واتساب</h3>
                <p class="text-brk-500 text-sm" dir="ltr"><?= htmlspecialchars($sitePhone ?: '+966 50 000 0000') ?></p>
            </div>
        </div>

        <!-- نموذج التواصل -->
        <div class="lg:col-span-2">
            <div class="erp-card p-6 sm:p-10">
                <h2 class="text-xl font-black text-pri-900 font-amiri mb-6 border-b border-gray-100 pb-3"><i class="fas fa-paper-plane text-gld-500 ml-2"></i>أرسل رسالتك</h2>

                <?php if($successMsg): ?>
                    <div class="p-4 rounded-xl mb-6 font-bold bg-green-50 text-green-700 border-r-4 border-green-500"><i class="fas fa-check-circle ml-2"></i><?= $successMsg ?></div>
                <?php endif; ?>
                <?php if($errMsg): ?>
                    <div class="p-4 rounded-xl mb-6 font-bold bg-red-50 text-red-700 border-r-4 border-red-500"><i class="fas fa-exclamation-circle ml-2"></i><?= $errMsg ?></div>
                <?php endif; ?>

                <form method="post" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group !mb-0">
                            <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required placeholder="أدخل اسمك الكامل">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" dir="ltr" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="example@email.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group !mb-0">
                            <label class="form-label">رقم الجوال</label>
                            <input type="tel" name="phone" class="form-control" dir="ltr" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="05XXXXXXXX">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label">الموضوع</label>
                            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="موضوع الرسالة">
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label">نص الرسالة <span class="req">*</span></label>
                        <textarea name="message" class="form-textarea" rows="6" required placeholder="اكتب رسالتك هنا بالتفصيل..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full sm:w-auto"><i class="fas fa-paper-plane"></i> إرسال الرسالة</button>
                </form>
            </div>
        </div>
    </div>
</div>