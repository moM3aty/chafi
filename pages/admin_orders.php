<?php
// مسار الملف: pages/admin_orders.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة تحديث الحالة السريع
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
    echo "<script>window.location.href='index.php?page=admin_orders&updated=1';</script>"; exit;
}

// جلب الطلبات
$orders = $pdo->query("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-file-invoice text-gld-500 ml-2"></i>إدارة طلبات المتجر</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم تحديث حالة الطلب بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>تحديث الحالة</th>
                        <th class="text-center">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد طلبات مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="font-bold text-pri-900" dir="ltr"><?= htmlspecialchars($o['order_number']) ?></td>
                                <td class="text-xs text-brk-500"><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <div class="font-bold text-pri-900"><?= htmlspecialchars($o['full_name']) ?></div>
                                    <div class="text-[10px] text-gray-500" dir="ltr"><?= htmlspecialchars($o['shipping_phone'] ?? $o['phone'] ?? '') ?></div>
                                </td>
                                <td class="font-black text-pri-700"><?= number_format($o['total_amount'], 2) ?> ر.س</td>
                                <td>
                                    <form method="post" class="flex items-center gap-2">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="status" class="form-select !py-1 !px-2 !text-xs !rounded-lg w-auto min-w-[120px] <?= $o['status']=='Pending'?'bg-yellow-50 text-yellow-700 border-yellow-200':($o['status']=='Delivered'?'bg-green-50 text-green-700 border-green-200':'bg-blue-50 text-blue-700 border-blue-200') ?>" onchange="this.form.submit()">
                                            <option value="Pending" <?= $o['status']=='Pending'?'selected':'' ?>>قيد الانتظار</option>
                                            <option value="Processing" <?= $o['status']=='Processing'?'selected':'' ?>>قيد التجهيز</option>
                                            <option value="Shipped" <?= $o['status']=='Shipped'?'selected':'' ?>>تم الشحن</option>
                                            <option value="Delivered" <?= $o['status']=='Delivered'?'selected':'' ?>>تم التسليم</option>
                                            <option value="Cancelled" <?= $o['status']=='Cancelled'?'selected':'' ?>>ملغي</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?page=admin_order_details&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-3 text-xs"><i class="fas fa-eye"></i> الفاتورة</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>