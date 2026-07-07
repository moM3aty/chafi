<?php
// مسار الملف: pages/admin_videos.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_videos&deleted=1';</script>"; exit;
}

$videos = $pdo->query("SELECT v.*, c.name as cat_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id ORDER BY v.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-video text-gld-500 ml-2"></i>إدارة الفيديوهات</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_video_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> إضافة فيديو</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الفيديو بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>الفيديو</th>
                        <th>المقدم / الشيخ</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th>المشاهدات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($videos)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-brk-400">لا توجد فيديوهات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($videos as $v): ?>
                            <tr>
                                <td>
                                    <div class="font-bold text-pri-900 line-clamp-1 max-w-[200px]"><?= htmlspecialchars($v['title']) ?></div>
                                </td>
                                <td class="text-sm font-bold text-brk-500"><?= htmlspecialchars($v['presenter'] ?? '-') ?></td>
                                <td class="text-sm text-brk-500"><?= htmlspecialchars($v['cat_name']) ?></td>
                                <td>
                                    <div class="font-black text-pri-700"><?= $v['price'] > 0 ? number_format($v['price'], 2) . ' ر.س' : 'مجاني' ?></div>
                                </td>
                                <td class="font-bold text-brk-500"><?= $v['view_count'] ?></td>
                                <td>
                                    <?php if($v['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_video_form&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post">
                                        <input type="hidden" name="delete_id" value="<?= $v['id'] ?>">
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