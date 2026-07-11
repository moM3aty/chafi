<?php
// مسار الملف: pages/audio_details.php
// النسخة الكاملة — مشغل صوتي محمي + إضافة للسلة + حجز موعد

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.color_hex as cat_color FROM audios a LEFT JOIN categories c ON a.category_id = c.id WHERE a.id = ? AND a.is_active = 1");
$stmt->execute([$id]);
$audio = $stmt->fetch();

if (!$audio) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center afiu'>
            <div class='w-28 h-28 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-headphones-slash'></i></div>
            <h1 class='text-3xl font-bold text-pri-900 mb-3'>المقطع الصوتي غير موجود</h1>
            <a href='index.php?page=home' class='btn btn-primary btn-lg mt-4'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
    return;
}

// زيادة عدد الاستماعات
$pdo->prepare("UPDATE audios SET listen_count = listen_count + 1 WHERE id = ?")->execute([$id]);

// جلب الصوتيات المشابهة
$relatedAudios = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM audios a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.is_active = 1 AND a.id != ? 
      AND (a.category_id = ? OR a.category_id IN (SELECT id FROM categories WHERE parent_id = ?))
    ORDER BY a.listen_count DESC LIMIT 6
");
$relatedAudios->execute([$id, $audio['category_id'], $audio['category_id']]);
$related = $relatedAudios->fetchAll();

// جلب مسار القسم
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
$catChain = getCatChain($pdo, $audio['category_id']);

function formatDur($sec) {
    if (!$sec) return '--:--';
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    $s = $sec % 60;
    if ($h > 0) return $h . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
    return str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}

function getAudioEmbed($url) {
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        if (!empty($m[1])) {
            return '<iframe src="https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1&rel=0&modestbranding=1" style="width:100%;height:120px;border:none;border-radius:12px;" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"></iframe>';
        }
    }
    return '<audio id="audioPlayer" controls preload="metadata" controlsList="nodownload" style="width:100%; height:52px; border-radius:12px; outline:none;"><source src="'.htmlspecialchars($url).'" type="audio/mpeg">متصفحك لا يدعم عنصر الصوت</audio>';
}

$catColor = $audio['cat_color'] ?? '#c8a020';
// التصحيح: الاعتماد فقط على السعر، إذا كان صفر فهو مجاني، وإذا كان أكبر فهو مدفوع.
$isFree = (float)$audio['price'] <= 0;

// ════════ نظام حماية المحتوى الرقمي ════════
$hasPurchased = false;
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

