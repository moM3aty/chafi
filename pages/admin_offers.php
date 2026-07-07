<?php
// مسار الملف: pages/admin_offers.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM offers WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_offers&deleted=1';</script>"; exit;
}

// جلب العروض من قاعدة البيانات الجديدة (بنية الـ ERP) التي صممناها سابقاً
// ملاحظة: تأكد من تشغيل ملف setup_db.php لإنشاء جداول الـ offers إذا لم تكن موجودة.
$offers = [];
try {
    $offers = $pdo->query("SELECT * FROM packages WHERE discount_percentage > 0 ORDER BY id DESC")->fetchAll(); 
    // ملاحظة: إذا كان لديك جدول منفصل للعروض (offers)، استبدل packages بـ offers
} catch(Exception $e) {
    // في حال عدم وجود جدول العروض المنفصل
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-tags text-gld-500 ml-2"></i>إدارة العروض والخصومات</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_offer_form" class="cf-btn cf-btn-gld cf-btn-sm"><i class="fas fa-plus"></i> عرض جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف العرض بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>عنوان العرض / الباقة</th>
                        <th class="text-center">نسبة الخصم</th>
                        <th class="text-center">المدة</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offers)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-brk-400">لا توجد عروض أو خصومات حتى الآن</td></tr>
                    <?php else: ?>
                        <?php foreach ($offers as $item): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="font-bold text-pri-900"><?= htmlspecialchars($item['name'] ?? $item['title']) ?></td>
                                <td class="text-center font-bold text-pri-700"><?= $item['discount_percentage'] ?? $item['discount_value'] ?> %</td>
                                <td class="text-center text-xs text-brk-400">
                                    <?= isset($item['starts_at']) ? date('Y/m/d', strtotime($item['starts_at'])) : '-' ?><br>إلى<br>
                                    <?= isset($item['expires_at']) ? date('Y/m/d', strtotime($item['expires_at'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['is_active']): ?> <span class="badge badge-success">نشط</span>
                                    <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <button onclick="showToast('يتم التعديل عبر صفحة الباقات', 'warn')" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>