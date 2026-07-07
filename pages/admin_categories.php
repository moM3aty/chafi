<?php
// مسار الملف: pages/admin_categories.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة الحذف
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_categories&deleted=1';</script>"; exit;
}

// جلب الأقسام
$categories = $pdo->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.sort_order ASC, c.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-sitemap text-gld-500 ml-2"></i>إدارة الأقسام</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_category_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> قسم جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف القسم بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>القسم</th>
                        <th>القسم الأب</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($categories)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-brk-400">لا توجد أقسام مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($categories as $cat): ?>
                            <tr>
                                <td class="font-bold text-pri-900">
                                    <i class="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?> text-gld-500 ml-2"></i>
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span class="block text-xs text-brk-400 font-normal mt-1" dir="ltr">/<?= htmlspecialchars($cat['slug']) ?></span>
                                </td>
                                <td class="text-sm text-brk-500"><?= $cat['parent_name'] ? htmlspecialchars($cat['parent_name']) : '<span class="text-xs bg-gray-100 px-2 py-1 rounded">رئيسي</span>' ?></td>
                                <td class="font-bold"><?= $cat['sort_order'] ?></td>
                                <td>
                                    <?php if($cat['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_category_form&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف هذا القسم؟');">
                                        <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
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