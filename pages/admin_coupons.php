<?php
// مسار الملف: pages/admin_coupons.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_coupons&deleted=1';</script>"; exit;
}

$coupons = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM coupon_usage cu WHERE cu.coupon_id = c.id) as usage_count FROM coupons c ORDER BY c.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-ticket-alt text-gld-500 ml-2"></i>إدارة كوبونات الخصم</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_coupon_form" class="cf-btn cf-btn-gld cf-btn-sm"><i class="fas fa-plus"></i> كوبون جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الكوبون بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>نوع الخصم</th>
                        <th>القيمة</th>
                        <th>الحد الأدنى للطلب</th>
                        <th>الاستخدام</th>
                        <th>الصلاحية</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($coupons)): ?>
                        <tr><td colspan="8" class="text-center py-10 text-brk-400">لا توجد كوبونات</td></tr>
                    <?php else: ?>
                        <?php foreach($coupons as $c): ?>
                            <tr>
                                <td>
                                    <div class="font-black text-pri-900 text-lg tracking-wider" dir="ltr"><?= htmlspecialchars($c['code']) ?></div>
                                    <?php if($c['description']): ?><div class="text-[10px] text-brk-400"><?= htmlspecialchars($c['description']) ?></div><?php endif; ?>
                                </td>
                                <td><span class="badge bg-pri-50 text-pri-700"><?= $c['discount_type'] == 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت' ?></span></td>
                                <td class="font-black text-gld-600"><?= $c['discount_type'] == 'percentage' ? $c['discount_value'] . '%' : number_format($c['discount_value'], 2) . ' ر.س' ?></td>
                                <td class="text-sm text-brk-500"><?= $c['min_order_amount'] > 0 ? number_format($c['min_order_amount'], 2) . ' ر.س' : 'بدون حد' ?></td>
                                <td>
                                    <div class="font-bold"><?= $c['usage_count'] ?></div>
                                    <div class="text-[10px] text-brk-400">من <?= $c['max_uses_total'] ?: '∞' ?></div>
                                </td>
                                <td class="text-xs text-brk-500">
                                    <?php if($c['starts_at']): ?>من: <?= date('Y/m/d', strtotime($c['starts_at'])) ?><br><?php endif; ?>
                                    <?php if($c['expires_at']): ?>إلى: <?= date('Y/m/d', strtotime($c['expires_at'])) ?><?php else: ?>بدون انتهاء<?php endif; ?>
                                </td>
                                <td>
                                    <?php if($c['is_active']): ?> <span class="badge badge-success">نشط</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_coupon_form&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الكوبون؟');">
                                        <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger !py-1 !px-2 text-xs" title="حذف"><i class="fas fa-trash"></i></button>
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