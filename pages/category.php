<?php
// مسار الملف: pages/category.php

$id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($id <= 0) {
    echo "<script>window.location.href='index.php?page=home';</script>"; exit;
}

// 1. جلب بيانات القسم الحالي
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center afiu'>
            <div class='w-24 h-24 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-folder-open'></i></div>
            <h1 class='text-3xl font-bold text-pri-900 mb-3'>القسم غير موجود</h1>
            <a href='index.php?page=home' class='btn btn-primary btn-lg mt-4'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
    return;
}

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
$allIds = getDescendantIds($pdo, $id);
$idsPlaceholder = implode(',', $allIds);

// 2. جلب الأقسام الفرعية
$childrenStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order");
$childrenStmt->execute([$id]);
$subCategories = $childrenStmt->fetchAll();

// 3. مسار التنقل
$breadcrumb = [];
$tempId = $id;
while ($tempId) {
    $s = $pdo->prepare("SELECT id, name, parent_id FROM categories WHERE id = ?");
    $s->execute([$tempId]);
    $c = $s->fetch();
    if (!$c) break;
    array_unshift($breadcrumb, $c);
    $tempId = $c['parent_id'];
}

// 4. المحتوى
$products = $pdo->query("SELECT * FROM products WHERE category_id IN ($idsPlaceholder) AND is_digital = 0 AND is_active = 1 ORDER BY is_featured DESC, id DESC")->fetchAll();
$digitalFiles = $pdo->query("SELECT * FROM products WHERE category_id IN ($idsPlaceholder) AND is_digital = 1 AND is_active = 1 ORDER BY id DESC")->fetchAll();
$audios = $pdo->query("SELECT * FROM audios WHERE category_id IN ($idsPlaceholder) AND is_active = 1 ORDER BY listen_count DESC")->fetchAll();
$videos = $pdo->query("SELECT * FROM videos WHERE category_id IN ($idsPlaceholder) AND is_active = 1 ORDER BY view_count DESC")->fetchAll();

$catColor = $category['color_hex'] ?? '#1a582a';

$defaultTab = 'products';
if (empty($products) && !empty($digitalFiles)) $defaultTab = 'files';
if (empty($products) && empty($digitalFiles) && !empty($audios)) $defaultTab = 'audios';
if (empty($products) && empty($digitalFiles) && empty($audios) && !empty($videos)) $defaultTab = 'videos';

