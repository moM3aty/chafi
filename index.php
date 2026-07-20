<?php
// مسار الملف: index.php
// الوظيفة: الموجه الرئيسي للمتجر (Router) شامل نظام الكتب الجديد والتحميل النقي
session_start();
require_once __DIR__ . '/config.php';

// ════════════ نظام التحميل النقي للكتب ════════════
// يجب أن يكون هذا الكود في الأعلى قبل طباعة أي كود HTML (header.php) لمنع تلف ملف الـ PDF
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $book = $stmt->fetch();

    if ($book) {
        $isFree = (float)$book['price'] <= 0;
        $hasPurchased = false;
        $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

        if (isset($_SESSION['user_id']) && !$isFree && !$isAdmin) {
            $stmtCheck = $pdo->prepare("SELECT oi.id FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.item_type = 'book' AND oi.item_id = ? AND o.status IN ('Processing', 'Shipped', 'Delivered', 'Completed') LIMIT 1");
            $stmtCheck->execute([$_SESSION['user_id'], $id]);
            if ($stmtCheck->fetch()) {
                $hasPurchased = true;
            }
        }

        if ($isFree || $hasPurchased || $isAdmin) {
            $filePath = __DIR__ . '/' . ltrim($book['book_file_url'], '/');
            if (!empty($book['book_file_url']) && file_exists($filePath)) {
                $pdo->prepare("UPDATE books SET download_count = download_count + 1 WHERE id = ?")->execute([$id]);
                
                // تنظيف جميع الـ Buffers لمنع تسرب أي كود HTML إلى الـ PDF
                while (ob_get_level()) { ob_end_clean(); }
                
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $contentType = 'application/pdf';
                if ($ext === 'epub') $contentType = 'application/epub+zip';
                elseif (in_array($ext, ['doc', 'docx'])) $contentType = 'application/msword';
                
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $contentType);
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                
                // إرسال الملف وإنهاء السكربت فوراً
                readfile($filePath);
                exit; 
            } else {
                die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>عذراً، ملف الكتاب غير موجود على الخادم حالياً.</h2></div>");
            }
        } else {
            die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>غير مصرح لك بتحميل هذا الكتاب، يرجى إتمام عملية الشراء أولاً.</h2></div>");
        }
    }
}
// ════════════════════════════════════════════════════

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
$page = preg_replace('/[^a-zA-Z0-9_]/', '', $page);

$allowed_pages = [
    // صفحات الواجهة
    'home', 'products', 'product_details', 'cart', 'checkout', 'contact', 'dashboard', 'success',
    'category', 'packages', 'package_details', 'audio_details', 'video_details', 'search', 'wishlist',
    'notifications', 'cms', 'book_appointment', 'meeting', 'books', 'book_details',
    // صفحات الإدارة
    'admin_dashboard',
    'admin_orders', 'admin_order_details',
    'admin_categories', 'admin_category_form',
    'admin_products', 'admin_product_form',
    'admin_audios', 'admin_audio_form',
    'admin_videos', 'admin_video_form',
    'admin_books', 'admin_book_form', // نظام الكتب الجديد
    'admin_packages', 'admin_package_form',
    'admin_messages', 'admin_reviews',
    'admin_offers', 'admin_offer_form',
    'admin_newsletter', 'admin_settings', 'admin_settings_form', 'admin_roles',
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

$pageTitles = [
    'home' => 'الرئيسية',
    'books' => 'المكتبة والكتب',
    'book_details' => 'تفاصيل الكتاب',
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
    'admin_books' => 'إدارة الكتب',
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

$fullScreenPages = ['meeting']; // صفحات تملأ الشاشة بدون هيدر وفوتر

if (!in_array($page, $fullScreenPages)) {
    require_once __DIR__ . '/includes/header.php';
}

$file = __DIR__ . "/pages/{$page}.php";

if (file_exists($file)) {
    require_once $file;
} else {
    echo "<div class='max-w-7xl mx-auto px-4 py-20 text-center'>
            <div class='w-24 h-24 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-exclamation-triangle'></i></div>
            <h1 class='text-4xl font-black text-pri-900 mb-3'>404</h1>
            <p class='text-brk-500 mb-4 text-lg'>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
            <a href='index.php' class='btn btn-primary btn-lg'><i class='fas fa-home'></i> العودة للرئيسية</a>
          </div>";
}

if (!in_array($page, $fullScreenPages)) {
    require_once __DIR__ . '/includes/footer.php';
}
?>