<?php
// مسار الملف: pages/video_details.php
// النسخة الكاملة — مشغل فيديو محمي + إضافة للسلة + حجز موعد

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT v.*, c.name as category_name, c.color_hex as cat_color FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ? AND v.is_active = 1");
$stmt->execute([$id]);
$video = $stmt->fetch();

if (!$video) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center afiu'>
            <div class='w-28 h-28 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-video-slash'></i></div>
            <h1 class='text-3xl font-bold text-pri-900 mb-3'>الفيديو غير موجود</h1>
            <a href='index.php?page=home' class='btn btn-primary btn-lg mt-4'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
    return;
}

// زيادة المشاهدات
$pdo->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);

// جلب الفيديوهات المشابهة
$relatedVideos = $pdo->prepare("
    SELECT v.*, c.name as category_name 
    FROM videos v 
    LEFT JOIN categories c ON v.category_id = c.id 
    WHERE v.is_active = 1 AND v.id != ? 
      AND (v.category_id = ? OR v.category_id IN (SELECT id FROM categories WHERE parent_id = ?))
    ORDER BY v.view_count DESC LIMIT 6
");
$relatedVideos->execute([$id, $video['category_id'], $video['category_id']]);
$related = $relatedVideos->fetchAll();

// مسار الأقسام
function getCatChain($pdo, $catId) {
    $names = [];
    while ($catId) {
        $s = $pdo->prepare("SELECT name, parent_id, icon_class FROM categories WHERE id = ? AND is_active = 1");
        $s->execute([$catId]);
        $c = $s->fetch();
        if (!$c) break;
        array_unshift($names, $c['name']);
        $catId = $c['parent_id'];
    }
    return $names;
}
$catChain = getCatChain($pdo, $video['category_id']);

function formatDur($sec) {
    if (!$sec) return '--:--';
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    $s = $sec % 60;
    if ($h > 0) return $h . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
    return str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}

function getYoutubeEmbed($url) {
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        if (!empty($m[1])) {
            return '<iframe src="https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1&rel=0&modestbranding=1" style="width:100%;aspect-ratio:16/9;border:none;border-radius:16px;" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"></iframe>';
        }
    }
    if (preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
        return '<video src="' . htmlspecialchars($url) . '" controls style="width:100%;border-radius:16px;max-height:500px;" controlslist="nodownload"></video>';
    }
    return '<div class="flex items-center justify-center h-64 bg-gray-900 rounded-2xl"><i class="fas fa-video-slash text-4xl text-gray-600"></i></div>';
}

$catColor = $video['cat_color'] ?? '#5a463c';
// التصحيح: الاعتماد فقط على السعر، إذا كان صفر فهو مجاني، وإذا كان أكبر فهو مدفوع.
$isFree = (float)$video['price'] <= 0;

// ════════ نظام حماية المحتوى الرقمي ════════
$hasPurchased = false;
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

