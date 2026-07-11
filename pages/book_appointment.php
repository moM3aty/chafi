<?php
// مسار الملف: pages/book_appointment.php

$successMsg = '';
$errMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? 'ذكر';
    $country = trim($_POST['country'] ?? '');
    $preferredTime = trim($_POST['preferred_time'] ?? '');
    $symptoms = trim($_POST['symptoms'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    
    // معالجة صورة إيصال التحويل
    $receiptUrl = '';
    if (isset($_FILES['transfer_receipt']) && $_FILES['transfer_receipt']['error'] == 0) {
        $uploadDir = 'assets/uploads/receipts/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['transfer_receipt']['name']));
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['transfer_receipt']['tmp_name'], $targetFile)) {
            $receiptUrl = 'assets/uploads/receipts/' . $fileName; // مسار نسبي للداتابيز
        } else {
            $errMsg = "فشل في رفع صورة الإيصال.";
        }
    } else {
        $errMsg = "صورة تأكيد التحويل مطلوبة.";
    }

    if (empty($errMsg)) {
        if (empty($fullName) || empty($phone) || empty($symptoms)) {
            $errMsg = 'يرجى ملء جميع الحقول الإلزامية.';
        } else {
            try {
                // إدخال البيانات في الجدول الذي أنشأناه للتو في setup_db
                $stmt = $pdo->prepare("INSERT INTO appointments (full_name, age, gender, country, preferred_time, symptoms, email, phone, whatsapp, transfer_receipt_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $age, $gender, $country, $preferredTime, $symptoms, $email, $phone, $whatsapp, $receiptUrl]);
                $successMsg = 'تم استلام طلب الحجز وإيصال الدفع بنجاح! سيقوم فريقنا بالتواصل معك قريباً لتأكيد الموعد.';
            } catch (PDOException $e) {
                $errMsg = 'حدث خطأ أثناء إرسال الطلب، يرجى المحاولة لاحقاً.';
            }
        }
    }
}

// محاولة جلب بيانات المستخدم إذا كان مسجلاً
$userEmail = '';
$userPhone = '';
$userName = '';
if (isset($_SESSION['user_id'])) {
    $uStmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $uData = $uStmt->fetch();
    if ($uData) {
        $userName = $uData['full_name'];
        $userEmail = $uData['email'];
        $userPhone = $uData['phone'];
    }
}
?>

