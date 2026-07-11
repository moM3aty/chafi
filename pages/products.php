<?php
// مسار الملف: pages/products.php

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

function getDescendantIds($pdo, $catId) {
    $ids = [$catId];
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE parent_id = ? AND is_active = 1");
    $stmt->execute([$catId]);
    $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($children as $childId) {
        $ids = array_merge($ids, getDescendantIds($pdo, $childId));
    }
    return array_unique($ids);
}

$category = null;
if ($categoryId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
}

$allIds = [];
$idsPlaceholder = '';
if ($categoryId > 0 && $category) {
    $allIds = getDescendantIds($pdo, $categoryId);
    $idsPlaceholder = implode(',', $allIds);
}

if ($categoryId > 0 && $category) {
    $childrenStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order");
    $childrenStmt->execute([$categoryId]);
    $displayCategories = $childrenStmt->fetchAll();
} else {
    $childrenStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order");
    $childrenStmt->execute();
    $displayCategories = $childrenStmt->fetchAll();
}

$breadcrumb = [];
if ($categoryId > 0 && $category) {
    $tempId = $categoryId;
    while ($tempId) {
        $s = $pdo->prepare("SELECT id, name, parent_id FROM categories WHERE id = ?");
        $s->execute([$tempId]);
        $c = $s->fetch();
        if (!$c) break;
        array_unshift($breadcrumb, $c);
        $tempId = $c['parent_id'];
    }
}

$showContentTabs = ($categoryId > 0 || !empty($searchQuery));

$products = [];
$packages = []; // تبويب جديد للباقات
$digitalFiles = [];
$audios = [];
$videos = [];

if ($showContentTabs) {
    $paramsProd = [];
    $paramsMedia = [];
    $searchWhereProd = "";
    $searchWherePkg = "";
    $searchWhereAudio = "";
    $searchWhereVideo = "";

    if (!empty($searchQuery)) {
        $searchWhereProd = " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchWherePkg = " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchWhereAudio = " AND (a.title LIKE ? OR a.description LIKE ?)";
        $searchWhereVideo = " AND (v.title LIKE ? OR v.description LIKE ?)";
        $like = "%" . $searchQuery . "%";
        $paramsProd = [$like, $like, $like];
        $paramsMedia = [$like, $like];
    }

    $catConditionP = !empty($idsPlaceholder) ? " AND p.category_id IN ($idsPlaceholder)" : "";
    $catConditionA = !empty($idsPlaceholder) ? " AND a.category_id IN ($idsPlaceholder)" : "";
    $catConditionV = !empty($idsPlaceholder) ? " AND v.category_id IN ($idsPlaceholder)" : "";

    $stmtProd = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 $catConditionP $searchWhereProd AND p.is_digital = 0 ORDER BY p.is_featured DESC, p.id DESC");
    $stmtProd->execute($paramsProd);
    $products = $stmtProd->fetchAll();

    $stmtPkgs = $pdo->prepare("SELECT p.*, c.name as category_name FROM packages p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 $catConditionP $searchWherePkg ORDER BY p.is_featured DESC, p.id DESC");
    $stmtPkgs->execute($paramsProd);
    $packages = $stmtPkgs->fetchAll();

    $stmtFiles = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 $catConditionP $searchWhereProd AND p.is_digital = 1 ORDER BY p.id DESC");
    $stmtFiles->execute($paramsProd);
    $digitalFiles = $stmtFiles->fetchAll();

    $stmtAudios = $pdo->prepare("SELECT a.*, c.name as category_name FROM audios a LEFT JOIN categories c ON a.category_id = c.id WHERE a.is_active = 1 $catConditionA $searchWhereAudio ORDER BY a.listen_count DESC");
    $stmtAudios->execute($paramsMedia);
    $audios = $stmtAudios->fetchAll();

    $stmtVideos = $pdo->prepare("SELECT v.*, c.name as category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.is_active = 1 $catConditionV $searchWhereVideo ORDER BY v.view_count DESC");
    $stmtVideos->execute($paramsMedia);
    $videos = $stmtVideos->fetchAll();
}

