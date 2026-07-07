<?php
// مسار الملف: pages/cms.php
// الوظيفة: عرض أي صفحة تعريفية من جدول cms_pages حسب الـ slug

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$cmsPage = $stmt->fetch();

if (!$cmsPage) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center'>
            <i class='fas fa-file-alt text-6xl text-brk-200 mb-4'></i>
            <h1 class='text-2xl font-bold text-pri-900 mb-2'>الصفحة غير موجودة</h1>
            <a href='index.php' class='btn btn-primary mt-4'>العودة للرئيسية</a>
          </div>";
    return;
}

$pageTitle = $cmsPage['title'];
?>

<div class="max-w-4xl mx-auto px-4 py-12 mb-14 afiu">
    <div class="flex items-center gap-2 text-sm text-brk-400 mb-8">
        <a href="index.php" class="hover:text-pri-600 transition"><i class="fas fa-home"></i> الرئيسية</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <span class="text-pri-900 font-bold"><?= htmlspecialchars($cmsPage['title']) ?></span>
    </div>

    <div class="erp-card p-8 sm:p-12 afiu" style="animation-delay:.1s">
        <h1 class="text-3xl font-black text-pri-900 font-amiri mb-8 border-b-4 border-gld-200 pb-4"><?= htmlspecialchars($cmsPage['title']) ?></h1>
        <div class="prose prose-lg max-w-none text-brk-600 leading-loose">
            <?= $cmsPage['content'] ?>
        </div>
    </div>
</div>