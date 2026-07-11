<?php
// مسار الملف: pages/admin_package_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $name));
    $original_price = (float)$_POST['original_total_price'];
    $package_price = (float)$_POST['package_price'];
    $short_desc = $_POST['short_description'];
    $desc = $_POST['description']; // المحتوى يأتي من المحرر كـ HTML
    
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $img_url = $_POST['current_image'] ?? ''; 
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/packages/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['image_file']['name']);
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
            $img_url = $uploadDir . $fileName;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE packages SET name=?, slug=?, original_total_price=?, package_price=?, short_description=?, description=?, image_url=?, is_active=?, is_featured=? WHERE id=?");
            $stmt->execute([$name, $slug, $original_price, $package_price, $short_desc, $desc, $img_url, $is_active, $is_featured, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO packages (name, slug, original_total_price, package_price, short_description, description, image_url, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $original_price, $package_price, $short_desc, $desc, $img_url, $is_active, $is_featured]);
            $id = $pdo->lastInsertId();
            $isEdit = true;
            $msg = "تم الإضافة بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: تأكد من عدم تكرار الـ Slug."; $msgType = "err";
    }
}

$item = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/translations/ar.js"></script>
<style>
    /* تنسيق المحرر ليناسب هوية متجر تشافي */
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

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-boxes' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل باقة' : 'إضافة باقة جديدة' ?></h1>
        <a href="index.php?page=admin_packages" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة الباقات</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">اسم الباقة <span class="req">*</span></label>
                    <!-- إضافة ID للحقل لاستخدامه في الجافاسكريبت -->
                    <input type="text" name="name" id="pkgName" class="form-control" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">الرابط النظيف (Slug) <span class="req">*</span></label>
                    <!-- إضافة ID للحقل لاستخدامه في الجافاسكريبت -->
                    <input type="text" name="slug" id="pkgSlug" class="form-control" dir="ltr" value="<?= htmlspecialchars($item['slug'] ?? '') ?>" placeholder="مثال: family-package">
                    <p class="text-[10px] text-brk-400 mt-1">يتم توليده تلقائياً ويمكنك تعديله يدوياً</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">إجمالي السعر الأصلي (ر.س) <span class="req">*</span></label>
                    <input type="number" step="0.01" name="original_total_price" class="form-control text-brk-400" value="<?= $item['original_total_price'] ?? 0 ?>" required>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">سعر الباقة (بعد التخفيض) <span class="req">*</span></label>
                    <input type="number" step="0.01" name="package_price" class="form-control font-bold text-pri-700" value="<?= $item['package_price'] ?? 0 ?>" required>
                </div>
            </div>

            <div class="cf-group mb-6">
                <label class="cf-label">وصف مختصر (يظهر في كروت الباقات)</label>
                <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($item['short_description'] ?? '') ?>">
            </div>

            <div class="cf-group mb-6">
                <label class="cf-label">وصف شامل للبيانات المحتواة في الباقة (يدعم التنسيقات والألوان)</label>
                <textarea name="description" id="editor" class="form-textarea" rows="4"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <div class="cf-group mb-6">
                <label class="cf-label">صورة الباقة المجمعة</label>
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
                <div class="flex items-center gap-4">
                    <?php if(!empty($item['image_url'])): ?>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-16 h-16 rounded-xl object-cover border border-gray-200 shadow-sm">
                    <?php endif; ?>
                    <input type="file" name="image_file" accept="image/*" class="form-control !py-2">
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">باقة مفعلة (تظهر للزوار)</span>
                </label>
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_featured" value="1" <?= (isset($item) && $item['is_featured'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-gld-600">باقة مميزة (تظهر في الرئيسية)</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ الباقة</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. تفعيل المحرر المتقدم CKEditor
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
            removePlugins: [
                'CKBox', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory',
                'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination', 'WProofreader', 'MathType',
                'SlashCommand', 'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'Autosave'
            ]
        }).catch(error => {
            console.error(error);
        });
    }

    // 2. التوليد التلقائي للرابط النظيف (Slug)
    const nameInput = document.getElementById('pkgName');
    const slugInput = document.getElementById('pkgSlug');

    function generateSlug(text) {
        return text.trim()
            .replace(/\s+/g, '-') 
            .replace(/[^\w\-\u0600-\u06FF]/g, '') 
            .replace(/\-\-+/g, '-') 
            .replace(/^-+/, '') 
            .replace(/-+$/, ''); 
    }

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (slugInput.value === '' || !slugInput.hasAttribute('data-touched')) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function() {
            this.setAttribute('data-touched', 'true');
        });
    }
});
</script>