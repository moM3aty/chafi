<?php
// مسار الملف: pages/admin_book_form.php
// الوظيفة: إضافة وتعديل الكتب مع معالجة ذكية لتكرار الـ Slug

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $baseSlug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(str_replace(' ', '-', $title));
    $baseSlug = preg_replace('/[^a-zA-Z0-9\-\p{Arabic}]/u', '', $baseSlug); // تنظيف الرابط
    
    // ══════════════ المعالجة الذكية لتكرار الـ Slug ══════════════
    $slug = $baseSlug;
    $counter = 1;
    while (true) {
        $checkStmt = $pdo->prepare("SELECT id FROM books WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $id]);
        if (!$checkStmt->fetch()) {
            break; // الرابط فريد وغير مكرر
        }
        // إذا كان مكرر، أضف رقماً تسلسلياً وجرب مرة أخرى
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    // ════════════════════════════════════════════════════════════

    $author = $_POST['author'];
    $cat_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $price = (float)$_POST['price'];
    $desc = $_POST['description'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $thumb_url = $_POST['current_thumb'] ?? '';
    if (isset($_FILES['thumb_file']) && $_FILES['thumb_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/books/thumbs/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['thumb_file']['name']);
        if (move_uploaded_file($_FILES['thumb_file']['tmp_name'], $uploadDir . $fileName)) {
            $thumb_url = $uploadDir . $fileName;
        }
    }

    $book_url = $_POST['current_book'] ?? '';
    if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/books/files/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($_FILES['book_file']['name']);
        if (move_uploaded_file($_FILES['book_file']['tmp_name'], $uploadDir . $fileName)) {
            $book_url = $uploadDir . $fileName;
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE books SET title=?, slug=?, author=?, category_id=?, price=?, description=?, thumbnail_url=?, book_file_url=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $slug, $author, $cat_id, $price, $desc, $thumb_url, $book_url, $is_active, $id]);
            $msg = "تم تحديث الكتاب بنجاح!"; $msgType = "ok";
        } else {
            $stmt = $pdo->prepare("INSERT INTO books (title, slug, author, category_id, price, description, thumbnail_url, book_file_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $author, $cat_id, $price, $desc, $thumb_url, $book_url, $is_active]);
            $id = $pdo->lastInsertId();
            $isEdit = true;
            $msg = "تم إضافة الكتاب بنجاح!"; $msgType = "ok";
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ غير متوقع في قاعدة البيانات."; $msgType = "err";
    }
}

$item = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/translations/ar.js"></script>
<style>
    .ck-editor__editable_inline { min-height: 250px; font-family: 'Cairo', sans-serif !important; font-size: 15px; direction: rtl; text-align: right; border-radius: 0 0 12px 12px !important; border-color: #e8dfd2 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.02) !important; }
    .ck-editor__editable_inline:focus { border-color: #1a582a !important; box-shadow: 0 0 0 4px rgba(26,88,42,.08) !important; }
    .ck-toolbar { border-radius: 12px 12px 0 0 !important; border-color: #e8dfd2 !important; background: #fdf9ed !important; }
</style>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-book' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل كتاب' : 'إضافة كتاب جديد' ?></h1>
        <a href="index.php?page=admin_books" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة الكتب</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">عنوان الكتاب <span class="req">*</span></label>
                    <input type="text" name="title" id="bookTitle" class="form-control" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">الرابط النظيف (Slug) <span class="req">*</span></label>
                    <input type="text" name="slug" id="bookSlug" class="form-control" dir="ltr" value="<?= htmlspecialchars($item['slug'] ?? '') ?>" placeholder="سيتم توليده وتصحيحه تلقائياً">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">المؤلف / الكاتب</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($item['author'] ?? '') ?>">
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">القسم</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- بدون قسم --</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($item['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="cf-group !mb-0">
                    <label class="cf-label">السعر (0 = مجاني) <span class="req">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control font-bold text-pri-700" value="<?= $item['price'] ?? 0 ?>" required>
                </div>
            </div>

            <div class="cf-group mb-6">
                <label class="cf-label">وصف الكتاب ومحتوياته</label>
                <textarea name="description" id="editor" class="form-textarea" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                    <label class="cf-label text-pri-600"><i class="fas fa-file-pdf"></i> ملف الكتاب (PDF) <span class="req">*</span></label>
                    <input type="hidden" name="current_book" value="<?= htmlspecialchars($item['book_file_url'] ?? '') ?>">
                    <input type="file" name="book_file" accept=".pdf,.doc,.docx,.epub" class="form-control !py-2 bg-white" <?= $isEdit ? '' : 'required' ?>>
                    <?php if(!empty($item['book_file_url'])): ?>
                        <div class="text-xs text-green-600 mt-2 font-bold"><i class="fas fa-check"></i> تم رفع ملف مسبقاً</div>
                    <?php endif; ?>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                    <label class="cf-label"><i class="fas fa-image"></i> صورة الغلاف</label>
                    <input type="hidden" name="current_thumb" value="<?= htmlspecialchars($item['thumbnail_url'] ?? '') ?>">
                    <input type="file" name="thumb_file" accept="image/*" class="form-control !py-2 bg-white">
                    <?php if(!empty($item['thumbnail_url'])): ?>
                        <img src="<?= htmlspecialchars($item['thumbnail_url']) ?>" class="w-16 h-20 mt-2 rounded object-cover border border-gray-200 shadow-sm">
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($item) || $item['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">كتاب مفعل يظهر في المكتبة</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ الكتاب</button>
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
                    'heading', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|', 'alignment', '|',
                    'numberedList', 'bulletedList', '|', 'outdent', 'indent', '|',
                    'link', 'insertTable', 'blockQuote', 'horizontalLine', '|', 'removeFormat', 'sourceEditing', 'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            list: { properties: { styles: true, startIndex: true, reversed: true } },
            removePlugins: ['CKBox', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination', 'WProofreader', 'MathType', 'SlashCommand', 'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'Autosave']
        }).catch(error => { console.error(error); });
    }

    const titleInput = document.getElementById('bookTitle');
    const slugInput = document.getElementById('bookSlug');

    function generateSlug(text) {
        return text.trim()
                   .replace(/\s+/g, '-')
                   .replace(/[^\w\-\u0600-\u06FF]/g, '')
                   .replace(/\-\-+/g, '-')
                   .replace(/^-+/, '')
                   .replace(/-+$/, ''); 
    }

    if(titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (slugInput.value === '' || !slugInput.hasAttribute('data-touched')) {
                slugInput.value = generateSlug(this.value);
            }
        });
        slugInput.addEventListener('input', function() { this.setAttribute('data-touched', 'true'); });
    }
});
</script>
