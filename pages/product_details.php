<?php
// مسار الملف: pages/product_details.php
// تم التطوير: إضافة نظام المفضلة AJAX التفاعلي ونظام التقييمات والمراجعات

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='text-center py-20'><h1 class='text-3xl font-bold'>المنتج غير موجود</h1></div>";
    return;
}

// 1. التحقق مما إذا كان المنتج في مفضلة المستخدم الحالي
$inWishlist = false;
if (isset($_SESSION['user_id'])) {
    $wStmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND wishlistable_type = 'product' AND wishlistable_id = ?");
    $wStmt->execute([$_SESSION['user_id'], $id]);
    if ($wStmt->fetch()) $inWishlist = true;
}

// 2. جلب التقييمات المعتمدة لهذا المنتج
$revStmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.avatar_url 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.reviewable_type = 'product' AND r.reviewable_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

// حساب متوسط التقييم
$avgRating = 0;
$totalReviews = count($reviews);
if ($totalReviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($sum / $totalReviews, 1);
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-brk-400 mb-8 afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition"><i class="fas fa-home"></i> الرئيسية</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <a href="index.php?page=products&category_id=<?= $product['category_id'] ?>" class="hover:text-pri-600 transition"><?= htmlspecialchars($product['category_name']) ?></a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <span class="text-pri-900 font-bold"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="erp-card p-6 sm:p-8 flex flex-col lg:flex-row gap-10 afiu mb-10" style="animation-delay: 0.1s">
        <!-- الصورة -->
        <div class="w-full lg:w-5/12 shrink-0">
            <div class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 aspect-square relative group">
                <?php if ($product['old_price'] > $product['price']): 
                    $discount = round((1 - $product['price'] / $product['old_price']) * 100);
                ?>
                    <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold z-10 shadow-md">خصم <?= $discount ?>%</span>
                <?php endif; ?>
                
                <!-- زر المفضلة فوق الصورة -->
                <button onclick="toggleWishlist(<?= $product['id'] ?>, 'product', this)" class="absolute top-4 left-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-lg shadow-md z-10 transition-transform hover:scale-110 <?= $inWishlist ? 'text-red-500' : 'text-gray-400' ?>">
                    <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                </button>

                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
        </div>

        <!-- التفاصيل -->
        <div class="w-full lg:w-7/12 flex flex-col">
            <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-3 leading-tight"><?= htmlspecialchars($product['name']) ?></h1>
            
            <!-- نجوم التقييم -->
            <div class="flex items-center gap-3 mb-4">
                <div class="flex text-gld-500 text-sm">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="<?= $i <= $avgRating ? 'fas' : ($i - 0.5 <= $avgRating ? 'fas fa-star-half-alt' : 'far') ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-sm font-bold text-pri-900"><?= $avgRating ?></span>
                <span class="text-xs text-brk-400">(<?= $totalReviews ?> تقييم)</span>
                <span class="text-gray-300">|</span>
                <span class="text-xs text-brk-500"><i class="fas fa-shopping-basket text-pri-400 ml-1"></i> تم شراءه <?= $product['sales_count'] ?> مرة</span>
            </div>

            <div class="flex items-end gap-3 mb-6 bg-pri-50 p-4 rounded-xl border border-pri-100 inline-flex w-fit">
                <span class="text-4xl font-black text-pri-700"><?= number_format($product['price'], 2) ?> <span class="text-lg font-bold">ر.س</span></span>
                <?php if ($product['old_price'] > $product['price']): ?>
                    <span class="text-lg text-brk-300 line-through mb-1"><?= number_format($product['old_price'], 2) ?> ر.س</span>
                <?php endif; ?>
            </div>

            <div class="prose prose-sm text-brk-600 leading-loose mb-8">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>

            <div class="mt-auto">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full <?= $product['stock_quantity'] > 0 ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    <span class="text-sm font-bold <?= $product['stock_quantity'] > 0 ? 'text-green-700' : 'text-red-600' ?>">
                        <?= $product['stock_quantity'] > 0 ? 'متوفر في المخزون ('.$product['stock_quantity'].')' : 'نفدت الكمية' ?>
                    </span>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="qty bg-gray-50 border-2 border-border h-14 rounded-xl">
                        <button type="button" class="qty-b w-12 h-full text-xl" onclick="decQty()">-</button>
                        <input type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock_quantity'] ?>" class="qty-v w-12 bg-transparent text-lg border-0 text-center" readonly>
                        <button type="button" class="qty-b w-12 h-full text-xl" onclick="incQty()">+</button>
                    </div>

                    <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary flex-1 h-14 text-lg shadow-lg" <?= $product['stock_quantity'] <= 0 ? 'disabled style="opacity:0.5;cursor:not-allowed"' : '' ?>>
                        <i class="fas fa-cart-plus"></i> <?= $product['stock_quantity'] > 0 ? 'أضف إلى السلة' : 'غير متوفر حالياً' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ قسم التقييمات والمراجعات ═══ -->
    <div class="erp-card p-6 sm:p-10 afiu" style="animation-delay: 0.2s">
        <h3 class="text-2xl font-black text-pri-900 font-amiri mb-6 border-b border-gray-100 pb-3"><i class="fas fa-comments text-gld-500 ml-2"></i>آراء العملاء</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- نموذج إضافة تقييم -->
            <div class="lg:col-span-1 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit">
                <h4 class="font-bold text-pri-900 mb-4 text-center">شاركنا رأيك بالمنتج</h4>
                
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="text-center py-6">
                        <i class="fas fa-lock text-3xl text-brk-200 mb-3 block"></i>
                        <p class="text-sm text-brk-500 mb-4">يجب تسجيل الدخول لتتمكن من كتابة تقييم.</p>
                        <button onclick="openMdl('authMdl')" class="btn btn-primary btn-sm btn-block"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</button>
                    </div>
                <?php else: ?>
                    <form id="reviewForm" onsubmit="submitReview(event)">
                        <input type="hidden" id="revItemId" value="<?= $product['id'] ?>">
                        <input type="hidden" id="revItemType" value="product">
                        <input type="hidden" id="revRating" value="0">

                        <div class="text-center mb-4">
                            <p class="text-xs font-bold text-brk-400 mb-2">تقييمك بالنجوم <span class="text-red-500">*</span></p>
                            <div class="flex justify-center flex-row-reverse gap-1 text-2xl text-gray-300 cursor-pointer" id="starSelector">
                                <!-- النجوم معكوسة لأننا نستخدم flex-row-reverse و CSS Hover -->
                                <i class="fas fa-star hover:text-gld-500 peer peer-hover:text-gld-500 transition" data-val="5" onclick="setRating(5)"></i>
                                <i class="fas fa-star hover:text-gld-500 peer peer-hover:text-gld-500 transition" data-val="4" onclick="setRating(4)"></i>
                                <i class="fas fa-star hover:text-gld-500 peer peer-hover:text-gld-500 transition" data-val="3" onclick="setRating(3)"></i>
                                <i class="fas fa-star hover:text-gld-500 peer peer-hover:text-gld-500 transition" data-val="2" onclick="setRating(2)"></i>
                                <i class="fas fa-star hover:text-gld-500 transition" data-val="1" onclick="setRating(1)"></i>
                            </div>
                        </div>

                        <div class="form-group !mb-4">
                            <textarea id="revText" class="form-textarea !min-h-[100px] !text-sm" placeholder="اكتب تجربتك مع المنتج هنا... (اختياري)"></textarea>
                        </div>
                        
                        <button type="submit" id="btnRevSubmit" class="btn btn-gold btn-block"><i class="fas fa-paper-plane"></i> إرسال التقييم</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- قائمة التقييمات -->
            <div class="lg:col-span-2">
                <?php if(empty($reviews)): ?>
                    <div class="text-center py-10">
                        <i class="far fa-comment-dots text-5xl text-brk-200 mb-3 block"></i>
                        <p class="text-brk-500 font-bold">لا توجد تقييمات لهذا المنتج حتى الآن.</p>
                        <p class="text-xs text-brk-400 mt-1">كن أول من يشاركنا رأيه!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 no-sb">
                        <?php foreach($reviews as $r): ?>
                            <div class="p-4 rounded-xl border border-gray-100 bg-white">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-pri-100 text-pri-600 flex items-center justify-center font-bold text-sm">
                                            <?= mb_substr($r['full_name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-pri-900 text-sm"><?= htmlspecialchars($r['full_name']) ?></div>
                                            <div class="text-[10px] text-brk-400" dir="ltr"><?= date('Y-m-d', strtotime($r['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-gld-500 text-xs">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?= $i <= $r['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if(!empty($r['review_text'])): ?>
                                    <p class="text-sm text-brk-600 mt-2 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <?= nl2br(htmlspecialchars($r['review_text'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// --- سلة التسوق ---
function incQty() { let i = document.getElementById('qtyInput'); if(i.value < <?= $product['stock_quantity'] ?>) i.value++; }
function decQty() { let i = document.getElementById('qtyInput'); if(i.value > 1) i.value--; }

function addToCart(productId) {
    const qty = document.getElementById('qtyInput').value;
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', qty);

    fetch('ajax/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast(data.message, 'ok');
            document.getElementById('cCount').innerText = data.total_items;
        }
    });
}

// --- المفضلة ---
async function toggleWishlist(itemId, itemType, btn) {
    const fd = new FormData();
    fd.append('item_id', itemId);
    fd.append('item_type', itemType);

    try {
        const res = await fetch('ajax/wishlist_action.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'ok');
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.remove('text-gray-400');
                btn.classList.add('text-red-500');
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                btn.classList.add('text-gray-400');
                btn.classList.remove('text-red-500');
                icon.classList.add('far');
                icon.classList.remove('fas');
            }
        } else {
            if (data.code === 'auth') {
                openMdl('authMdl');
            } else {
                showToast(data.message, 'err');
            }
        }
    } catch(err) {
        showToast('خطأ في الاتصال بالخادم', 'err');
    }
}

// --- نظام التقييم ---
function setRating(val) {
    document.getElementById('revRating').value = val;
    // تلوين النجوم
    const stars = document.getElementById('starSelector').querySelectorAll('i');
    stars.forEach(star => {
        if (parseInt(star.getAttribute('data-val')) <= val) {
            star.classList.add('text-gld-500');
            star.classList.remove('text-gray-300');
        } else {
            star.classList.remove('text-gld-500');
            star.classList.add('text-gray-300');
        }
    });
}

async function submitReview(e) {
    e.preventDefault();
    const rating = document.getElementById('revRating').value;
    if (rating == 0) {
        showToast('يرجى تحديد التقييم بالنجوم أولاً', 'err');
        return;
    }

    const btn = document.getElementById('btnRevSubmit');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';

    const fd = new FormData();
    fd.append('item_id', document.getElementById('revItemId').value);
    fd.append('item_type', document.getElementById('revItemType').value);
    fd.append('rating', rating);
    fd.append('review_text', document.getElementById('revText').value);

    try {
        const res = await fetch('ajax/review_action.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'ok');
            document.getElementById('reviewForm').innerHTML = '<div class="text-center text-green-600 font-bold py-6"><i class="fas fa-check-circle text-4xl mb-3"></i><p>' + data.message + '</p></div>';
        } else {
            showToast(data.message, 'err');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    } catch(err) {
        showToast('خطأ في الاتصال بالخادم', 'err');
        btn.disabled = false;
        btn.innerHTML = origText;
    }
}

// إضافة CSS محدد لنجوم التقييم
const style = document.createElement('style');
style.innerHTML = `
    #starSelector:hover i { color: #d1d5db; } /* رمادي عند الوقوف على الحاوية */
    #starSelector i:hover, #starSelector i:hover ~ i { color: #c8a020 !important; } /* تلوين النجمة وما قبلها (لأن الاتجاه معكوس) */
`;
document.head.appendChild(style);
</script>