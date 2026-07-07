<?php
// مسار الملف: pages/admin_product_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

// جلب الأقسام
$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $name));
    $sku = $_POST['sku'];
    $cat_id = $_POST['category_id'];
    $price = (float)$_POST['price'];
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
    $stock = (int)$_POST['stock_quantity'];
    $short_desc = $_POST['short_description'];
    $desc = $_POST['description'];
    
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // معالجة الصورة
    $img_url = $_POST['current_image'] ?? ''; // تم الإصلاح
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/products/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        
        $fileName = time() . '_' . basename($_FILES['image_file']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $img_url = $targetFile;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, sku=?, category_id=?, price=?, old_price=?, cost_price=?, stock_quantity=?, short_description=?, description=?, image_url=?, is_active=?, is_featured=? WHERE id=?");
            $stmt->execute([$name, $slug, $sku, $cat_id, $price, $old_price, $cost_price, $stock, $short_desc, $desc, $img_url, $is_active, $is_featured, $id]);
            $msg = "تم تحديث المنتج بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, slug, sku, category_id, price, old_price, cost_price, stock_quantity, short_description, description, image_url, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $sku, $cat_id, $price, $old_price, $cost_price, $stock, $short_desc, $desc, $img_url, $is_active, $is_featured]);
            $id = $pdo->lastInsertId();
            $isEdit = true;
            $msg = "تم إضافة المنتج بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        // تم الإصلاح: تصحيح الخطأ المطبعي הـ
        $msg = "حدث خطأ في قاعدة البيانات، تأكد من عدم تكرار الـ Slug أو الـ SKU."; $msgType = "err";
    }
}

// جلب بيانات المنتج إن وجد
$prod = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
}
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-box-open' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل منتج' : 'إضافة منتج جديد' ?></h1>
        <a href="index.php?page=admin_products" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة المنتجات</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post" enctype="multipart/form-data">
            <h3 class="text-lg font-bold text-pri-900 mb-4 border-b border-gray-100 pb-2">البيانات الأساسية</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">اسم المنتج <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($prod['name'] ?? '') ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">القسم التابع له <span class="req">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- اختر القسم --</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($prod['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">الرابط النظيف (Slug) <span class="req">*</span></label>
                    <input type="text" name="slug" class="form-control" dir="ltr" value="<?= htmlspecialchars($prod['slug'] ?? '') ?>" placeholder="مثال: black-musk">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">رمز المنتج (SKU)</label>
                    <input type="text" name="sku" class="form-control" dir="ltr" value="<?= htmlspecialchars($prod['sku'] ?? '') ?>" placeholder="مثال: PRD-001">
                </div>
            </div>

            <h3 class="text-lg font-bold text-pri-900 mb-4 border-b border-gray-100 pb-2 mt-8">الأسعار والمخزون</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">سعر البيع (ر.س) <span class="req">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control font-bold text-pri-700" value="<?= $prod['price'] ?? '' ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">السعر القديم (للخصم)</label>
                    <input type="number" step="0.01" name="old_price" class="form-control text-red-500" value="<?= $prod['old_price'] ?? '' ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">سعر التكلفة (سري)</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control bg-gray-50" value="<?= $prod['cost_price'] ?? '' ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">الكمية بالمخزون <span class="req">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control" value="<?= $prod['stock_quantity'] ?? 10 ?>" required>
                </div>
            </div>

            <h3 class="text-lg font-bold text-pri-900 mb-4 border-b border-gray-100 pb-2 mt-8">الوصف والصور</h3>
            <div class="form-group mb-6">
                <label class="form-label">وصف مختصر</label>
                <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($prod['short_description'] ?? '') ?>">
            </div>
            <div class="form-group mb-6">
                <label class="form-label">الوصف الشامل (يدعم HTML)</label>
                <textarea name="description" class="form-textarea" rows="6"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">صورة المنتج الرئيسية</label>
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($prod['image_url'] ?? '') ?>">
                <div class="flex items-center gap-4">
                    <?php if(!empty($prod['image_url'])): ?>
                        <img src="<?= htmlspecialchars($prod['image_url']) ?>" class="w-16 h-16 rounded-xl object-cover border border-gray-200 shadow-sm">
                    <?php endif; ?>
                    <input type="file" name="image_file" accept="image/*" class="form-control !py-2">
                </div>
            </div>

            <div class="flex flex-wrap gap-8 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($prod) || $prod['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">منتج مفعل (يظهر للزوار)</span>
                </label>
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_featured" value="1" <?= (isset($prod) && $prod['is_featured'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-gld-600">منتج مميز (يظهر بالرئيسية)</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ بيانات المنتج</button>
            </div>
        </form>
    </div>
</div>