$catColor = $category ? ($category['color_hex'] ?? '#1a582a') : '#c8a020';
$catName = $category ? $category['name'] : 'المتجر الشامل';
$catDesc = $category ? $category['description'] : 'تصفح جميع المنتجات والصوتيات والفيديوهات المتوفرة في متجرنا.';
$catIcon = $category ? ($category['icon_class'] ?? 'fas fa-folder-open') : 'fas fa-store';

$defaultTab = 'products';
if (empty($products) && !empty($packages)) $defaultTab = 'packages';
if (empty($products) && empty($packages) && !empty($digitalFiles)) $defaultTab = 'files';
if (empty($products) && empty($packages) && empty($digitalFiles) && !empty($audios)) $defaultTab = 'audios';
if (empty($products) && empty($packages) && empty($digitalFiles) && empty($audios) && !empty($videos)) $defaultTab = 'videos';

function formatDur($sec) {
    if (!$sec) return '--:--';
    $m = floor($sec / 60); $s = $sec % 60;
    return $m . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}

function buildCategoryTree($pdo, $parentId = null) {
    $stmt = $pdo->prepare("SELECT id, name, slug, icon_class, color_hex, parent_id FROM categories WHERE parent_id " . ($parentId === null ? "IS NULL" : "= ?") . " AND is_active = 1 ORDER BY sort_order");
    if ($parentId !== null) $stmt->execute([$parentId]);
    else $stmt->execute();
    $items = $stmt->fetchAll();
    $tree = [];
    foreach ($items as $item) {
        $children = buildCategoryTree($pdo, $item['id']);
        $tree[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'icon' => $item['icon_class'] ?? 'fas fa-folder',
            'color' => $item['color_hex'] ?? '#1a582a',
            'children' => $children
        ];
    }
    return $tree;
}
$categoryTree = buildCategoryTree($pdo);

function renderFilterCategory($cat, $selectedId) {
    $isActive = ($cat['id'] == $selectedId);
    $hasActiveChild = false;
    if (!$isActive) {
        if (isInTree($cat, $selectedId)) $hasActiveChild = true;
    }
    $cls = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition ';
    if ($isActive) {
        $cls .= 'bg-pri-600 text-white shadow-sm ring-2 ring-pri-300 ring-offset-1';
    } elseif ($hasActiveChild) {
        $cls .= 'bg-pri-50 text-pri-700 border-2 border-pri-200 font-extrabold';
    } else {
        $cls .= 'bg-white border border-gray-200 text-brk-500 hover:border-pri-200 hover:text-pri-600';
    }

    echo '<a href="index.php?page=products&category_id=' . $cat['id'] . '" class="' . $cls . ' no-underline flex items-center gap-1.5">';
    echo '<i class="' . ($cat['icon'] ?? 'fas fa-folder') . ' text-[11px]" style="color:' . ($isActive ? '#fff' : $cat['color']) . '"></i>';
    echo '<span>' . htmlspecialchars($cat['name']) . '</span>';
    echo '</a>';

    if (!empty($cat['children'])) {
        echo '<div class="flex flex-wrap items-center gap-1.5 mr-2">';
        foreach ($cat['children'] as $child) {
            renderFilterCategory($child, $selectedId);
        }
        echo '</div>';
    }
}

