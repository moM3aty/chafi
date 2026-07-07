<?php
// مسار الملف: pages/search.php
// النسخة الاحترافية — بحث شامل في كل المحتوى (تم إصلاح الاستعلامات)

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$products = $audios = $videos = [];

if (!empty($q)) {
    $like = "%" . strtolower($q) . "%";

    $stmtP = $pdo->prepare("SELECT p.*, c.name as category_name, c.color_hex as cat_color FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND (LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(p.short_description) LIKE ?) ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 20");
    $stmtP->execute([$like, $like, $like]);
    $products = $stmtP->fetchAll();

    $stmtA = $pdo->prepare("SELECT a.*, c.name as category_name, c.color_hex as cat_color FROM audios a LEFT JOIN categories c ON a.category_id = c.id WHERE a.is_active = 1 AND (LOWER(a.title) LIKE ? OR LOWER(a.narrator) LIKE ? OR LOWER(a.description) LIKE ?) ORDER BY a.listen_count DESC LIMIT 12");
    $stmtA->execute([$like, $like, $like]);
    $audios = $stmtA->fetchAll();

    $stmtV = $pdo->prepare("SELECT v.*, c.name as category_name, c.color_hex as cat_color FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.is_active = 1 AND (LOWER(v.title) LIKE ? OR LOWER(v.presenter) LIKE ? OR LOWER(v.description) LIKE ?) ORDER BY v.view_count DESC LIMIT 12");
    $stmtV->execute([$like, $like, $like]);
    $videos = $stmtV->fetchAll();
}

$totalResults = count($products) + count($audios) + count($videos);

