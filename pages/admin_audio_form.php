<?php
// مسار الملف: pages/admin_audio_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $title));
    $narrator = $_POST['narrator'];
    $cat_id = $_POST['category_id'];
    $price = (float)$_POST['price'];
    $desc = $_POST['description'];
    $duration = (int)$_POST['audio_duration'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // معالجة صورة الغلاف
    $thumb_url = $_POST['current_thumb'] ?? '';
    if (isset($_FILES['thumb_file']) && $_FILES['thumb_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/audios/thumbs/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['thumb_file']['name']);
        if (move_uploaded_file($_FILES['thumb_file']['tmp_name'], $uploadDir . $fileName)) {
            $thumb_url = $uploadDir . $fileName;
        }
    }

    // معالجة الصوت (رابط خارجي أو رفع ملف)
    $audio_url = $_POST['current_audio'] ?? '';
    $external_url = trim($_POST['external_audio_url'] ?? '');
    
    if (!empty($external_url)) {
        // إذا قام المدير بوضع رابط يوتيوب أو رابط خارجي، نعتمد الرابط الخارجي
        $audio_url = $external_url;
    } elseif (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
        // إذا قام برفع ملف MP3
        $uploadDir = 'assets/uploads/audios/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $uploadDir . $fileName)) {
            $audio_url = $uploadDir . $fileName;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE audios SET title=?, slug=?, narrator=?, category_id=?, price=?, description=?, thumbnail_url=?, audio_url=?, audio_duration=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $slug, $narrator, $cat_id, $price, $desc, $thumb_url, $audio_url, $duration, $is_active, $id]);
            $msg = "تم التحديث بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO audios (title, slug, narrator, category_id, price, description, thumbnail_url, audio_url, audio_duration, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $narrator, $cat_id, $price, $desc, $thumb_url, $audio_url, $duration, $is_active]);
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
    $stmt = $pdo->prepare("SELECT * FROM audios WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<!-- تضمين مكتبة CKEditor 5 باللغة العربية عبر الـ CDN -->
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
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-headphones' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل مقطع صوتي' : 'إضافة مقطع صوتي' ?></h1>
        <a href="index.php?page=admin_audios" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة الصوتيات</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">عنوان المقطع <span class="req">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">القسم التابع له <span class="req">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- اختر القسم --</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($item['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">القارئ / الراوي</label>
                    <input type="text" name="narrator" class="form-control" value="<?= htmlspecialchars($item['narrator'] ?? '') ?>">
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">السعر (0 = مجاني)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $item['price'] ?? 0 ?>">
                </div>
            </div>

            <div class="cf-group mb-6">
                <label class="cf-label">وصف المقطع (يدعم التنسيقات والألوان)</label>
                <textarea name="description" id="editor" class="form-textarea" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <!-- تعديل مصدر الصوت ليقبل رابط خارجي أو رفع ملف -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 mb-6">
                <h4 class="font-bold text-pri-900 mb-4 border-b border-gray-200 pb-2">مصدر المقطع الصوتي (اختر أحدهما)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="cf-group !mb-0">
                        <label class="cf-label text-blue-600"><i class="fab fa-youtube"></i> رابط خارجي (يوتيوب مثلاً)</label>
                        <input type="url" name="external_audio_url" dir="ltr" class="form-control" value="<?= htmlspecialchars((!empty($item['audio_url']) && strpos($item['audio_url'], 'http') === 0) ? $item['audio_url'] : '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="cf-group !mb-0">
                        <label class="cf-label text-pri-600"><i class="fas fa-upload"></i> أو رفع ملف من الجهاز (MP3)</label>
                        <input type="hidden" name="current_audio" value="<?= htmlspecialchars($item['audio_url'] ?? '') ?>">
                        <input type="file" name="audio_file" accept="audio/*" class="form-control !py-2">
                        <?php if(!empty($item['audio_url']) && strpos($item['audio_url'], 'http') !== 0): ?>
                            <div class="text-[10px] text-green-600 mt-1 font-bold">✓ يوجد ملف محفوظ مسبقاً</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">مدة المقطع (بالثواني)</label>
                    <input type="number" name="audio_duration" class="form-control" value="<?= $item['audio_duration'] ?? 0 ?>">
                    <p class="text-[10px] text-brk-400 mt-1">مثال: 40 دقيقة = 2400 ثانية</p>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">صورة الغلاف (اختياري)</label>
                    <input type="hidden" name="current_thumb" value="<?= htmlspecialchars($item['thumbnail_url'] ?? '') ?>">
                    <input type="file" name="thumb_file" accept="image/*" class="form-control !py-2">
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">مقطع مفعل يظهر للزوار</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ البيانات</button>
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