<?php
// مسار الملف: pages/admin_audios.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة الحذف
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM audios WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_audios&deleted=1';</script>"; exit;
}

// جلب الصوتيات مع الأقسام
$audios = $pdo->query("SELECT a.*, c.name as cat_name FROM audios a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-headphones text-gld-500 ml-2"></i>إدارة الصوتيات</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_audio_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> إضافة مقطع</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف المقطع الصوتي بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المقطع</th>
                        <th>القسم</th>
                        <th>القارئ / الراوي</th>
                        <th>السعر</th>
                        <th>الاستماعات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($audios)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-brk-400">لا توجد صوتيات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($audios as $a): ?>
                            <tr>
                                <td>
                                    <div class="font-bold text-pri-900 line-clamp-1 max-w-[200px]"><?= htmlspecialchars($a['title']) ?></div>
                                </td>
                                <td class="text-sm text-brk-500"><?= htmlspecialchars($a['cat_name']) ?></td>
                                <td class="text-sm text-pri-700 font-bold"><?= htmlspecialchars($a['narrator'] ?? '-') ?></td>
                                <td>
                                    <div class="font-black text-pri-700"><?= $a['price'] > 0 ? number_format($a['price'], 2) . ' ر.س' : 'مجاني' ?></div>
                                </td>
                                <td class="font-bold text-brk-500"><?= $a['listen_count'] ?></td>
                                <td>
                                    <?php if($a['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_audio_form&id=<?= $a['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف المقطع الصوتي؟');">
                                        <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
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