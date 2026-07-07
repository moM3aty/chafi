<?php
// مسار الملف: pages/audio_details.php
// النسخة الاحترافية — مشغل صوتي + قائمة + صوتيات مشابهة

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

// جلب الصوتيات المشابهة (نفس القسم أو الأب)
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

// جلب اسم القسم من السلسلة الهرمية
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

$catColor = $audio['cat_color'] ?? '#c8a020';
$isFree = $audio['price'] <= 0 || $audio['is_free'] == 1;
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
                        <i class="fas fa-headphones-alt text-white text-5xl sm:text-6xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- محتوى المعلومات -->
        <div class="p-6 sm:p-10 flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-3/5">
                <h1 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri mb-3 leading-tight"><?= htmlspecialchars($audio['title']) ?></h1>
                
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
                            <?= nl2br(htmlspecialchars($audio['description'])) ?>
                        <?php else: ?>
                            <p>مقطع صوتي عالي الجودة للرقية الشرعية، يمكنك الاستماع إليه وتنزيله بعد إتمام عملية الشراء. يتم قراءته بواسطة متخصصين معتمدين بعناية في الرقية الشرعية.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-pri-800 mb-3 flex items-center gap-2"><i class="fas fa-play-circle text-pri-500 text-xs"></i> استمع الآن</h3>
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-5 border border-gray-200">
                        <?php if (!empty($audio['audio_url']) && $audio['audio_url'] !== '#'): ?>
                            <audio 
                                id="audioPlayer" 
                                controls 
                                preload="metadata"
                                style="width:100%; height:52px; border-radius:12px; outline:none;"
                                onplay="onAudioPlay()"
                                ontimeupdate="updateAudioTime()"
                            >
                                <source src="<?= htmlspecialchars($audio['audio_url']) ?>" type="audio/mpeg">
                                متصفحك لا يدعم عنصر الصوت
                            </audio>
                            <div class="mt-3 flex items-center gap-3">
                                <span id="audioCurrentTime" class="text-xs text-brk-400 font-mono w-16 text-right" dir="ltr">0:00</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5 overflow-hidden cursor-pointer relative group" id="audioSeek" onclick="seekAudio(event)">
                                    <div class="h-full bg-gradient-to-l from-pri-400 to-pri-500 rounded-full transition-all duration-150" id="audioProgress" style="width:0%"></div>
                                </div>
                                <span id="audioDuration" class="text-xs text-brk-400 font-mono w-16 text-right" dir="ltr"><?= formatDur($audio['audio_duration']) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-brk-400">
                                <i class="fas fa-lock text-4xl mb-3 block opacity-30"></i>
                                <p class="font-bold text-sm">الملف الصوتي غير متاح حالياً</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- زر الإضافة للسلة -->
                <div class="flex flex-wrap gap-3 mt-2">
                    <button onclick="addToCart(0, 1, 0, <?= $audio['id'] ?>)" class="cf-btn cf-btn-pri flex-1 h-14 text-base">
                        <i class="fas fa-cart-plus"></i> <?= $isFree ? 'إضافة مجاناً' : 'شراء المقطع الصوتي' ?>
                    </button>
                    <button onclick="downloadAudio()" class="cf-btn cf-btn-out h-14 px-6" title="تنزيل المقطع">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>

            <?php if (!empty($related)): ?>
            <div class="w-full lg:w-2/5">
                <h3 class="text-base font-black text-pri-900 font-amiri mb-5 flex items-center gap-2 border-b-2 border-gld-200 pb-3">
                    <i class="fas fa-list-music text-gld-500"></i> صوتيات مشابهة
                </h3>
                <div class="space-y-3">
                    <?php foreach ($related as $ra): ?>
                    <a href="index.php?page=audio_details&id=<?= $ra['id'] ?>" class="aud-card bg-white no-underline group">
                        <button class="aud-play !w-12 !h-12 !min-w-[48px] text-sm" onclick="event.preventDefault()">
                            <i class="fas fa-play" style="margin-right:-2px"></i>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-pri-900 text-sm truncate group-hover:text-pri-600 transition"><?= htmlspecialchars($ra['title']) ?></h4>
                            <div class="flex items-center gap-3 text-brk-400 text-xs mt-1">
                                <span><i class="fas fa-user-circle text-[10px] ml-0.5"></i><?= htmlspecialchars($ra['narrator'] ?? '-') ?></span>
                                <span><i class="fas fa-headphones text-[10px] ml-0.5"></i><?= $ra['listen_count'] ?> استماع</span>
                            </div>
                        </div>
                        <span class="font-bold text-pri-700 text-sm whitespace-nowrap shrink-0"><?= $ra['price'] > 0 ? number_format($ra['price'], 0) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- تحميل الصوتي (مخفي) -->
<form id="downloadForm" method="post" action="ajax/cart_action.php" style="display:none">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="audio_id" id="dlAudioId">
    <input type="hidden" name="quantity" value="1">
</form>

<script>
// مشغل الصوت المتقدم
let audioPlayer = document.getElementById('audioPlayer');
let audioSeek = document.getElementById('audioSeek');

function onAudioPlay() {
    if (audioPlayer) {
        setInterval(updateAudioTime, 250);
    }
}

function updateAudioTime() {
    if (!audioPlayer || isNaN(audioPlayer.currentTime)) return;
    const cur = audioPlayer.currentTime;
    const dur = audioPlayer.duration || 0;
    const cm = document.getElementById('audioCurrentTime');
    const prog = document.getElementById('audioProgress');
    if (cm) cm.textContent = formatTime(cur);
    if (prog && dur > 0) prog.style.width = (cur / dur * 100) + '%';
}

function seekAudio(e) {
    if (!audioPlayer || !audioPlayer.duration) return;
    const rect = audioSeek.getBoundingClientRect();
    const pct = (e.clientX - rect.left) / rect.width;
    audioPlayer.currentTime = pct * audioPlayer.duration;
    updateAudioTime();
}

function formatTime(sec) {
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60);
    return m + ':' + String(s).padStart(2, '0');
}

function downloadAudio() {
    const id = document.getElementById('dlAudioId');
    if (!id) return;
    document.getElementById('dlAudioId').value = id;
    document.getElementById('downloadForm').submit();
    showToast('جاري تحميل المقطع...', 'ok');
}
</script>