<?php
// مسار الملف: pages/admin_advertisement_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $linkUrl = $_POST['link_url'] ?? '';
    $linkText = $_POST['link_text'] ?? 'تصفح الآن';
    $linkTarget = $_POST['link_target'] ?? '_self';
    $position = (int)$_POST['position'];
    $displayOrder = (int)$_POST['display_order'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $imageUrl = $_POST['current_image'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/ads/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['image_file']['name']);
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
            $imageUrl = $uploadDir . $fileName;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE advertisements SET title=?, subtitle=?, image_url=?, link_url=?, link_text=?, link_target=?, position=?, display_order=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $imageUrl, $linkUrl, $linkText, $linkTarget, $position, $displayOrder, $isActive, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO advertisements (title, subtitle, image_url, link_url, link_text, link_target, position, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $imageUrl, $linkUrl, $linkText, $linkTarget, $position, $displayOrder, $isActive]);
            $id = $pdo->lastInsertId(); $isEdit = true;
            $msg = "تم الإضافة بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: " . $e->getMessage(); $msgType = "err";
    }
}

$item = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM advertisements WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-image' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل إعلان' : 'إضافة إعلان جديد' ?></h1>
        <a href="index.php?page=admin_advertisements" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group mb-6">
                <label class="form-label">عنوان الإعلان (يدعم HTML مثل <code>&lt;br&gt;</code>) <span class="req">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">العنوان الفرعي</label>
                <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($item['subtitle'] ?? '') ?>">
            </div>

            <div class="form-group mb-6">
                <label class="form-label">صورة الإعلان <span class="req">*</span></label>
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
                <div class="flex items-center gap-4 mb-3">
                    <?php if(!empty($item['image_url'])): ?>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-40 h-24 rounded-xl object-cover border border-gray-200 shadow-sm">
                    <?php endif; ?>
                </div>
                <input type="file" name="image_file" accept="image/*" class="form-control !py-2" <?= $isEdit ? '' : 'required' ?>>
                <p class="text-xs text-brk-400 mt-2">الأبعاد المفضلة للسلايدر: 1920×600 بكسل</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">رابط الانتقال</label>
                    <input type="url" name="link_url" dir="ltr" class="form-control" value="<?= htmlspecialchars($item['link_url'] ?? '') ?>" placeholder="https://...">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">نص الزر</label>
                    <input type="text" name="link_text" class="form-control" value="<?= htmlspecialchars($item['link_text'] ?? 'تصفح الآن') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">موقع العرض <span class="req">*</span></label>
                    <select name="position" class="form-select" required>
                        <option value="0" <?= ($item['position'] ?? 0) == 0 ? 'selected' : '' ?>>هيرو سلايدر (رئيسي)</option>

                    </select>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control" value="<?= $item['display_order'] ?? 0 ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">فتح الرابط في</label>
                    <select name="link_target" class="form-select">
                        <option value="_self" <?= ($item['link_target'] ?? '_self') == '_self' ? 'selected' : '' ?>>نفس النافذة</option>
                        <option value="_blank" <?= ($item['link_target'] ?? '_self') == '_blank' ? 'selected' : '' ?>>نافذة جديدة</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">إعلان مفعل</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ الإعلان</button>
            </div>
        </form>
    </div>
</div>