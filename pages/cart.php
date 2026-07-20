<?php
// مسار الملف: pages/cart.php

if (!isset($_SESSION['user_id'])) {
    echo "<div class='text-center py-20'><h1 class='text-2xl font-bold mb-4'>يجب تسجيل الدخول لرؤية السلة</h1><button onclick=\"openMdl('authMdl')\" class='btn btn-primary'>تسجيل الدخول</button></div>";
    return;
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subTotal = 0;
$hasPhysicalItems = false;
$couponDiscount = 0;
$couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : '';

$finalItems = [];

// جلب وتجهيز كافة العناصر في السلة بناءً على نوعها
foreach ($cartItems as $key => $item) {
    if (!is_array($item)) continue;
    
    $table = 'products';
    $nameCol = 'name';
    $priceCol = 'price';
    $imgCol = 'image_url';
    
    if ($item['type'] === 'audio') { $table = 'audios'; $nameCol = 'title'; $imgCol = 'thumbnail_url'; }
    elseif ($item['type'] === 'video') { $table = 'videos'; $nameCol = 'title'; $imgCol = 'thumbnail_url'; }
    elseif ($item['type'] === 'book') { $table = 'books'; $nameCol = 'title'; $imgCol = 'thumbnail_url'; }
    elseif ($item['type'] === 'package') { $table = 'packages'; $priceCol = 'package_price'; }
    
    $query = "SELECT id, $nameCol as name, $priceCol as price, $imgCol as image";
    if ($item['type'] === 'product') {
        $query .= ", stock_quantity, old_price, is_digital";
    }
    $query .= " FROM $table WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$item['id']]);
    $dbItem = $stmt->fetch();
    
    if ($dbItem) {
        $qty = $item['qty'];
        $stock = 99999; // افتراضي للرقمي
        
        if ($item['type'] === 'product' && $dbItem['is_digital'] == 0) {
            $stock = $dbItem['stock_quantity'];
            $hasPhysicalItems = true;
        }
        
        $newQty = min($qty, $stock);
        if ($newQty <= 0) {
            unset($_SESSION['cart'][$key]);
            continue;
        }
        
        $lineTotal = $dbItem['price'] * $newQty;
        $subTotal += $lineTotal;
        $_SESSION['cart'][$key]['qty'] = $newQty;
        
        $finalItems[] = [
            'cart_key' => $key,
            'type' => $item['type'],
            'id' => $dbItem['id'],
            'name' => $dbItem['name'],
            'image' => $dbItem['image'] ?? 'https://picsum.photos/100',
            'price' => $dbItem['price'],
            'qty' => $newQty,
            'lineTotal' => $lineTotal,
            'stock' => $stock
        ];
    } else {
        unset($_SESSION['cart'][$key]);
    }
}

$shippingCost = ($hasPhysicalItems && $subTotal > 0 && $subTotal < 200) ? 25 : 0;

if (!empty($couponCode) && $subTotal > 0) {
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

$total = max(0, $subTotal + $shippingCost - $couponDiscount);
$totalQty = array_sum(array_column($_SESSION['cart'], 'qty'));
?>

<div class="max-w-7xl mx-auto px-4 py-10 mb-14">
    <div class="flex items-center gap-3 mb-8 afiu">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gld-400 to-gld-600 flex items-center justify-center text-pri-900 text-xl shadow-lg">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <h1 class="text-3xl font-black text-pri-900 font-amiri">سلة التسوق</h1>
        <p class="text-brk-400 text-sm mr-auto"><span id="cartHeaderCount"><?= $totalQty ?></span> عنصر في السلة</p>
    </div>

    <?php if (empty($finalItems)): ?>
    <div class="bg-white rounded-3xl border-2 border-gray-100 p-16 text-center shadow-sm afiu">
        <div class="w-24 h-24 bg-gray-50 text-brk-300 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-cart-arrow-down text-4xl opacity-30"></i>
        </div>
        <h3 class="text-2xl font-bold text-pri-900 mb-2">سلة التسوق فارغة</h3>
        <a href="index.php?page=home" class="btn btn-primary btn-lg mt-6"><i class="fas fa-store"></i> تصفح المتجر</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 afiu">
        
        <div class="lg:col-span-2">
            <div class="erp-card overflow-hidden">
                <div class="p-4 space-y-3 max-h-[600px] overflow-y-auto no-sb" id="cartItemsContainer">
                    <?php foreach ($finalItems as $fi): 
                        $typeLabel = ['product'=>'منتج', 'audio'=>'صوتي', 'video'=>'فيديو', 'package'=>'باقة', 'book'=>'كتاب'][$fi['type']];
                        $typeIcon = ['product'=>'fa-box', 'audio'=>'fa-headphones', 'video'=>'fa-video', 'package'=>'fa-gift', 'book'=>'fa-book'][$fi['type']];
                    ?>
                        <div class="p-4 flex gap-4 border border-gray-100 rounded-2xl hover:border-pri-200 transition-colors group" id="cart-item-<?= $fi['cart_key'] ?>">
                            <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-gray-50 border border-gray-100 relative">
                                <span class="absolute top-1 right-1 bg-white/90 text-pri-600 text-[8px] font-bold px-1.5 py-0.5 rounded shadow-sm backdrop-blur-sm"><i class="fas <?= $typeIcon ?>"></i> <?= $typeLabel ?></span>
                                <img src="<?= htmlspecialchars($fi['image']) ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-1">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h4 class="font-bold text-pri-900 text-sm truncate max-w-[280px]"><?= htmlspecialchars($fi['name']) ?></h4>
                                        <div class="text-pri-700 font-black text-sm mt-1"><?= number_format($fi['price'], 2) ?> ر.س</div>
                                    </div>
                                    <div class="text-left shrink-0">
                                        <div class="text-xs text-brk-400 mb-0.5">الإجمالي</div>
                                        <div class="font-black text-pri-900" id="line-total-<?= $fi['cart_key'] ?>"><?= number_format($fi['lineTotal'], 2) ?> ر.س</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <?php if ($fi['type'] === 'product'): ?>
                                    <div class="qty bg-gray-50 border border-gray-200 rounded-xl h-10 flex">
                                        <button type="button" class="qty-b" onclick="updateCartQty('<?= $fi['type'] ?>', <?= $fi['id'] ?>, -1, <?= $fi['stock'] ?>)">−</button>
                                        <input type="number" class="qty-v" id="qty-<?= $fi['cart_key'] ?>" value="<?= $fi['qty'] ?>" readonly>
                                        <button type="button" class="qty-b" onclick="updateCartQty('<?= $fi['type'] ?>', <?= $fi['id'] ?>, 1, <?= $fi['stock'] ?>)">+</button>
                                    </div>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-pri-600 bg-pri-50 px-3 py-1.5 rounded-lg"><i class="fas fa-check-circle"></i> منتج رقمي (مرة واحدة)</span>
                                    <?php endif; ?>
                                    <button onclick="removeCartItem('<?= $fi['type'] ?>', <?= $fi['id'] ?>)" class="text-red-500 hover:text-red-700 transition p-2 px-3 rounded-lg hover:bg-red-50 text-xs font-bold mr-auto">
                                        <i class="fas fa-trash-alt"></i> إزالة
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="erp-card p-6 sticky top-24">
                <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3 text-base">ملخص الطلب</h3>
                
                <div class="mb-5 border-b border-gray-100 pb-5">
                    <label class="text-xs font-bold text-pri-900 mb-2 block">لديك كود خصم؟</label>
                    <div class="flex gap-2">
                        <input type="text" id="couponInput" class="form-control !py-2 !text-sm uppercase text-center tracking-widest" placeholder="أدخل الكود" value="<?= htmlspecialchars($couponCode) ?>">
                        <button type="button" onclick="applyCoupon()" id="btnApplyCoupon" class="btn btn-outline !py-2 !px-4"><i class="fas fa-check"></i></button>
                    </div>
                    <div id="couponMsg" class="text-[10px] mt-2 font-bold hidden"></div>
                </div>

                <div class="space-y-3 text-sm text-brk-500 mb-5">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>المجموع الفرعي</span>
                        <span class="font-bold text-pri-700" id="summarySubTotal"><?= number_format($subTotal, 2) ?> ر.س</span>
                    </div>
                    <?php if ($hasPhysicalItems): ?>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>شحن المنتجات</span>
                        <span class="font-bold text-green-600" id="summaryShipping"><?= $shippingCost == 0 ? 'مجاني' : number_format($shippingCost, 2) . ' ر.س' ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between py-2 border-b border-gray-50 bg-gld-50 -mx-5 px-5 py-2.5 rounded-xl border border-gld-200 transition-all <?= $couponDiscount > 0 ? '' : 'hidden' ?>" id="discountRow">
                        <div class="flex items-center gap-2 text-gld-800 font-bold">
                            <i class="fas fa-tag text-xs"></i> الخصم 
                            <button type="button" onclick="removeCoupon()" class="text-red-500 hover:text-red-700 text-[10px] mr-2"><i class="fas fa-times"></i> إزالة</button>
                        </div>
                        <span class="font-black text-pri-700" id="summaryDiscount">- <?= number_format($couponDiscount, 2) ?> ر.س</span>
                    </div>

                    <div class="flex justify-between py-3 border-b-2 border-gld-300 border-dashed pt-3">
                        <span class="font-bold text-pri-900 text-base">الإجمالي المستحق</span>
                        <span class="font-black text-pri-700 text-xl" id="summaryTotal"><?= number_format($total, 2) ?> ر.س</span>
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
function updateCartQty(type, id, change, maxStock) {
    const input = document.getElementById('qty-' + type + '_' + id);
    if (!input) return;
    let newQty = parseInt(input.value) + change;
    
    if (newQty < 1) newQty = 1;
    if (newQty > maxStock) newQty = maxStock;
    if (newQty == parseInt(input.value)) return;
    
    input.value = newQty;
    
    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('item_type', type);
    fd.append('item_id', id);
    fd.append('quantity', newQty);

    sendCartAjax(fd);
}

function removeCartItem(type, id) {
    if(!confirm('إزالة هذا العنصر من السلة؟')) return;
    
    const fd = new FormData();
    fd.append('action', 'remove');
    fd.append('item_type', type);
    fd.append('item_id', id);

    sendCartAjax(fd, () => {
        const itemEl = document.getElementById('cart-item-' + type + '_' + id);
        if(itemEl) itemEl.remove();
        if(document.querySelectorAll('[id^="cart-item-"]').length === 0) window.location.reload();
    });
}

function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim();
    if(!code) return;
    const fd = new FormData();
    fd.append('action', 'apply_coupon');
    fd.append('coupon_code', code);
    sendCartAjax(fd, (data) => {
        const msgEl = document.getElementById('couponMsg');
        msgEl.classList.remove('hidden', 'text-red-500', 'text-green-600');
        msgEl.classList.add(data.success ? 'text-green-600' : 'text-red-500');
        msgEl.innerText = data.message;
    });
}

function removeCoupon() {
    const fd = new FormData();
    fd.append('action', 'remove_coupon');
    document.getElementById('couponInput').value = '';
    document.getElementById('couponMsg').classList.add('hidden');
    sendCartAjax(fd);
}

function sendCartAjax(formData, callback = null) {
    fetch('ajax/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.totals) {
            if(document.getElementById('cCount')) document.getElementById('cCount').innerText = data.totals.count;
            if(document.getElementById('cartHeaderCount')) document.getElementById('cartHeaderCount').innerText = data.totals.count;
            
            document.getElementById('summarySubTotal').innerText = data.totals.subTotal + ' ر.س';
            if(document.getElementById('summaryShipping')) document.getElementById('summaryShipping').innerText = data.totals.shipping;
            document.getElementById('summaryTotal').innerText = data.totals.total + ' ر.س';
            
            if (data.item_key && document.getElementById('line-total-' + data.item_key)) {
                document.getElementById('line-total-' + data.item_key).innerText = data.item_line_total + ' ر.س';
            }

            const discountRow = document.getElementById('discountRow');
            if (parseFloat(data.totals.discount.replace(/,/g, '')) > 0) {
                discountRow.classList.remove('hidden');
                document.getElementById('summaryDiscount').innerText = '- ' + data.totals.discount + ' ر.س';
            } else {
                discountRow.classList.add('hidden');
            }
        }
        if(callback) callback(data);
    });
}
</script>