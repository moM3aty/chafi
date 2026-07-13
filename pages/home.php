<?php
// مسار الملف: pages/home.php
// الوظيفة: الواجهة الرئيسية الفاخرة للمتجر مع تأثيرات Canvas التفاعلية

// 1. جلب بيانات السلايدر النشطة (يجب أن يكون موقع العرض = 0 في لوحة التحكم)
$heroSliders = $pdo->query("SELECT * FROM advertisements WHERE is_active = 1 AND position = 0 ORDER BY display_order ASC, id DESC")->fetchAll();

// 2. جلب الأقسام الرئيسية فقط التي تم تحديد إظهارها في الرئيسية
$mainCategories = $pdo->query("
    SELECT * FROM categories 
    WHERE is_active = 1 AND parent_id IS NULL AND show_on_home = 1
    ORDER BY sort_order
")->fetchAll();

// 3. جلب أبرز المنتجات الملموسة المميزة (التي ليست منتجات رقمية)
$featuredProducts = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND p.is_featured = 1 AND p.is_digital = 0
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// 4. جلب الصوتيات والفيديوهات المميزة للعرض في المكتبة الرقمية بالرئيسية
$featuredAudios = $pdo->query("SELECT a.*, c.name as cat_name FROM audios a LEFT JOIN categories c ON a.category_id = c.id WHERE a.is_active = 1 AND a.is_featured = 1 ORDER BY a.listen_count DESC LIMIT 4")->fetchAll();
$featuredVideos = $pdo->query("SELECT v.*, c.name as cat_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.is_active = 1 AND v.is_featured = 1 ORDER BY v.view_count DESC LIMIT 3")->fetchAll();

// 5. الباقات والعروض المميزة
$featuredPackages = $pdo->query("SELECT * FROM packages WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();

if (!function_exists('formatDurationHome')) {
    function formatDurationHome($seconds) {
        if (!$seconds) return '';
        $m = floor($seconds / 60); $s = $seconds % 60;
        return $m . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
    }
}
?>

<style>
    .lux-gradient { background: linear-gradient(180deg, #fdfbf7 0%, #ffffff 100%); }
    .gold-gradient { background: linear-gradient(135deg, #d4a017, #aa7c11); }
    .green-gradient { background: linear-gradient(135deg, #1b4332, #0e2f18); }
    .lux-card { border: 1px solid #f0eae1; box-shadow: 0 4px 20px rgba(0,0,0,0.015); transition: all 0.3s cubic-bezier(.4,0,.2,1); }
    .lux-card:hover { border-color: #c8a020; box-shadow: 0 12px 30px rgba(200,160,32,0.08); transform: translateY(-4px); }
</style>

<section class="relative w-full h-[70vh] min-h-[460px] overflow-hidden rounded-b-[2rem] sm:rounded-b-[3.5rem] shadow-md bg-pri-900" id="heroSlider">
    
    <!-- الكانفاس التفاعلي للجسيمات المضيئة (Particles Canvas) -->
    <canvas id="heroCanvas" class="absolute inset-0 w-full h-full z-[15] pointer-events-none"></canvas>

    <?php if (empty($heroSliders)): ?>
        <div class="hero-slide on absolute inset-0 green-gradient flex items-center justify-center z-10">
            <div class="absolute inset-0 bg-black/35 z-10"></div>
            <div class="relative z-20 h-full flex flex-col justify-center items-center text-center max-w-4xl mx-auto px-6">
                <span class="text-gld-300 text-xs sm:text-sm font-bold mb-4 tracking-widest uppercase bg-white/10 px-4 py-1.5 rounded-full backdrop-blur-sm shadow-lg">نقاء، جودة، بركة</span>
                <h1 class="text-3xl sm:text-5xl md:text-6xl font-black text-white leading-[1.25] mb-6 font-amiri drop-shadow">تشافي للرقية الشرعية</h1>
                <p class="text-white/85 text-sm sm:text-lg mb-8 max-w-2xl leading-relaxed">منتجات طبيعية 100% مقروء عليها آيات الشفاء والرقية الشرعية بأسلوب شرعي موثوق.</p>
                <a href="index.php?page=products" class="bg-white text-pri-900 font-bold px-10 py-4 rounded-full hover:bg-gld-500 hover:text-white transition-all duration-300 shadow-lg hover:shadow-gld-500/20 transform hover:scale-105">تسوق الآن <i class="fas fa-arrow-left mr-1.5 text-xs"></i></a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($heroSliders as $index => $slide): ?>
            <div class="hero-slide <?= $index === 0 ? 'on' : '' ?> absolute inset-0 opacity-0 invisible transition-opacity duration-1000 ease-in-out z-10 flex items-center justify-center">
                <div class="absolute inset-0 bg-cover bg-center transform scale-100 transition-transform duration-[10s] ease-out slide-bg" style="background-image: url('<?= htmlspecialchars($slide['image_url']) ?>')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/20 z-10"></div>
                
                <div class="relative z-20 h-full flex flex-col justify-center items-center text-center max-w-4xl mx-auto px-6">
                    <div class="slide-content transform translate-y-8 opacity-0 transition-all duration-1000 delay-200">
                        <?php if (!empty($slide['subtitle'])): ?>
                            <span class="text-gld-400 text-xs sm:text-sm font-bold mb-4 inline-block tracking-widest uppercase bg-white/5 border border-white/10 px-4 py-1.5 rounded-full backdrop-blur-sm shadow-md"><?= htmlspecialchars($slide['subtitle']) ?></span>
                        <?php endif; ?>
                        <h1 class="text-3xl sm:text-5xl md:text-6xl font-black text-white leading-[1.25] mb-6 font-amiri drop-shadow-lg">
                            <?= $slide['title'] ?>
                        </h1>
                        <?php if (!empty($slide['description'])): ?>
                            <p class="text-white/90 text-sm sm:text-base md:text-lg mb-8 max-w-2xl mx-auto leading-relaxed line-clamp-2">
                                <?= htmlspecialchars($slide['description']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($slide['link_url'])): ?>
                            <a href="<?= htmlspecialchars($slide['link_url']) ?>" target="<?= htmlspecialchars($slide['link_target']) ?>" class="gold-gradient text-white font-bold px-10 py-4 rounded-full hover:shadow-[0_0_25px_rgba(212,175,55,0.4)] transition-all duration-300 inline-block transform hover:scale-105">
                                <?= htmlspecialchars($slide['link_text'] ?? 'تصفح الآن') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (count($heroSliders) > 1): ?>
        <button class="absolute right-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white hover:text-pri-900 backdrop-blur-md transition-all border border-white/10" onclick="heroNav(1)"><i class="fas fa-chevron-right text-xs"></i></button>
        <button class="absolute left-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white hover:text-pri-900 backdrop-blur-md transition-all border border-white/10" onclick="heroNav(-1)"><i class="fas fa-chevron-left text-xs"></i></button>
        <div class="hero-dots flex gap-2 absolute bottom-6 left-1/2 -translate-x-1/2 z-30" id="heroDots"></div>
    <?php endif; ?>
</section>

<!-- مميزات الموقع -->
<div class="max-w-7xl mx-auto px-4 -mt-10 relative z-40 mb-12 afiu">
    <div class="bg-white rounded-3xl p-5 sm:p-7 flex flex-wrap items-center justify-center gap-6 sm:gap-12 shadow-xl border border-[#f0eae1]">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-pri-50 text-pri-600 flex items-center justify-center text-lg"><i class="fas fa-leaf"></i></div>
            <div>
                <h4 class="font-bold text-pri-900 text-xs sm:text-sm">طبيعي وأصلي 100%</h4>
                <p class="text-[10px] text-brk-400">عسل وزيوت مختبرة مخبرياً</p>
            </div>
        </div>
        <div class="hidden md:block w-px h-8 bg-gray-100"></div>
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gld-50 text-gld-600 flex items-center justify-center text-lg"><i class="fas fa-headphones"></i></div>
            <div>
                <h4 class="font-bold text-pri-900 text-xs sm:text-sm">رقية شرعية موثوقة</h4>
                <p class="text-[10px] text-brk-400">بأصوات نخبة من القراء والمشايخ</p>
            </div>
        </div>
        <div class="hidden lg:block w-px h-8 bg-gray-100"></div>
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-lg"><i class="fas fa-truck-fast"></i></div>
            <div>
                <h4 class="font-bold text-pri-900 text-xs sm:text-sm">توصيل آمن وسريع</h4>
                <p class="text-[10px] text-brk-400">لباب المنزل بكل مدن المملكة</p>
            </div>
        </div>
    </div>
</div>

<section class="py-12 bg-transparent relative overflow-hidden mb-8">
    <div class="max-w-7xl mx-auto px-4 relative z-10 afiu" style="animation-delay: 0.1s">
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 sm:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- النص (يمين) -->
                <div class="lg:col-span-6 order-2 lg:order-1">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gld-50 text-gld-600 flex items-center justify-center text-xl shadow-sm border border-gld-100">
                            <i class="fas fa-quote-right"></i>
                        </div>
                        <h2 class="text-3xl font-black text-pri-900 font-amiri border-b-2 border-gld-200 pb-2 inline-block">كلمة الشيخ</h2>
                    </div>
                    
                    <div class="text-lg leading-loose text-brk-600 space-y-4">
                        <p class="font-bold text-pri-800 text-xl mb-4 font-amiri">بسم الله الرحمن الرحيم والصلاة والسلام على أشرف الأنبياء والمرسلين سيدنا محمد وآله وصحبه أجمعين أما بعد..</p>
                        
                        <p>قال صلى الله عليه وسلم: <span class="text-gld-700 font-bold font-amiri text-xl bg-gld-50 px-2 py-0.5 rounded">(خير الناس أنفعهم للناس)</span>، وقال أيضًا: <span class="text-gld-700 font-bold font-amiri text-xl bg-gld-50 px-2 py-0.5 rounded">(من استطاع منكم أن ينفع أخاه فليفعل)</span>.</p>
                        
                        <p>فأسأل الله العظيم بأن ينفع بمحتوى هذا الموقع كل من أراد الفائدة، وأسأله سبحانه أن يجعل هذا العمل خالصاً لوجهه الكريم وعمل ينتفع به.</p>
                        
                        <div class="font-bold text-pri-800 bg-pri-50 p-5 rounded-2xl border-r-4 border-pri-500 mt-8 text-xl font-amiri leading-loose">
                            أسأل الله العلي العظيم أن يشفى كل مريض ويهدي كل ضال وأن يغفر لنا ولجميع المسلمين إنه غفور رحيم.
                        </div>
                    </div>
                </div>

                <!-- الصورة (يسار) -->
                <div class="lg:col-span-6 order-1 lg:order-2 relative">
                    <!-- خلفيات زخرفية للصورة -->
                    <div class="absolute inset-0 bg-gld-500 rounded-[2rem] transform rotate-3 scale-105 opacity-10"></div>
                    <div class="absolute inset-0 bg-pri-500 rounded-[2rem] transform -rotate-3 scale-105 opacity-10"></div>
                    
                    <div class="relative rounded-[2rem] overflow-hidden shadow-lg border-4 border-white aspect-[4/3] lg:aspect-square bg-gray-100 flex items-center justify-center">
                        <img src="../assets/images/home.jpg" alt="كلمة الشيخ" class="w-full h-full object-cover">
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php if (!empty($mainCategories)): ?>
<section class="py-10 mb-12">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="text-center mb-10">
            <span class="text-gld-600 text-xs font-bold tracking-widest uppercase block mb-2">أقسامنا العلمية والشرعية</span>
            <h2 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri">أقسام متجر تشافي</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full mt-3"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-6 sm:gap-12">
            <?php foreach ($mainCategories as $cat): 
                $cColor = $cat['color_hex'] ?? '#1a582a';
            ?>
            <a href="index.php?page=category&category_id=<?= $cat['id'] ?>" class="group flex flex-col items-center text-center w-28 sm:w-32 no-underline">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white border border-[#f0eae1] flex items-center justify-center text-2xl sm:text-3xl mb-3.5 group-hover:border-gld-500 group-hover:shadow-[0_0_20px_rgba(200,160,32,0.15)] transition-all duration-300 relative overflow-hidden" style="color: <?= $cColor ?>">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-5 transition-opacity duration-300" style="background-color: <?= $cColor ?>"></div>
                    <?php if (!empty($cat['image_url'])): ?>
                        <img src="<?= htmlspecialchars($cat['image_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <i class="<?= $cat['icon_class'] ?? 'fas fa-folder' ?> group-hover:scale-110 transition-transform duration-300"></i>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-xs sm:text-sm group-hover:text-gld-600 transition-colors leading-relaxed"><?= htmlspecialchars($cat['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredPackages)): ?>
<section class="py-20 green-gradient relative overflow-hidden mb-12">
    <div class="absolute top-0 right-0 w-96 h-96 bg-gld-500/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-pri-500/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 relative z-10 afiu">
        <div class="text-center mb-14">
            <span class="text-gld-400 font-bold text-xs tracking-widest uppercase mb-2 block">حماية وبركة بأسعار توفيرية</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white font-amiri mb-3">الباقات والعروض</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full mt-3"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($featuredPackages as $pkg): 
                $savings = $pkg['original_total_price'] > 0 ? round((($pkg['original_total_price'] - $pkg['package_price']) / $pkg['original_total_price']) * 100) : 0;
            ?>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-[2rem] p-6 text-center hover:bg-white/10 hover:border-gld-500/50 transition-all duration-500 group relative flex flex-col justify-between">
                    <div class="absolute top-0 right-1/2 translate-x-1/2 -translate-y-1/2 bg-gld-500 text-white text-[9px] font-bold px-5 py-1 rounded-full uppercase tracking-wider shadow-md">باقة مخصصة</div>
                    
                    <div>
                        <div class="w-28 h-28 mx-auto rounded-full overflow-hidden border-4 border-white/15 mb-6 group-hover:scale-105 transition-transform duration-500 shadow-lg">
                            <img src="<?= htmlspecialchars($pkg['image_url'] ?? 'https://picsum.photos/400') ?>" alt="<?= htmlspecialchars($pkg['name']) ?>" class="w-full h-full object-cover">
                        </div>
                        
                        <h3 class="text-lg sm:text-xl font-bold text-white mb-2 font-amiri leading-snug"><?= htmlspecialchars($pkg['name']) ?></h3>
                        <?php if (!empty($pkg['short_description'])): ?>
                            <p class="text-gray-300 text-xs mb-6 line-clamp-2 leading-relaxed"><?= htmlspecialchars($pkg['short_description']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <div class="bg-black/25 rounded-2xl p-4 mb-6">
                            <div class="text-gray-400 text-xs line-through mb-1">بدلاً من <?= number_format($pkg['original_total_price'], 2) ?> ر.س</div>
                            <!-- إصلاح مسافة العملات لضمان تحويلها -->
                            <div class="text-2xl sm:text-3xl font-black text-gld-400"><?= number_format($pkg['package_price'], 2) ?> ر.س</div>
                        </div>
                        
                        <a href="index.php?page=package_details&id=<?= $pkg['id'] ?>" class="block w-full py-3.5 rounded-full border border-gld-500 text-gld-400 font-bold hover:bg-gld-500 hover:text-pri-900 transition-all duration-300 shadow-sm text-sm">
                            عرض التفاصيل والبرنامج العلاجي
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredAudios) || !empty($featuredVideos)): ?>
<section class="py-16 bg-white mb-12 border-y border-gray-50">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="text-center mb-14">
            <span class="text-gld-600 text-xs font-bold tracking-widest uppercase block mb-2">مكتبة رقمية غنية</span>
            <h2 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri">الصوتيات والفيديوهات</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full mt-3"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- قسم الصوتيات -->
            <?php if (!empty($featuredAudios)): ?>
            <div class="bg-gray-50/50 rounded-[2rem] p-6 sm:p-8 border border-gray-100">
                <h3 class="font-bold text-base sm:text-lg mb-6 flex items-center gap-2.5 border-b border-gray-100 pb-4 text-pri-900"><i class="fas fa-headphones text-pri-600"></i> الصوتيات</h3>
                <div class="space-y-3">
                    <?php foreach ($featuredAudios as $audio): ?>
                    <a href="index.php?page=audio_details&id=<?= $audio['id'] ?>" class="flex items-center gap-4 p-3.5 rounded-2xl bg-white border border-transparent hover:border-gld-200 hover:shadow-md transition-all duration-300 group no-underline">
                        <div class="w-11 h-11 rounded-full bg-pri-50 text-pri-600 flex items-center justify-center shrink-0 group-hover:bg-pri-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-play text-xs ml-1"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate group-hover:text-pri-700 transition-colors"><?= htmlspecialchars($audio['title']) ?></h4>
                            <p class="text-[10px] text-gray-500 mt-1 truncate">القارئ: <?= htmlspecialchars($audio['narrator'] ?? 'الرقية الشرعية') ?></p>
                        </div>
                        <div class="text-left shrink-0">
                            <div class="text-[10px] text-gray-400 mb-1"><?= formatDurationHome($audio['audio_duration']) ?> دقيقة</div>
                            <!-- إصلاح مسافة العملات -->
                            <div class="font-bold text-pri-700 text-xs sm:text-sm"><?= $audio['price'] > 0 ? number_format($audio['price'], 2) . ' ر.س' : '<span class="text-green-600">متاح</span>' ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- قسم الفيديوهات -->
            <?php if (!empty($featuredVideos)): ?>
            <div class="bg-gray-50/50 rounded-[2rem] p-6 sm:p-8 border border-gray-100">
                <h3 class="font-bold text-base sm:text-lg mb-6 flex items-center gap-2.5 border-b border-gray-100 pb-4 text-pri-900"><i class="fas fa-video text-gld-600"></i> الدروس والمحاضرات المرئية</h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach (array_slice($featuredVideos, 0, 2) as $video): ?>
                    <a href="index.php?page=video_details&id=<?= $video['id'] ?>" class="block group no-underline">
                        <div class="relative aspect-video rounded-2xl overflow-hidden bg-gray-900 mb-3 shadow-sm group-hover:shadow-md transition-shadow">
                            <img src="<?= htmlspecialchars($video['thumbnail_url'] ?? 'https://picsum.photos/400/225') ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" loading="lazy">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-pri-900 shadow-md group-hover:scale-110 transition-transform duration-300"><i class="fas fa-play ml-0.5"></i></div>
                            </div>
                        </div>
                        <h4 class="font-bold text-gray-900 text-xs leading-relaxed line-clamp-2 group-hover:text-pri-700 transition-colors"><?= htmlspecialchars($video['title']) ?></h4>
                        <p class="text-[10px] text-gray-500 mt-1"><i class="fas fa-user-circle ml-1"></i><?= htmlspecialchars($video['presenter'] ?? '') ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="py-14 bg-white border-b border-gray-50">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="flex items-end justify-between mb-10 border-b border-gray-100 pb-5">
            <div>
                <span class="text-gld-600 text-xs font-bold tracking-widest uppercase block mb-1">المنتجات الطبيعية والمقروء عليها</span>
                <h2 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri">المنتجات الأكثر مبيعاً والجديدة</h2>
            </div>
            <a href="index.php?page=products" class="text-xs sm:text-sm font-bold text-pri-600 hover:text-gld-600 transition flex items-center gap-1.5">عرض الكل <i class="fas fa-arrow-left text-[10px]"></i></a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($featuredProducts as $prod):
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
                                <!-- إصلاح مسافة العملات -->
                                <span class="text-pri-700 font-black text-lg"><?= number_format($prod['price'], 2) ?> ر.س</span>
                            </div>
                            <button onclick="event.preventDefault(); addToCart('product', <?= $prod['id'] ?>)" class="cf-btn cf-btn-pri cf-btn-sm text-xs py-2 px-3"><i class="fas fa-cart-plus"></i></button>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ════════════════════════════════════════════════════════════
    // سكريبت التحكم بالسلايدر (Hero Slider)
    // ════════════════════════════════════════════════════════════
    let slideIndex = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dotsContainer = document.getElementById('heroDots');
    let slideInterval;

    if (slides.length > 0) {
        slides.forEach((_, i) => {
            let dot = document.createElement('div');
            dot.className = `w-10 h-1 rounded-full cursor-pointer transition-all duration-300 ${i === 0 ? 'bg-gld-500' : 'bg-white/40 hover:bg-white/70'}`;
            dot.onclick = () => { showSlide(i); resetInterval(); };
            if(dotsContainer) dotsContainer.appendChild(dot);
        });

        function showSlide(n) {
            slides[slideIndex].classList.remove('on', 'opacity-100', 'visible');
            slides[slideIndex].classList.add('opacity-0', 'invisible');
            
            const oldBg = slides[slideIndex].querySelector('.slide-bg');
            if(oldBg) { oldBg.classList.remove('scale-105'); oldBg.classList.add('scale-100'); }
            
            const oldContent = slides[slideIndex].querySelector('.slide-content');
            if(oldContent) oldContent.classList.add('translate-y-8', 'opacity-0');

            if(dotsContainer) {
                dotsContainer.children[slideIndex].classList.remove('bg-gld-500');
                dotsContainer.children[slideIndex].classList.add('bg-white/40');
            }

            slideIndex = (n + slides.length) % slides.length;
            
            slides[slideIndex].classList.add('on', 'opacity-100', 'visible');
            slides[slideIndex].classList.remove('opacity-0', 'invisible');
            
            if(dotsContainer) {
                dotsContainer.children[slideIndex].classList.add('bg-gld-500');
                dotsContainer.children[slideIndex].classList.remove('bg-white/40');
            }

            const newBg = slides[slideIndex].querySelector('.slide-bg');
            if(newBg) setTimeout(() => { newBg.classList.remove('scale-100'); newBg.classList.add('scale-105'); }, 50);

            const newContent = slides[slideIndex].querySelector('.slide-content');
            if(newContent) setTimeout(() => { newContent.classList.remove('translate-y-8', 'opacity-0'); }, 100);
        }

        window.heroNav = function(dir) { showSlide(slideIndex + dir); resetInterval(); }
        function resetInterval() { clearInterval(slideInterval); slideInterval = setInterval(() => showSlide(slideIndex + 1), 6000); }

        const firstBg = slides[0].querySelector('.slide-bg');
        if(firstBg) setTimeout(() => { firstBg.classList.add('scale-105'); }, 50);
        
        const firstContent = slides[0].querySelector('.slide-content');
        if(firstContent) setTimeout(() => { firstContent.classList.remove('translate-y-8', 'opacity-0'); }, 100);

        slideInterval = setInterval(() => showSlide(slideIndex + 1), 6000);
    }

    // ════════════════════════════════════════════════════════════
    // سكريبت Canvas للجسيمات المضيئة (Glowing Particles)
    // ════════════════════════════════════════════════════════════
    const canvas = document.getElementById('heroCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width, height, particles;

        function initCanvas() {
            width = canvas.width = canvas.offsetWidth;
            height = canvas.height = canvas.offsetHeight;
            particles = [];
            
            // عدد الجسيمات يتناسب مع حجم الشاشة (كثافة متوسطة لعدم الإزعاج)
            const particleCount = Math.min(Math.floor((width * height) / 12000), 80); 
            
            for (let i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    size: Math.random() * 2.5 + 0.5,
                    speedX: Math.random() * 0.5 - 0.25,
                    speedY: Math.random() * 0.5 - 0.25,
                    opacity: Math.random() * 0.5 + 0.1
                });
            }
        }

        window.addEventListener('resize', initCanvas);
        initCanvas();

        function animateParticles() {
            ctx.clearRect(0, 0, width, height);
            
            particles.forEach((p, i) => {
                p.x += p.speedX;
                p.y += p.speedY;

                // ارتداد ناعم من الحواف
                if (p.x < 0 || p.x > width) p.speedX *= -1;
                if (p.y < 0 || p.y > height) p.speedY *= -1;

                // رسم الجسيم الدائري המضيء
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(212, 160, 23, ${p.opacity})`; // لون ذهبي
                ctx.shadowBlur = 15;
                ctx.shadowColor = "rgba(212, 160, 23, 0.8)";
                ctx.fill();

                // رسم خطوط اتصال شفافة جداً بين الجسيمات القريبة
                for (let j = i + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 100) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(212, 160, 23, ${0.1 - dist / 1000})`; // ذهبي شفاف جداً
                        ctx.lineWidth = 0.5;
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                }
            });

            requestAnimationFrame(animateParticles);
        }
        
        animateParticles();
    }
});
</script>
