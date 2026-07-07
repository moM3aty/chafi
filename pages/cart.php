<?php
// مسار الملف: pages/cart.php
// المكان: داخل مجلد pages

if (!isset($_SESSION['user_id'])) {
    echo "<div class='text-center py-20'><h1 class='text-2xl font-bold mb-4'>يجب تسجيل الدخول لرؤية السلة</h1><button onclick=\"openMdl('authMdl')\" class='btn btn-primary'>تسجيل الدخول</button></div>";
    return;
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$productsInCart = [];
$subTotal = 0;
$shippingCost = 0;
$couponDiscount = 0;
$couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : '';

$finalItems = [];

// جلب وتجهيز المنتجات
if (!empty($cartItems)) {
    $ids = implode(',', array_keys($cartItems));
    $stmt = $pdo->query("SELECT id, name, price, old_price, image_url, stock_quantity FROM products WHERE id IN ($ids)");
    $productsInCart = $stmt->fetchAll();

    foreach ($productsInCart as $item) {
        $qty = $cartItems[$item['id']];
        // تحقق من المخزون وتصحيحه إن لزم
        $newQty = min($qty, $item['stock_quantity']);
        if ($newQty <= 0) {
            unset($_SESSION['cart'][$item['id']]);
            continue;
        }
        
        $lineTotal = $item['price'] * $newQty;
        $subTotal += $lineTotal;
        $finalItems[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'image' => $item['image_url'],
            'price' => $item['price'],
            'qty' => $newQty,
            'old_price' => $item['old_price'],
            'lineTotal' => $lineTotal,
            'stock' => $item['stock_quantity']
        ];
        $_SESSION['cart'][$item['id']] = $newQty;
    }

    $shippingCost = $subTotal >= 200 ? 0 : 25;

    // حساب الكوبون إن وجد
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
        } else {
            unset($_SESSION['coupon_code']);
            $couponCode = '';
        }
    }
}

$total = max(0, $subTotal + $shippingCost - $couponDiscount);
?>

