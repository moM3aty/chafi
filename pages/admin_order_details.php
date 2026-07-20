<?php
// مسار الملف: pages/admin_order_details.php
// المكان: داخل مجلد pages

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['update_order_status'])) {
    $newStatus = $_POST['new_status'];
    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmtUpdate->execute([$newStatus, $id]);
    echo "<script>window.location.href='index.php?page=admin_order_details&id={$id}&updated=1';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='text-center py-20 font-bold text-red-500'>الطلب غير موجود</div>"; exit;
}

$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

$statusAr = ['Pending'=>'قيد الانتظار','Processing'=>'قيد التجهيز','Shipped'=>'تم الشحن','Delivered'=>'تم التسليم','Cancelled'=>'ملغي','Refunded'=>'مسترد','Failed'=>'فاشل'];
$statusClr = ['Pending'=>'bg-yellow-50 text-yellow-700 border-yellow-200','Processing'=>'bg-blue-50 text-blue-700 border-blue-200','Shipped'=>'bg-purple-50 text-purple-700 border-purple-200','Delivered'=>'bg-green-50 text-green-700 border-green-200','Cancelled'=>'bg-red-50 text-red-700 border-red-200','Refunded'=>'bg-gray-50 text-gray-600 border-gray-200','Failed'=>'bg-red-50 text-red-600 border-red-200'];
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6 shadow-sm"><i class="fas fa-check-circle ml-2"></i> تم تحديث حالة الطلب بنجاح. (إذا تم تغيير الحالة إلى "تم التسليم" أو "قيد التجهيز"، سيتمكن العميل من تحميل الكتب الرقمية فوراً).</div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4 border-b border-gray-200 pb-4">
        <div>
            <h1 class="text-2xl font-black text-pri-900 font-amiri mb-1"><i class="fas fa-file-invoice-dollar text-gld-500 ml-2"></i>فاتورة الطلب</h1>
            <p class="text-sm text-brk-500" dir="ltr">#<?= htmlspecialchars($order['order_number']) ?></p>
        </div>
        
        <div class="flex flex-wrap gap-3 items-center">
            <!-- فورم تغيير حالة الطلب -->
            <form method="post" class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-lg border border-gray-200">
                <input type="hidden" name="update_order_status" value="1">
                <span class="text-xs font-bold text-gray-600 mr-2">تحديث الحالة:</span>
                <select name="new_status" class="form-select !py-1 !px-2 !text-xs !rounded-md font-bold <?= $statusClr[$order['status']] ?? '' ?>">
                    <?php foreach($statusAr as $key => $val): ?>
                        <option value="<?= $key ?>" <?= $order['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm !py-1 !px-3 shadow-sm text-xs"><i class="fas fa-save"></i> حفظ</button>
            </form>

            <button onclick="window.print()" class="cf-btn cf-btn-gld cf-btn-sm"><i class="fas fa-print"></i> طباعة</button>
            <a href="index.php?page=admin_orders" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> للطلبات</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- بيانات العميل والشحن -->
        <div class="erp-card p-6 bg-gray-50/50">
            <h3 class="font-bold text-pri-900 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-user-tag text-gld-500 ml-1"></i> معلومات العميل والشحن</h3>
            <ul class="space-y-3 text-sm text-brk-500">
                <li class="flex justify-between"><span class="font-bold">الاسم:</span> <span><?= htmlspecialchars($order['shipping_full_name'] ?? $order['full_name'] ?? 'زائر') ?></span></li>
                <li class="flex justify-between"><span class="font-bold">البريد:</span> <span dir="ltr"><?= htmlspecialchars($order['email'] ?? 'غير مسجل') ?></span></li>
                <li class="flex justify-between"><span class="font-bold">الجوال:</span> <span dir="ltr"><?= htmlspecialchars($order['shipping_phone'] ?? '') ?></span></li>
                <li class="flex justify-between"><span class="font-bold">المدينة:</span> <span><?= htmlspecialchars($order['shipping_city'] ?? '') ?></span></li>
                <li>
                    <span class="font-bold block mb-1">العنوان التفصيلي:</span>
                    <div class="bg-white p-2 rounded border border-gray-200"><?= htmlspecialchars($order['shipping_address'] ?? 'منتجات رقمية (بدون عنوان)') ?></div>
                </li>
            </ul>
        </div>

        <!-- ملخص الدفع وإيصال التحويل -->
        <div class="erp-card p-6 bg-gray-50/50">
            <h3 class="font-bold text-pri-900 mb-4 border-b border-gray-200 pb-2"><i class="fas fa-money-check-alt text-gld-500 ml-1"></i> ملخص الدفع</h3>
            <ul class="space-y-3 text-sm text-brk-500 mb-4">
                <li class="flex justify-between"><span class="font-bold">تاريخ الطلب:</span> <span dir="ltr"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></span></li>
                <li class="flex justify-between"><span class="font-bold">طريقة الدفع:</span> <span class="badge bg-pri-50 text-pri-700 border-pri-200"><?= $order['payment_method'] == 'BankTransfer' ? 'تحويل بنكي' : $order['payment_method'] ?></span></li>
                <li class="flex justify-between"><span class="font-bold">المجموع الفرعي:</span> <span><?= number_format($order['sub_total'], 2) ?> ر.س</span></li>
                <li class="flex justify-between"><span class="font-bold">تكلفة الشحن:</span> <span><?= number_format($order['shipping_cost'], 2) ?> ر.س</span></li>
                <?php if ($order['discount_amount'] > 0): ?>
                <li class="flex justify-between text-gld-600 font-bold"><span class="font-bold">الخصم:</span> <span>- <?= number_format($order['discount_amount'], 2) ?> ر.س</span></li>
                <?php endif; ?>
                <li class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                    <span class="font-black text-pri-900 text-base">الإجمالي النهائي:</span> 
                    <span class="font-black text-pri-700 text-lg"><?= number_format($order['total_amount'], 2) ?> ر.س</span>
                </li>
            </ul>

            <!-- إيصال التحويل الذي طلبه العميل -->
            <?php if(!empty($order['transfer_receipt_url'])): ?>
                <div class="mt-4 pt-4 border-t border-dashed border-gray-300">
                    <h4 class="font-bold text-pri-900 mb-2 text-xs"><i class="fas fa-receipt text-pri-500"></i> إيصال التحويل المرفق:</h4>
                    <a href="../<?= htmlspecialchars($order['transfer_receipt_url']) ?>" target="_blank" class="block border border-gray-200 rounded-xl overflow-hidden hover:opacity-80 transition relative bg-gray-100 flex items-center justify-center h-32">
                        <?php 
                        $ext = strtolower(pathinfo($order['transfer_receipt_url'], PATHINFO_EXTENSION));
                        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                            <img src="../<?= htmlspecialchars($order['transfer_receipt_url']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-file-pdf text-4xl text-red-500"></i>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                            <span class="text-white font-bold text-sm"><i class="fas fa-search-plus"></i> عرض المرفق</span>
                        </div>
                    </a>
                </div>
            <?php else: ?>
                <div class="mt-4 pt-4 border-t border-dashed border-gray-300 text-center text-xs text-red-500 font-bold">
                    <i class="fas fa-exclamation-triangle"></i> لم يتم إرفاق إيصال تحويل
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <h3 class="font-bold text-pri-900 p-6 border-b border-gray-100 bg-white"><i class="fas fa-shopping-basket text-gld-500 ml-1"></i> المنتجات المطلوبة</h3>
        <div class="table-responsive !border-0 !shadow-none !rounded-none">
            <table class="erp-table !border-0">
                <thead class="bg-gray-50">
                    <tr>
                        <th>النوع</th>
                        <th>المنتج</th>
                        <th class="text-center">السعر</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): 
                        $typeLabel = ['product'=>'منتج ملموس', 'audio'=>'مقطع صوتي', 'video'=>'فيديو', 'package'=>'باقة', 'book'=>'كتاب رقمي'][$item['item_type']] ?? 'غير محدد';
                        $typeColor = ['product'=>'text-pri-600 bg-pri-50', 'audio'=>'text-green-600 bg-green-50', 'video'=>'text-purple-600 bg-purple-50', 'package'=>'text-gld-600 bg-gld-50', 'book'=>'text-blue-600 bg-blue-50'][$item['item_type']] ?? 'text-gray-600 bg-gray-50';
                    ?>
                        <tr>
                            <td><span class="px-2 py-1 rounded text-[10px] font-bold <?= $typeColor ?>"><?= $typeLabel ?></span></td>
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