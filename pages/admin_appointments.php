<?php
// مسار الملف: pages/admin_appointments.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// التأكد من وجود عمود scheduled_time (يتم تنفيذه صامتاً مرة واحدة)
try {
    $pdo->query("SELECT scheduled_time FROM appointments LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN scheduled_time VARCHAR(200) DEFAULT NULL AFTER preferred_time");
}

// معالجة تحديث الحالة والموعد
if (isset($_POST['update_status'])) {
    $aptId = (int)$_POST['apt_id'];
    $newStatus = $_POST['status'];
    $scheduledTime = trim($_POST['scheduled_time'] ?? '');
    
    $pdo->prepare("UPDATE appointments SET status = ?, scheduled_time = ? WHERE id = ?")->execute([$newStatus, $scheduledTime, $aptId]);
    echo "<script>window.location.href='index.php?page=admin_appointments&updated=1';</script>"; exit;
}

// معالجة الحذف
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_appointments&deleted=1';</script>"; exit;
}

$appointments = $pdo->query("SELECT * FROM appointments ORDER BY id DESC")->fetchAll();

$statusAr = [
    'Pending' => 'قيد المراجعة',
    'Confirmed' => 'مؤكد (جاهز للاتصال)',
    'Completed' => 'تم الانتهاء',
    'Cancelled' => 'ملغي'
];
$statusClr = [
    'Pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
    'Confirmed' => 'bg-green-50 text-green-700 border-green-200',
    'Completed' => 'bg-gray-100 text-gray-600 border-gray-200',
    'Cancelled' => 'bg-red-50 text-red-700 border-red-200'
];
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-video text-gld-500 ml-2"></i>إدارة حجوزات الجلسات (أونلاين)</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6 shadow-sm"><i class="fas fa-check-circle ml-2"></i> تم تحديث حالة وموعد الجلسة بنجاح، سيظهر للعميل في حسابه.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-red-50 border-r-4 border-red-500 p-4 rounded-xl text-red-700 font-bold mb-6 shadow-sm"><i class="fas fa-trash ml-2"></i> تم حذف الحجز بنجاح.</div>
    <?php endif; ?>

    <div class="bg-pri-50 p-5 rounded-xl border border-pri-200 mb-6 text-sm text-pri-800 leading-loose shadow-inner">
        <h3 class="font-bold mb-2 text-pri-900"><i class="fas fa-info-circle text-pri-600 ml-1"></i> آلية تحديد المواعيد:</h3>
        <ul class="list-disc pr-5 space-y-1">
            <li>العميل يكتب <b>"الوقت المفضل له"</b> عند الحجز.</li>
            <li>أنت كمدير تقوم بالتواصل معه (أو تحديد الوقت المناسب لك) وتكتبه في حقل <b>"الموعد النهائي המعتمد"</b>.</li>
            <li>عند تغيير الحالة إلى <span class="font-bold text-green-700">"مؤكد"</span> وحفظها، سيظهر الموعد النهائي للعميل، وسيتمكن من رؤية زر دخول الغرفة.</li>
        </ul>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>رقم الحجز</th>
                        <th>بيانات العميل</th>
                        <th>الأوقات</th>
                        <th>تحديث الحالة والموعد</th>
                        <th class="text-center">إيصال الدفع / التفاصيل</th>
                        <th class="text-center">دخول الغرفة / إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($appointments)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد حجوزات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($appointments as $apt): ?>
                            <tr>
                                <td class="font-bold text-pri-900" dir="ltr">#APT-<?= $apt['id'] ?></td>
                                <td>
                                    <div class="font-bold text-pri-900"><?= htmlspecialchars($apt['full_name']) ?> <span class="text-xs text-brk-400 font-normal">(<?= $apt['age'] ?> سنة - <?= $apt['gender'] ?>)</span></div>
                                    <div class="text-[10px] text-gray-500 flex gap-2 mt-1" dir="ltr">
                                        <span><i class="fab fa-whatsapp text-green-500"></i> <?= htmlspecialchars($apt['whatsapp']) ?></span>
                                        <span>|</span>
                                        <span><?= htmlspecialchars($apt['country']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[10px] text-brk-400 mb-1">وقت العميل المفضل:</div>
                                    <div class="text-xs text-pri-800 font-bold max-w-[150px] leading-relaxed mb-2 bg-gray-50 p-1.5 rounded border border-gray-100">
                                        <?= htmlspecialchars($apt['preferred_time']) ?>
                                    </div>
                                </td>
                                <td class="w-64">
                                    <form method="post" class="flex flex-col gap-2 bg-white p-2 rounded-lg border border-gray-100 shadow-sm">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="apt_id" value="<?= $apt['id'] ?>">
                                        
                                        <div class="flex items-center gap-2">
                                            <select name="status" class="form-select flex-1 !py-1.5 !px-2 !text-xs !rounded-md <?= $statusClr[$apt['status']] ?>">
                                                <option value="Pending" <?= $apt['status']=='Pending'?'selected':'' ?>>قيد المراجعة</option>
                                                <option value="Confirmed" <?= $apt['status']=='Confirmed'?'selected':'' ?>>مؤكد (جاهز للاتصال)</option>
                                                <option value="Completed" <?= $apt['status']=='Completed'?'selected':'' ?>>تم الانتهاء</option>
                                                <option value="Cancelled" <?= $apt['status']=='Cancelled'?'selected':'' ?>>ملغي</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm !py-1.5 !px-3 shrink-0"><i class="fas fa-save"></i> حفظ</button>
                                        </div>
                                        
                                        <input type="text" name="scheduled_time" placeholder="اكتب الموعد النهائي (مثال: غداً 4 عصراً)" value="<?= htmlspecialchars($apt['scheduled_time'] ?? '') ?>" class="form-control !py-1.5 !px-2 !text-xs bg-pri-50/30 border-pri-200 placeholder-pri-300" title="هذا الموعد سيظهر للعميل في حسابه">
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button onclick="openAptModal(<?= htmlspecialchars(json_encode($apt)) ?>)" class="btn btn-sm btn-outline !py-1 !px-3 text-xs"><i class="fas fa-eye"></i> عرض البيانات</button>
                                </td>
                                <td class="text-center space-y-1.5">
                                    <?php if ($apt['status'] == 'Confirmed'): ?>
                                        <!-- زر دخول الشيخ للغرفة -->
                                        <a href="index.php?page=meeting&id=<?= $apt['id'] ?>" class="btn btn-sm btn-gold !py-1.5 !px-3 text-xs w-full block shadow-md animate-pulse" title="دخول الغرفة كمدير"><i class="fas fa-video"></i> دخول الجلسة</a>
                                    <?php endif; ?>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الحجز نهائياً؟');">
                                        <input type="hidden" name="delete_id" value="<?= $apt['id'] ?>">
                                        <button type="submit" class="btn btn-sm bg-gray-100 hover:bg-red-500 text-gray-500 hover:text-white border-0 !py-1 !px-3 text-xs w-full transition-colors"><i class="fas fa-trash"></i> حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال عرض تفاصيل الحجز -->
<div id="aptModal" class="modal-backdrop">
    <div class="modal-dialog" style="max-width:600px">
        <button onclick="closeMdl('aptModal')" class="modal-close"><i class="fas fa-times"></i></button>
        <div class="modal-header pb-4">
            <h2 class="text-xl font-black text-pri-900 font-amiri">تفاصيل الحجز <span id="m_apt_id" class="text-gld-500" dir="ltr"></span></h2>
        </div>
        <div class="modal-body space-y-4 !pt-4">
            
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h4 class="font-bold text-pri-900 mb-2 border-b border-gray-200 pb-1 text-sm"><i class="fas fa-stethoscope text-pri-500"></i> تفاصيل الأعراض</h4>
                <p id="m_symptoms" class="text-sm text-brk-600 leading-loose whitespace-pre-wrap"></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h4 class="font-bold text-pri-900 mb-2 border-b border-gray-200 pb-1 text-sm"><i class="fas fa-receipt text-pri-500"></i> إيصال الدفع البنكي المرفق</h4>
                <div id="m_receipt_container" class="text-center">
                    <!-- سيتم حقن صورة الإيصال هنا -->
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function openAptModal(data) {
    document.getElementById('m_apt_id').innerText = '#APT-' + data.id;
    document.getElementById('m_symptoms').innerText = data.symptoms || 'لم يتم كتابة أعراض.';
    
    const receiptContainer = document.getElementById('m_receipt_container');
    if (data.transfer_receipt_url) {
        const url = data.transfer_receipt_url;
        if (url.toLowerCase().endsWith('.pdf')) {
            receiptContainer.innerHTML = `<a href="../${url}" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> عرض ملف الـ PDF المرفق</a>`;
        } else {
            receiptContainer.innerHTML = `<a href="../${url}" target="_blank"><img src="../${url}" class="max-w-full h-auto max-h-64 mx-auto rounded-lg shadow-sm border border-gray-200 hover:opacity-80 transition cursor-zoom-in"></a>`;
        }
    } else {
        receiptContainer.innerHTML = '<span class="text-red-500 text-sm font-bold">لم يقم العميل بإرفاق إيصال.</span>';
    }

    openMdl('aptModal');
}
</script>