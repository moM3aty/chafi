<?php
// مسار الملف: pages/admin_reviews.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة الاعتماد أو الحذف
if (isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] == 'approve') {
        $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")->execute([$id]);
    } elseif ($_POST['action'] == 'delete') {
        $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    }
    echo "<script>window.location.href='index.php?page=admin_reviews';</script>"; exit;
}

// جلب التقييمات مع بيانات المستخدم والمنتج
$reviews = $pdo->query("
    SELECT r.*, u.full_name as user_name, p.name as product_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.reviewable_id = p.id 
    WHERE r.reviewable_type = 'product'
    ORDER BY r.created_at DESC
")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-star-half-alt text-gld-500 ml-2"></i>آراء العملاء والتقييمات</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <div class="erp-card overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>العميل</th>
                        <th class="text-center">التقييم</th>
                        <th>التعليق</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد تقييمات حتى الآن</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $item): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="font-bold text-pri-900">
                                    <a href="index.php?page=product_details&id=<?= $item['reviewable_id'] ?>" target="_blank" class="hover:text-gld-600 transition truncate block max-w-[150px]"><?= htmlspecialchars($item['product_name']) ?></a>
                                </td>
                                <td class="text-brk-500 text-sm"><?= htmlspecialchars($item['user_name']) ?></td>
                                <td class="text-center text-gld-500 text-sm whitespace-nowrap">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $item['rating'] ? '' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td class="text-brk-500 text-xs max-w-xs truncate" title="<?= htmlspecialchars($item['review_text']) ?>"><?= htmlspecialchars($item['review_text']) ?></td>
                                <td class="text-center">
                                    <?php if ($item['is_approved']): ?> <span class="badge badge-success">معتمد</span>
                                    <?php else: ?> <span class="badge badge-warning">قيد المراجعة</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <?php if (!$item['is_approved']): ?>
                                        <form method="post">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline !py-1 !px-2 text-xs text-green-600 border-green-200 hover:bg-green-50" title="اعتماد وإظهار"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
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