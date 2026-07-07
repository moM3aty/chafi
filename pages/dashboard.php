<?php
// مسار الملف: pages/dashboard.php
// النسخة الاحترافية — ملف شخصي متكامل

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$userId = $_SESSION['user_id'];

// ═══ بيانات المستخدم الحالي ═══
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// ═══ عناوين المستخدم ═══
$userAddresses = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$userAddresses->execute([$userId]);
$addresses = $userAddresses->fetchAll();

// ═══ الطلبات ═══
$stmtOrders = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.id DESC
");
$stmtOrders->execute([$userId]);
$orders = $stmtOrders->fetchAll();

// ═══ المفضلة ═══
$wishlistCount = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
$wishlistCount->execute([$userId]);
$wishlistCount = $wishlistCount->fetchColumn();

// ═══ الإشعارات ═══
$notifCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifCount->execute([$userId]);
$notifCount = $notifCount->fetchColumn();

$ordersCount = count($orders);
$totalSpent = array_sum(array_column($orders, 'total_amount'));

// Fix missing parameter for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_address'])) {
    $addrId = (int)$_POST['set_default_address'];
    $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$addrId, $userId]);
    echo "<script>window.location.href='index.php?page=dashboard';</script>"; exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addr_city'])) {
    $label = $_POST['addr_label'];
    $phone = $_POST['addr_phone'];
    $city = $_POST['addr_city'];
    $district = $_POST['addr_district'];
    $street = $_POST['addr_street'];
    $landmark = $_POST['addr_landmark'];
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    if($isDefault) {
        $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
    }
    
    $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, address_label, full_name, phone, city, district, street, landmark, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $label, $user['full_name'], $phone, $city, $district, $street, $landmark, $isDefault]);
    // It's handled by AJAX so we can just output a success code if requested via JS but currently it relies on index.php post.
}

?>

