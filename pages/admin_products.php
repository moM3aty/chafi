<?php
// مسار الملف: pages/admin_products.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة الحذف
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_products&deleted=1';</script>"; exit;
}

// جلب المنتجات مع الأقسام
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-box-open text-gld-500 ml-2"></i>إدارة المنتجات</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_product_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> منتج جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف المنتج بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>صورة</th>
                        <th>المنتج</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-brk-400">لا توجد منتجات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($p['image_url'] ?? 'https://picsum.photos/100') ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                </td>
                                <td>
                                    <div class="font-bold text-pri-900 line-clamp-1 max-w-[200px]"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-[10px] text-gray-400" dir="ltr"><?= htmlspecialchars($p['sku'] ?? 'NO-SKU') ?></div>
                                </td>
                                <td class="text-sm text-brk-500"><?= htmlspecialchars($p['cat_name']) ?></td>
                                <td>
                                    <div class="font-black text-pri-700"><?= number_format($p['price'], 2) ?> ر.س</div>
                                    <?php if($p['old_price'] > $p['price']): ?><div class="text-xs text-red-500 line-through"><?= number_format($p['old_price'], 2) ?></div><?php endif; ?>
                                </td>
                                <td class="font-bold <?= $p['stock_quantity'] < 5 ? 'text-red-500' : 'text-green-600' ?>"><?= $p['stock_quantity'] ?></td>
                                <td>
                                    <?php if($p['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_product_form&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف هذا المنتج نهائياً؟');">
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