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
    $desc = $_POST['description']; // المحتوى سيأتي هنا من المحرر منسقاً بأكواد HTML
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

<!-- تضمين مكتبة CKEditor 5 باللغة العربية عبر الـ CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/translations/ar.js"></script>
<style>
    /* تنسيق المحرر ليناسب هوية الموقع */
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
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-folder-plus' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل قسم' : 'إضافة قسم جديد' ?></h1>
        <a href="index.php?page=admin_categories" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> إدارة الأقسام</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- حقل الاسم (يتم من خلاله توليد الـ Slug تلقائياً) -->
                <div class="form-group !mb-0">
                    <label class="form-label">اسم القسم <span class="req">*</span></label>
                    <input type="text" name="name" id="catName" class="form-control" value="<?= htmlspecialchars($cat['name'] ?? '') ?>" required>
                </div>
                <!-- حقل الـ Slug -->
                <div class="form-group !mb-0">
                    <label class="form-label">الرابط النظيف (Slug) <span class="req">*</span></label>
                    <input type="text" name="slug" id="catSlug" class="form-control" dir="ltr" value="<?= htmlspecialchars($cat['slug'] ?? '') ?>" placeholder="مثال: herbs-oils">
                    <p class="text-[10px] text-brk-400 mt-1">يتم توليده تلقائياً ويمكنك تعديله يدوياً</p>
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
                <label class="form-label">وصف شامل للقسم (محرر متقدم)</label>
                <textarea name="description" id="editor" class="form-textarea" rows="4"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-t border-gray-100 pt-6">
                <!-- نظام اختيار الأيقونات البصري -->
                <div class="form-group !mb-0 relative" id="iconPickerContainer">
                    <label class="form-label">أيقونة القسم <span class="req">*</span></label>
                    <input type="hidden" name="icon_class" id="iconClassInput" value="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?>">
                    
                    <button type="button" id="iconToggleBtn" class="form-control flex items-center justify-between !cursor-pointer hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <span id="selectedIconBox" class="w-8 h-8 rounded bg-pri-50 text-pri-600 flex items-center justify-center text-lg">
                                <i id="selectedIcon" class="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?>"></i>
                            </span>
                            <span class="text-sm font-bold text-gray-700">اختر الأيقونة</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-brk-300"></i>
                    </button>

                    <div id="iconDropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4 max-h-64 overflow-y-auto">
                        <p class="text-[10px] text-brk-400 mb-3 font-bold border-b border-gray-100 pb-2">اختر الأيقونة المناسبة للقسم:</p>
                        <div class="grid grid-cols-5 sm:grid-cols-6 gap-2" id="iconGrid">
                            <!-- سيتم تعبئتها بواسطة الجافاسكريبت -->
                        </div>
                    </div>
                </div>

                <div class="form-group !mb-0">
                    <label class="form-label">لون القسم (Hex)</label>
                    <div class="flex gap-2">
                        <input type="color" name="color_hex" class="h-14 w-16 rounded cursor-pointer border-0 p-0" value="<?= htmlspecialchars($cat['color_hex'] ?? '#1a582a') ?>">
                        <input type="text" class="form-control flex-1 text-left" disabled value="اختر اللون من المربع بجانبك" dir="rtl">
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

    const nameInput = document.getElementById('catName');
    const slugInput = document.getElementById('catSlug');

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

    const iconList = [
        'fas fa-folder', 'fas fa-folder-open', 'fas fa-box', 'fas fa-box-open',
        'fas fa-leaf', 'fas fa-seedling', 'fas fa-tree', 'fas fa-oil-can',
        'fas fa-jar', 'fas fa-flask', 'fas fa-tint', 'fas fa-water',
        'fas fa-fire', 'fas fa-burn', 'fas fa-magic', 'fas fa-brain',
        'fas fa-eye', 'fas fa-heart', 'fas fa-shield-alt', 'fas fa-hands-helping',
        'fas fa-praying-hands', 'fas fa-book-open', 'fas fa-book-quran', 'fas fa-book',
        'fas fa-headphones', 'fas fa-podcast', 'fas fa-music', 'fas fa-video',
        'fas fa-play-circle', 'fas fa-film', 'fas fa-chalkboard-teacher', 'fas fa-gift',
        'fas fa-tags', 'fas fa-gem', 'fas fa-crown', 'fas fa-star'
    ];

    const iconToggleBtn = document.getElementById('iconToggleBtn');
    const iconDropdown = document.getElementById('iconDropdown');
    const iconGrid = document.getElementById('iconGrid');
    const iconClassInput = document.getElementById('iconClassInput');
    const selectedIcon = document.getElementById('selectedIcon');
    const iconPickerContainer = document.getElementById('iconPickerContainer');

    iconList.forEach(iconClass => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full aspect-square rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-xl text-gray-500 hover:bg-pri-50 hover:text-pri-600 hover:border-pri-200 hover:scale-105 transition-all';
        btn.innerHTML = `<i class="${iconClass}"></i>`;
        
        btn.addEventListener('click', () => {
            iconClassInput.value = iconClass;
            selectedIcon.className = iconClass;
            iconDropdown.classList.add('hidden');
        });
        
        iconGrid.appendChild(btn);
    });

    iconToggleBtn.addEventListener('click', () => {
        iconDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!iconPickerContainer.contains(e.target)) {
            iconDropdown.classList.add('hidden');
        }
    });
});
</script>   