<div class="max-w-6xl mx-auto px-4 py-8 mb-14">

    <!-- رأس الصفحة -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4 afiu">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-gld-400 text-2xl shadow-lg relative group hover:scale-105 transition-transform">
                <i class="fas fa-user"></i>
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gld-500 text-pri-900 text-[10px] rounded-full flex items-center justify-center font-black">✓</div>
            </div>
            <div>
                <h1 class="text-2xl font-black text-pri-900 font-amiri">مرحباً بك <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
                <p class="text-brk-400 text-sm"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <?php if($notifCount > 0): ?>
                <a href="index.php?page=notifications" class="relative cf-btn cf-btn-pri cf-btn-sm bg-white text-xs">
                    <i class="fas fa-bell"></i> الإشعارات
                    <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-black"><?= $notifCount ?></span>
                </a>
            <?php endif; ?>
            <a href="index.php?page=wishlist" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs relative">
                <i class="fas fa-heart"></i> المفضلة
                <?php if($wishlistCount > 0): ?><span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-black"><?= $wishlistCount ?></span><?php endif; ?>
            </a>
            <a href="ajax/logout.php" class="cf-btn cf-btn-out cf-btn-sm bg-white text-red-500 border-red-200 hover:!bg-red-500 hover:!text-white text-xs">
                <i class="fas fa-sign-out-alt"></i> خروج
            </a>
        </div>
    </div>

    <!-- بطاقات سريعة -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 afiu" style="animation-delay:.08s">
        <a href="index.php?page=products" class="dash-mini-stat no-underline">
            <div class="w-11 h-11 rounded-xl bg-pri-100 text-pri-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-box"></i></div>
            <span class="font-black text-pri-900"><?= $ordersCount ?></span>
            <span class="text-[10px] text-brk-400">الطلبات</span>
        </a>
        <a href="index.php?page=wishlist" class="dash-mini-stat no-underline">
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-heart"></i></div>
            <span class="font-black text-pri-900"><?= $wishlistCount ?></span>
            <span class="text-[10px] text-brk-400">المفضلة</span>
        </a>
        <div class="dash-mini-stat no-underline !cursor-default">
            <div class="w-11 h-11 rounded-xl bg-gld-100 text-gld-700 flex items-center justify-center text-lg"><i class="fas fa-wallet"></i></div>
            <span class="font-black text-pri-900"><?= number_format($totalSpent, 0) ?></span>
            <span class="text-[10px] text-brk-400">إجمالي المشتريات</span>
        </div>
        <a href="index.php?page=notifications" class="dash-mini-stat no-underline">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-bell"></i></div>
            <span class="font-black text-pri-900"><?= $notifCount ?></span>
            <span class="text-[10px] text-brk-400">إشعارات</span>
        </a>
    </div>

    <!-- التبويبات -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8 afiu" style="animation-delay:.12s">
        <div class="flex border-b border-gray-100">
            <button type="button" onclick="switchDashTab('orders')" id="dtab-orders" class="dash-dtab on flex-1 py-4 text-center font-bold text-sm transition-colors text-pri-700 bg-pri-50 border-b-[3px] border-pri-500">
                <i class="fas fa-shopping-bag ml-1"></i> سجل الطلبات
            </button>
            <button type="button" onclick="switchDashTab('addresses')" id="dtab-addresses" class="dash-dtab flex-1 py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent">
                <i class="fas fa-map-marker-alt ml-1"></i> عناويني
            </button>
            <button type="button" onclick="switchDashTab('profile')" id="dtab-profile" class="dash-dtab flex-1 py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent">
                <i class="fas fa-user-edit ml-1"></i> بياناتي
            </button>
        </div>

        <!-- ═══ تبويب: سجل الطلبات ═══ -->
        <div id="dpanel-orders" class="dash-dpanel block">
            <?php if (empty($orders)): ?>
                <div class="text-center py-14 text-brk-400">
                    <i class="fas fa-box-open text-5xl mb-4 block opacity-20"></i>
                    <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد طلبات حتى الآن</h3>
                    <a href="index.php?page=products" class="btn btn-primary mt-4"><i class="fas fa-store"></i> تصفح المتجر</a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php 
                    $statusAr = ['Pending'=>'قيد الانتظار','Processing'=>'قيد التجهيز','Shipped'=>'تم الشحن','Delivered'=>'تم التسليم','Cancelled'=>'ملغي','Refunded'=>'مسترد','Failed'=>'فاشل'];
                    $statusClr = ['Pending'=>'bg-yellow-50 text-yellow-700 border-yellow-200','Processing'=>'bg-blue-50 text-blue-700 border-blue-200','Shipped'=>'bg-purple-50 text-purple-700 border-purple-200','Delivered'=>'bg-green-50 text-green-700 border-green-200','Cancelled'=>'bg-red-50 text-red-700 border-red-200','Refunded'=>'bg-gray-50 text-gray-600 border-gray-200','Failed'=>'bg-red-50 text-red-600 border-red-200'];
                    foreach ($orders as $o):
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 hover:border-pri-200 hover:shadow-md transition-all group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-pri-50 text-pri-600 flex items-center justify-center text-sm font-bold shrink-0"><?= mb_substr($o['full_name'] ?? $_SESSION['user_name'], 0, 1) ?></div>
                                <div class="min-w-0">
                                    <div class="font-bold text-pri-900 text-sm truncate"><?= htmlspecialchars($o['shipping_full_name'] ?? $_SESSION['user_name']) ?></div>
                                    <div class="text-[10px] text-brk-400" dir="ltr">#<?= $o['order_number'] ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-center">
                                    <div class="font-black text-pri-700 text-base"><?= number_format($o['total_amount'], 2) ?> ر.س</div>
                                    <div class="text-[10px] text-brk-400"><?= $o['items_count'] ?> عنصر</div>
                                </div>
                                <span class="badge <?= $statusClr[$o['status']] ?? 'bg-gray-100 text-gray-600' ?> !text-xs"><?= $statusAr[$o['status']] ?? $o['status'] ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-50">
                            <a href="index.php?page=admin_order_details&id=<?= $o['id'] ?>" class="text-xs font-bold text-pri-600 hover:text-pri-800 transition flex items-center gap-1 no-underline">
                                <i class="fas fa-file-invoice"></i> الفاتورة
                            </a>
                            <span class="text-[10px] text-brk-300" dir="ltr"><?= date('Y/m/d H:i', strtotime($o['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ تبويب: العناوين ═══ -->
        <div id="dpanel-addresses" class="dash-dpanel hidden">
            <?php if (empty($addresses)): ?>
                <div class="text-center py-14 text-brk-400">
                    <i class="fas fa-map-marker-alt text-5xl mb-4 block opacity-20"></i>
                    <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد عناوين محفوظة</h3>
                    <p class="text-brk-400 text-sm mb-6">أضف عنوانك الأول لتسهيل عملية الطلب</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($addresses as $addr): ?>
                    <div class="bg-white rounded-2xl border-2 border-gray-100 p-5 hover:border-pri-200 hover:shadow-md transition-all group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-pri-50 text-pri-600 flex items-center justify-center text-sm font-bold"><?= htmlspecialchars($addr['address_label']) ?></div>
                                <div>
                                    <div class="font-bold text-pri-900 text-sm"><?= htmlspecialchars($addr['full_name']) ?></div>
                                    <div class="text-xs text-brk-400" dir="ltr"><?= htmlspecialchars($addr['phone']) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (!$addr['is_default']): ?>
                                <form method="post" action="index.php?page=dashboard" class="inline" onsubmit="return confirm('هل تريد جعل هذا العنوان هو الافتراضي؟')">
                                    <input type="hidden" name="set_default_address" value="<?= $addr['id'] ?>">
                                    <button type="submit" class="text-xs font-bold text-gld-600 hover:text-gld-800 transition no-underline"><i class="fas fa-star ml-1"></i> اجعل افتراضي</button>
                                </form>
                                <?php else: ?>
                                <span class="text-[10px] font-bold text-green-600 flex items-center gap-1"><i class="fas fa-check-circle"></i> الافتراضي</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-xs text-brk-400 bg-gray-50 rounded-xl p-3 mt-2">
                            <div class="flex items-center gap-2 mb-1"><i class="fas fa-map-pin text-[10px]"></i> <?= htmlspecialchars($addr['city']) ?></div>
                            <div class="flex items-center gap-2"><i class="fas fa-street-view text-[10px]"></i> <?= htmlspecialchars($addr['street'] ?? '-') ?></div>
                            <?php if ($addr['landmark']): ?>
                                <div class="flex items-center gap-2"><i class="fas fa-map-signs text-[10px]"></i> <?= htmlspecialchars($addr['landmark']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="mt-6">
                <!-- زر إضافة عنوان جديد -->
                <button onclick="openAddAddress()" class="w-full py-3 rounded-2xl border-2 border-dashed border-gray-300 text-sm font-bold text-brk-500 hover:border-pri-400 hover:text-pri-600 hover:bg-pri-50 transition-all no-underline">
                    <i class="fas fa-plus-circle ml-1"></i> إضافة عنوان جديد
                </button>
            </div>
        </div>

        <!-- ═══ تبويب: بيانات الحساب ═══ -->
        <div id="dpanel-profile" class="dash-dpanel hidden p-6">
            <form method="post" action="#" id="profileForm" onsubmit="updateProfile(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">رقم الجوال</label>
                        <input type="tel" name="phone" class="form-control" dir="ltr" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="05XXXXXXXX">
                    </div>
                </div>

                <div class="form-group mb-6">
                    <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control" dir="ltr" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <button type="submit" id="profileBtn" class="btn btn-primary btn-lg w-full sm:w-auto px-10">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </form>
        </div>
    </div>
</div>

<!-- نموذج إضافة عنوان جديد -->
<div id="addAddressModal" class="modal-backdrop">
    <div class="modal-dialog" style="max-width:500px">
        <button onclick="closeMdl('addAddressModal')" class="modal-close"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <div class="w-14 h-14 bg-gld-50 text-gld-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 shadow"><i class="fas fa-map-marker-alt"></i></div>
            <h2 class="text-xl font-black text-pri-900 font-amiri">إضافة عنوان جديد</h2>
        </div>
        <div class="modal-body">
            <form method="post" action="index.php?page=dashboard" id="addAddressForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label">مسمى العنوان <span class="req">*</span></label>
                        <input type="text" name="addr_label" class="form-control" value="المنزل" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">رقم الجوال <span class="req">*</span></label>
                        <input type="tel" name="addr_phone" class="form-control" dir="ltr" placeholder="05XXXXXXXX" required>
                    </div>
                </div>
                <div class="form-group mb-5">
                    <label class="form-label">المدينة <span class="req">*</span></label>
                    <input type="text" name="addr_city" class="form-control" placeholder="الرياض، جدة..." required>
                </div>
                <div class="form-group mb-5">
                    <label class="form-label">الحي</label>
                    <input type="text" name="addr_district" class="form-control" placeholder="اسم الحي">
                </div>
                <div class="form-group mb-5">
                    <label class="form-label">الشارع التفصيلي <span class="req">*</span></label>
                    <textarea name="addr_street" class="form-textarea" rows="3" placeholder="الشارع، رقم المبنى..." required></textarea>
                </div>
                <div class="form-group mb-5">
                    <label class="form-label">علامة مميزة (اختياري)</label>
                    <input type="text" name="addr_landmark" class="form-control" placeholder="بجانب المسجد...">
                </div>
                <div class="flex items-center gap-4 mb-6 pt-5 border-t border-gray-100 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-2xl">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_default" value="1">
                        <span class="toggle-slider"></span>
                        <span class="mr-3 font-bold text-pri-900">عنوان افتراضي</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-full"><i class="fas fa-plus-circle"></i> حفظ العنوان</button>
            </form>
        </div>
    </div>
</div>

<script>
// ═════════════════════════════════════════════════════
// لوحة تحكم المستخدم
// ═════════════════════════════════════════════════════

// تبديل التبويبات
function switchDashTab(tabName) {
    document.querySelectorAll('.dash-dtab').forEach(el => el.classList.remove('on'));
    document.querySelectorAll('.dash-dpanel').forEach(el => el.classList.add('hidden', 'p-6'));
    document.getElementById('dtab-' + tabName).classList.add('on');
    const panel = document.getElementById('dpanel-' + tabName);
    panel.classList.remove('hidden');
    panel.classList.add('block');
    panel.style.animation = 'tab-fade .35s ease-out';
}

// تحديث البيانات الشخصية
async function updateProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('profileBtn');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

    try {
        const form = document.getElementById('profileForm');
        const fd = new FormData(form);

        const res = await fetch('ajax/auth.php?action=update_profile', {
            method: 'POST',
            body: fd
        });

        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'ok');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(result.message, 'err');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    } catch(err) {
        showToast('حدث خطأ في الاتصال', 'err');
        btn.disabled = false;
        btn.innerHTML = origText;
    }
}

// فتح نموذج إضافة عنوان
function openAddAddress() {
    openMdl('addAddressModal');
    document.getElementById('addAddressForm').reset();
}

// حفظ عنوان جديد (بدون إعادة تحميل)
document.getElementById('addAddressForm').addEventListener('submit', async function(e) {
    // Let it submit normally to index.php?page=dashboard so the PHP handler works.
    // No preventDefault needed unless doing AJAX entirely.
});
</script>