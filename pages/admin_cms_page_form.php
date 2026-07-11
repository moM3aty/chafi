<?php
// مسار الملف: pages/admin_cms_page_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $title));
    $content = $_POST['content'] ?? '';
    $metaTitle = $_POST['meta_title'] ?? '';
    $metaDesc = $_POST['meta_description'] ?? '';
    $sortOrder = (int)$_POST['sort_order'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE cms_pages SET title=?, slug=?, content=?, meta_title=?, meta_description=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $metaTitle, $metaDesc, $sortOrder, $isActive, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO cms_pages (title, slug, content, meta_title, meta_description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $metaTitle, $metaDesc, $sortOrder, $isActive]);
            $id = $pdo->lastInsertId(); $isEdit = true;
            $msg = "تم إنشاء الصفحة بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: تأكد من عدم تكرار الرابط (Slug)."; $msgType = "err";
    }
}

$page = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
}
?>

<!-- تضمين مكتبة CKEditor 5 باللغة العربية عبر الـ CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/translations/ar.js"></script>
<style>
    /* تنسيق المحرر ليناسب هوية متجر تشافي */
    .ck-editor__editable_inline {
        min-height: 350px;
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
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-file-alt' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل صفحة' : 'صفحة تعريفية جديدة' ?></h1>
        <a href="index.php?page=admin_cms_pages" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">عنوان الصفحة <span class="req">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">الرابط (Slug) <span class="req">*</span></label>
                    <input type="text" name="slug" dir="ltr" class="form-control" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" placeholder="about-us">
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">محتوى الصفحة (يدعم الألوان والتنسيقات)</label>
                <textarea name="content" id="editor" class="form-textarea" rows="16" style="font-family: monospace, 'Cairo'; font-size: 13px; line-height: 1.8;"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
            </div>

            <h3 class="text-sm font-bold text-brk-500 mb-4 border-b border-gray-100 pb-2"><i class="fas fa-search ml-1"></i> إعدادات SEO</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">عنوان SEO</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">وصف SEO</label>
                    <input type="text" name="meta_description" class="form-control" value="<?= htmlspecialchars($page['meta_description'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <div class="form-group !mb-0">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $page['sort_order'] ?? 0 ?>">
                </div>
                <div class="form-group !mb-0 flex items-end">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" value="1" <?= (!isset($page) || $page['is_active'] == 1) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="mr-3 font-bold text-pri-900">صفحة مفعلة</span>
                    </label>
                </div>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ الصفحة</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>