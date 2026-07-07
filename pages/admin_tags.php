<?php
// مسار الملف: pages/admin_tags.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// إضافة وسم جديد
if (isset($_POST['add_tag'])) {
    $name = trim($_POST['tag_name']);
    $slug = !empty($_POST['tag_slug']) ? trim($_POST['tag_slug']) : strtolower(str_replace(' ', '-', $name));
    try {
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
    } catch(PDOException $e) {}
    echo "<script>window.location.href='index.php?page=admin_tags';</script>"; exit;
}

// حذف وسم
if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_tags&deleted=1';</script>"; exit;
}

$tags = $pdo->query("SELECT t.*, (SELECT COUNT(*) FROM product_tags pt WHERE pt.tag_id = t.id) as products_count FROM tags t ORDER BY t.usage_count DESC, t.id DESC")->fetchAll();
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-tags text-gld-500 ml-2"></i>إدارة الوسوم</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الوسم بنجاح.</div>
    <?php endif; ?>

    <!-- إضافة وسم سريع -->
    <div class="erp-card p-6 mb-8">
        <h3 class="font-bold text-pri-900 mb-4"><i class="fas fa-plus-circle text-gld-500 ml-1"></i> إضافة وسم جديد</h3>
        <form method="post" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="form-group !mb-0 flex-1">
                <label class="form-label">اسم الوسم <span class="req">*</span></label>
                <input type="text" name="tag_name" class="form-control" required>
            </div>
            <div class="form-group !mb-0 w-full sm:w-48">
                <label class="form-label">الرابط (Slug)</label>
                <input type="text" name="tag_slug" dir="ltr" class="form-control" placeholder="تلقائي">
            </div>
            <button type="submit" name="add_tag" value="1" class="btn btn-primary h-[46px] px-6 shrink-0"><i class="fas fa-plus"></i> إضافة</button>
        </form>
    </div>

    <!-- قائمة الوسوم -->
    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>الوسم</th>
                        <th>الرابط</th>
                        <th>المنتجات المرتبطة</th>
                        <th>مرات الاستخدام</th>
                        <th class="text-center">حذف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($tags)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-brk-400">لا توجد وسوم</td></tr>
                    <?php else: ?>
                        <?php foreach($tags as $t): ?>
                            <tr>
                                <td>
                                    <span class="inline-flex items-center gap-2 bg-gld-50 text-gld-800 px-3 py-1.5 rounded-full text-sm font-bold border border-gld-200">
                                        <i class="fas fa-tag text-[10px]"></i> <?= htmlspecialchars($t['name']) ?>
                                    </span>
                                </td>
                                <td class="text-sm text-brk-500" dir="ltr"><?= htmlspecialchars($t['slug']) ?></td>
                                <td class="font-bold text-center"><?= $t['products_count'] ?></td>
                                <td class="font-bold text-center text-brk-500"><?= $t['usage_count'] ?></td>
                                <td class="text-center">
                                    <form method="post" onsubmit="return confirm('حذف هذا الوسم؟');">
                                        <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
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