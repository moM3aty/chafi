<?php
// مسار الملف: pages/admin_cms_pages.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM cms_pages WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_cms_pages&deleted=1';</script>"; exit;
}

$pages = $pdo->query("SELECT * FROM cms_pages ORDER BY sort_order ASC, id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-file-alt text-gld-500 ml-2"></i>إدارة الصفحات التعريفية</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_cms_page_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> صفحة جديدة</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الصفحة بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>عنوان الصفحة</th>
                        <th>الرابط (Slug)</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pages)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-brk-400">لا توجد صفحات تعريفية</td></tr>
                    <?php else: ?>
                        <?php foreach($pages as $p): ?>
                            <tr>
                                <td class="font-bold text-pri-900"><?= htmlspecialchars($p['title']) ?></td>
                                <td class="text-sm text-brk-500" dir="ltr">/page/<?= htmlspecialchars($p['slug']) ?></td>
                                <td class="font-bold"><?= $p['sort_order'] ?></td>
                                <td>
                                    <?php if($p['is_active']): ?> <span class="badge badge-success">مفعلة</span> <?php else: ?> <span class="badge badge-danger">معطلة</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_cms_page_form&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <a href="index.php?page=cms&slug=<?= $p['slug'] ?>" target="_blank" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="معاينة"><i class="fas fa-eye"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد الحذف؟');">
                                        <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger !py-1 !px-2 text-xs"><i class="fas fa-trash"></i></button>
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