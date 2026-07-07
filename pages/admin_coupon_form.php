<?php
// مسار الملف: pages/admin_coupon_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code']));
    $description = $_POST['description'] ?? '';
    $discountType = $_POST['discount_type'];
    $discountValue = (float)$_POST['discount_value'];
    $maxDiscount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $minOrder = (float)($_POST['min_order_amount'] ?? 0);
    $maxUsesTotal = !empty($_POST['max_uses_total']) ? (int)$_POST['max_uses_total'] : null;
    $maxUsesPerUser = (int)($_POST['max_uses_per_user'] ?? 1);
    $startsAt = !empty($_POST['starts_at']) ? $_POST['starts_at'] : null;
    $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $appliesTo = $_POST['applies_to'] ?? 'all';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE coupons SET code=?, description=?, discount_type=?, discount_value=?, max_discount=?, min_order_amount=?, max_uses_total=?, max_uses_per_user=?, starts_at=?, expires_at=?, applies_to=?, is_active=? WHERE id=?");
            $stmt->execute([$code, $description, $discountType, $discountValue, $maxDiscount, $minOrder, $maxUsesTotal, $maxUsesPerUser, $startsAt, $expiresAt, $appliesTo, $isActive, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, max_discount, min_order_amount, max_uses_total, max_uses_per_user, starts_at, expires_at, applies_to, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $description, $discountType, $discountValue, $maxDiscount, $minOrder, $maxUsesTotal, $maxUsesPerUser, $startsAt, $expiresAt, $appliesTo, $isActive]);
            $id = $pdo->lastInsertId(); $isEdit = true;
            $msg = "تم إنشاء الكوبون بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: تأكد من عدم تكرار كود الكوبون."; $msgType = "err";
    }
}

$item = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-ticket-alt' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل كوبون' : 'إنشاء كوبون جديد' ?></h1>
        <a href="index.php?page=admin_coupons" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">كود الكوبون <span class="req">*</span></label>
                    <input type="text" name="code" class="form-control font-black text-lg tracking-widest" dir="ltr" value="<?= htmlspecialchars($item['code'] ?? '') ?>" required placeholder="WINTER20" style="text-transform:uppercase">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">نوع الخصم <span class="req">*</span></label>
                    <select name="discount_type" class="form-select" required>
                        <option value="percentage" <?= ($item['discount_type'] ?? '') == 'percentage' ? 'selected' : '' ?>>نسبة مئوية (%)</option>
                        <option value="fixed" <?= ($item['discount_type'] ?? '') == 'fixed' ? 'selected' : '' ?>>مبلغ ثابت (ر.س)</option>
                    </select>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">قيمة الخصم <span class="req">*</span></label>
                    <input type="number" step="0.01" name="discount_value" class="form-control font-bold text-gld-600" value="<?= $item['discount_value'] ?? '' ?>" required>
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">وصف الكوبون</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($item['description'] ?? '') ?>" placeholder="مثال: خصم شتوي لجميع المنتجات">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">حد أقصى للخصم</label>
                    <input type="number" step="0.01" name="max_discount" class="form-control" value="<?= $item['max_discount'] ?? '' ?>" placeholder="للنسب فقط">
                    <p class="text-[10px] text-brk-400 mt-1">للنسب المئوية فقط</p>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">حد أدنى للطلب (ر.س)</label>
                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="<?= $item['min_order_amount'] ?? 0 ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">حد أقصى للاستخدام الكلي</label>
                    <input type="number" name="max_uses_total" class="form-control" value="<?= $item['max_uses_total'] ?? '' ?>" placeholder="فارغ = غير محدود">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">حد لكل مستخدم</label>
                    <input type="number" name="max_uses_per_user" class="form-control" value="<?= $item['max_uses_per_user'] ?? 1 ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="<?= $item['starts_at'] ?? '' ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="expires_at" class="form-control" value="<?= $item['expires_at'] ?? '' ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-t border-gray-100 pt-6">
                <div class="form-group !mb-0">
                    <label class="form-label">ينطبق على</label>
                    <select name="applies_to" class="form-select">
                        <option value="all" <?= ($item['applies_to'] ?? 'all') == 'all' ? 'selected' : '' ?>>الكل</option>
                        <option value="products" <?= ($item['applies_to'] ?? '') == 'products' ? 'selected' : '' ?>>المنتجات فقط</option>
                        <option value="packages" <?= ($item['applies_to'] ?? '') == 'packages' ? 'selected' : '' ?>>الباقات فقط</option>
                        <option value="audios" <?= ($item['applies_to'] ?? '') == 'audios' ? 'selected' : '' ?>>الصوتيات فقط</option>
                        <option value="videos" <?= ($item['applies_to'] ?? '') == 'videos' ? 'selected' : '' ?>>الفيديوهات فقط</option>
                    </select>
                </div>
                <div class="form-group !mb-0 flex items-end">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="mr-3 font-bold text-pri-900">كوبون نشط</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ الكوبون</button>
        </form>
    </div>
</div>