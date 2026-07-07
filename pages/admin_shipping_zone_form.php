<?php
// مسار الملف: pages/admin_shipping_zone_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zoneName = $_POST['zone_name'];
    $cities = $_POST['cities'] ?? '';
    $baseCost = (float)$_POST['base_cost'];
    $extraCostPerItem = (float)$_POST['extra_cost_per_item'];
    $freeThreshold = !empty($_POST['free_shipping_threshold']) ? (float)$_POST['free_shipping_threshold'] : null;
    $daysMin = (int)$_POST['estimated_days_min'];
    $daysMax = (int)$_POST['estimated_days_max'];
    $sortOrder = (int)$_POST['sort_order'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // تحويل المدن لـ JSON
    $citiesJson = null;
    if (!empty($cities)) {
        $citiesArr = array_map('trim', explode(',', $cities));
        $citiesJson = json_encode($citiesArr, JSON_UNESCAPED_UNICODE);
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE shipping_zones SET zone_name=?, cities=?, base_cost=?, extra_cost_per_item=?, free_shipping_threshold=?, estimated_days_min=?, estimated_days_max=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$zoneName, $citiesJson, $baseCost, $extraCostPerItem, $freeThreshold, $daysMin, $daysMax, $sortOrder, $isActive, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO shipping_zones (zone_name, cities, base_cost, extra_cost_per_item, free_shipping_threshold, estimated_days_min, estimated_days_max, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$zoneName, $citiesJson, $baseCost, $extraCostPerItem, $freeThreshold, $daysMin, $daysMax, $sortOrder, $isActive]);
            $id = $pdo->lastInsertId(); $isEdit = true;
            $msg = "تم إنشاء المنطقة بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: " . $e->getMessage(); $msgType = "err";
    }
}

$item = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM shipping_zones WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}

$citiesText = '';
if ($item && $item['cities']) {
    $arr = json_decode($item['cities'], true);
    $citiesText = is_array($arr) ? implode(', ', $arr) : $item['cities'];
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-truck' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل منطقة شحن' : 'إضافة منطقة شحن' ?></h1>
        <a href="index.php?page=admin_shipping_zones" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">اسم المنطقة <span class="req">*</span></label>
                    <input type="text" name="zone_name" class="form-control" value="<?= htmlspecialchars($item['zone_name'] ?? '') ?>" required placeholder="مثال: المنطقة الوسطى">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $item['sort_order'] ?? 0 ?>">
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">المدن التابعة (مفصولة بفاصلة)</label>
                <input type="text" name="cities" class="form-control" value="<?= htmlspecialchars($citiesText) ?>" placeholder="الرياض, الخرج, الدوادمي">
                <p class="text-[10px] text-brk-400 mt-1">اتركها فارغة لتشمل جميع المدن</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">تكلفة الشحن الأساسية <span class="req">*</span></label>
                    <input type="number" step="0.01" name="base_cost" class="form-control font-bold text-pri-700" value="<?= $item['base_cost'] ?? 25 ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">تكلفة إضافية/عنصر</label>
                    <input type="number" step="0.01" name="extra_cost_per_item" class="form-control" value="<?= $item['extra_cost_per_item'] ?? 0 ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">شحن مجاني فوق (ر.س)</label>
                    <input type="number" step="0.01" name="free_shipping_threshold" class="form-control" value="<?= $item['free_shipping_threshold'] ?? '' ?>" placeholder="فارغ = بدون">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">مدة التسليم (أيام)</label>
                    <div class="flex gap-2 items-center">
                        <input type="number" name="estimated_days_min" class="form-control" value="<?= $item['estimated_days_min'] ?? 1 ?>" min="1" placeholder="من">
                        <span class="text-brk-400 font-bold">—</span>
                        <input type="number" name="estimated_days_max" class="form-control" value="<?= $item['estimated_days_max'] ?? 3 ?>" min="1" placeholder="إلى">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">منطقة مفعلة</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ المنطقة</button>
            </div>
        </form>
    </div>
</div>