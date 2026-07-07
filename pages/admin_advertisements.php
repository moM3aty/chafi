<?php
// مسار الملف: pages/admin_advertisements.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM advertisements WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_advertisements&deleted=1';</script>"; exit;
}

$ads = $pdo->query("SELECT * FROM advertisements ORDER BY position, display_order ASC")->fetchAll();
$positionLabels = [0 => 'هيرو سلايدر', 1 => 'بانر جانبي', 2 => 'بانر وسط الصفحة', 3 => 'نافذة منبثقة'];
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-images text-gld-500 ml-2"></i>إدارة الإعلانات والسلايدر</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_advertisement_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> إعلان جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الإعلان بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>العنوان</th>
                        <th>الموقع</th>
                        <th>الترتيب</th>
                        <th>النقرات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ads)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-brk-400">لا توجد إعلانات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($ads as $ad): ?>
                            <tr>
                                <td><img src="<?= htmlspecialchars($ad['image_url']) ?>" class="w-20 h-12 rounded-lg object-cover border border-gray-200"></td>
                                <td class="font-bold text-pri-900 max-w-[200px] truncate"><?= htmlspecialchars(strip_tags($ad['title'])) ?></td>
                                <td><span class="badge bg-pri-50 text-pri-700"><?= $positionLabels[$ad['position']] ?? 'غير معروف' ?></span></td>
                                <td class="font-bold"><?= $ad['display_order'] ?></td>
                                <td class="font-bold text-brk-500"><?= $ad['click_count'] ?></td>
                                <td>
                                    <?php if($ad['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_advertisement_form&id=<?= $ad['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الإعلان؟');">
                                        <input type="hidden" name="delete_id" value="<?= $ad['id'] ?>">
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