if (isset($_SESSION['user_id'])) {
    try {
        $stmtCheck = $pdo->prepare("
            SELECT oi.id 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = ? 
              AND oi.item_type = 'video' 
              AND oi.item_id = ? 
              AND o.status NOT IN ('Pending', 'Cancelled', 'Refunded', 'Failed')
        ");
        $stmtCheck->execute([$_SESSION['user_id'], $id]);
        if ($stmtCheck->fetch()) {
            $hasPurchased = true;
        }
    } catch(Exception $e) {}
}

$canView = $isFree || $hasPurchased || $isAdmin;
// ══════════════════════════════════════════
?>

<div class="max-w-6xl mx-auto px-4 py-8 mb-14">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-brk-400 mb-8 flex-wrap afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition no-underline flex items-center gap-1"><i class="fas fa-home text-xs"></i> الرئيسية</a>
        <?php foreach ($catChain as $i => $cn): ?>
            <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
            <?php if ($i < count($catChain) - 1): ?>
                <a href="index.php?page=category&category_id=<?= $video['category_id'] ?>" class="hover:text-pri-600 transition no-underline"><?= htmlspecialchars($cn) ?></a>
            <?php else: ?>
                <span class="text-pri-900 font-bold"><?= htmlspecialchars($cn) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
        <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
        <span class="text-pri-900 font-bold truncate max-w-[200px]"><?= htmlspecialchars($video['title']) ?></span>
    </nav>

    <div class="bg-white rounded-3xl border-2 border-gray-100 overflow-hidden shadow-sm afiu" style="animation-delay:.1s">
        
        <!-- مشغل الفيديو -->
        <div class="relative bg-gray-900 aspect-video max-h-[520px]">
            <?php if ($canView): ?>
                <?php if (!empty($video['video_url'])): ?>
                    <div class="w-full h-full" id="videoContainer">
                        <?= getYoutubeEmbed($video['video_url']) ?>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-center h-full bg-gray-900">
                        <i class="fas fa-video-slash text-5xl text-gray-700 opacity-40"></i>
                    </div>
                <?php endif; ?>
                <!-- غطاء فيديو أثناء التشغيل -->
                <div id="videoOverlay" class="absolute inset-0 bg-black/0 hover:bg-black/40 transition-all duration-300 flex items-center justify-center cursor-pointer z-10" onclick="toggleVideoPlay()">
                    <div id="playBtnBig" class="w-20 h-20 rounded-full bg-white/95 flex items-center justify-center text-2xl shadow-2xl transition-all duration-300 hover:scale-110" style="color:<?= $catColor ?>">
                        <i class="fas fa-play ml-1"></i>
                    </div>
                </div>
            <?php else: ?>
                <!-- فيديو مقفل -->
                <img src="<?= htmlspecialchars($video['thumbnail_url'] ?? 'https://picsum.photos/1280/720') ?>" class="absolute inset-0 w-full h-full object-cover opacity-30 blur-sm">
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 backdrop-blur-sm z-10 text-center px-4">
                    <div class="w-20 h-20 rounded-full bg-gld-500/20 text-gld-500 flex items-center justify-center text-4xl mb-4 border-2 border-gld-500/50 shadow-[0_0_30px_rgba(200,160,32,0.4)]">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="text-white font-black font-amiri text-2xl sm:text-3xl mb-2 drop-shadow-md">هذا الفيديو حصري ومدفوع</h3>
                    <p class="text-gray-300 text-sm max-w-md">قم بشراء الفيديو لتتمكن من مشاهدته كاملاً، سيتم فتح المشغل فور إتمام الطلب.</p>
                </div>
            <?php endif; ?>
            
            <!-- غلاف معلومات فوق الفيديو -->
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-5 pt-16 z-20 pointer-events-none">
                <div class="max-w-2xl">
                    <h2 class="text-xl sm:text-2xl font-black text-white font-amiri mb-2 leading-tight drop-shadow-lg"><?= htmlspecialchars($video['title']) ?></h2>
                    <div class="flex flex-wrap items-center gap-3 text-white/60 text-sm">
                        <?php if (!empty($video['presenter'])): ?>
                            <span class="flex items-center gap-1.5"><i class="fas fa-chalkboard-teacher text-gld-400 text-xs"></i> <?= htmlspecialchars($video['presenter']) ?></span>
                        <?php endif; ?>
                        <?php if ($video['video_duration']): ?>
                            <span class="flex items-center gap-1.5"><i class="fas fa-clock text-gld-400 text-xs"></i> <?= formatDur($video['video_duration']) ?></span>
                        <?php endif; ?>
                        <span class="flex items-center gap-1.5"><i class="fas fa-eye text-gld-400 text-xs"></i> <?= number_format($video['view_count'] + 1) ?> مشاهدة</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات تفصيلية للفيديو -->
        <div class="p-6 sm:p-8 flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-3/5">
                <div class="grid grid-cols-2 gap-4 mb-6 p-5 bg-gray-50 rounded-2xl border border-gray-100">
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">المقدم / الشيخ</div>
                        <div class="font-bold text-pri-900 text-sm"><?= htmlspecialchars($video['presenter'] ?? 'غير محدد') ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">المدة</div>
                        <div class="font-bold text-pri-900 text-sm" dir="ltr"><?= formatDur($video['video_duration']) ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">المشاهدات</div>
                        <div class="font-bold text-pri-900 text-sm"><?= number_format($video['view_count'] + 1) ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">الحالة</div>
                        <div class="font-bold text-green-600 text-sm flex items-center gap-1"><i class="fas fa-check-circle text-xs"></i> متاح</div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-pri-800 mb-3 flex items-center gap-2"><i class="fas fa-info-circle text-gld-500 text-xs"></i> وصف الفيديو</h3>
                    <div class="prose prose-sm text-brk-600 leading-loose bg-white border border-gray-100 rounded-2xl p-5">
                        <?php if (!empty($video['description'])): ?>
                            <?= nl2br($video['description']) ?>
                        <?php else: ?>
                            <p>فيديو تعليمي وتوضيحي يخص الرقية الشرعية. يتميز بمحتوى موثوق ومعتمد من علماء الأمة.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- زر الإضافة للسلة -->
                <div class="flex flex-wrap gap-3 mt-6">
                    <?php if (!$hasPurchased && !$isFree && !$isAdmin): ?>
                        <button onclick="addToCart('video', <?= $video['id'] ?>)" class="cf-btn cf-btn-gld flex-1 h-14 text-base">
                            <i class="fas fa-cart-arrow-down"></i> شراء الفيديو (<?= number_format($video['price'], 2) ?> ر.س)
                        </button>
                    <?php else: ?>
                        <div class="flex-1 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center justify-center font-bold text-sm h-14">
                            <i class="fas fa-check-circle ml-2"></i> <?= $isFree ? 'هذا الفيديو مجاني' : 'تم شراء هذا الفيديو مسبقاً' ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- بانر حجز الجلسة (أونلاين) -->
                <div class="mt-8 bg-gradient-to-r from-pri-50 to-white border-2 border-pri-100 rounded-3xl p-6 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-full bg-gld-100 text-gld-600 flex items-center justify-center text-2xl shrink-0"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-pri-900 font-amiri mb-1">احجز موعد لجلسة أونلاين</h3>
                            <p class="text-xs text-brk-500">جلسة تشخيص ورقية مباشرة عبر الإنترنت.</p>
                        </div>
                    </div>
                    <a href="index.php?page=book_appointment" class="btn btn-gold shrink-0 shadow-md hover:scale-105 transition-transform">
                        احجز موعدك <i class="fas fa-arrow-left mr-2"></i>
                    </a>
                </div>

            </div>

            <!-- الفيديوهات المشابهة -->
            <?php if (!empty($related)): ?>
            <div class="w-full lg:w-2/5">
                <h3 class="text-base font-black text-pri-900 font-amiri mb-5 flex items-center gap-2 border-b-2 border-pri-100 pb-3">
                    <i class="fas fa-film text-pri-500"></i> فيديوهات مشابهة
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($related as $rv): ?>
                    <a href="index.php?page=video_details&id=<?= $rv['id'] ?>" class="vid-card bg-white no-underline group">
                        <div class="vid-thumb h-36">
                            <img src="<?= htmlspecialchars($rv['thumbnail_url'] ?? 'https://picsum.photos/400/225') ?>" alt="" class="w-full h-full object-cover" loading="lazy">
                            <div class="vid-ov">
                                <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-pri-600 text-xs shadow group-hover:scale-110 transition-transform">
                                    <i class="fas <?= ((float)$rv['price'] > 0) ? 'fa-lock' : 'fa-play' ?> ml-0.5"></i>
                                </div>
                            </div>
                            <?php if ($rv['video_duration']): ?>
                                <span class="absolute bottom-1.5 left-1.5 bg-black/70 text-white text-[8px] px-1.5 py-0.5 rounded"><?= formatDur($rv['video_duration']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <h4 class="font-bold text-pri-900 text-xs mb-1 line-clamp-2"><?= htmlspecialchars($rv['title']) ?></h4>
                            <div class="flex items-center justify-between mt-2">
                                <span class="font-black text-pri-600 text-xs"><?= (float)$rv['price'] > 0 ? number_format($rv['price'], 0) . ' ر.س' : '<span class="text-green-600 text-xs font-bold">مجاني</span>' ?></span>
                                <span class="text-[9px] text-brk-400"><i class="fas fa-eye ml-0.5"></i><?= $rv['view_count'] ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const videoContainer = document.getElementById('videoContainer');
const videoOverlay = document.getElementById('videoOverlay');
const playBtnBig = document.getElementById('playBtnBig');
let isPlaying = false;

function getYouTubeIframe() {
    if (!videoContainer) return null;
    const iframe = videoContainer.querySelector('iframe');
    return iframe;
}

function toggleVideoPlay() {
    const iframe = getYouTubeIframe();
    if (!iframe) return;

    if (!isPlaying) {
        iframe.contentWindow.postMessage('{"event":"command","func":"playVideo"}', '*');
        playBtnBig.innerHTML = '<i class="fas fa-pause"></i>';
        playBtnBig.style.color = '#fff';
        isPlaying = true;
        videoOverlay.style.backgroundColor = 'rgba(0,0,0,0)';
    } else {
        iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo"}', '*');
        playBtnBig.innerHTML = '<i class="fas fa-play" style="margin-left:2px"></i>';
        isPlaying = false;
        videoOverlay.style.backgroundColor = 'rgba(0,0,0,0.4)';
    }
}

if (videoOverlay) {
    videoOverlay.addEventListener('click', function(e) {
        if (e.target === this) toggleVideoPlay();
    });
}
</script>