if (isset($_SESSION['user_id'])) {
    // التحقق هل العميل اشترى هذا المقطع وحالة الطلب مكتملة أو قيد التجهيز
    try {
        $stmtCheck = $pdo->prepare("
            SELECT oi.id 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = ? 
              AND oi.item_type = 'audio' 
              AND oi.item_id = ? 
              AND o.status NOT IN ('Pending', 'Cancelled', 'Refunded', 'Failed')
        ");
        $stmtCheck->execute([$_SESSION['user_id'], $id]);
        if ($stmtCheck->fetch()) {
            $hasPurchased = true;
        }
    } catch(Exception $e) {}
}

// يمكنه المشاهدة إذا كان: مجاني، أو اشتراه، أو هو مدير
$canView = $isFree || $hasPurchased || $isAdmin;
// ══════════════════════════════════════════
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-brk-400 mb-8 flex-wrap afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition no-underline flex items-center gap-1"><i class="fas fa-home text-xs"></i> الرئيسية</a>
        <?php foreach ($catChain as $i => $cn): ?>
            <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
            <?php if ($i < count($catChain) - 1): ?>
                <a href="index.php?page=category&category_id=<?= $audio['category_id'] ?>" class="hover:text-pri-600 transition no-underline"><?= htmlspecialchars($cn) ?></a>
            <?php else: ?>
                <span class="text-pri-900 font-bold"><?= htmlspecialchars($cn) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
        <i class="fas fa-chevron-left text-[9px] text-brk-300"></i>
        <span class="text-pri-900 font-bold truncate max-w-[200px]"><?= htmlspecialchars($audio['title']) ?></span>
    </nav>

    <!-- البطاقة الرئيسية -->
    <div class="bg-white rounded-3xl border-2 border-gray-100 overflow-hidden shadow-sm afiu" style="animation-delay:.1s">
        
        <!-- رأس الصورة -->
        <div class="relative h-56 sm:h-72 bg-gradient-to-br" style="background:linear-gradient(135deg, <?= $catColor ?>dd, <?= $catColor ?>88, <?= $catColor ?>55)">
            <div class="absolute inset-0 flex items-center justify-center z-10">
                <?php if (!empty($audio['thumbnail_url'])): ?>
                    <img src="<?= htmlspecialchars($audio['thumbnail_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30 blur-sm">
                <?php endif; ?>
                
                <div class="relative z-10">
                    <div class="w-40 h-40 sm:w-52 sm:h-52 rounded-full bg-white/10 backdrop-blur-xl border-2 border-white/20 flex items-center justify-center relative">
                        <div class="absolute inset-0 rounded-full border-2 border-white/15 audio-pulse-ring"></div>
                        <?php if($canView): ?>
                            <i class="fas fa-headphones-alt text-white text-5xl sm:text-6xl drop-shadow-md"></i>
                        <?php else: ?>
                            <i class="fas fa-lock text-white text-5xl sm:text-6xl drop-shadow-md opacity-80"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- محتوى المعلومات -->
        <div class="p-6 sm:p-10 flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-3/5">
                <h1 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri mb-3 leading-tight"><?= htmlspecialchars($audio['title']) ?></h1>
                
                <!-- البيانات المنسقة كما طلبت -->
                <div class="grid grid-cols-2 gap-4 mb-6 p-5 bg-gray-50 rounded-2xl border border-gray-100">
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">القارئ / الراوي</div>
                        <div class="font-bold text-pri-900 text-sm"><?= htmlspecialchars($audio['narrator'] ?? 'غير محدد') ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">المدة</div>
                        <div class="font-bold text-pri-900 text-sm" dir="ltr"><?= formatDur($audio['audio_duration']) ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">الاستماعات</div>
                        <div class="font-bold text-pri-900 text-sm"><?= number_format($audio['listen_count'] + 1) ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-brk-400 uppercase tracking-wider mb-1">الحالة</div>
                        <div class="font-bold text-green-600 text-sm flex items-center gap-1"><i class="fas fa-check-circle text-xs"></i> متاح</div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-pri-800 mb-3 flex items-center gap-2"><i class="fas fa-info-circle text-gld-500 text-xs"></i> وصف المقطع</h3>
                    <div class="prose prose-sm text-brk-600 leading-loose bg-white border border-gray-100 rounded-2xl p-5">
                        <?php if (!empty($audio['description'])): ?>
                            <?= nl2br($audio['description']) ?>
                        <?php else: ?>
                            <p>مقطع صوتي عالي الجودة للرقية الشرعية، يمكنك الاستماع إليه وتنزيله بعد إتمام عملية الشراء. يتم قراءته بواسطة متخصصين معتمدين بعناية في الرقية الشرعية.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ══ منطقة المشغل المحمية ══ -->
                <div class="mb-6 relative">
                    <h3 class="text-sm font-bold text-pri-800 mb-3 flex items-center gap-2"><i class="fas fa-play-circle text-pri-500 text-xs"></i> استمع الآن</h3>
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-5 border border-gray-200 overflow-hidden relative">
                        <?php if ($canView): ?>
                            <?php if (!empty($audio['audio_url']) && $audio['audio_url'] !== '#'): ?>
                                <?= getAudioEmbed($audio['audio_url']) ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-brk-400">
                                    <i class="fas fa-file-audio text-3xl mb-2 block opacity-30"></i>
                                    <p class="font-bold text-sm">الملف الصوتي جاري تجهيزه</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- قفل المحتوى -->
                            <div class="absolute inset-0 bg-white/60 backdrop-blur-[6px] flex flex-col items-center justify-center z-20 border-2 border-gld-200 rounded-2xl">
                                <div class="w-12 h-12 bg-gld-50 text-gld-600 rounded-full flex items-center justify-center text-xl mb-2 shadow-sm border border-gld-100"><i class="fas fa-lock"></i></div>
                                <h4 class="font-bold text-pri-900 text-sm mb-1">المحتوى حصري ومدفوع</h4>
                                <p class="text-xs text-brk-500">قم بشراء المقطع لفتح الاستماع المباشر والتحميل.</p>
                            </div>
                            <!-- مشغل وهمي في الخلفية كشكل جمالي -->
                            <div class="opacity-30 pointer-events-none">
                                <audio controls style="width:100%; height:52px;"></audio>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- زر الإضافة للسلة أو التنزيل -->
                <div class="flex flex-wrap gap-3 mt-4">
                    <?php if (!$hasPurchased && !$isFree && !$isAdmin): ?>
                        <button onclick="addToCart('audio', <?= $audio['id'] ?>)" class="cf-btn cf-btn-pri flex-1 h-14 text-base">
                            <i class="fas fa-cart-plus"></i> شراء المقطع الصوتي (<?= number_format($audio['price'], 2) ?> ر.س)
                        </button>
                    <?php else: ?>
                        <div class="flex-1 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center justify-center font-bold text-sm h-14">
                            <i class="fas fa-check-circle ml-2"></i> <?= $isFree ? 'هذا المقطع مجاني' : 'تم شراء هذا المقطع مسبقاً' ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canView): ?>
                        <button onclick="downloadAudio()" class="cf-btn cf-btn-out h-14 px-6" title="تنزيل المقطع">
                            <i class="fas fa-download"></i>
                        </button>
                    <?php else: ?>
                        <button onclick="showToast('يجب شراء المقطع أولاً لتتمكن من تنزيله', 'err')" class="cf-btn bg-gray-100 text-gray-400 border border-gray-200 h-14 px-6 cursor-not-allowed">
                            <i class="fas fa-download"></i>
                        </button>
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

            <!-- صوتيات مشابهة -->
            <?php if (!empty($related)): ?>
            <div class="w-full lg:w-2/5">
                <h3 class="text-base font-black text-pri-900 font-amiri mb-5 flex items-center gap-2 border-b-2 border-gld-200 pb-3">
                    <i class="fas fa-list-music text-gld-500"></i> صوتيات مشابهة
                </h3>
                <div class="space-y-3">
                    <?php foreach ($related as $ra): ?>
                    <a href="index.php?page=audio_details&id=<?= $ra['id'] ?>" class="aud-card bg-white no-underline group">
                        <button class="aud-play !w-12 !h-12 !min-w-[48px] text-sm" onclick="event.preventDefault()">
                            <i class="fas <?= ((float)$ra['price'] > 0) ? 'fa-lock' : 'fa-play' ?>" style="margin-right:-2px"></i>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-pri-900 text-sm truncate group-hover:text-pri-600 transition"><?= htmlspecialchars($ra['title']) ?></h4>
                            <div class="flex items-center gap-3 text-brk-400 text-xs mt-1">
                                <span><i class="fas fa-user-circle text-[10px] ml-0.5"></i><?= htmlspecialchars($ra['narrator'] ?? '-') ?></span>
                                <span><i class="fas fa-headphones text-[10px] ml-0.5"></i><?= $ra['listen_count'] ?> استماع</span>
                            </div>
                        </div>
                        <span class="font-bold text-pri-700 text-sm whitespace-nowrap shrink-0"><?= (float)$ra['price'] > 0 ? number_format($ra['price'], 0) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- تحميل الصوتي (مخفي) -->
<form id="downloadForm" method="get" action="<?= htmlspecialchars($audio['audio_url']) ?>" target="_blank" style="display:none"></form>

<script>
function downloadAudio() {
    document.getElementById('downloadForm').submit();
}
</script>