<?php
// مسار الملف: pages/checkout.php
// المكان: داخل مجلد pages

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center afiu'>
            <div class='w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-lock'></i></div>
            <h2 class='text-2xl font-black text-pri-900 mb-4'>يجب تسجيل الدخول لإتمام الطلب</h2>
            <p class='text-brk-500 mb-8'>قم بتسجيل الدخول أو إنشاء حساب جديد لمتابعة عملية الدفع.</p>
            <button onclick=\"openMdl('authMdl')\" class='btn btn-primary btn-lg'><i class='fas fa-sign-in-alt'></i> تسجيل الدخول</button>
          </div>";
    return;
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cartItems)) {
    echo "<script>window.location.href='index.php?page=cart';</script>";
    exit;
}

// 1. حساب السلة
$subTotal = 0;
$ids = implode(',', array_keys($cartItems));
$stmt = $pdo->query("SELECT id, name, price, image_url, stock_quantity FROM products WHERE id IN ($ids)");
$productsInCart = $stmt->fetchAll();

foreach ($productsInCart as $item) {
    $qty = min($cartItems[$item['id']], $item['stock_quantity']); // تأمين المخزون
    $subTotal += $item['price'] * $qty;
}

$shippingCost = $subTotal >= 200 ? 0 : 25;
$couponDiscount = 0;
$couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : '';

// 2. التحقق من الكوبون مرة أخرى لحماية الطلب
if (!empty($couponCode)) {
    $couponStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $couponStmt->execute([$couponCode]);
    $coupon = $couponStmt->fetch();

    if ($coupon && $subTotal >= $coupon['min_order_amount']) {
        if ($coupon['discount_type'] === 'percentage') {
            $couponDiscount = $subTotal * ($coupon['discount_value'] / 100);
            if ($coupon['max_discount'] && $couponDiscount > $coupon['max_discount']) {
                $couponDiscount = $coupon['max_discount'];
            }
        } else {
            $couponDiscount = $coupon['discount_value'];
        }
    }
}

$total = max(0, $subTotal + $shippingCost - $couponDiscount);

// 3. جلب العنوان الافتراضي للمستخدم
$stmtUserAddr = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC LIMIT 1");
$stmtUserAddr->execute([$_SESSION['user_id']]);
$address = $stmtUserAddr->fetch();

