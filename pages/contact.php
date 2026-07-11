<?php
// مسار الملف: pages/contact.php

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
?>

<div class="max-w-7xl mx-auto px-4 py-12 mb-14">
    <div class="sec-hd mb-12 afiu">
        <div class="relative z-10 text-center">
            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center text-gld-400 text-3xl mx-auto mb-4 shadow-inner">
                <i class="fas fa-headset"></i>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white font-amiri mb-3">تواصل معنا</h1>
            <p class="text-white/80 max-w-2xl mx-auto text-sm leading-relaxed font-medium">
                نقدم خدمات الرقية الشرعية والاستشارات، مع توصيل المنتجات إلى جميع مناطق الخليج، وبعض دول العالم.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 afiu" style="animation-delay:.1s">
        <!-- معلومات التواصل المخصصة -->
        <div class="space-y-4">
            
            <div class="erp-card p-6 hover:border-pri-200 transition bg-gradient-to-bl from-white to-gray-50 border border-gray-100 shadow-sm hover:shadow-md">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-3xl drop-shadow-sm">🇸🇦</span>
                    <h3 class="font-bold text-pri-900 text-lg">المملكة العربية السعودية</h3>
                </div>
                <p class="text-brk-500 text-xs mb-4 font-bold">نخدم جميع مناطق المملكة.</p>
                <div class="text-sm font-bold text-pri-700 bg-white border border-pri-100 p-3 rounded-xl flex items-center justify-between shadow-sm" dir="ltr">
                    <span class="tracking-wider">+966 53 548 8493</span>
                    <a href="https://wa.me/966535488493" target="_blank" class="hover:scale-110 transition-transform"><i class="fab fa-whatsapp text-green-500 text-2xl drop-shadow-sm"></i></a>
                </div>
            </div>

            <div class="erp-card p-6 hover:border-pri-200 transition bg-gradient-to-bl from-white to-gray-50 border border-gray-100 shadow-sm hover:shadow-md">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-3xl drop-shadow-sm">🇦🇪</span>
                    <h3 class="font-bold text-pri-900 text-lg">دولة الإمارات العربية</h3>
                </div>
                <p class="text-brk-500 text-xs mb-4 font-bold">نخدم جميع إمارات الدولة.</p>
                <div class="text-sm font-bold text-pri-700 bg-white border border-pri-100 p-3 rounded-xl flex items-center justify-between shadow-sm" dir="ltr">
                    <span class="tracking-wider">+971 55 797 3809</span>
                    <a href="https://wa.me/971557973809" target="_blank" class="hover:scale-110 transition-transform"><i class="fab fa-whatsapp text-green-500 text-2xl drop-shadow-sm"></i></a>
                </div>
            </div>

            <div class="erp-card p-6 hover:border-pri-200 transition bg-gradient-to-bl from-white to-gray-50 border border-gray-100 shadow-sm hover:shadow-md">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-3xl drop-shadow-sm">🇴🇲</span>
                    <h3 class="font-bold text-pri-900 text-lg">سلطنة عُمان</h3>
                </div>
                <p class="text-brk-500 text-xs mb-4 font-bold">نخدم جميع محافظات السلطنة.</p>
                <div class="text-sm font-bold text-pri-700 bg-white border border-pri-100 p-3 rounded-xl flex items-center justify-between shadow-sm" dir="ltr">
                    <span class="tracking-wider">+968 99 199 476</span>
                    <a href="https://wa.me/96899199476" target="_blank" class="hover:scale-110 transition-transform"><i class="fab fa-whatsapp text-green-500 text-2xl drop-shadow-sm"></i></a>
                </div>
            </div>

            <div class="bg-pri-50/50 border border-pri-100 rounded-2xl p-6 text-sm text-brk-600 leading-loose shadow-inner relative overflow-hidden">
                <div class="absolute top-0 right-0 w-1.5 h-full bg-gld-500"></div>
                <p class="mb-3 font-bold text-pri-900"><i class="fab fa-whatsapp text-green-500 ml-1"></i> جميع الأرقام السابقة متاحة للتواصل عبر تطبيق واتساب.</p>
                <p class="mb-4 text-xs"><i class="fas fa-shipping-fast text-pri-500 ml-1"></i> كما نوفر خدمة شحن وتوصيل المنتجات إلى جميع دول مجلس التعاون الخليجي، وإلى عدد من الدول حول العالم.</p>
                
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <p class="font-black text-pri-900 text-center text-base mb-2 font-amiri">
                        نسأل الله أن يكتب الشفاء لكل مريض، وأن يجعل القرآن العظيم شفاءً ورحمةً للمؤمنين.
                    </p>
                    <p class="font-bold text-xs text-center text-brk-500 border-t border-gray-100 pt-2 mt-2">
                        <span class="text-red-500">تنبيه:</span> "الرقية الشرعية والاستشارات" "نسأل الله أن يجعل فيها الشفاء"، لأن الشفاء في العقيدة الإسلامية هو من الله وحده، والرقية سبب من الأسباب.
                    </p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="erp-card p-6 sm:p-10 h-full border border-gray-100 shadow-sm">
                <h2 class="text-xl font-black text-pri-900 font-amiri mb-6 border-b border-gray-100 pb-3"><i class="fas fa-paper-plane text-gld-500 ml-2"></i>أرسل استفسارك مباشرة</h2>

                <?php if($successMsg): ?>
                    <div class="p-4 rounded-xl mb-6 font-bold bg-green-50 text-green-700 border-r-4 border-green-500 shadow-sm"><i class="fas fa-check-circle ml-2"></i><?= $successMsg ?></div>
                <?php endif; ?>
                <?php if($errMsg): ?>
                    <div class="p-4 rounded-xl mb-6 font-bold bg-red-50 text-red-700 border-r-4 border-red-500 shadow-sm"><i class="fas fa-exclamation-circle ml-2"></i><?= $errMsg ?></div>
                <?php endif; ?>

                <form method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group !mb-0">
                            <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required placeholder="أدخل اسمك الكامل">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" dir="ltr" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="example@email.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group !mb-0">
                            <label class="form-label">رقم الجوال (مع رمز الدولة)</label>
                            <input type="tel" name="phone" class="form-control" dir="ltr" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+966 5X XXX XXXX">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label">الموضوع</label>
                            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="موضوع الرسالة أو الاستشارة">
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label">نص الرسالة <span class="req">*</span></label>
                        <textarea name="message" class="form-textarea" rows="7" required placeholder="اكتب رسالتك أو استشارتك هنا بالتفصيل..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold btn-lg w-full sm:w-auto shadow-md hover:scale-105 transition-transform"><i class="fas fa-paper-plane"></i> إرسال الرسالة</button>
                </form>
            </div>
        </div>
    </div>
</div>