<div class="max-w-7xl mx-auto px-4 py-10 mb-14">
    <!-- رأس الصفحة -->
    <div class="flex items-center gap-3 mb-8 afiu">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gld-400 to-gld-600 flex items-center justify-center text-pri-900 text-xl shadow-lg group-hover:scale-105 transition-transform">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <h1 class="text-3xl font-black text-pri-900 font-amiri">سلة التسوق</h1>
        <p class="text-brk-400 text-sm mr-auto"><span id="cartHeaderCount"><?= count($finalItems) ?></span> منتج في السلة</p>
    </div>

    <?php if (empty($finalItems)): ?>
    <div class="bg-white rounded-3xl border-2 border-gray-100 p-16 text-center shadow-sm afiu" style="animation-delay:.1s">
        <div class="w-24 h-24 bg-gray-50 text-brk-300 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-cart-arrow-down text-4xl opacity-30"></i>
        </div>
        <h3 class="text-2xl font-bold text-pri-900 mb-2">سلة التسوق فارغة</h3>
        <p class="text-brk-400 mb-6">لم تقم بإضافة أي منتجات إلى سلتك بعد.</p>
        <a href="index.php?page=products" class="btn btn-primary btn-lg mt-6"><i class="fas fa-store"></i> تصفح المتجر</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 afiu" style="animation-delay:.1s">
        
        <!-- ═══ قائمة المنتجات ═══ -->
        <div class="lg:col-span-2">
            <div class="erp-card overflow-hidden">
                <div class="p-4 space-y-3 max-h-[600px] overflow-y-auto no-sb" id="cartItemsContainer">
                    <?php foreach ($finalItems as $fi): 
                        $hasDisc = $fi['old_price'] > 0;
                    ?>
                        <div class="p-4 flex gap-4 border border-gray-100 rounded-2xl hover:border-pri-200 transition-colors group" id="cart-item-<?= $fi['id'] ?>">
                            <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-gray-50 border border-gray-100">
                                <img src="<?= htmlspecialchars($fi['image']) ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-1">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h4 class="font-bold text-pri-900 text-sm truncate max-w-[280px]"><a href="index.php?page=product_details&id=<?= $fi['id'] ?>" class="hover:text-pri-600 transition"><?= htmlspecialchars($fi['name']) ?></a></h4>
                                        <div class="text-pri-700 font-black text-sm mt-1"><?= number_format($fi['price'], 2) ?> ر.س</div>
                                    </div>
                                    <div class="text-left shrink-0">
                                        <div class="text-xs text-brk-400 mb-0.5">الإجمالي</div>
                                        <div class="font-black text-pri-900" id="line-total-<?= $fi['id'] ?>"><?= number_format($fi['lineTotal'], 2) ?> ر.س</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="qty bg-gray-50 border border-gray-200 rounded-xl h-10 flex">
                                        <button type="button" class="qty-b" onclick="updateCartQty(<?= $fi['id'] ?>, -1, <?= $fi['stock'] ?>)">−</button>
                                        <input type="number" class="qty-v" id="qty-<?= $fi['id'] ?>" value="<?= $fi['qty'] ?>" readonly>
                                        <button type="button" class="qty-b" onclick="updateCartQty(<?= $fi['id'] ?>, 1, <?= $fi['stock'] ?>)">+</button>
                                    </div>
                                    <button onclick="removeCartItem(<?= $fi['id'] ?>)" class="text-red-500 hover:text-red-700 transition p-2 px-3 rounded-lg hover:bg-red-50 text-xs font-bold mr-auto">
                                        <i class="fas fa-trash-alt"></i> إزالة
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ═══ ملخص الطلب ═══ -->
        <div>
            <div class="erp-card p-6 sticky top-24">
                <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span class="text-base font-bold text-pri-900">ملخص الطلب</span>
                </h3>
                
                <!-- نظام الكوبونات -->
                <div class="mb-5 border-b border-gray-100 pb-5">
                    <label class="text-xs font-bold text-pri-900 mb-2 block">لديك كود خصم؟</label>
                    <div class="flex gap-2">
                        <input type="text" id="couponInput" class="form-control !py-2 !text-sm uppercase text-center tracking-widest" placeholder="أدخل الكود" value="<?= htmlspecialchars($couponCode) ?>">
                        <button type="button" onclick="applyCoupon()" id="btnApplyCoupon" class="btn btn-outline !py-2 !px-4"><i class="fas fa-check"></i></button>
                    </div>
                    <div id="couponMsg" class="text-[10px] mt-2 font-bold hidden"></div>
                </div>

                <!-- المجاميع -->
                <div class="space-y-3 text-sm text-brk-500 mb-5">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>المجموع الفرعي</span>
                        <span class="font-bold text-pri-700" id="summarySubTotal"><?= number_format($subTotal, 2) ?> ر.س</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>الشحن</span>
                        <span class="font-bold text-green-600" id="summaryShipping"><?= $shippingCost == 0 ? 'مجاني' : number_format($shippingCost, 2) . ' ر.س' ?></span>
                    </div>
                    
                    <div class="flex justify-between py-2 border-b border-gray-50 bg-gld-50 -mx-5 px-5 py-2.5 rounded-xl border border-gld-200 transition-all <?= $couponDiscount > 0 ? '' : 'hidden' ?>" id="discountRow">
                        <div class="flex items-center gap-2 text-gld-800 font-bold">
                            <i class="fas fa-tag text-xs"></i> كود الخصم 
                            <button type="button" onclick="removeCoupon()" class="text-red-500 hover:text-red-700 text-[10px] mr-2"><i class="fas fa-times"></i> إزالة</button>
                        </div>
                        <span class="font-black text-pri-700" id="summaryDiscount">- <?= number_format($couponDiscount, 2) ?> ر.س</span>
                    </div>

                    <div class="flex justify-between py-3 border-b-2 border-gld-300 border-dashed pt-3">
                        <span class="font-bold text-pri-900 text-base">الإجمالي المستحق</span>
                        <span class="font-black text-pri-700 text-xl"><span id="summaryTotal"><?= number_format($total, 2) ?></span> <span class="text-sm font-bold">ر.س</span></span>
                    </div>
                </div>

                <a href="index.php?page=checkout" class="btn btn-gold btn-block btn-lg shadow-lg w-full !py-4 h-14 text-base">
                    <i class="fas fa-credit-card"></i> متابعة للدفع
                </a>
            </div>
        </div>

    </div>
    <?php endif; ?>