function formatDur($sec) {
    if (!$sec) return '--:--';
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    $s = $sec % 60;
    if ($h > 0) return $h . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
    return str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">

    <!-- رأس البحث -->
    <div class="sec-hd-premium mb-10 afiu" style="animation-delay:.05s">
        <div class="relative z-10 text-center">
            <h1 class="text-3xl sm:text-4xl font-black text-white font-amiri leading-tight mb-3">نتائج البحث</h1>
            <p class="text-white/70 text-base max-w-2xl mx-auto">
                <?php if (!empty($q)): ?>
                    عن "<span class='text-gld-300 font-bold'><?= htmlspecialchars($q) ?></span>" — تم العثور على <?= $totalResults ?> نتيجة
                <?php else: ?>
                    ابحث في المنتجات والصوتيات والفيديوهات
                <?php endif; ?>
            </p>
        </div>
    </div>

    <?php if ($totalResults == 0): ?>
    <div class="bg-white rounded-3xl border-2 border-gray-100 p-16 text-center shadow-sm afiu" style="animation-delay:.1s">
        <div class="w-24 h-24 bg-gray-50 text-brk-300 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-search text-5xl opacity-30"></i>
        </div>
        <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد نتائج</h3>
        <p class="text-brk-400">جرب البحث بكلمات أخرى أو تأكد من الإملاء الصحيح</p>
        <a href="index.php?page=home" class="btn btn-primary btn-lg mt-4"><i class="fas fa-home"></i> العودة للرئيسية</a>
    </div>
    <?php else: ?>

    <!-- تبويبات -->
    <div class="mb-8 afiu" style="animation-delay:.1s">
        <div class="ct-tabs max-w-lg mx-auto sm:mx-0">
            <button class="ct-tab <?= empty($products) ? '' : 'on' ?>" onclick="switchSearchTab('products', this)">
                <i class="fas fa-box text-xs"></i> منتجات (<?= count($products) ?>)
            </button>
            <button class="ct-tab <?= empty($audios) && !empty($products) ? '' : (empty($products) && !empty($audios) ? 'on' : '') ?>" onclick="switchSearchTab('audios', this)">
                <i class="fas fa-headphones text-xs"></i> صوتيات (<?= count($audios) ?>)
            </button>
            <button class="ct-tab <?= empty($products) && empty($audios) && !empty($videos) ? 'on' : '' ?>" onclick="switchSearchTab('videos', this)">
                <i class="fas fa-play-circle text-xs"></i> فيديوهات (<?= count($videos) ?>)
            </button>
        </div>
    </div>

    <!-- تبويب المنتجات -->
    <div id="stab-products" class="tab-content <?= empty($products) ? 'hidden' : 'block' ?> afiu" style="animation-delay:.15s">
        <?php if (!empty($products)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach ($products as $prod):
                    $hasDisc = $prod['old_price'] > $prod['price'];
                    $discPct = $hasDisc ? round((1 - $prod['price'] / $prod['old_price']) * 100) : 0;
                ?>
                <div class="prod-card">
                    <?php if ($hasDisc): ?>
                        <span class="prod-badge offer">خصم <?= $discPct ?>%</span>
                    <?php endif; ?>
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
                                <button onclick="event.preventDefault(); addToCart(<?= $prod['id'] ?>, 1)" class="cf-btn cf-btn-pri cf-btn-sm text-xs py-2 px-3"><i class="fas fa-cart-plus"></i></button>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- تبويب الصوتيات -->
    <div id="stab-audios" class="tab-content <?= empty($audios) || !empty($products) ? 'hidden' : 'block' ?> afiu" style="animation-delay:.15s">
        <?php if (!empty($audios)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($audios as $audio): ?>
                <a href="index.php?page=audio_details&id=<?= $audio['id'] ?>" class="aud-card bg-white no-underline group">
                    <button class="aud-play" onclick="event.preventDefault()" style="color:#fff;background:linear-gradient(135deg, <?= $audio['cat_color'] ?? '#c8a020' ?>, <?= $audio['cat_color'] ?? '#0e2f18' ?>)"><i class="fas fa-play" style="margin-right:-2px"></i></button>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-pri-900 text-sm truncate group-hover:text-pri-600 transition"><?= htmlspecialchars($audio['title']) ?></h4>
                        <div class="flex items-center gap-3 text-brk-400 text-xs mt-1">
                            <span><i class="fas fa-user-circle text-[10px] ml-0.5"></i><?= htmlspecialchars($audio['narrator'] ?? 'غير محدد') ?></span>
                            <span><i class="fas fa-headphones text-[10px] ml-0.5"></i><?= $audio['listen_count'] ?> استماع</span>
                        </div>
                    </div>
                    <span class="font-bold text-pri-700 text-sm whitespace-nowrap shrink-0"><?= $audio['price'] > 0 ? number_format($audio['price'], 0) . ' ر.س' : '<span class="text-green-600 font-bold">مجاني</span>' ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- تبويب الفيديوهات -->
    <div id="stab-videos" class="tab-content <?= (empty($products) && empty($audios) && !empty($videos)) ? 'block' : 'hidden' ?> afiu" style="animation-delay:.15s">
        <?php if (!empty($videos)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($videos as $video): ?>
                <a href="index.php?page=video_details&id=<?= $video['id'] ?>" class="vid-card bg-white no-underline group">
                    <div class="vid-thumb h-44">
                        <img src="<?= htmlspecialchars($video['thumbnail_url'] ?? 'https://picsum.photos/400/225') ?>" alt="" class="w-full h-full object-cover" loading="lazy">
                        <div class="vid-ov">
                            <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center text-pri-600 text-sm shadow group-hover:scale-110 transition-transform"><i class="fas fa-play ml-0.5"></i></div>
                        </div>
                        <?php if ($video['video_duration']): ?>
                            <span class="absolute bottom-2 left-2 bg-black/70 text-white text-[10px] px-2 py-1 rounded-lg backdrop-blur-sm"><?= formatDur($video['video_duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h4 class="font-bold text-pri-900 text-xs mb-1 line-clamp-2"><?= htmlspecialchars($video['title']) ?></h4>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-black text-pri-600 text-sm"><?= $video['price'] > 0 ? number_format($video['price'], 0) . ' ر.س' : '<span class="text-green-600 font-bold">مجاني</span>' ?></span>
                            <span class="text-[9px] text-brk-400"><i class="fas fa-eye ml-0.5"></i><?= $video['view_count'] ?></span>
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
// تبديل تبويبات البحث
function switchSearchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => { el.classList.add('hidden'); el.classList.remove('block'); });
    document.querySelectorAll('.ct-tab').forEach(el => el.classList.remove('on'));
    document.getElementById('stab-' + tab).classList.remove('hidden');
    document.getElementById('stab-' + tab).classList.add('block');
    document.getElementById('stab-' + tab).style.animation = 'tab-fade .35s ease-out';
    btn.classList.add('on');
}
</script>