<?php
// مسار الملف: pages/admin_order_details.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب الطلب
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='text-center py-20 font-bold text-red-500'>الطلب غير موجود</div>"; exit;
}

// جلب عناصر الطلب
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4 border-b border-gray-200 pb-4">
        <div>
            <h1 class="text-2xl font-black text-pri-900 font-amiri mb-1"><i class="fas fa-file-invoice-dollar text-gld-500 ml-2"></i>فاتورة الطلب</h1>
            <p class="text-sm text-brk-500" dir="ltr">#<?= $order['order_number'] ?></p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="cf-btn cf-btn-gld cf-btn-sm"><i class="fas fa-print"></i> طباعة</button>
            <a href="index.php?page=admin_orders" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة للطلبات</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- بيانات العميل والشحن -->
        <div class="erp-card p-6 bg-gray-50/50">
            <h3 class="font-bold text-pri-900 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-user-tag text-gld-500 ml-1"></i> معلومات العميل والشحن</h3>
            <ul class="space-y-3 text-sm text-brk-500">
                <li class="flex justify-between"><span class="font-bold">الاسم:</span> <span><?= htmlspecialchars($order['full_name']) ?></span></li>
                <li class="flex justify-between"><span class="font-bold">البريد:</span> <span dir="ltr"><?= htmlspecialchars($order['email']) ?></span></li>
                <li class="flex justify-between"><span class="font-bold">الجوال:</span> <span dir="ltr"><?= htmlspecialchars($order['shipping_phone'] ?? $order['phone'] ?? '') ?></span></li>
                <li class="flex justify-between"><span class="font-bold">المدينة:</span> <span><?= htmlspecialchars($order['shipping_city'] ?? $order['city'] ?? '') ?></span></li>
                <li>
                    <span class="font-bold block mb-1">العنوان التفصيلي:</span>
                    <div class="bg-white p-2 rounded border border-gray-200"><?= htmlspecialchars($order['shipping_address'] ?? '') ?></div>
                </li>
            </ul>
        </div>

        <!-- ملخص الدفع -->
        <div class="erp-card p-6 bg-gray-50/50">
            <h3 class="font-bold text-pri-900 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-money-check-alt text-gld-500 ml-1"></i> ملخص الدفع</h3>
            <ul class="space-y-3 text-sm text-brk-500">
                <li class="flex justify-between"><span class="font-bold">تاريخ الطلب:</span> <span dir="ltr"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></span></li>
                <li class="flex justify-between"><span class="font-bold">طريقة الدفع:</span> <span class="badge bg-pri-50 text-pri-700 border-pri-200"><?= $order['payment_method'] ?></span></li>
                <li class="flex justify-between"><span class="font-bold">المجموع الفرعي:</span> <span><?= number_format($order['sub_total'], 2) ?> ر.س</span></li>
                <li class="flex justify-between"><span class="font-bold">تكلفة الشحن:</span> <span><?= number_format($order['shipping_cost'], 2) ?> ر.س</span></li>
                <li class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                    <span class="font-black text-pri-900 text-base">الإجمالي النهائي:</span> 
                    <span class="font-black text-pri-700 text-lg"><?= number_format($order['total_amount'], 2) ?> ر.س</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- جدول المنتجات -->
    <div class="erp-card overflow-hidden">
        <h3 class="font-bold text-pri-900 p-6 border-b border-gray-100 bg-white"><i class="fas fa-shopping-basket text-gld-500 ml-1"></i> المنتجات المطلوبة</h3>
        <div class="table-responsive !border-0 !shadow-none !rounded-none">
            <table class="erp-table !border-0">
                <thead class="bg-gray-50">
                    <tr>
                        <th>المنتج</th>
                        <th class="text-center">السعر</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                        <tr>
                            <td class="font-bold text-pri-900"><?= htmlspecialchars($item['item_name']) ?></td>
                            <td class="text-center text-brk-500"><?= number_format($item['unit_price'], 2) ?> ر.س</td>
                            <td class="text-center font-bold">x <?= $item['quantity'] ?></td>
                            <td class="text-center font-black text-pri-700"><?= number_format($item['total_price'], 2) ?> ر.س</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>