</div>

<script>
// 1. تحديث الكمية عبر AJAX
function updateCartQty(id, change, maxStock) {
    const input = document.getElementById('qty-' + id);
    let newQty = parseInt(input.value) + change;
    
    if (newQty < 1) newQty = 1;
    if (newQty > maxStock) newQty = maxStock;
    if (newQty == parseInt(input.value)) return; // لا تغيير
    
    input.value = newQty;
    
    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('product_id', id);
    fd.append('quantity', newQty);

    sendCartAjax(fd);
}

// 2. إزالة منتج بالكامل
function removeCartItem(id) {
    if(!confirm('هل أنت متأكد من إزالة هذا المنتج من السلة؟')) return;
    
    const fd = new FormData();
    fd.append('action', 'remove');
    fd.append('product_id', id);

    sendCartAjax(fd, () => {
        const itemEl = document.getElementById('cart-item-' + id);
        if(itemEl) itemEl.remove();
        // التحقق إذا فرغت السلة
        const remaining = document.querySelectorAll('[id^="cart-item-"]').length;
        if(remaining === 0) window.location.reload();
    });
}

// 3. تطبيق الكوبون
function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim();
    if(!code) return;
    
    const btn = document.getElementById('btnApplyCoupon');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    const fd = new FormData();
    fd.append('action', 'apply_coupon');
    fd.append('coupon_code', code);

    sendCartAjax(fd, (data) => {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        const msgEl = document.getElementById('couponMsg');
        msgEl.classList.remove('hidden', 'text-red-500', 'text-green-600');
        if(data.success) {
            msgEl.classList.add('text-green-600');
            msgEl.innerText = data.message;
        } else {
            msgEl.classList.add('text-red-500');
            msgEl.innerText = data.message;
        }
    });
}

// 4. إزالة الكوبون
function removeCoupon() {
    const fd = new FormData();
    fd.append('action', 'remove_coupon');
    document.getElementById('couponInput').value = '';
    document.getElementById('couponMsg').classList.add('hidden');
    sendCartAjax(fd);
}

// الدالة الأساسية للإرسال وتحديث الواجهة
function sendCartAjax(formData, callback = null) {
    fetch('ajax/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.totals) {
            // تحديث الإجماليات في الواجهة
            document.getElementById('cCount').innerText = data.totals.count;
            const headerCount = document.getElementById('cartHeaderCount');
            if(headerCount) headerCount.innerText = data.totals.count;
            
            document.getElementById('summarySubTotal').innerText = data.totals.subTotal + ' ر.س';
            document.getElementById('summaryShipping').innerText = data.totals.shipping;
            document.getElementById('summaryTotal').innerText = data.totals.total;
            
            // تحديث إجمالي الصنف الواحد إذا كان تحديث كمية
            if (data.item_id && document.getElementById('line-total-' + data.item_id)) {
                document.getElementById('line-total-' + data.item_id).innerText = data.item_line_total + ' ر.س';
            }

            // عرض أو إخفاء سطر الخصم
            const discountRow = document.getElementById('discountRow');
            if (parseFloat(data.totals.discount.replace(/,/g, '')) > 0) {
                discountRow.classList.remove('hidden');
                document.getElementById('summaryDiscount').innerText = '- ' + data.totals.discount + ' ر.س';
            } else {
                discountRow.classList.add('hidden');
            }
        }
        if(callback) callback(data);
    })
    .catch(err => console.error('Error:', err));
}
</script>