<div class="max-w-5xl mx-auto px-4 py-12 mb-14">
    <div class="text-center mb-10 afiu">
        <div class="w-20 h-20 rounded-full bg-gld-50 text-gld-600 flex items-center justify-center text-4xl mx-auto mb-4 shadow-sm border border-gld-100">
            <i class="fas fa-video"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-3">احجز موعد للجلسة المباشرة (أونلاين)</h1>
        <p class="text-brk-500 max-w-2xl mx-auto text-sm leading-relaxed">
            نسأل الله لك الشفاء العاجل. يرجى تعبئة النموذج أدناه بدقة وإرفاق صورة إيصال التحويل البنكي ليتم تأكيد حجز جلستك المباشرة مع الشيخ عبر اتصال الفيديو.
        </p>
    </div>

    <div class="erp-card p-6 sm:p-10 afiu" style="animation-delay:.1s">
        
        <?php if($successMsg): ?>
            <div class="p-8 rounded-3xl mb-8 bg-green-50 text-green-700 border border-green-200 text-center shadow-sm">
                <i class="fas fa-check-circle text-5xl mb-4 text-green-500 animate-bounce"></i>
                <h3 class="text-2xl font-bold font-amiri mb-2">تم تأكيد طلبك!</h3>
                <div class="text-lg"><?= $successMsg ?></div>
                <div class="mt-6">
                    <a href="index.php?page=dashboard" class="btn btn-primary"><i class="fas fa-user"></i> تابع حالة جلستك من حسابك</a>
                </div>
            </div>
        <?php endif; ?>
        <?php if($errMsg): ?>
            <div class="p-4 rounded-xl mb-8 font-bold bg-red-50 text-red-700 border-r-4 border-red-500 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> <?= $errMsg ?>
            </div>
        <?php endif; ?>

        <?php if(!$successMsg): ?>
        <form method="post" enctype="multipart/form-data" class="space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- العمود الأيمن: البيانات -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-lg font-bold text-pri-900 border-b border-gray-100 pb-2 mb-4"><i class="fas fa-user text-gld-500 ml-2"></i>البيانات الشخصية</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group !mb-0">
                                <label class="form-label">الاسم <span class="req">*</span></label>
                                <input type="text" name="full_name" class="form-control" required placeholder="الاسم الكامل" value="<?= htmlspecialchars($userName) ?>">
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label">العمر <span class="req">*</span></label>
                                <input type="number" name="age" class="form-control" required placeholder="العمر بالسنوات">
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label">الجنس <span class="req">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="ذكر">ذكر</option>
                                    <option value="أنثى">أنثى</option>
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label">الدولة <span class="req">*</span></label>
                                <input type="text" name="country" class="form-control" required placeholder="مثال: السعودية">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-pri-900 border-b border-gray-100 pb-2 mb-4"><i class="fas fa-address-book text-gld-500 ml-2"></i>بيانات التواصل</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-group !mb-0">
                                <label class="form-label">رقم الهاتف <span class="req">*</span></label>
                                <input type="tel" name="phone" class="form-control" dir="ltr" required placeholder="+966..." value="<?= htmlspecialchars($userPhone) ?>">
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label">رقم الواتساب <span class="req">*</span></label>
                                <input type="tel" name="whatsapp" class="form-control" dir="ltr" required placeholder="+966..." value="<?= htmlspecialchars($userPhone) ?>">
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                                <input type="email" name="email" class="form-control" dir="ltr" placeholder="example@email.com" value="<?= htmlspecialchars($userEmail) ?>" required>
                                <p class="text-[10px] text-brk-400 mt-1">سنقوم بربط الجلسة بهذا البريد ليظهر في حسابك.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-pri-900 border-b border-gray-100 pb-2 mb-4"><i class="fas fa-stethoscope text-gld-500 ml-2"></i>تفاصيل الحالة والموعد</h3>
                        <div class="form-group">
                            <label class="form-label">الوقت المرغوب به للجلسة <span class="req">*</span></label>
                            <input type="text" name="preferred_time" class="form-control" required placeholder="مثال: بعد صلاة العصر بتوقيت مكة، يوم الثلاثاء">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label">ما هي الأعراض التي تعاني منها؟ <span class="req">*</span></label>
                            <textarea name="symptoms" class="form-textarea" rows="4" required placeholder="يرجى وصف الحالة والأعراض بالتفصيل لتسهيل التشخيص..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- العمود الأيسر: الدفع والإيصال -->
                <div>
                    <div class="bg-gray-50 border border-gray-200 rounded-3xl p-6 h-full flex flex-col">
                        <h3 class="text-xl font-black text-pri-900 mb-4 border-b border-gray-200 pb-3"><i class="fas fa-university text-gld-500 ml-2"></i> رسوم الجلسة والدفع</h3>
                        
                        <div class="bg-white rounded-xl p-4 border border-gld-200 text-center mb-6 shadow-sm">
                            <div class="text-brk-500 text-sm mb-1">رسوم الجلسة المباشرة (تشخيص + رقية)</div>
                            <!-- تم دمج الرقم مع العملة ليعمل سكربت التحويل -->
                            <div class="text-3xl font-black text-pri-700">150 ر.س</div>
                        </div>

                        <p class="text-brk-500 text-sm mb-4">يرجى تحويل قيمة الطلب إلى الحساب البنكي الخاص بالدولة التي تقيم فيها، وذلك لتسهيل عملية التحويل.</p>

                        <!-- الحسابات البنكية -->
                        <div class="space-y-4 mb-6">
                            
                            <!-- الإمارات -->
                            <div class="bg-white border border-gray-200 rounded-xl p-4 relative overflow-hidden group hover:border-pri-300 transition-colors">
                                <div class="absolute top-0 right-0 bg-pri-50 text-pri-700 px-3 py-0.5 rounded-bl-xl font-bold text-xs">🇦🇪 الإمارات العربية المتحدة</div>
                                <ul class="space-y-1.5 text-xs text-brk-600 mt-4">
                                    <li><span class="font-bold text-pri-900">اسم البنك:</span> بنك الإمارات الإسلامي</li>
                                    <li><span class="font-bold text-pri-900">اسم صاحب الحساب:</span> أحمــد مبارك حمد</li>
                                    <li><span class="font-bold text-pri-900">رقم الحساب:</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('3578521802301'); showToast('تم النسخ', 'ok')">3578521802301</span></li>
                                    <li><span class="font-bold text-pri-900">الآيبان (IBAN):</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('AE520340003578521802301'); showToast('تم النسخ', 'ok')">AE520340003578521802301</span></li>
                                    <li><span class="font-bold text-pri-900">العملة:</span> الدرهم الإماراتي</li>
                                    <li><span class="font-bold text-pri-900">رقم التوجية:</span> <span dir="ltr" class="font-mono">703420114</span></li>
                                </ul>
                            </div>

                            <!-- السعودية -->
                            <div class="bg-white border border-gray-200 rounded-xl p-4 relative overflow-hidden group hover:border-pri-300 transition-colors">
                                <div class="absolute top-0 right-0 bg-pri-50 text-pri-700 px-3 py-0.5 rounded-bl-xl font-bold text-xs">🇸🇦 المملكة العربية السعودية</div>
                                <ul class="space-y-1.5 text-xs text-brk-600 mt-4">
                                    <li><span class="font-bold text-pri-900">اسم البنك:</span> بنك الرياض</li>
                                    <li><span class="font-bold text-pri-900">اسم صاحب الحساب:</span> أحمــد مبارك حمد</li>
                                    <li><span class="font-bold text-pri-900">رقم الحساب:</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('1575973509940'); showToast('تم النسخ', 'ok')">1575973509940</span></li>
                                    <li><span class="font-bold text-pri-900">الآيبان (IBAN):</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('SA352000000157597350994'); showToast('تم النسخ', 'ok')">SA352000000157597350994</span></li>
                                    <li><span class="font-bold text-pri-900">العملة:</span> الريال السعودي</li>
                                </ul>
                            </div>

                            <!-- سلطنة عمان -->
                            <div class="bg-white border border-gray-200 rounded-xl p-4 relative overflow-hidden group hover:border-pri-300 transition-colors">
                                <div class="absolute top-0 right-0 bg-pri-50 text-pri-700 px-3 py-0.5 rounded-bl-xl font-bold text-xs">🇴🇲 سلطنة عمان</div>
                                <ul class="space-y-1.5 text-xs text-brk-600 mt-4">
                                    <li><span class="font-bold text-pri-900">اسم البنك:</span> بنك صحار الدولي</li>
                                    <li><span class="font-bold text-pri-900">اسم صاحب الحساب:</span> أحمــد مبارك حمد</li>
                                    <li><span class="font-bold text-pri-900">رقم الحساب:</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('023010072025'); showToast('تم النسخ', 'ok')">023010072025</span></li>
                                    <li><span class="font-bold text-pri-900">الآيبان (IBAN):</span> <span dir="ltr" class="font-mono bg-gray-50 px-1 py-0.5 rounded cursor-pointer hover:bg-pri-50" onclick="navigator.clipboard.writeText('OM340300000023010072025'); showToast('تم النسخ', 'ok')">OM340300000023010072025</span></li>
                                    <li><span class="font-bold text-pri-900">العملة:</span> الريال العماني</li>
                                    <li><span class="font-bold text-pri-900">سوفت (SWIFT code):</span> <span dir="ltr" class="font-mono">BSHROMRUXXX</span></li>
                                </ul>
                            </div>

                        </div>

                        <div class="bg-pri-50 p-4 rounded-xl border-r-4 border-pri-500 text-xs text-pri-800 leading-relaxed mb-4">
                            <p class="mb-1"><i class="fas fa-info-circle text-pri-600 ml-1"></i> بعد إتمام عملية التحويل، يرجى إرفاق إيصال التحويل بالأسفل أو إرساله عبر واتساب أو البريد الإلكتروني مع ذكر اسم العميل ورقم الطلب، حتى نتمكن من تأكيد العملية والبدء في تجهيز الطلب بأسرع وقت.</p>
                            <p class="font-bold">شكرًا لثقتكم في تشافي، ونسأل الله لكم الصحة والعافية.</p>
                        </div>

                        <div class="mt-auto bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                            <h4 class="text-sm font-bold text-pri-900 mb-2 flex items-center gap-2"><i class="fas fa-file-invoice-dollar text-pri-600"></i> إرفاق إيصال التحويل <span class="req">*</span></h4>
                            <p class="text-xs text-brk-600 mb-3">لن يتم تأكيد الجلسة وتفعيل الرابط المباشر إلا بعد إرفاق صورة الحوالة.</p>
                            <input type="file" name="transfer_receipt" accept="image/*,application/pdf" class="form-control !bg-white !py-2 text-sm" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 text-center border-t border-gray-100 mt-8">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full md:w-auto px-16 !py-4 text-lg">
                    <i class="fas fa-check-circle"></i> تأكيد الحجز وإرسال الطلب
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>