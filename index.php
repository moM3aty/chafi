<?php
// مسار الملف: index.php
// النسخة المحدثة (Smart Router) — الواجهة الأمامية الشاملة وجهاز التوجيه

session_start();

// استخدام المسار المطلق لضمان عمل الملفات على جميع سيرفرات Hostinger
require_once __DIR__ . '/config.php';

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

// تنظيف اسم الصفحة لتفادي أي مسافات أو أحرف غريبة
$page = preg_replace('/[^a-zA-Z0-9_]/', '', $page);

$allowed_pages = [
    // صفحات الواجهة
    'home', 'products', 'product_details', 'cart', 'checkout', 'contact', 'dashboard', 'success',
    'category', 'packages', 'package_details', 'audio_details', 'video_details', 'search', 'wishlist',
    'notifications', 'cms', 'book_appointment', 'meeting',
    // صفحات الإدارة
    'admin_dashboard',
    'admin_orders', 'admin_order_details',
    'admin_categories', 'admin_category_form',
    'admin_products', 'admin_product_form',
    'admin_audios', 'admin_audio_form',
    'admin_videos', 'admin_video_form',
    'admin_packages', 'admin_package_form',
    'admin_messages', 'admin_reviews',
    'admin_offers', 'admin_offer_form',
    'admin_newsletter', 'admin_settings', 'admin_settings_form', 'admin_roles',
    // صفحات الإدارة الجديدة
    'admin_advertisements', 'admin_advertisement_form',
    'admin_coupons', 'admin_coupon_form',
    'admin_shipping_zones', 'admin_shipping_zone_form',
    'admin_users', 'admin_user_form',
    'admin_cms_pages', 'admin_cms_page_form',
    'admin_tags', 'admin_media', 'admin_reports',
    'admin_appointments' 
];

if (!in_array($page, $allowed_pages)) {
    $page = '404';
}

// تعيين عنوان الصفحة
$pageTitles = [
    'home' => 'الرئيسية',
    'products' => 'المتجر',
    'contact' => 'تواصل معنا',
    'cart' => 'سلة التسوق',
    'checkout' => 'الدفع',
    'dashboard' => 'حسابي',
    'packages' => 'الباقات',
    'search' => 'البحث',
    'wishlist' => 'المفضلة',
    'notifications' => 'الإشعارات',
    'book_appointment' => 'احجز موعد جلسة',
    'meeting' => 'غرفة الجلسة المباشرة',
    'admin_dashboard' => 'لوحة القيادة',
    'admin_orders' => 'إدارة الطلبات',
    'admin_appointments' => 'إدارة حجوزات الرقية',
    'admin_categories' => 'إدارة الأقسام',
    'admin_products' => 'إدارة المنتجات',
    'admin_audios' => 'إدارة الصوتيات',
    'admin_videos' => 'إدارة الفيديوهات',
    'admin_packages' => 'إدارة الباقات',
    'admin_messages' => 'رسائل الزوار',
    'admin_reviews' => 'التقييمات',
    'admin_offers' => 'العروض',
    'admin_settings' => 'الإعدادات',
    'admin_roles' => 'الصلاحيات',
    'admin_advertisements' => 'الإعلانات',
    'admin_coupons' => 'كوبونات الخصم',
    'admin_shipping_zones' => 'مناطق الشحن',
    'admin_users' => 'المستخدمين',
    'admin_cms_pages' => 'الصفحات التعريفية',
    'admin_tags' => 'الوسوم',
    'admin_media' => 'المكتبة الرقمية',
    'admin_reports' => 'التقارير',
];

$pageTitle = $pageTitles[$page] ?? 'تشافي للرقية الشرعية';

// ═════ التعديل هنا: استثناء صفحة الميتينج من الهيدر والفوتر لتظهر بكامل الشاشة ═════
$fullScreenPages = ['meeting'];

if (!in_array($page, $fullScreenPages)) {
    require_once __DIR__ . '/includes/header.php';
}

// بناء مسار الملف المطلوب بدقة
$file = __DIR__ . "/pages/{$page}.php";

// التحقق من وجود الملف قبل استدعائه
if (file_exists($file)) {
    require_once $file;
} else {
    // رسالة الخطأ الذكية
    echo "<div class='max-w-7xl mx-auto px-4 py-20 text-center'>
            <div class='w-24 h-24 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-exclamation-triangle'></i></div>
            <h1 class='text-4xl font-black text-pri-900 mb-3'>404</h1>
            <p class='text-brk-500 mb-4 text-lg'>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
            <p class='text-red-500 text-xs mb-8 font-mono bg-red-50 inline-block px-4 py-2 rounded-lg' dir='ltr'>
                <b>تنبيه للمطور:</b> يرجى التأكد من أن هذا الملف موجود بهذا الاسم وبحروف صغيرة:<br> {$file}
            </p><br>
            <a href='index.php' class='btn btn-primary btn-lg'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
}

if (!in_array($page, $fullScreenPages)) {
    require_once __DIR__ . '/includes/footer.php';
}
?>