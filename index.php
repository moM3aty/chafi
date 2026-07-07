<?php
// مسار الملف: index.php
// النسخة المحدثة — الواجهة الأمامية الشاملة وجهاز التوجيه (Router)

session_start();
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$allowed_pages = [
    // صفحات الواجهة
    'home', 'products', 'product_details', 'cart', 'checkout', 'contact', 'dashboard', 'success',
    'category', 'packages', 'package_details', 'audio_details', 'video_details', 'search', 'wishlist',
    'notifications', 'cms',
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
    'admin_tags', 'admin_media', 'admin_reports'
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
    'admin_dashboard' => 'لوحة القيادة',
    'admin_orders' => 'إدارة الطلبات',
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

require_once 'includes/header.php';

$file = "pages/{$page}.php";
if (file_exists($file)) {
    require_once $file;
} else {
    echo "<div class='max-w-7xl mx-auto px-4 py-20 text-center'>
            <div class='w-24 h-24 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-exclamation-triangle'></i></div>
            <h1 class='text-4xl font-black text-pri-900 mb-3'>404</h1>
            <p class='text-brk-500 mb-8 text-lg'>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
            <a href='index.php' class='btn btn-primary btn-lg'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
}

require_once 'includes/footer.php';
?>