// جلب المستخدم الأساسي للاحتياط
$stmtUser = $pdo->prepare("SELECT full_name, phone FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();
?>

<div class="max-w-7xl mx-auto px-4 py-10 mb-14">
    <div class="flex items-center gap-3 mb-8 afiu">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-gld-400 text-xl shadow-lg">
            <i class="fas fa-credit-card"></i>
        </div>
        <h1 class="text-3xl font-black text-pri-900 font-amiri">الدفع وإتمام الطلب</h1>
    </div>

    <form action="ajax/checkout_action.php" method="post" class="erp-grid lg:grid-cols-3 afiu" style="animation-delay: 0.1s" onsubmit="document.getElementById('btnSubmitOrder').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> جاري المعالجة...'; document.getElementById('btnSubmitOrder').disabled = true;">
        
        <div class="lg:col-span-2 space-y-8">
            <!-- 1. بيانات الشحن -->
            <div class="erp-card p-6 sm:p-8">
                <h3 class="text-lg font-black text-pri-900 mb-6 border-b border-gray-100 pb-3 flex justify-between items-center">
                    <span><i class="fas fa-map-marked-alt text-gld-500 ml-2"></i> بيانات الاستلام</span>
                    <a href="index.php?page=dashboard" class="text-xs text-pri-600 font-bold hover:underline"><i class="fas fa-edit"></i> تعديل العناوين</a>
                </h3>
                
                <div class="erp-grid md:grid-cols-2 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($address['full_name'] ?? $user['full_name']) ?>" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">رقم الجوال <span class="req">*</span></label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($address['phone'] ?? $user['phone']) ?>" required dir="ltr" placeholder="05XXXXXXXX">
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">المدينة <span class="req">*</span></label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($address['city'] ?? '') ?>" required placeholder="الرياض، جدة...">
                    </div>
                </div>
                
                <div class="form-group !mb-0">
                    <label class="form-label">العنوان التفصيلي <span class="req">*</span></label>
                    <textarea name="address" class="form-textarea" rows="3" required placeholder="اسم الحي، الشارع، رقم المبنى..."><?= htmlspecialchars($address['street'] ?? '') ?><?= !empty($address['landmark']) ? ' - بجوار: ' . htmlspecialchars($address['landmark']) : '' ?></textarea>
                </div>
            </div>

            <!-- 2. طريقة الدفع -->
            <div class="erp-card p-6 sm:p-8">
                <h3 class="text-lg font-black text-pri-900 mb-6 border-b border-gray-100 pb-3"><i class="fas fa-wallet text-gld-500 ml-2"></i> طريقة الدفع</h3>
                
                <div class="space-y-4">
                    <label class="flex items-center gap-4 p-4 rounded-xl border-2 border-pri-200 bg-pri-50 cursor-pointer transition">
                        <input type="radio" name="payment_method" value="CreditCard" class="w-5 h-5 accent-pri-600" checked>
                        <div class="flex-1">
                            <h4 class="font-bold text-pri-900">البطاقة الائتمانية / مدى</h4>
                            <p class="text-xs text-brk-400 mt-1">دفع إلكتروني آمن ومشفّر 100%</p>
                        </div>
                        <div class="flex gap-1 text-2xl text-pri-600"><i class="fab fa-cc-visa"></i> <i class="fab fa-cc-mastercard"></i></div>
                    </label>

                    <label class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-gray-300 cursor-pointer transition bg-white">
                        <input type="radio" name="payment_method" value="CashOnDelivery" class="w-5 h-5 accent-pri-600">
                        <div class="flex-1">
                            <h4 class="font-bold text-pri-900">الدفع عند الاستلام</h4>
                            <p class="text-xs text-brk-400 mt-1">يتم الدفع لمندوب التوصيل يداً بيد</p>
                        </div>
                        <i class="fas fa-money-bill-wave text-2xl text-brk-300"></i>
                    </label>
                </div>
            </div>
        </div>

        <!-- ملخص الطلب النهائي -->
        <div>
            <div class="erp-card p-6 sticky top-24 bg-gray-50/50">
                <h3 class="text-lg font-black text-pri-900 mb-5 border-b border-gray-200 pb-3">الفاتورة النهائية</h3>
                
                <div class="space-y-4 mb-6 max-h-48 overflow-y-auto pr-2 no-sb">
                    <?php foreach ($productsInCart as $item): ?>
                        <div class="flex items-center gap-3">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-10 h-10 rounded object-cover border border-gray-100">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-pri-900 truncate"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="text-[10px] text-brk-400">× <?= $cartItems[$item['id']] ?></div>
                            </div>
                            <div class="font-bold text-pri-700 text-xs shrink-0"><?= number_format($item['price'] * $cartItems[$item['id']], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-3 text-sm text-brk-600 mb-5 border-t border-gray-200 pt-4">
                    <div class="flex justify-between"><span>المجموع الفرعي</span><span class="font-bold"><?= number_format($subTotal, 2) ?> ر.س</span></div>
                    
                    <?php if ($couponDiscount > 0): ?>
                        <div class="flex justify-between text-gld-700 font-bold bg-gld-50 px-2 py-1 -mx-2 rounded">
                            <span>خصم (<?= htmlspecialchars($couponCode) ?>)</span>
                            <span>- <?= number_format($couponDiscount, 2) ?> ر.س</span>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-between"><span>الشحن</span><span class="<?= $shippingCost == 0 ? 'text-green-600 font-bold' : '' ?>"><?= $shippingCost == 0 ? 'مجاني' : number_format($shippingCost, 2) . ' ر.س' ?></span></div>
                </div>

                <div class="border-t border-gray-200 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-black text-pri-900">الإجمالي المستحق</span>
                        <span class="text-2xl font-black text-pri-700"><?= number_format($total, 2) ?> ر.س</span>
                    </div>
                </div>

                <button type="submit" id="btnSubmitOrder" class="btn btn-primary btn-block btn-lg shadow-xl !py-4 text-base">
                    <i class="fas fa-lock"></i> تأكيد الطلب
                </button>
            </div>
        </div>
    </form>
</div>