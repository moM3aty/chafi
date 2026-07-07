<?php
// مسار الملف: pages/admin_shipping_zones.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM shipping_zones WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    echo "<script>window.location.href='index.php?page=admin_shipping_zones&deleted=1';</script>"; exit;
}

$zones = $pdo->query("SELECT * FROM shipping_zones ORDER BY sort_order ASC, id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-truck text-gld-500 ml-2"></i>إدارة مناطق الشحن</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_shipping_zone_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> منطقة جديدة</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف المنطقة بنجاح.</div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(empty($zones)): ?>
            <div class="col-span-full text-center py-14 text-brk-400"><i class="fas fa-truck text-4xl mb-3 block opacity-25"></i><p>لا توجد مناطق شحن مسجلة</p></div>
        <?php else: ?>
            <?php foreach($zones as $z): ?>
                <div class="erp-card p-6 hover:border-pri-200 transition relative">
                    <?php if(!$z['is_active']): ?><div class="absolute top-3 left-3"><span class="badge badge-danger">معطلة</span></div><?php endif; ?>
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-pri-900 text-lg"><?= htmlspecialchars($z['zone_name']) ?></h3>
                            <?php if($z['cities']): ?>
                                <div class="text-xs text-brk-400 mt-1"><?= is_array(json_decode($z['cities'])) ? implode('، ', json_decode($z['cities'])) : htmlspecialchars($z['cities']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-2xl font-black text-pri-700"><?= number_format($z['base_cost'], 0) ?> <span class="text-xs font-bold">ر.س</span></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-center text-xs mb-4 bg-gray-50 rounded-xl p-3">
                        <div>
                            <div class="text-brk-400 mb-1">إضافي/عنصر</div>
                            <div class="font-bold text-pri-700"><?= number_format($z['extra_cost_per_item'], 1) ?></div>
                        </div>
                        <div>
                            <div class="text-brk-400 mb-1">شحن مجاني فوق</div>
                            <div class="font-bold text-green-600"><?= $z['free_shipping_threshold'] ? number_format($z['free_shipping_threshold'], 0) : '—' ?></div>
                        </div>
                        <div>
                            <div class="text-brk-400 mb-1">مدة التسليم</div>
                            <div class="font-bold"><?= $z['estimated_days_min'] ?>-<?= $z['estimated_days_max'] ?> يوم</div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="index.php?page=admin_shipping_zone_form&id=<?= $z['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-3 text-xs"><i class="fas fa-edit"></i> تعديل</a>
                        <form method="post" onsubmit="return confirm('تأكيد الحذف؟');">
                            <input type="hidden" name="delete_id" value="<?= $z['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger !py-1 !px-3 text-xs"><i class="fas fa-trash"></i> حذف</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>