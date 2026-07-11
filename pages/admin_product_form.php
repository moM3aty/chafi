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
    
    // استقبال نوع المنتج (ملموس أم رقمي)
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;

    // 1. معالجة صورة المنتج
    $img_url = $_POST['current_image'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/products/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['image_file']['name']));
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $img_url = $targetFile;
        }
    }

    // 2. معالجة رفع الملف الرقمي (الكتب/PDF) إذا كان المنتج رقمياً
    $digital_file_url = $_POST['current_digital_file'] ?? '';
    if ($is_digital == 1 && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] == 0) {
        $uploadDirDig = 'assets/uploads/digital_files/';
        if (!is_dir($uploadDirDig)) { mkdir($uploadDirDig, 0777, true); }
        
        $fileNameDig = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['digital_file']['name']));
        $targetFileDig = $uploadDirDig . $fileNameDig;
        
        if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $targetFileDig)) {
            $digital_file_url = $targetFileDig;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, sku=?, category_id=?, price=?, old_price=?, cost_price=?, stock_quantity=?, short_description=?, description=?, image_url=?, is_active=?, is_featured=?, is_digital=?, digital_file_url=? WHERE id=?");
            $stmt->execute([$name, $slug, $sku, $cat_id, $price, $old_price, $cost_price, $stock, $short_desc, $desc, $img_url, $is_active, $is_featured, $is_digital, $digital_file_url, $id]);
            $msg = "تم تحديث المنتج بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, slug, sku, category_id, price, old_price, cost_price, stock_quantity, short_description, description, image_url, is_active, is_featured, is_digital, digital_file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $sku, $cat_id, $price, $old_price, $cost_price, $stock, $short_desc, $desc, $img_url, $is_active, $is_featured, $is_digital, $digital_file_url]);
            $id = $pdo->lastInsertId();
            $isEdit = true;
            $msg = "تم إضافة المنتج بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
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

// توليد رمز SKU عشوائي للمنتجات الجديدة
$defaultSku = !$isEdit ? 'PRD-' . strtoupper(substr(uniqid(), -5)) : '';
?>

<!-- تضمين مكتبة CKEditor 5 باللغة العربية عبر الـ CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/translations/ar.js"></script>
<style>
    .ck-editor__editable_inline {
        min-height: 250px;
        font-family: 'Cairo', sans-serif !important;
        font-size: 15px;
        direction: rtl;
        text-align: right;
        border-radius: 0 0 12px 12px !important;
        border-color: #e8dfd2 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02) !important;
    }
    .ck-editor__editable_inline:focus {
        border-color: #1a582a !important;
        box-shadow: 0 0 0 4px rgba(26,88,42,.08) !important;
    }
    .ck-toolbar {
        border-radius: 12px 12px 0 0 !important;
        border-color: #e8dfd2 !important;
        background: #fdf9ed !important;
    }
</style>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-box-open' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل منتج/ملف' : 'إضافة منتج/ملف جديد' ?></h1>
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
                    <label class="form-label">اسم المنتج أو الملف <span class="req">*</span></label>
                    <input type="text" name="name" id="prodName" class="form-control" value="<?= htmlspecialchars($prod['name'] ?? '') ?>" required>
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
                    <input type="text" name="slug" id="prodSlug" class="form-control" dir="ltr" value="<?= htmlspecialchars($prod['slug'] ?? '') ?>" placeholder="مثال: black-musk">
                    <p class="text-[10px] text-brk-400 mt-1">يتم توليده تلقائياً ويمكنك تعديله يدوياً</p>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">رمز المنتج (SKU)</label>
                    <input type="text" name="sku" class="form-control font-bold text-pri-700" dir="ltr" value="<?= htmlspecialchars($prod['sku'] ?? $defaultSku) ?>">
                    <p class="text-[10px] text-brk-400 mt-1">رمز فريد للمنتج (تلقائي)</p>
                </div>
            </div>

            <!-- قسم الملفات الرقمية -->
            <h3 class="text-lg font-bold text-blue-800 mb-4 border-b border-blue-100 pb-2 mt-8 bg-blue-50 p-3 rounded-t-xl"><i class="fas fa-file-download text-blue-500 ml-1"></i> نوع المنتج والملف الرقمي</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 bg-blue-50/30 p-5 rounded-b-xl border border-blue-50">
                <div class="form-group !mb-0 flex items-center">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_digital" id="isDigitalToggle" value="1" <?= (isset($prod) && $prod['is_digital'] == 1) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="mr-3 font-bold text-blue-900">هذا منتج رقمي (ملف / كتاب إلكتروني)</span>
                    </label>
                </div>
                <div class="form-group !mb-0 <?= (isset($prod) && $prod['is_digital'] == 1) ? '' : 'hidden' ?>" id="digitalFileContainer">
                    <label class="form-label text-blue-800">رفع الملف الرقمي (PDF, ZIP, إلخ)</label>
                    <input type="hidden" name="current_digital_file" value="<?= htmlspecialchars($prod['digital_file_url'] ?? '') ?>">
                    <input type="file" name="digital_file" accept=".pdf,.zip,.rar,.doc,.docx" class="form-control !py-2 bg-white border-blue-200">
                    <?php if(!empty($prod['digital_file_url'])): ?>
                        <p class="text-xs text-green-600 font-bold mt-2"><i class="fas fa-check"></i> تم رفع ملف مسبقاً</p>
                    <?php endif; ?>
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
                <div class="form-group !mb-0" id="stockContainer">
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
                <label class="form-label">الوصف الشامل (يدعم الألوان والتنسيقات)</label>
                <textarea name="description" id="editor" class="form-textarea" rows="6"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">صورة الغلاف / المنتج</label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. تشغيل وإخفاء حقل الملف الرقمي والمخزون
    const isDigitalToggle = document.getElementById('isDigitalToggle');
    const digitalFileContainer = document.getElementById('digitalFileContainer');
    const stockContainer = document.getElementById('stockContainer');
    
    if(isDigitalToggle && digitalFileContainer) {
        isDigitalToggle.addEventListener('change', function() {
            if(this.checked) {
                digitalFileContainer.classList.remove('hidden');
                stockContainer.style.opacity = '0.5'; // تظليل المخزون لأنه لا يهم للمنتج الرقمي
            } else {
                digitalFileContainer.classList.add('hidden');
                stockContainer.style.opacity = '1';
            }
        });
    }

    // 2. تشغيل المحرر المتقدم بالنسخة الشاملة (Super Build)
    if (document.querySelector('#editor')) {
        CKEDITOR.ClassicEditor.create(document.querySelector('#editor'), {
            language: 'ar',
            toolbar: {
                items: [
                    'heading', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'alignment', '|',
                    'numberedList', 'bulletedList', '|',
                    'outdent', 'indent', '|',
                    'link', 'insertTable', 'blockQuote', 'horizontalLine', '|',
                    'removeFormat', 'sourceEditing', 'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            list: { properties: { styles: true, startIndex: true, reversed: true } },
            heading: {
                options: [
                    { model: 'paragraph', title: 'فقرة', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'عنوان 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'عنوان 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'عنوان 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'عنوان 4', class: 'ck-heading_heading4' }
                ]
            },
            fontFamily: {
                options: [ 'default', 'Cairo, sans-serif', 'Amiri, serif', 'Arial, sans-serif' ],
                supportAllValues: true
            },
            fontSize: {
                options: [ 10, 12, 14, 'default', 18, 20, 24, 28, 32, 36 ],
                supportAllValues: true
            },
            // استثناء الإضافات المدفوعة لكي لا ينهار المحرر
            removePlugins: [
                'CKBox', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory',
                'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination', 'WProofreader', 'MathType',
                'SlashCommand', 'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'Autosave'
            ]
        }).catch(error => {
            console.error(error);
        });
    }

    // 3. التوليد التلقائي للـ Slug
    const nameInput = document.getElementById('prodName');
    const slugInput = document.getElementById('prodSlug');

    function generateSlug(text) {
        return text.trim()
            .replace(/\s+/g, '-') 
            .replace(/[^\w\-\u0600-\u06FF]/g, '') 
            .replace(/\-\-+/g, '-') 
            .replace(/^-+/, '') 
            .replace(/-+$/, ''); 
    }

    nameInput.addEventListener('input', function() {
        if (slugInput.value === '' || !slugInput.hasAttribute('data-touched')) {
            slugInput.value = generateSlug(this.value);
        }
    });

    slugInput.addEventListener('input', function() {
        this.setAttribute('data-touched', 'true');
    });
});
</script>