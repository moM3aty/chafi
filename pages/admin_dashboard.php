<?php
// مسار الملف: pages/admin_dashboard.php
// النسخة الشاملة والمطورة — سهلة الاستخدام ومربوطة بكل النظام (شاملة جميع الشارتات)

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// 1. الإحصائيات الرئيسية
$catsCount       = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$subCatsCount    = $pdo->query("SELECT COUNT(*) FROM categories WHERE parent_id IS NOT NULL")->fetchColumn();
$prodsCount      = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProds     = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$audiosCount     = $pdo->query("SELECT COUNT(*) FROM audios")->fetchColumn();
$videosCount     = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$packagesCount   = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$ordersCount     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders   = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$processingOrders= $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Processing'")->fetchColumn();
$shippedOrders   = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Shipped'")->fetchColumn();
$usersCount      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newUsersMonth   = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$reviewsCount    = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$pendingReviews  = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn();
$messagesCount   = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$newMessages     = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'New'")->fetchColumn();
$adsCount        = $pdo->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();
$couponsCount    = $pdo->query("SELECT COUNT(*) FROM coupons WHERE is_active = 1")->fetchColumn();
$tagsCount       = $pdo->query("SELECT COUNT(*) FROM tags")->fetchColumn();
$mediaCount      = $pdo->query("SELECT COUNT(*) FROM media")->fetchColumn();
$cmsPagesCount   = $pdo->query("SELECT COUNT(*) FROM cms_pages")->fetchColumn();
$zonesCount      = $pdo->query("SELECT COUNT(*) FROM shipping_zones WHERE is_active = 1")->fetchColumn();

// إحصائيات الحجوزات (الجديدة)
$apptCount       = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppt     = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$confirmedAppt   = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Confirmed'")->fetchColumn();

$totalRevenue    = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('Cancelled','Refunded')")->fetchColumn();
$monthRevenue    = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('Cancelled','Refunded') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$weekRevenue     = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('Cancelled','Refunded') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$avgOrder        = $ordersCount > 0 ? round($totalRevenue / $ordersCount, 2) : 0;
$lowStock        = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND manage_stock = 1 AND is_active = 1")->fetchColumn();
$outOfStock      = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0 AND is_active = 1")->fetchColumn();
$totalAudioListens = $pdo->query("SELECT COALESCE(SUM(listen_count),0) FROM audios")->fetchColumn();
$totalVideoViews  = $pdo->query("SELECT COALESCE(SUM(view_count),0) FROM videos")->fetchColumn();
$totalSales       = $pdo->query("SELECT COALESCE(SUM(sales_count),0) FROM products")->fetchColumn();

