<?php
// مسار الملف: pages/home.php
// الوظيفة: الواجهة الرئيسية للمتجر (تصميم فاخر، نظيف، وبسيط Luxury Minimalist)

// ═══════════════════════════════════════════
// 1. جلب بيانات السلايدر
// ═══════════════════════════════════════════
$heroSliders = $pdo->query("SELECT * FROM advertisements WHERE is_active = 1 AND position = 0 ORDER BY display_order")->fetchAll();

// ═══════════════════════════════════════════
// 2. جلب الأقسام الرئيسية فقط
// ═══════════════════════════════════════════
$mainCategories = $pdo->query("
    SELECT * FROM categories 
    WHERE is_active = 1 AND parent_id IS NULL AND show_on_home = 1
    ORDER BY sort_order
")->fetchAll();

// ═══════════════════════════════════════════
// 3. جلب المنتجات الملموسة "المميزة" (Featured)
// ═══════════════════════════════════════════
$featuredProducts = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND p.is_featured = 1 AND p.is_digital = 0
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// ═══════════════════════════════════════════
// 4. جلب الصوتيات والفيديوهات "المميزة"
// ═══════════════════════════════════════════
$featuredAudios = $pdo->query("SELECT a.*, c.name as cat_name FROM audios a LEFT JOIN categories c ON a.category_id = c.id WHERE a.is_active = 1 AND a.is_featured = 1 ORDER BY a.listen_count DESC LIMIT 4")->fetchAll();
$featuredVideos = $pdo->query("SELECT v.*, c.name as cat_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.is_active = 1 AND v.is_featured = 1 ORDER BY v.view_count DESC LIMIT 3")->fetchAll();

// ═══════════════════════════════════════════
// 5. الباقات والعروض المميزة
// ═══════════════════════════════════════════
$featuredPackages = $pdo->query("SELECT * FROM packages WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();

function formatDuration($seconds) {
    if (!$seconds) return '';
    $m = floor($seconds / 60); $s = $seconds % 60;
    return $m . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}
?>

<!-- ستايل إضافي مخصص للرئيسية لضمان نعومة التصميم -->
<style>
    .lux-gradient { background: linear-gradient(180deg, #fdfbf7 0%, #ffffff 100%); }
    .gold-gradient { background: linear-gradient(135deg, #d4af37, #aa7c11); }
    .green-gradient { background: linear-gradient(135deg, #1b4332, #081c15); }
    .lux-card { border: 1px solid #f0eae1; box-shadow: 0 4px 20px rgba(0,0,0,0.02); transition: all 0.3s ease; }
    .lux-card:hover { border-color: #d4af37; box-shadow: 0 10px 30px rgba(212,175,55,0.1); transform: translateY(-3px); }
</style>

<!-- ═══════════════════════════════════════════════════════
     1. الهيرو سلايدر (Hero Slider) - نظيف وأنيق
     ═══════════════════════════════════════════════════════ -->
<section class="relative w-full h-[75vh] min-h-[500px] overflow-hidden" id="heroSlider">
    <?php if (empty($heroSliders)): ?>
        <div class="hero-slide on absolute inset-0 green-gradient">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative z-20 h-full flex flex-col justify-center items-center text-center max-w-4xl mx-auto px-4">
                <span class="text-gld-300 text-sm font-bold mb-4 tracking-widest uppercase">نقاء، جودة، بركة</span>
                <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6 font-amiri">تشافي للرقية الشرعية</h1>
                <p class="text-white/80 text-lg mb-8 max-w-2xl">منتجات طبيعية 100% مقروء عليها آيات الشفاء بأسلوب شرعي موثوق.</p>
                <a href="index.php?page=products" class="bg-white text-pri-900 font-bold px-8 py-3.5 rounded-full hover:bg-gld-400 transition-colors duration-300">تسوق الآن</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($heroSliders as $index => $slide): ?>
            <div class="hero-slide <?= $index === 0 ? 'on' : '' ?> absolute inset-0 opacity-0 invisible transition-opacity duration-1000 ease-in-out z-10">
                <!-- صورة الخلفية مع تأثير تقريب بطيء -->
                <div class="absolute inset-0 bg-cover bg-center transform scale-100 transition-transform duration-[10s] ease-out slide-bg" style="background-image: url('<?= htmlspecialchars($slide['image_url']) ?>')"></div>
                <!-- تدرج لوني لتوضيح النص -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/10"></div>
                
                <div class="relative z-20 h-full flex flex-col justify-center items-center text-center max-w-4xl mx-auto px-4">
                    <div class="slide-content transform translate-y-8 opacity-0 transition-all duration-1000 delay-200">
                        <?php if (!empty($slide['subtitle'])): ?>
                            <span class="text-gld-300 text-sm font-bold mb-4 block tracking-widest uppercase"><?= htmlspecialchars($slide['subtitle']) ?></span>
                        <?php endif; ?>
                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white leading-[1.2] mb-6 font-amiri">
                            <?= $slide['title'] ?>
                        </h1>
                        <?php if (!empty($slide['description'])): ?>
                            <p class="text-white/90 text-base md:text-lg mb-8 max-w-2xl mx-auto line-clamp-2">
                                <?= htmlspecialchars($slide['description']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($slide['link_url'])): ?>
                            <a href="<?= htmlspecialchars($slide['link_url']) ?>" class="gold-gradient text-white font-bold px-10 py-4 rounded-full hover:shadow-[0_0_20px_rgba(212,175,55,0.5)] transition-all duration-300 inline-block">
                                <?= htmlspecialchars($slide['link_text'] ?? 'تصفح الآن') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- أزرار التحكم -->
    <?php if (count($heroSliders) > 1): ?>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-white/20 text-white flex items-center justify-center hover:bg-white hover:text-pri-900 backdrop-blur-sm transition" onclick="heroNav(1)"><i class="fas fa-chevron-right text-sm"></i></button>
        <button class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-white/20 text-white flex items-center justify-center hover:bg-white hover:text-pri-900 backdrop-blur-sm transition" onclick="heroNav(-1)"><i class="fas fa-chevron-left text-sm"></i></button>
        <div class="hero-dots flex gap-2 absolute bottom-8 left-1/2 -translate-x-1/2 z-30" id="heroDots"></div>
    <?php endif; ?>
</section>

<!-- ═══════════════════════════════════════════════════════
     2. شريط الثقة (Minimalist Trust Bar)
     ═══════════════════════════════════════════════════════ -->
<div class="border-b border-gray-100 bg-white">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:py-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center divide-x-0 md:divide-x md:divide-x-reverse divide-gray-100">
            <div class="flex flex-col items-center justify-center gap-2">
                <i class="fas fa-leaf text-pri-600 text-xl"></i>
                <span class="text-xs font-bold text-gray-800">منتجات طبيعية 100%</span>
            </div>
            <div class="flex flex-col items-center justify-center gap-2">
                <i class="fas fa-truck-fast text-pri-600 text-xl"></i>
                <span class="text-xs font-bold text-gray-800">شحن سريع وآمن</span>
            </div>
            <div class="flex flex-col items-center justify-center gap-2">
                <i class="fas fa-shield-alt text-pri-600 text-xl"></i>
                <span class="text-xs font-bold text-gray-800">دفع إلكتروني مشفر</span>
            </div>
            <div class="flex flex-col items-center justify-center gap-2">
                <i class="fas fa-medal text-pri-600 text-xl"></i>
                <span class="text-xs font-bold text-gray-800">جودة شرعية معتمدة</span>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     3. الأقسام الرئيسية (Categories - Circular Layout)
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($mainCategories)): ?>
<section class="py-16 lux-gradient">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black text-pri-900 font-amiri mb-2">أقسام المتجر</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full"></div>
        </div>

        <!-- شبكة مرنة ومستقرة للأقسام -->
        <div class="flex flex-wrap justify-center gap-6 sm:gap-10">
            <?php foreach ($mainCategories as $cat): ?>
            <a href="index.php?page=category&category_id=<?= $cat['id'] ?>" class="group flex flex-col items-center text-center w-28 sm:w-36 no-underline">
                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-white border-2 border-gray-100 flex items-center justify-center text-3xl sm:text-4xl text-pri-700 mb-4 group-hover:border-gld-500 group-hover:shadow-[0_0_20px_rgba(212,175,55,0.2)] transition-all duration-300 relative overflow-hidden">
                    <!-- إذا كان للقسم صورة نضعها هنا، وإلا نضع الأيقونة -->
                    <?php if (!empty($cat['image_url'])): ?>
                        <img src="<?= htmlspecialchars($cat['image_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <i class="<?= $cat['icon_class'] ?? 'fas fa-folder' ?> group-hover:scale-110 transition-transform duration-300"></i>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-sm sm:text-base group-hover:text-gld-600 transition-colors"><?= htmlspecialchars($cat['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     4. المنتجات المميزة (Featured Products - Clean Cards)
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="flex items-center justify-between mb-10 border-b border-gray-100 pb-4">
            <h2 class="text-2xl sm:text-3xl font-black text-pri-900 font-amiri">وصل حديثاً و الأكثر طلباً</h2>
            <a href="index.php?page=products" class="text-sm font-bold text-pri-600 hover:text-gld-600 transition flex items-center gap-1">عرض الكل <i class="fas fa-arrow-left text-[10px]"></i></a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($featuredProducts as $prod): 
                $hasDisc = $prod['old_price'] > $prod['price'];
            ?>
            <div class="lux-card bg-white rounded-2xl overflow-hidden group relative flex flex-col">
                <!-- شارة الخصم -->
                <?php if ($hasDisc): ?>
                    <span class="absolute top-3 right-3 z-10 bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">-<?= round((1 - $prod['price'] / $prod['old_price']) * 100) ?>%</span>
                <?php endif; ?>
                
                <!-- زر المفضلة -->
                <button onclick="toggleWishlist(<?= $prod['id'] ?>, 'product', this)" class="absolute top-3 left-3 z-10 w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-300 hover:text-red-500 shadow-sm transition">
                    <i class="fas fa-heart text-sm"></i>
                </button>

                <!-- صورة المنتج (مساحة بيضاء كبيرة) -->
                <a href="index.php?page=product_details&id=<?= $prod['id'] ?>" class="block relative aspect-square bg-[#fbfbfb] p-4">
                    <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500" loading="lazy">
                </a>

                <!-- تفاصيل المنتج -->
                <div class="p-4 flex flex-col flex-1 bg-white">
                    <?php if (!empty($prod['category_name'])): ?>
                        <div class="text-[10px] text-gld-600 font-bold mb-1 uppercase tracking-wider"><?= htmlspecialchars($prod['category_name']) ?></div>
                    <?php endif; ?>
                    
                    <h4 class="font-bold text-gray-900 text-sm mb-2 leading-relaxed line-clamp-2 flex-1"><a href="index.php?page=product_details&id=<?= $prod['id'] ?>" class="no-underline text-inherit"><?= htmlspecialchars($prod['name']) ?></a></h4>
                    
                    <div class="flex items-center justify-between mt-3">
                        <div>
                            <?php if ($hasDisc): ?>
                                <div class="text-gray-400 text-[11px] line-through mb-0.5"><?= number_format($prod['old_price'], 2) ?> ر.س</div>
                            <?php endif; ?>
                            <div class="text-pri-700 font-black text-lg"><?= number_format($prod['price'], 2) ?> <span class="text-[10px] font-bold">ر.س</span></div>
                        </div>
                        <button onclick="event.preventDefault(); addToCart(<?= $prod['id'] ?>, 1)" class="w-10 h-10 rounded-full bg-pri-50 text-pri-700 flex items-center justify-center hover:bg-pri-600 hover:text-white transition-colors duration-300" title="إضافة للسلة">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     5. الباقات الفاخرة (Premium Packages - Dark Mode)
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($featuredPackages)): ?>
<section class="py-20 green-gradient relative overflow-hidden">
    <!-- زخرفة ذهبية خافتة في الخلفية -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-gld-500/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-pri-500/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 relative z-10 afiu">
        <div class="text-center mb-14">
            <span class="text-gld-400 font-bold text-sm tracking-widest uppercase mb-2 block">توفير وحماية</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white font-amiri mb-4">الباقات الحصرية</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($featuredPackages as $pkg): 
                $savings = $pkg['original_total_price'] > 0 ? round((($pkg['original_total_price'] - $pkg['package_price']) / $pkg['original_total_price']) * 100) : 0;
            ?>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-6 text-center hover:bg-white/10 hover:border-gld-500/50 transition-all duration-500 group relative">
                    <div class="absolute top-0 right-1/2 translate-x-1/2 -translate-y-1/2 bg-gld-500 text-white text-[10px] font-bold px-4 py-1 rounded-full uppercase tracking-widest shadow-md">باقة مميزة</div>
                    
                    <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-white/10 mb-6 group-hover:scale-105 transition-transform duration-500">
                        <img src="<?= htmlspecialchars($pkg['image_url'] ?? 'https://picsum.photos/400') ?>" alt="<?= htmlspecialchars($pkg['name']) ?>" class="w-full h-full object-cover">
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 font-amiri"><?= htmlspecialchars($pkg['name']) ?></h3>
                    <?php if (!empty($pkg['short_description'])): ?>
                        <p class="text-gray-300 text-sm mb-6 line-clamp-2"><?= htmlspecialchars($pkg['short_description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="bg-black/20 rounded-2xl p-4 mb-6">
                        <div class="text-gray-400 text-xs line-through mb-1">بدلاً من <?= number_format($pkg['original_total_price'], 2) ?> ر.س</div>
                        <div class="text-3xl font-black text-gld-400"><?= number_format($pkg['package_price'], 2) ?> <span class="text-sm">ر.س</span></div>
                    </div>
                    
                    <a href="index.php?page=package_details&id=<?= $pkg['id'] ?>" class="block w-full py-3.5 rounded-full border border-gld-500 text-gld-400 font-bold hover:bg-gld-500 hover:text-pri-900 transition-colors duration-300">
                        عرض التفاصيل
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     6. المكتبة الرقمية (Clean List View)
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($featuredAudios) || !empty($featuredVideos)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 afiu">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black text-pri-900 font-amiri mb-2">المكتبة الرقمية</h2>
            <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full"></div>
            <p class="text-gray-500 text-sm mt-4">رقيات صوتية ودروس مرئية مختارة.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- الصوتيات (List View) -->
            <?php if (!empty($featuredAudios)): ?>
            <div>
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2 border-b border-gray-100 pb-3"><i class="fas fa-headphones text-pri-600"></i> الصوتيات</h3>
                <div class="space-y-3">
                    <?php foreach ($featuredAudios as $audio): ?>
                    <a href="index.php?page=audio_details&id=<?= $audio['id'] ?>" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition group no-underline">
                        <div class="w-12 h-12 rounded-full bg-pri-50 text-pri-600 flex items-center justify-center shrink-0 group-hover:bg-pri-600 group-hover:text-white transition-colors">
                            <i class="fas fa-play text-sm ml-1"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 text-sm truncate"><?= htmlspecialchars($audio['title']) ?></h4>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($audio['narrator'] ?? 'الرقية الشرعية') ?></p>
                        </div>
                        <div class="text-left shrink-0">
                            <div class="font-bold text-pri-700 text-sm"><?= $audio['price'] > 0 ? number_format($audio['price'], 0) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- الفيديوهات (Minimal Cards) -->
            <?php if (!empty($featuredVideos)): ?>
            <div>
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2 border-b border-gray-100 pb-3"><i class="fas fa-video text-gld-600"></i> الدروس المرئية</h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach (array_slice($featuredVideos, 0, 2) as $video): ?>
                    <a href="index.php?page=video_details&id=<?= $video['id'] ?>" class="block group no-underline">
                        <div class="relative aspect-video rounded-xl overflow-hidden bg-gray-900 mb-3">
                            <img src="<?= htmlspecialchars($video['thumbnail_url'] ?? 'https://picsum.photos/400/225') ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" loading="lazy">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-pri-900 shadow-md group-hover:scale-110 transition-transform"><i class="fas fa-play ml-0.5"></i></div>
                            </div>
                        </div>
                        <h4 class="font-bold text-gray-900 text-sm line-clamp-2"><?= htmlspecialchars($video['title']) ?></h4>
                        <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($video['presenter'] ?? '') ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- سكريبت السلايدر -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dotsContainer = document.getElementById('heroDots');
    let slideInterval;

    if (slides.length > 0) {
        slides.forEach((_, i) => {
            let dot = document.createElement('div');
            dot.className = `w-10 h-1.5 rounded-full cursor-pointer transition-all duration-300 ${i === 0 ? 'bg-gld-500' : 'bg-white/40 hover:bg-white/70'}`;
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
});
</script>