<?php
// مسار الملف: pages/admin_packages.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM packages WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_packages&deleted=1';</script>"; exit;
}

$packages = $pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-boxes text-gld-500 ml-2"></i>إدارة الباقات</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_package_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> إضافة باقة</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الباقة بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>الباقة</th>
                        <th>السعر الأصلي</th>
                        <th>سعر الباقة</th>
                        <th>المبيعات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($packages)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد باقات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($packages as $p): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($p['image_url'] ?? 'https://picsum.photos/100') ?>" class="w-12 h-12 rounded object-cover border border-gray-200">
                                        <div class="font-bold text-pri-900"><?= htmlspecialchars($p['name']) ?></div>
                                    </div>
                                </td>
                                <td class="text-brk-400 line-through text-sm"><?= number_format($p['original_total_price'], 2) ?> ر.س</td>
                                <td><div class="font-black text-pri-700"><?= number_format($p['package_price'], 2) ?> ر.س</div></td>
                                <td class="font-bold text-brk-500"><?= $p['sales_count'] ?></td>
                                <td>
                                    <?php if($p['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_package_form&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الباقة؟');">
                                        <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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