// 2. أحدث الطلبات والحجوزات
$recentOrders = $pdo->query("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll();
$recentAppts  = $pdo->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 5")->fetchAll();

// 3. منتجات نفاد المخزون
$lowStockProducts = $pdo->query("SELECT id, name, stock_quantity, low_stock_threshold, image_url FROM products WHERE stock_quantity <= low_stock_threshold AND manage_stock = 1 AND is_active = 1 ORDER BY stock_quantity ASC LIMIT 5")->fetchAll();

// 4. رسائل جديدة
$newMsgs = $pdo->query("SELECT id, full_name, subject, created_at FROM contact_messages WHERE status = 'New' ORDER BY id DESC LIMIT 3")->fetchAll();

// 5. إيرادات آخر 7 أيام للشارت
$dailyRevenue = $pdo->query("
    SELECT DATE(created_at) as day, SUM(total_amount) as revenue, COUNT(*) as cnt
    FROM orders WHERE status NOT IN ('Cancelled','Refunded') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY day ASC
")->fetchAll();

// 6. أفضل المنتجات مبيعاً
$topProducts = $pdo->query("SELECT id, name, price, sales_count, image_url FROM products WHERE is_active = 1 ORDER BY sales_count DESC LIMIT 4")->fetchAll();

// 7. توزيع الطلبات حسب الحالة
$statusDist = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status ORDER BY cnt DESC")->fetchAll();
$statusAr = ['Pending'=>'قيد الانتظار','Processing'=>'قيد التجهيز','Shipped'=>'تم الشحن','Delivered'=>'تم التسليم','Cancelled'=>'ملغي','Refunded'=>'مسترد','Failed'=>'فاشل'];
$statusClr = ['Pending'=>'bg-yellow-100 text-yellow-700','Processing'=>'bg-blue-100 text-blue-700','Shipped'=>'bg-purple-50 text-purple-600','Delivered'=>'bg-green-100 text-green-700','Cancelled'=>'bg-red-100 text-red-700','Refunded'=>'bg-gray-100 text-gray-600','Failed'=>'bg-red-50 text-red-500'];
$statusBarClr = ['Pending'=>'bg-yellow-400','Processing'=>'bg-blue-400','Shipped'=>'bg-purple-400','Delivered'=>'bg-green-500','Cancelled'=>'bg-red-400','Refunded'=>'bg-gray-400','Failed'=>'bg-red-300'];

$isSuperAdmin = $_SESSION['user_role'] === 'SuperAdmin';

// دالة مساعدة
function fmt($n) { return number_format($n, 0); }
function fmtD($n) { return number_format($n, 2); }
?>

<div class="max-w-[1400px] mx-auto px-4 py-6 mb-14">

    <!-- ═══ رأس الداشبورد ═══ -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-8 gap-4 afiu">
        <div>
            <h1 class="text-3xl font-black text-pri-900 font-amiri flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-gld-400 text-xl shadow-lg"><i class="fas fa-tachometer-alt"></i></div>
                لوحة القيادة الشاملة
            </h1>
            <p class="text-brk-400 text-sm mt-1 mr-4">مرحباً <?= htmlspecialchars($_SESSION['user_name']) ?> — نظرة عامة شاملة على أداء المتجر والجلسات.</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="index.php" target="_blank" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs"><i class="fas fa-external-link-alt"></i> زيارة المتجر</a>
            <a href="index.php?page=admin_reports" class="cf-btn cf-btn-pri cf-btn-sm text-xs"><i class="fas fa-chart-bar"></i> التقارير المالية</a>
        </div>
    </div>

    <!-- ═══ بطاقات الإحصائيات — الصف الأول ═══ -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6 afiu" style="animation-delay:.05s">
        <!-- الإيرادات -->
        <div class="dash-stat-card bg-gradient-to-br from-pri-600 to-pri-800 text-white border-b-4 border-gld-400 shadow-lg">
            <div class="dash-stat-icon bg-gld-400/30 text-gld-300"><i class="fas fa-wallet"></i></div>
            <div class="dash-stat-val"><?= fmt($totalRevenue) ?> <span class="text-sm font-normal">ر.س</span></div>
            <div class="dash-stat-label text-pri-100">إجمالي المبيعات</div>
            <div class="dash-stat-sub text-gld-200">المبيعات هذا الشهر: <strong><?= fmt($monthRevenue) ?> ر.س</strong></div>
        </div>

        <!-- الطلبات -->
        <a href="index.php?page=admin_orders" class="dash-stat-card bg-white border border-gray-100 text-gray-800 hover:border-blue-300 transition block">
            <div class="dash-stat-icon bg-blue-50 text-blue-500"><i class="fas fa-shopping-cart"></i></div>
            <div class="dash-stat-val text-pri-900"><?= fmt($ordersCount) ?></div>
            <div class="dash-stat-label text-gray-500">الطلبات</div>
            <div class="dash-stat-sub text-gray-500 flex justify-between">
                <span>تأكيد: <strong class="text-blue-600"><?= $pendingOrders ?></strong></span>
                <span>تجهيز: <strong class="text-orange-500"><?= $processingOrders ?></strong></span>
            </div>
        </a>

        <!-- الحجوزات (الجديدة) -->
        <a href="index.php?page=admin_appointments" class="dash-stat-card bg-white border border-gray-100 text-gray-800 hover:border-gld-300 transition block relative overflow-hidden">
            <?php if($pendingAppt > 0): ?><span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg animate-pulse">جديد</span><?php endif; ?>
            <div class="dash-stat-icon bg-gld-50 text-gld-600"><i class="fas fa-video"></i></div>
            <div class="dash-stat-val text-pri-900"><?= fmt($apptCount) ?></div>
            <div class="dash-stat-label text-gray-500">حجوزات الجلسات</div>
            <div class="dash-stat-sub text-gray-500 flex justify-between">
                <span>مراجعة: <strong class="text-red-500"><?= $pendingAppt ?></strong></span>
                <span>مؤكد: <strong class="text-green-600"><?= $confirmedAppt ?></strong></span>
            </div>
        </a>

        <!-- العملاء -->
        <div class="dash-stat-card bg-white border border-gray-100 text-gray-800">
            <div class="dash-stat-icon bg-purple-50 text-purple-500"><i class="fas fa-users"></i></div>
            <div class="dash-stat-val text-pri-900"><?= fmt($usersCount) ?></div>
            <div class="dash-stat-label text-gray-500">العملاء والرسائل</div>
            <div class="dash-stat-sub text-gray-500 flex justify-between">
                <a href="index.php?page=admin_messages" class="hover:text-pri-600">رسائل: <strong class="text-purple-600"><?= $newMessages ?></strong></a>
                <a href="index.php?page=admin_reviews" class="hover:text-pri-600">تقييمات: <strong class="text-orange-500"><?= $pendingReviews ?></strong></a>
            </div>
        </div>

        <!-- المنتجات -->
        <div class="dash-stat-card bg-white border border-gray-100 text-gray-800">
            <div class="dash-stat-icon bg-green-50 text-green-600"><i class="fas fa-box"></i></div>
            <div class="dash-stat-val text-pri-900"><?= fmt($prodsCount) ?></div>
            <div class="dash-stat-label text-gray-500">المنتجات الملموسة</div>
            <div class="dash-stat-sub text-gray-500 flex justify-between">
                <span>نشط: <strong><?= fmt($activeProds) ?></strong></span>
                <span>أقسام: <strong><?= fmt($catsCount) ?></strong></span>
            </div>
        </div>

        <!-- تنبيهات المخزون -->
        <div class="dash-stat-card <?= $lowStock > 0 ? 'bg-gradient-to-br from-red-500 to-red-700' : 'bg-gradient-to-br from-green-500 to-green-700' ?> text-white">
            <div class="dash-stat-icon bg-white/15"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="dash-stat-val"><?= $lowStock ?></div>
            <div class="dash-stat-label">مخزون منخفض</div>
            <div class="dash-stat-sub"><?= $outOfStock ?> نفد تماماً</div>
        </div>
    </div>

    <!-- ═══ بطاقات إحصائيات ثانوية ═══ -->
    <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-9 gap-3 mb-8 afiu" style="animation-delay:.1s">
        <a href="index.php?page=admin_audios" class="dash-mini-stat no-underline">
            <i class="fas fa-headphones text-gld-500"></i>
            <span class="font-black text-pri-900"><?= fmt($audiosCount) ?></span>
            <span class="text-[10px] text-brk-400">صوتيات</span>
        </a>
        <a href="index.php?page=admin_videos" class="dash-mini-stat no-underline">
            <i class="fas fa-video text-brk-500"></i>
            <span class="font-black text-pri-900"><?= fmt($videosCount) ?></span>
            <span class="text-[10px] text-brk-400">فيديوهات</span>
        </a>
        <a href="index.php?page=admin_packages" class="dash-mini-stat no-underline">
            <i class="fas fa-gift text-pri-500"></i>
            <span class="font-black text-pri-900"><?= fmt($packagesCount) ?></span>
            <span class="text-[10px] text-brk-400">باقات</span>
        </a>
        <a href="index.php?page=admin_reviews" class="dash-mini-stat no-underline">
            <i class="fas fa-star text-gld-500"></i>
            <span class="font-black text-pri-900"><?= fmt($reviewsCount) ?></span>
            <span class="text-[10px] text-brk-400">تقييمات</span>
            <?php if($pendingReviews > 0): ?><span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[8px] rounded-full flex items-center justify-center font-bold"><?= $pendingReviews ?></span><?php endif; ?>
        </a>
        <a href="index.php?page=admin_messages" class="dash-mini-stat no-underline">
            <i class="fas fa-envelope text-blue-500"></i>
            <span class="font-black text-pri-900"><?= fmt($messagesCount) ?></span>
            <span class="text-[10px] text-brk-400">رسائل</span>
            <?php if($newMessages > 0): ?><span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[8px] rounded-full flex items-center justify-center font-bold"><?= $newMessages ?></span><?php endif; ?>
        </a>
        <a href="index.php?page=admin_advertisements" class="dash-mini-stat no-underline">
            <i class="fas fa-images text-purple-500"></i>
            <span class="font-black text-pri-900"><?= fmt($adsCount) ?></span>
            <span class="text-[10px] text-brk-400">إعلانات</span>
        </a>
        <a href="index.php?page=admin_coupons" class="dash-mini-stat no-underline">
            <i class="fas fa-ticket-alt text-green-600"></i>
            <span class="font-black text-pri-900"><?= fmt($couponsCount) ?></span>
            <span class="text-[10px] text-brk-400">كوبونات</span>
        </a>
        <a href="index.php?page=admin_tags" class="dash-mini-stat no-underline">
            <i class="fas fa-tags text-gld-600"></i>
            <span class="font-black text-pri-900"><?= fmt($tagsCount) ?></span>
            <span class="text-[10px] text-brk-400">وسوم</span>
        </a>
        <a href="index.php?page=admin_media" class="dash-mini-stat no-underline">
            <i class="fas fa-photo-video text-pink-500"></i>
            <span class="font-black text-pri-900"><?= fmt($mediaCount) ?></span>
            <span class="text-[10px] text-brk-400">وسائط</span>
        </a>
    </div>

    <!-- ═══ المحتوى الرئيسي: 3 أعمدة ═══ -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ═══ العمود الأيسر: الوصول السريع (3 أعمدة) ═══ -->
        <div class="lg:col-span-3 space-y-5 afiu" style="animation-delay:.12s">
            
            <!-- إدارة المحتوى -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-boxes text-gld-500"></i> إدارة المحتوى</h3>
                <div class="dash-link-grid">
                    <a href="index.php?page=admin_categories" class="dash-link"><i class="fas fa-sitemap text-pri-500"></i><span>الأقسام</span><span class="dash-link-count"><?= fmt($catsCount) ?></span></a>
                    <a href="index.php?page=admin_category_form" class="dash-link dash-link-add"><i class="fas fa-plus-circle text-pri-400"></i><span>قسم جديد</span></a>
                    <a href="index.php?page=admin_products" class="dash-link"><i class="fas fa-box text-gld-600"></i><span>المنتجات</span><span class="dash-link-count"><?= fmt($prodsCount) ?></span></a>
                    <a href="index.php?page=admin_product_form" class="dash-link dash-link-add"><i class="fas fa-plus-circle text-gld-400"></i><span>منتج جديد</span></a>
                    <a href="index.php?page=admin_audios" class="dash-link"><i class="fas fa-headphones text-gld-500"></i><span>الصوتيات</span><span class="dash-link-count"><?= fmt($audiosCount) ?></span></a>
                    <a href="index.php?page=admin_audio_form" class="dash-link dash-link-add"><i class="fas fa-plus-circle text-gld-400"></i><span>مقطع جديد</span></a>
                    <a href="index.php?page=admin_videos" class="dash-link"><i class="fas fa-video text-brk-500"></i><span>الفيديوهات</span><span class="dash-link-count"><?= fmt($videosCount) ?></span></a>
                    <a href="index.php?page=admin_video_form" class="dash-link dash-link-add"><i class="fas fa-plus-circle text-brk-400"></i><span>فيديو جديد</span></a>
                    <a href="index.php?page=admin_packages" class="dash-link"><i class="fas fa-gift text-pri-600"></i><span>الباقات</span><span class="dash-link-count"><?= fmt($packagesCount) ?></span></a>
                    <a href="index.php?page=admin_package_form" class="dash-link dash-link-add"><i class="fas fa-plus-circle text-pri-400"></i><span>باقة جديدة</span></a>
                </div>
            </div>

            <!-- المبيعات والطلبات -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-file-invoice-dollar text-pri-500"></i> المبيعات والطلبات</h3>
                <div class="dash-link-grid">
                    <a href="index.php?page=admin_orders" class="dash-link"><i class="fas fa-list-alt text-blue-500"></i><span>كل الطلبات</span><span class="dash-link-count"><?= fmt($ordersCount) ?></span></a>
                    <?php if($pendingOrders > 0): ?>
                    <a href="index.php?page=admin_orders" class="dash-link dash-link-warn"><i class="fas fa-clock text-yellow-500"></i><span>بانتظار</span><span class="dash-link-count bg-yellow-100 text-yellow-700"><?= fmt($pendingOrders) ?></span></a>
                    <?php endif; ?>
                    <?php if($processingOrders > 0): ?>
                    <a href="index.php?page=admin_orders" class="dash-link"><i class="fas fa-cog text-blue-400"></i><span>قيد التجهيز</span><span class="dash-link-count"><?= fmt($processingOrders) ?></span></a>
                    <?php endif; ?>
                    <?php if($shippedOrders > 0): ?>
                    <a href="index.php?page=admin_orders" class="dash-link"><i class="fas fa-truck text-purple-400"></i><span>تم الشحن</span><span class="dash-link-count"><?= fmt($shippedOrders) ?></span></a>
                    <?php endif; ?>
                    <a href="index.php?page=admin_offers" class="dash-link"><i class="fas fa-tags text-red-400"></i><span>العروض</span></a>
                </div>
            </div>

            <!-- التسويق والإعدادات -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-cogs text-brk-500"></i> النظام والتسويق</h3>
                <div class="dash-link-grid">
                    <a href="index.php?page=admin_users" class="dash-link"><i class="fas fa-users text-blue-500"></i><span>المستخدمين</span><span class="dash-link-count"><?= fmt($usersCount) ?></span></a>
                    <a href="index.php?page=admin_shipping_zones" class="dash-link"><i class="fas fa-truck text-green-600"></i><span>مناطق الشحن</span><span class="dash-link-count"><?= fmt($zonesCount) ?></span></a>
                    <a href="index.php?page=admin_cms_pages" class="dash-link"><i class="fas fa-file-alt text-brk-500"></i><span>الصفحات</span><span class="dash-link-count"><?= fmt($cmsPagesCount) ?></span></a>
                    <a href="index.php?page=admin_settings" class="dash-link"><i class="fas fa-sliders-h text-brk-500"></i><span>الإعدادات</span></a>
                    <?php if($isSuperAdmin): ?>
                    <a href="index.php?page=admin_roles" class="dash-link"><i class="fas fa-shield-alt text-gld-600"></i><span>الصلاحيات</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ═══ العمود الأوسط: البيانات الحية (5 أعمدة) ═══ -->
        <div class="lg:col-span-5 space-y-5 afiu" style="animation-delay:.15s">

            <!-- أحدث حجوزات الجلسات (جديد) -->
            <div class="dash-section border-2 border-pri-100 shadow-md relative overflow-hidden">
                <div class="absolute top-0 right-0 w-1.5 h-full bg-gld-500"></div>
                <div class="flex items-center justify-between mb-0 bg-pri-50 border-b border-pri-100 p-3 px-4">
                    <h3 class="dash-section-title !mb-0 !border-0 !p-0 !bg-transparent"><i class="fas fa-video text-pri-600"></i> أحدث طلبات الجلسات</h3>
                    <a href="index.php?page=admin_appointments" class="text-xs font-bold text-pri-600 hover:text-gld-600 transition bg-white px-3 py-1 rounded-full shadow-sm">إدارة الحجوزات</a>
                </div>
                <div class="divide-y divide-gray-50 p-2">
                    <?php if(empty($recentAppts)): ?>
                        <div class="text-center py-8 text-brk-300 text-sm"><i class="fas fa-calendar-times text-2xl mb-2 block opacity-40"></i>لا توجد حجوزات</div>
                    <?php else: ?>
                        <?php foreach($recentAppts as $apt): 
                            $aptStatus = [
                                'Pending' => ['text'=>'مراجعة', 'color'=>'bg-yellow-50 text-yellow-700'],
                                'Confirmed' => ['text'=>'مؤكد', 'color'=>'bg-green-50 text-green-700'],
                                'Completed' => ['text'=>'منتهي', 'color'=>'bg-gray-100 text-gray-600'],
                                'Cancelled' => ['text'=>'ملغي', 'color'=>'bg-red-50 text-red-700']
                            ][$apt['status']] ?? ['text'=>$apt['status'], 'color'=>'bg-gray-100'];
                        ?>
                        <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-10 h-10 rounded-full bg-pri-100 text-pri-600 flex items-center justify-center text-sm font-bold shrink-0"><i class="fas fa-user"></i></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-pri-900 truncate"><?= htmlspecialchars($apt['full_name']) ?></span>
                                    <span class="badge <?= $aptStatus['color'] ?> !text-[10px] !py-0 !px-1.5"><?= $aptStatus['text'] ?></span>
                                </div>
                                <div class="text-xs text-brk-500 mt-1 truncate">الوقت: <?= htmlspecialchars($apt['preferred_time']) ?></div>
                            </div>
                            <?php if($apt['status'] == 'Confirmed'): ?>
                                <a href="index.php?page=meeting&id=<?= $apt['id'] ?>" target="_blank" class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs hover:bg-green-500 hover:text-white transition shrink-0 shadow-sm" title="دخول الغرفة"><i class="fas fa-video"></i></a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- إيرادات آخر 7 أيام — شارت بسيط -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-chart-line text-green-500"></i> إيرادات آخر 7 أيام</h3>
                <div class="p-4">
                    <?php
                    $maxRev = 1;
                    foreach($dailyRevenue as $d) { if($d['revenue'] > $maxRev) $maxRev = $d['revenue']; }
                    $dayNamesAr = ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
                    // بناء مصفوفة 7 أيام كاملة
                    $weekData = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $dateStr = date('Y-m-d', strtotime("-$i days"));
                        $found = false;
                        foreach($dailyRevenue as $d) {
                            if ($d['day'] === $dateStr) { $weekData[] = $d; $found = true; break; }
                        }
                        if (!$found) $weekData[] = ['day' => $dateStr, 'revenue' => 0, 'cnt' => 0];
                    }
                    ?>
                    <div class="space-y-2.5">
                        <?php foreach($weekData as $d):
                            $pct = $maxRev > 0 ? ($d['revenue'] / $maxRev) * 100 : 0;
                            $dayName = $dayNamesAr[date('w', strtotime($d['day']))];
                            $isToday = $d['day'] === date('Y-m-d');
                        ?>
                        <div class="flex items-center gap-3">
                            <div class="w-12 text-[10px] text-brk-400 font-bold leading-tight text-center shrink-0">
                                <div class="text-pri-700 text-xs"><?= $dayName ?></div>
                                <div dir="ltr"><?= date('m/d', strtotime($d['day'])) ?></div>
                            </div>
                            <div class="flex-1 bg-gray-100 rounded-full h-7 overflow-hidden relative">
                                <div class="h-full rounded-full transition-all duration-700 flex items-center justify-end px-3 <?= $isToday ? 'bg-gradient-to-l from-gld-400 to-gld-500' : 'bg-gradient-to-l from-pri-400 to-pri-500' ?>" style="width:<?= max($pct, $d['revenue'] > 0 ? 8 : 0) ?>%">
                                    <?php if($pct > 20): ?><span class="text-white text-[10px] font-bold whitespace-nowrap"><?= fmtD($d['revenue']) ?></span><?php endif; ?>
                                </div>
                            </div>
                            <div class="w-14 text-center shrink-0">
                                <?php if($d['revenue'] > 0): ?>
                                    <div class="text-xs font-black text-pri-700"><?= fmtD($d['revenue']) ?></div>
                                    <div class="text-[9px] text-brk-300"><?= $d['cnt'] ?> طلب</div>
                                <?php else: ?>
                                    <div class="text-xs text-brk-300">—</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 text-xs">
                        <span class="text-brk-400">هذا الأسبوع</span>
                        <span class="font-black text-pri-700"><?= fmtD($weekRevenue) ?> ر.س</span>
                    </div>
                </div>
            </div>

            <!-- أحدث الطلبات -->
            <div class="dash-section">
                <div class="flex items-center justify-between mb-0 p-3 px-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="dash-section-title !mb-0 !border-0 !p-0 !bg-transparent"><i class="fas fa-shopping-bag text-blue-500"></i> أحدث طلبات المتجر</h3>
                    <a href="index.php?page=admin_orders" class="text-xs font-bold text-pri-600 hover:text-gld-600 transition bg-white px-3 py-1 rounded-full shadow-sm">عرض الكل →</a>
                </div>
                <div class="divide-y divide-gray-50 p-2">
                    <?php if(empty($recentOrders)): ?>
                        <div class="text-center py-8 text-brk-300 text-sm"><i class="fas fa-inbox text-2xl mb-2 block opacity-40"></i>لا توجد طلبات</div>
                    <?php else: ?>
                        <?php foreach($recentOrders as $o):
                            $sClr = $statusClr[$o['status']] ?? 'bg-gray-100 text-gray-600';
                            $sAr  = $statusAr[$o['status']] ?? $o['status'];
                        ?>
                        <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold shrink-0" dir="ltr">#<?= substr($o['order_number'], -4) ?></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-pri-900 truncate"><?= htmlspecialchars($o['shipping_full_name'] ?? $o['full_name']) ?></span>
                                    <span class="badge <?= $sClr ?> !text-[9px] !py-0 !px-1.5"><?= $sAr ?></span>
                                </div>
                                <div class="text-[10px] text-brk-400 mt-1" dir="ltr"><?= date('m/d H:i', strtotime($o['created_at'])) ?></div>
                            </div>
                            <div class="text-left shrink-0">
                                <div class="text-sm font-black text-pri-700"><?= fmtD($o['total_amount']) ?> ر.س</div>
                            </div>
                            <a href="index.php?page=admin_order_details&id=<?= $o['id'] ?>" class="w-8 h-8 rounded-full bg-gray-100 text-brk-500 flex items-center justify-center text-xs hover:bg-pri-50 hover:text-pri-600 transition shrink-0" title="عرض التفاصيل"><i class="fas fa-eye"></i></a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- توزيع حالات الطلبات -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-chart-pie text-gld-500"></i> توزيع حالات الطلبات</h3>
                <div class="p-4 space-y-2.5">
                    <?php if(empty($statusDist)): ?>
                        <div class="text-center py-4 text-brk-300 text-sm">لا توجد بيانات</div>
                    <?php else: ?>
                        <?php $maxCnt = max(array_column($statusDist, 'cnt')) ?: 1; ?>
                        <?php foreach($statusDist as $s): ?>
                        <div class="flex items-center gap-3">
                            <span class="badge <?= $statusClr[$s['status']] ?? '' ?> !w-20 justify-center shrink-0 text-[10px]"><?= $statusAr[$s['status']] ?? $s['status'] ?></span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 <?= $statusBarClr[$s['status']] ?? 'bg-gray-400' ?>" style="width:<?= ($s['cnt'] / $maxCnt) * 100 ?>%"></div>
                            </div>
                            <span class="text-xs font-black text-pri-900 w-6 text-center shrink-0"><?= $s['cnt'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ═══ العمود الأيمن: التنبيهات + أفضل المنتجات + اختصارات (4 أعمدة) ═══ -->
        <div class="lg:col-span-4 space-y-5 afiu" style="animation-delay:.2s">

            <!-- إضافة سريعة (Grid) -->
            <div class="bg-gradient-to-br from-pri-50 to-gld-50/30 rounded-2xl p-5 border border-pri-100">
                <div class="text-center mb-4">
                    <div class="text-[10px] font-bold text-brk-400 uppercase tracking-wider mb-1">اختصارات إضافة سريعة</div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <a href="index.php?page=admin_product_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-gld-100 text-gld-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">منتج</span>
                    </a>
                    <a href="index.php?page=admin_audio_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">صوتي</span>
                    </a>
                    <a href="index.php?page=admin_video_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">فيديو</span>
                    </a>
                    <a href="index.php?page=admin_coupon_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">كوبون</span>
                    </a>
                    <a href="index.php?page=admin_advertisement_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-500 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">إعلان</span>
                    </a>
                    <a href="index.php?page=admin_cms_page_form" class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl hover:shadow-md transition no-underline group">
                        <div class="w-10 h-10 rounded-xl bg-brk-100 text-brk-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-plus"></i></div>
                        <span class="text-[10px] font-bold text-pri-900">صفحة</span>
                    </a>
                </div>
            </div>

            <!-- أفضل المنتجات مبيعاً -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-trophy text-gld-500"></i> أفضل المنتجات مبيعاً</h3>
                <div class="space-y-2.5 p-3">
                    <?php if(empty($topProducts)): ?>
                        <div class="text-center py-4 text-brk-300 text-sm">لا توجد مبيعات بعد</div>
                    <?php else: ?>
                        <?php foreach($topProducts as $i => $tp): ?>
                        <a href="index.php?page=admin_product_form&id=<?= $tp['id'] ?>" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100 no-underline">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black shrink-0
                                <?= $i == 0 ? 'bg-gld-500 text-white shadow-md' : ($i == 1 ? 'bg-gray-200 text-brk-600' : 'bg-gray-100 text-brk-400') ?>
                            "><?= $i + 1 ?></div>
                            <img src="<?= htmlspecialchars($tp['image_url'] ?? 'https://picsum.photos/60') ?>" class="w-9 h-9 rounded-lg object-cover border border-gray-100 shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-pri-900 truncate"><?= htmlspecialchars($tp['name']) ?></div>
                                <div class="text-[10px] text-brk-400"><?= fmtD($tp['price']) ?> ر.س</div>
                            </div>
                            <div class="text-left shrink-0 bg-gray-50 px-2 py-1 rounded">
                                <div class="text-xs font-black text-pri-700"><?= fmt($tp['sales_count']) ?></div>
                                <div class="text-[8px] text-brk-300">مبيع</div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- تنبيهات المخزون -->
            <?php if($lowStock > 0): ?>
            <div class="dash-section !border-red-200">
                <h3 class="dash-section-title !text-red-600 bg-red-50/50"><i class="fas fa-exclamation-triangle"></i> تنبيهات المخزون (<?= $lowStock ?>)</h3>
                <div class="space-y-1 p-2">
                    <?php foreach($lowStockProducts as $lp): ?>
                    <a href="index.php?page=admin_product_form&id=<?= $lp['id'] ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 transition border border-transparent hover:border-red-100">
                        <img src="<?= htmlspecialchars($lp['image_url'] ?? 'https://picsum.photos/80') ?>" class="w-8 h-8 rounded object-cover border border-gray-100 shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-pri-900 truncate"><?= htmlspecialchars($lp['name']) ?></div>
                        </div>
                        <div class="text-center shrink-0">
                            <div class="text-sm font-black <?= $lp['stock_quantity'] == 0 ? 'text-red-600' : 'text-orange-500' ?>"><?= $lp['stock_quantity'] ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- رسائل جديدة -->
            <?php if($newMessages > 0): ?>
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-envelope-open-text text-blue-500"></i> رسائل جديدة <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full ml-auto"><?= $newMessages ?></span></h3>
                <div class="space-y-2 p-3">
                    <?php foreach($newMsgs as $m): ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100">
                        <div class="w-8 h-8 min-w-[32px] rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold mt-0.5"><?= mb_substr($m['full_name'], 0, 1) ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-pri-900 truncate"><?= htmlspecialchars($m['full_name']) ?></div>
                            <div class="text-[10px] text-pri-600 truncate"><?= htmlspecialchars($m['subject'] ?? 'بدون موضوع') ?></div>
                            <div class="text-[9px] text-brk-300 mt-1" dir="ltr"><?= date('m/d H:i', strtotime($m['created_at'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="index.php?page=admin_messages" class="block text-center text-xs font-bold text-blue-600 hover:text-blue-700 mt-3 py-2 bg-blue-50 rounded-xl transition">الرد على الرسائل ←</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- إحصائيات سريعة ومختصرة -->
            <div class="dash-section">
                <h3 class="dash-section-title"><i class="fas fa-bolt text-brk-500"></i> ملخص سريع</h3>
                <div class="grid grid-cols-2 gap-2 p-3">
                    <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <div class="text-lg font-black text-pri-700"><?= fmtD($avgOrder) ?></div>
                        <div class="text-[10px] text-brk-400">متوسط الطلب (ر.س)</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <div class="text-lg font-black text-gld-600"><?= fmt($totalSales) ?></div>
                        <div class="text-[10px] text-brk-400">إجمالي المبيعات</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <div class="text-lg font-black text-blue-600"><?= fmt($totalAudioListens) ?></div>
                        <div class="text-[10px] text-brk-400">استماع صوتي</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <div class="text-lg font-black text-purple-600"><?= fmt($totalVideoViews) ?></div>
                        <div class="text-[10px] text-brk-400">مشاهدة فيديو</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>