function formatDur($sec) {
    if (!$sec) return '--:--';
    $m = floor($sec / 60); $s = $sec % 60;
    return $m . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">

    <nav class="flex items-center gap-2 text-sm text-brk-400 mb-6 flex-wrap afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition no-underline flex items-center gap-1"><i class="fas fa-home text-xs"></i> الرئيسية</a>
        <?php foreach ($breadcrumb as $i => $bc): ?>
            <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
            <?php if ($i < count($breadcrumb) - 1): ?>
                <a href="index.php?page=category&category_id=<?= $bc['id'] ?>" class="hover:text-pri-600 transition no-underline font-medium"><?= htmlspecialchars($bc['name']) ?></a>
            <?php else: ?>
                <span class="text-pri-900 font-bold"><?= htmlspecialchars($bc['name']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- 1. تعريف القسم (Intro) - تم إزالة htmlspecialchars ليعمل الـ HTML بنجاح -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-10 afiu" style="animation-delay:.05s">
        <div class="p-8 sm:p-12 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r" style="background-image: linear-gradient(to right, <?= $catColor ?>, <?= $catColor ?>88)"></div>
            <div class="absolute -left-20 -top-20 w-64 h-64 rounded-full opacity-5 pointer-events-none" style="background-color: <?= $catColor ?>"></div>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 relative z-10 mb-8 border-b border-gray-100 pb-8">
                <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl flex items-center justify-center text-4xl shadow-lg text-white" style="background: linear-gradient(135deg, <?= $catColor ?>, <?= $catColor ?>dd)">
                    <i class="<?= $category['icon_class'] ?? 'fas fa-folder-open' ?>"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri leading-tight"><?= htmlspecialchars($category['name']) ?></h1>
                </div>
            </div>

            <div class="relative z-10 text-pri-900">
                <?php if (!empty($category['description'])): ?>
                    <div class="custom-html-content">
                        <!-- هنا يتم طباعة الكود كما هو لكي يعمل التصميم -->
                        <?= $category['description'] ?>
                    </div>
                <?php else: ?>
                    <p class="text-brk-400 text-sm">تصفح محتوى هذا القسم من منتجات وصوتيات وفيديوهات.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2. الأقسام الفرعية -->
    <?php if (!empty($subCategories)): ?>
    <div class="mb-12 afiu" style="animation-delay:.1s">
        <h3 class="text-xl font-black text-pri-900 font-amiri mb-5 border-r-4 pr-3 flex items-center gap-2" style="border-color: <?= $catColor ?>">
            تفرعات القسم
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php foreach ($subCategories as $sub): ?>
                <a href="index.php?page=category&category_id=<?= $sub['id'] ?>" class="bg-white rounded-2xl border border-gray-100 p-4 text-center hover:-translate-y-1 hover:shadow-md transition-all duration-300 group no-underline">
                    <div class="w-12 h-12 mx-auto rounded-xl flex items-center justify-center text-xl mb-3 transition-transform group-hover:scale-110" style="background: <?= $sub['color_hex'] ?? $catColor ?>15; color: <?= $sub['color_hex'] ?? $catColor ?>">
                        <i class="<?= $sub['icon_class'] ?? 'fas fa-folder' ?>"></i>
                    </div>
                    <h4 class="font-bold text-pri-900 text-sm mb-1"><?= htmlspecialchars($sub['name']) ?></h4>
                    <p class="text-[10px] text-brk-400 line-clamp-1"><?= htmlspecialchars($sub['short_description'] ?? 'تصفح القسم') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. محتوى القسم (تبويبات) -->
    <div class="mb-8 afiu" style="animation-delay:.15s">
        <div class="flex flex-wrap gap-2 p-1.5 bg-gray-100 rounded-2xl md:inline-flex w-full md:w-auto overflow-x-auto no-sb">
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'products' ? 'on' : '' ?>" onclick="switchCatTab('products', this)">
                <i class="fas fa-box text-xs"></i> المنتجات <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($products) ?></span>
            </button>
            <button class="ct-tab flex-1 md:flex-none justify-center <?= $defaultTab == 'files' ? 'on' : '' ?>" onclick="switchCatTab('files', this)">
                <i class="fas fa-file-pdf text-xs"></i> الملفات والكتب <span class="bg-black/10 px-2 py-0.5 rounded-full text-[10px] mr-1"><?= count($digitalFiles) ?></span>
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
                <p class="text-lg font-bold text-pri-900">لا توجد منتجات ملموسة في هذا القسم</p>
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
                                        <span class="text-brk-300 text-xs line-through ml-1"><?= number_format($prod['old_price'], 2) ?></span>
                                    <?php endif; ?>
                                    <span class="text-pri-700 font-black text-lg"><?= number_format($prod['price'], 2) ?> <span class="text-xs font-bold">ر.س</span></span>
                                </div>
                                <button onclick="event.preventDefault(); addToCart(<?= $prod['id'] ?>, 1)" class="cf-btn cf-btn-pri cf-btn-sm text-xs py-2 px-3"><i class="fas fa-cart-plus"></i></button>
                            </div>
                        </div>
                    </a>
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
                <p class="text-lg font-bold text-pri-900">لا توجد ملفات أو كتب رقمية في هذا القسم</p>
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
                <p class="text-lg font-bold text-pri-900">لا توجد مقاطع صوتية في هذا القسم</p>
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
                        <span class="font-bold text-pri-700 text-sm"><?= $audio['price'] > 0 ? number_format($audio['price'], 0) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></span>
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
                <p class="text-lg font-bold text-pri-900">لا توجد مرئيات في هذا القسم</p>
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
                            <span class="font-black text-pri-600"><?= $video['price'] > 0 ? number_format($video['price'], 0) . ' ر.س' : '<span class="text-green-600 font-bold">مجاني</span>' ?></span>
                            <span class="cf-btn cf-btn-gld cf-btn-sm text-xs !py-1 !px-3">مشاهدة ←</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function switchCatTab(tabName, btn) {
    document.querySelectorAll('.tab-content').forEach(el => { el.classList.add('hidden'); el.classList.remove('block'); });
    document.querySelectorAll('.ct-tab').forEach(el => { el.classList.remove('on'); el.classList.replace('bg-white', 'bg-transparent'); });
    
    const panel = document.getElementById('tab-' + tabName);
    panel.classList.remove('hidden');
    panel.classList.add('block');
    
    btn.classList.add('on', 'bg-white', 'shadow-sm');
}

document.addEventListener('DOMContentLoaded', () => {
    const activeTab = document.querySelector('.ct-tab.on');
    if(activeTab) {
        activeTab.classList.add('bg-white', 'shadow-sm');
    }
});
</script>