function isInTree($node, $targetId) {
    if ($node['id'] == $targetId) return true;
    if (!empty($node['children'])) {
        foreach ($node['children'] as $child) {
            if (isInTree($child, $targetId)) return true;
        }
    }
    return false;
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-brk-400 mb-6 flex-wrap afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition no-underline flex items-center gap-1"><i class="fas fa-home text-xs"></i> الرئيسية</a>
        <?php if (!empty($breadcrumb)): ?>
            <?php foreach ($breadcrumb as $i => $bc): ?>
                <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
                <?php if ($i < count($breadcrumb) - 1): ?>
                    <a href="index.php?page=products&category_id=<?= $bc['id'] ?>" class="hover:text-pri-600 transition no-underline font-medium"><?= htmlspecialchars($bc['name']) ?></a>
                <?php else: ?>
                    <span class="text-pri-900 font-bold"><?= htmlspecialchars($bc['name']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
            <span class="text-pri-900 font-bold">المتجر الشامل</span>
        <?php endif; ?>
    </nav>

    <!-- 1. تعريف القسم أو المتجر (Intro) -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-8 afiu" style="animation-delay:.05s">
        <div class="p-8 sm:p-12 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r" style="background-image: linear-gradient(to right, <?= $catColor ?>, <?= $catColor ?>88)"></div>
            <div class="absolute -left-20 -top-20 w-64 h-64 rounded-full opacity-5 pointer-events-none" style="background-color: <?= $catColor ?>"></div>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 relative z-10 mb-8 border-b border-gray-100 pb-8">
                <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl flex items-center justify-center text-4xl shadow-lg text-white" style="background: linear-gradient(135deg, <?= $catColor ?>, <?= $catColor ?>dd)">
                    <i class="<?= $catIcon ?>"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri leading-tight">
                        <?php if (!empty($searchQuery)): ?>
                            نتائج البحث عن: "<?= htmlspecialchars($searchQuery) ?>"
                        <?php else: ?>
                            <?= htmlspecialchars($catName) ?>
                        <?php endif; ?>
                    </h1>
                </div>
            </div>

            <div class="relative z-10 text-pri-900">
                <?php if (!empty($searchQuery)): ?>
                    <p class="text-brk-500 text-sm">تم العثور على <?= count($products) + count($packages) + count($digitalFiles) + count($audios) + count($videos) ?> نتيجة في مختلف الأقسام.</p>
                <?php elseif (!empty($catDesc)): ?>
                    <div class="custom-html-content">
                        <?= $catDesc ?>
                    </div>
                <?php else: ?>
                    <p class="text-brk-400 text-sm">تصفح محتوى هذا القسم من منتجات وصوتيات وفيديوهات.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- شريط البحث والفلترة -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-10 afiu" style="animation-delay:.1s">
        <form action="index.php" method="get" class="flex flex-col sm:flex-row gap-3 mb-5">
            <input type="hidden" name="page" value="products">
            <?php if($categoryId > 0): ?>
                <input type="hidden" name="category_id" value="<?= $categoryId ?>">
            <?php endif; ?>
            <div class="relative flex-1">
                <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-brk-300"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="ابحث بالاسم أو الوصف..." class="form-control !pr-11 !pl-10" dir="rtl">
                <?php if (!empty($searchQuery)): ?>
                    <a href="index.php?page=products<?= $categoryId ? '&category_id='.$categoryId : '' ?>" class="absolute left-3 top-1/2 -translate-y-1/2 text-brk-400 hover:text-red-500 transition"><i class="fas fa-times-circle"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary !py-3 px-8"><i class="fas fa-search"></i> بحث</button>
        </form>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-brk-500 font-bold text-sm flex items-center gap-1.5 shrink-0"><i class="fas fa-filter text-gld-500 text-xs"></i> تصفية الأقسام:</span>
            <a href="index.php?page=products" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= !$categoryId ? 'bg-pri-600 text-white shadow-sm ring-2 ring-pri-300 ring-offset-1' : 'bg-white border border-gray-200 text-brk-500 hover:border-pri-200 hover:text-pri-600' ?>">الرئيسية</a>
            <?php foreach ($categoryTree as $catNode): ?>
                <?php renderFilterCategory($catNode, $categoryId); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2. الأقسام الفرعية (إن وجدت) -->
    <?php if (empty($searchQuery) && !empty($displayCategories)): 
        $gridClass = ($categoryId == 0) ? 'lg:grid-cols-4' : 'lg:grid-cols-5';
    ?>
    <div class="mb-12 afiu" style="animation-delay:.12s">
        <h3 class="text-xl font-black text-pri-900 font-amiri mb-5 border-r-4 pr-3 flex items-center gap-2" style="border-color: <?= $catColor ?>">
            <?= $categoryId > 0 ? 'تفرعات القسم' : 'أقسام المتجر الرئيسية' ?>
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 <?= $gridClass ?> gap-5">
            <?php foreach ($displayCategories as $sub): ?>
                <a href="index.php?page=products&category_id=<?= $sub['id'] ?>" class="bg-white rounded-3xl border border-gray-100 p-6 text-center hover:-translate-y-2 hover:shadow-xl hover:border-pri-200 transition-all duration-300 group no-underline">
                    <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-2xl mb-4 transition-transform group-hover:scale-110 shadow-sm" style="background: <?= $sub['color_hex'] ?? $catColor ?>15; color: <?= $sub['color_hex'] ?? $catColor ?>">
                        <i class="<?= $sub['icon_class'] ?? 'fas fa-folder' ?>"></i>
                    </div>
                    <h4 class="font-black text-pri-900 text-base mb-2 group-hover:text-pri-600 transition-colors"><?= htmlspecialchars($sub['name']) ?></h4>
                    <p class="text-xs text-brk-400 line-clamp-2 leading-relaxed"><?= htmlspecialchars($sub['short_description'] ?? 'تصفح محتوى هذا القسم') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. محتوى المتجر (تبويبات) -->
    <?php if ($showContentTabs): ?>
    <div class="mb-8 afiu" style="animation-delay:.15s">
        <h3 class="text-xl font-black text-pri-900 font-amiri mb-5 border-r-4 pr-3 flex items-center gap-2" style="border-color: <?= $catColor ?>">
            محتويات القسم
        </h3>
        <div class="flex flex-wrap gap-2 p-1.5 bg-gray-100 rounded-2xl md:inline-flex w-full md:w-auto overflow-x-auto no-sb shadow-inner">
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'products' ? 'on' : '' ?>" onclick="switchCatTab('products', this)">
                <i class="fas fa-box text-xs"></i> المنتجات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($products) ?></span>
            </button>
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'packages' ? 'on' : '' ?>" onclick="switchCatTab('packages', this)">
                <i class="fas fa-gift text-xs"></i> الباقات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($packages) ?></span>
            </button>
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'files' ? 'on' : '' ?>" onclick="switchCatTab('files', this)">
                <i class="fas fa-file-pdf text-xs"></i> الملفات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($digitalFiles) ?></span>
            </button>
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'audios' ? 'on' : '' ?>" onclick="switchCatTab('audios', this)">
                <i class="fas fa-headphones text-xs"></i> الصوتيات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($audios) ?></span>
            </button>
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'videos' ? 'on' : '' ?>" onclick="switchCatTab('videos', this)">
                <i class="fas fa-play-circle text-xs"></i> المرئيات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($videos) ?></span>
            </button>
        </div>
    </div>

    <!-- محتوى: المنتجات -->
    <div id="tab-products" class="tab-content <?= $defaultTab == 'products' ? 'block' : 'hidden' ?> afiu" style="animation-delay:.2s">
        <?php if (empty($products)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                <i class="fas fa-box-open text-5xl mb-4 text-brk-200 block opacity-50"></i>
                <p class="text-lg font-bold text-pri-900">لا توجد منتجات ملموسة هنا</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach ($products as $prod):
                    $hasDisc = $prod['old_price'] > $prod['price'];
                    $discPct = $hasDisc ? round((1 - $prod['price'] / $prod['old_price']) * 100) : 0;
                ?>
                <div class="prod-card group">
                    <?php if ($hasDisc): ?><span class="prod-badge offer">خصم <?= $discPct ?>%</span><?php endif; ?>
                    <a href="index.php?page=product_details&id=<?= $prod['id'] ?>" class="block no-underline">
                        <div class="prod-img-w">
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <h4 class="font-bold text-pri-900 text-sm mb-1.5 leading-relaxed line-clamp-2" style="min-height:40px"><?= htmlspecialchars($prod['name']) ?></h4>
                            <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-50">
                                <div>
                                    <?php if ($hasDisc): ?>
                                        <span class="text-brk-300 text-xs line-through ml-1"><?= number_format($prod['old_price'], 2) ?> ر.س</span>
                                    <?php endif; ?>
                                    <span class="text-pri-700 font-black text-lg"><?= number_format($prod['price'], 2) ?> <span class="text-xs font-bold">ر.س</span></span>
                                </div>
                                <button onclick="event.preventDefault(); addToCart('product', <?= $prod['id'] ?>)" class="cf-btn cf-btn-pri cf-btn-sm text-xs py-2 px-3"><i class="fas fa-cart-plus"></i></button>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- محتوى: الباقات -->
    <div id="tab-packages" class="tab-content <?= $defaultTab == 'packages' ? 'block' : 'hidden' ?> afiu" style="animation-delay:.2s">
        <?php if (empty($packages)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                <i class="fas fa-gift text-5xl mb-4 text-brk-200 block opacity-50"></i>
                <p class="text-lg font-bold text-pri-900">لا توجد باقات وعروض في هذا القسم</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($packages as $p): 
                    $savings = $p['original_total_price'] > 0 ? round((($p['original_total_price'] - $p['package_price']) / $p['original_total_price']) * 100) : 0;
                ?>
                    <div class="pkg-card <?= $p['is_featured'] ? 'feat' : '' ?>">
                        <?php if ($p['is_featured']): ?>
                            <div class="pkg-feat-badge">الأكثر طلباً</div>
                        <?php endif; ?>
                        <div class="pkg-hd">
                            <img src="<?= htmlspecialchars($p['image_url'] ?? "https://picsum.photos/400") ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-24 h-24 rounded-2xl mx-auto mb-4 object-cover <?= !$p['is_featured'] ? 'border-2 border-gray-100' : '' ?>" loading="lazy">
                            <h3 class="text-xl font-black <?= $p['is_featured'] ? 'text-white' : 'text-pri-900' ?>"><?= htmlspecialchars($p['name']) ?></h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-center gap-3 mb-4">
                                <span class="pkg-save"><i class="fas fa-bolt text-[10px]"></i> وفّر <?= $savings ?>%</span>
                                <span class="line-through text-brk-300 text-sm"><?= number_format($p['original_total_price'], 2) ?> ر.س</span>
                            </div>
                            <div class="text-center mb-6">
                                <span class="text-4xl font-black text-pri-700"><?= number_format($p['package_price'], 2) ?></span>
                                <span class="text-brk-400 text-sm mr-1">ر.س</span>
                            </div>
                            <a href="index.php?page=package_details&id=<?= $p['id'] ?>" class="btn <?= $p['is_featured'] ? 'btn-gold' : 'btn-primary' ?> btn-block">
                                تفاصيل الباقة <i class="fas fa-arrow-left text-xs mr-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- محتوى: الملفات الرقمية والكتب -->
    <div id="tab-files" class="tab-content <?= $defaultTab == 'files' ? 'block' : 'hidden' ?> afiu" style="animation-delay:.2s">
        <?php if (empty($digitalFiles)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                <i class="fas fa-file-pdf text-5xl mb-4 text-brk-200 block opacity-50"></i>
                <p class="text-lg font-bold text-pri-900">لا توجد ملفات أو كتب رقمية هنا</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($digitalFiles as $file): ?>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md hover:border-blue-200 transition-all group">
                    <div class="w-16 h-16 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-3xl shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-pri-900 text-sm truncate mb-1"><?= htmlspecialchars($file['name']) ?></h4>
                        <div class="text-xs text-brk-400 mb-2 truncate"><?= htmlspecialchars($file['short_description'] ?? 'ملف رقمي قابل للتنزيل') ?></div>
                        <div class="flex items-center justify-between">
                            <span class="font-black text-pri-700 text-sm"><?= $file['price'] > 0 ? number_format($file['price'], 2) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></span>
                            <a href="index.php?page=product_details&id=<?= $file['id'] ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg transition no-underline">
                                <i class="fas fa-download ml-1"></i> تحميل
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- محتوى: الصوتيات -->
    <div id="tab-audios" class="tab-content <?= $defaultTab == 'audios' ? 'block' : 'hidden' ?> afiu" style="animation-delay:.2s">
        <?php if (empty($audios)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                <i class="fas fa-volume-mute text-5xl mb-4 text-brk-200 block opacity-50"></i>
                <p class="text-lg font-bold text-pri-900">لا توجد مقاطع صوتية هنا</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($audios as $audio): ?>
                <a href="index.php?page=audio_details&id=<?= $audio['id'] ?>" class="aud-card bg-white no-underline group">
                    <button class="aud-play group-hover:scale-110" onclick="event.preventDefault()"><i class="fas fa-play" style="margin-right:-2px"></i></button>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-pri-900 text-sm mb-1 truncate group-hover:text-pri-600 transition"><?= htmlspecialchars($audio['title']) ?></h4>
                        <div class="flex items-center gap-3 text-brk-400 text-xs">
                            <span><i class="fas fa-user-circle text-[10px] ml-0.5"></i><?= htmlspecialchars($audio['narrator'] ?? 'غير محدد') ?></span>
                            <?php if ($audio['audio_duration']): ?>
                                <span><i class="fas fa-clock text-[10px] ml-0.5"></i><?= formatDur($audio['audio_duration']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="font-bold text-pri-700 text-sm"><?= $audio['price'] > 0 ? number_format($audio['price'], 2) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></span>
                        <span class="cf-btn cf-btn-out cf-btn-sm text-[10px] !py-1 !px-3">استماع ←</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- محتوى: المرئيات -->
    <div id="tab-videos" class="tab-content <?= $defaultTab == 'videos' ? 'block' : 'hidden' ?> afiu" style="animation-delay:.2s">
        <?php if (empty($videos)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                <i class="fas fa-video-slash text-5xl mb-4 text-brk-200 block opacity-50"></i>
                <p class="text-lg font-bold text-pri-900">لا توجد مرئيات هنا</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($videos as $video): ?>
                <a href="index.php?page=video_details&id=<?= $video['id'] ?>" class="vid-card bg-white no-underline group border border-gray-100">
                    <div class="vid-thumb h-48">
                        <img src="<?= htmlspecialchars($video['thumbnail_url'] ?? 'https://picsum.photos/400/225') ?>" alt="" class="w-full h-full object-cover" loading="lazy">
                        <div class="vid-ov">
                            <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center text-pri-600 text-sm shadow group-hover:scale-110 transition-transform"><i class="fas fa-play ml-0.5"></i></div>
                        </div>
                        <?php if ($video['video_duration']): ?>
                            <span class="absolute bottom-2 left-2 bg-black/70 text-white text-[10px] px-2 py-1 rounded-lg backdrop-blur-sm"><?= formatDur($video['video_duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 bg-white">
                        <h4 class="font-bold text-pri-900 text-sm mb-2 line-clamp-2" style="min-height:40px"><?= htmlspecialchars($video['title']) ?></h4>
                        <div class="flex items-center gap-3 text-brk-400 text-xs mb-3">
                            <span><i class="fas fa-chalkboard-teacher text-[10px] ml-0.5"></i><?= htmlspecialchars($video['presenter'] ?? 'غير محدد') ?></span>
                            <span><i class="fas fa-eye text-[10px] ml-0.5"></i><?= $video['view_count'] ?></span>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                            <span class="font-black text-pri-600"><?= $video['price'] > 0 ? number_format($video['price'], 2) . ' ر.س' : '<span class="text-green-600 font-bold">مجاني</span>' ?></span>
                            <span class="cf-btn cf-btn-gld cf-btn-sm text-xs !py-1 !px-3">مشاهدة ←</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<script>
function switchCatTab(tabName, btn) {
    document.querySelectorAll('.tab-content').forEach(el => { el.classList.add('hidden'); el.classList.remove('block'); });
    document.querySelectorAll('.ct-tab').forEach(el => { el.classList.remove('on'); el.classList.replace('bg-white', 'bg-transparent'); });
    
    const panel = document.getElementById('tab-' + tabName);
    if(panel) {
        panel.classList.remove('hidden');
        panel.classList.add('block');
    }
    
    btn.classList.add('on', 'bg-white', 'shadow-sm');
}

document.addEventListener('DOMContentLoaded', () => {
    const activeTab = document.querySelector('.ct-tab.on');
    if(activeTab) {
        activeTab.classList.add('bg-white', 'shadow-sm');
    }
});
</script>