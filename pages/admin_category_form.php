<?php
// مسار الملف: pages/admin_category_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

// جلب الأقسام الرئيسية للـ Dropdown
$parents = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL AND id != $id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $name));
    $short_desc = $_POST['short_description'];
    $desc = $_POST['description'];
    $icon = $_POST['icon_class'];
    $color = $_POST['color_hex'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $sort = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $show_menu = isset($_POST['show_in_menu']) ? 1 : 0;

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, short_description=?, description=?, icon_class=?, color_hex=?, parent_id=?, sort_order=?, is_active=?, show_in_menu=? WHERE id=?");
            $stmt->execute([$name, $slug, $short_desc, $desc, $icon, $color, $parent_id, $sort, $is_active, $show_menu, $id]);
            $msg = "تم تحديث القسم بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, short_description, description, icon_class, color_hex, parent_id, sort_order, is_active, show_in_menu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $short_desc, $desc, $icon, $color, $parent_id, $sort, $is_active, $show_menu]);
            $id = $pdo->lastInsertId();
            $isEdit = true;
            $msg = "تم إنشاء القسم بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: تأكد من أن الرابط النظيف (Slug) غير مكرر."; $msgType = "err";
    }
}

// جلب بيانات القسم إذا كان تعديل
$cat = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-folder-plus' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل قسم' : 'إضافة قسم جديد' ?></h1>
        <a href="index.php?page=admin_categories" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة الأقسام</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">اسم القسم <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name'] ?? '') ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">الرابط النظيف (Slug) <span class="req">*</span></label>
                    <input type="text" name="slug" class="form-control" dir="ltr" value="<?= htmlspecialchars($cat['slug'] ?? '') ?>" placeholder="مثال: herbs-oils">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">القسم الأب (اختياري)</label>
                    <select name="parent_id" class="form-control">
                        <option value="">-- كقسم رئيسي --</option>
                        <?php foreach($parents as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($cat['parent_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $cat['sort_order'] ?? 0 ?>">
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">وصف مختصر (يظهر في الكروت)</label>
                <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($cat['short_description'] ?? '') ?>">
            </div>

            <div class="form-group mb-6">
                <label class="form-label">وصف شامل للقسم</label>
                <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-t border-gray-100 pt-6">
                <div class="form-group !mb-0">
                    <label class="form-label">أيقونة القسم (FontAwesome)</label>
                    <input type="text" name="icon_class" class="form-control" dir="ltr" value="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">لون القسم (Hex)</label>
                    <div class="flex gap-2">
                        <input type="color" name="color_hex" class="h-12 w-14 rounded cursor-pointer border-0 p-0" value="<?= htmlspecialchars($cat['color_hex'] ?? '#1a582a') ?>">
                        <input type="text" class="form-control flex-1" disabled value="اختر اللون من المربع بجانبك">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($cat) || $cat['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">قسم مفعل</span>
                </label>
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="show_in_menu" value="1" <?= (!isset($cat) || $cat['show_in_menu'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">يظهر في القائمة العلوية</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ بيانات القسم</button>
            </div>
        </form>
    </div>
</div>