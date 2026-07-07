<?php
// مسار الملف: setup_db.php
// المكان: يُحفظ في المجلد الرئيسي للمشروع (chafi/setup_db.php)
// الوظيفة: إعداد قاعدة البيانات الشاملة الخاصة بالـ ERP وزرع المحتوى الاحترافي

require_once 'config.php';

echo "<div style='font-family: Tahoma, Arial; padding: 40px; max-width: 800px; margin: 40px auto; text-align: right; background: #f8fafc; border-radius: 20px; border: 2px solid #e2e8f0; direction: rtl;'>";
echo "<h2 style='color: #1a582a; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;'>جاري بناء قاعدة بيانات متجر تشافي (النسخة الاحترافية الشاملة)... ⏳</h2>";

try {
    // تفعيل تعدد الاستعلامات (Multi-Statements)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    // استخدام NOWDOC (<<<'SQL') لمنع PHP من محاولة ترجمة المتغيرات داخل الباسوردات المشفرة
    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- =====================================================
-- 1. جدول الإعدادات العامة (settings)
-- =====================================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'مفتاح الإعداد مثل site_name',
    `setting_value` TEXT COMMENT 'قيمة الإعداد',
    `setting_type` ENUM('text','number','boolean','json','color','image') DEFAULT 'text' COMMENT 'نوع القيمة',
    `setting_group` VARCHAR(50) DEFAULT 'general' COMMENT 'مجموعة الإعداد: general, seo, social, payment',
    `label_ar` VARCHAR(200) DEFAULT NULL COMMENT 'التسمية العربية للإعداد',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='إعدادات الموقع العامة';

-- =====================================================
-- 2. جدول الوسائط / المكتبة الرقمية (media)
-- =====================================================
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_name` VARCHAR(255) NOT NULL COMMENT 'اسم الملف الأصلي',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'مسار الملف على السيرفر',
    `file_url` VARCHAR(500) NOT NULL COMMENT 'رابط الوصول للملف',
    `file_type` ENUM('image','video','audio','document','other') NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT NULL COMMENT 'مثل image/jpeg',
    `file_size` BIGINT UNSIGNED DEFAULT 0 COMMENT 'الحجم بالبايت',
    `dimensions` VARCHAR(50) DEFAULT NULL COMMENT 'الأبعاد مثل 1920x1080',
    `alt_text` VARCHAR(255) DEFAULT NULL COMMENT 'نص بديل للصور',
    `uploaded_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_file_type` (`file_type`),
    INDEX `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مكتبة الوسائط الرقمية';

-- =====================================================
-- 3. جدول المستخدمين (users) - محسّن
-- =====================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `avatar_url` VARCHAR(500) DEFAULT NULL,
    `role` ENUM('User','Admin','SuperAdmin') DEFAULT 'User',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT '1=مفعل 0=معطل',
    `email_verified_at` DATETIME DEFAULT NULL COMMENT 'تاريخ تأكيد البريد',
    `last_login_at` DATETIME DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_user_email` (`email`),
    INDEX `idx_user_role` (`role`),
    INDEX `idx_user_active` (`is_active`),
    INDEX `idx_user_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المستخدمين';

-- =====================================================
-- 4. جدول عناوين المستخدمين (user_addresses)
-- =====================================================
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_label` VARCHAR(50) DEFAULT 'المنزل' COMMENT 'مثل: المنزل، العمل',
    `full_name` VARCHAR(100) NOT NULL COMMENT 'اسم مستلم محتمل مختلف',
    `phone` VARCHAR(20) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) DEFAULT NULL COMMENT 'الحي',
    `street` VARCHAR(255) DEFAULT NULL COMMENT 'الشارع',
    `building_no` VARCHAR(50) DEFAULT NULL,
    `floor_no` VARCHAR(20) DEFAULT NULL,
    `apartment_no` VARCHAR(20) DEFAULT NULL,
    `landmark` VARCHAR(255) DEFAULT NULL COMMENT 'علامة مميزة',
    `notes` TEXT DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0 COMMENT 'العنوان الافتراضي',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_addr_user` (`user_id`),
    CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='عناوين الشحن للمستخدمين';

-- =====================================================
-- 5. جدول الأقسام - Nested Categories (categories)
-- =====================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL COMMENT 'اسم القسم',
    `slug` VARCHAR(200) NOT NULL UNIQUE COMMENT 'الرابط النظيف',
    `short_description` VARCHAR(300) DEFAULT NULL COMMENT 'وصف مختصر يظهر في الكروت',
    `description` TEXT DEFAULT NULL COMMENT 'وصف تفصيلي خاص بالقسم يظهر في صفحة القسم',
    `icon_class` VARCHAR(100) DEFAULT NULL COMMENT 'مثل fas fa-leaf',
    `color_hex` VARCHAR(7) DEFAULT '#1a582a' COMMENT 'لون القسم',
    `image_url` VARCHAR(500) DEFAULT NULL COMMENT 'صورة الغلاف',
    `banner_url` VARCHAR(500) DEFAULT NULL COMMENT 'بانر صفحة القسم',
    `parent_id` INT DEFAULT NULL COMMENT 'NULL = قسم رئيسي، غير ذلك = قسم فرعي',
    `level` TINYINT UNSIGNED DEFAULT 0 COMMENT 'المستوى: 0=رئيسي، 1=فرعي، 2=تحت فرعي',
    `path` VARCHAR(500) DEFAULT NULL COMMENT 'المسار الكامل مثل: 1,5,12',
    `sort_order` INT DEFAULT 0 COMMENT 'ترتيب العرض',
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `show_in_menu` TINYINT(1) DEFAULT 1 COMMENT 'إظهار في قائمة التنقل',
    `show_on_home` TINYINT(1) DEFAULT 1 COMMENT 'إظهار في الصفحة الرئيسية',
    `content_type` ENUM('products','videos','audios','mixed') DEFAULT 'products' COMMENT 'نوع المحتوى داخل القسم',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cat_parent` (`parent_id`),
    INDEX `idx_cat_slug` (`slug`),
    INDEX `idx_cat_active` (`is_active`),
    INDEX `idx_cat_menu` (`show_in_menu`, `is_active`),
    INDEX `idx_cat_home` (`show_on_home`, `is_active`),
    INDEX `idx_cat_sort` (`sort_order`),
    INDEX `idx_cat_content_type` (`content_type`),
    INDEX `idx_cat_level` (`level`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الأقسام المتداخلة - Nested Categories';

-- =====================================================
-- 6. جدول المنتجات (products) - محسّن
-- =====================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL COMMENT 'وصف تفصيلي بال HTML',
    `price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `old_price` DECIMAL(18,2) DEFAULT NULL COMMENT 'السعر قبل الخصم',
    `cost_price` DECIMAL(18,2) DEFAULT NULL COMMENT 'سعر التكلفة (للأدمن)',
    `currency` VARCHAR(5) DEFAULT 'SAR',
    `stock_quantity` INT DEFAULT 0,
    `low_stock_threshold` INT DEFAULT 5 COMMENT 'حد التنبيه لنفاد المخزون',
    `manage_stock` TINYINT(1) DEFAULT 1 COMMENT '1=إدارة مخزون 0=غير محدود',
    `sku` VARCHAR(100) DEFAULT NULL COMMENT 'رمز المنتج',
    `weight` DECIMAL(10,3) DEFAULT NULL COMMENT 'الوزن بالكيلوجرام',
    `length` DECIMAL(10,2) DEFAULT NULL COMMENT 'الطول بالسنتيمتر',
    `width` DECIMAL(10,2) DEFAULT NULL,
    `height` DECIMAL(10,2) DEFAULT NULL,
    `free_shipping` TINYINT(1) DEFAULT 0 COMMENT 'شحن مجاني',
    `category_id` INT DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL COMMENT 'الصورة الرئيسية',
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'منتج مميز',
    `is_digital` TINYINT(1) DEFAULT 0 COMMENT 'منتج رقمي (صوتي/فيديو)',
    `digital_file_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط التحميل للمنتج الرقمي',
    `sales_count` INT DEFAULT 0 COMMENT 'عدد المبيعات',
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_prod_category` (`category_id`),
    INDEX `idx_prod_active` (`is_active`),
    INDEX `idx_prod_featured` (`is_featured`, `is_active`),
    INDEX `idx_prod_slug` (`slug`),
    INDEX `idx_prod_price` (`price`),
    INDEX `idx_prod_sku` (`sku`),
    INDEX `idx_prod_digital` (`is_digital`),
    INDEX `idx_prod_sales` (`sales_count` DESC),
    CONSTRAINT `fk_prod_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المنتجات';

-- =====================================================
-- 7. جدول صور المنتجات الإضافية (product_images)
-- =====================================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_primary` TINYINT(1) DEFAULT 0 COMMENT 'الصورة الرئيسية',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pi_product` (`product_id`),
    CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='صور المنتجات الإضافية';

-- =====================================================
-- 8. جدول الوسوم (tags)
-- =====================================================
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `usage_count` INT DEFAULT 0 COMMENT 'عدد مرات الاستخدام',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tag_name` (`name`),
    INDEX `idx_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الوسوم';

-- =====================================================
-- 9. جدول ربط المنتجات بالوسوم (product_tags)
-- =====================================================
DROP TABLE IF EXISTS `product_tags`;
CREATE TABLE `product_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    UNIQUE INDEX `idx_pt_unique` (`product_id`, `tag_id`),
    INDEX `idx_pt_tag` (`tag_id`),
    CONSTRAINT `fk_pt_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ربط المنتجات بالوسوم';

-- =====================================================
-- 10. جدول الصوتيات (audios) - محسّن
-- =====================================================
DROP TABLE IF EXISTS `audios`;
CREATE TABLE `audios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `narrator` VARCHAR(150) DEFAULT NULL COMMENT 'القارئ / الراوي',
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(18,2) DEFAULT 0.00 COMMENT '0 = مجاني',
    `old_price` DECIMAL(18,2) DEFAULT NULL,
    `audio_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط الملف الصوتي',
    `audio_duration` INT DEFAULT NULL COMMENT 'المدة بالثواني',
    `file_size_mb` DECIMAL(10,2) DEFAULT NULL COMMENT 'حجم الملف بالميجا',
    `thumbnail_url` VARCHAR(500) DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `listen_count` INT DEFAULT 0,
    `download_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_free` TINYINT(1) DEFAULT 1 COMMENT '1=مجاني 0=مدفوع',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_audio_category` (`category_id`),
    INDEX `idx_audio_active` (`is_active`),
    INDEX `idx_audio_featured` (`is_featured`, `is_active`),
    INDEX `idx_audio_free` (`is_free`, `is_active`),
    INDEX `idx_audio_slug` (`slug`),
    INDEX `idx_audio_narrator` (`narrator`),
    INDEX `idx_audio_listens` (`listen_count` DESC),
    CONSTRAINT `fk_audio_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الصوتيات والرقيات المسجلة';

-- =====================================================
-- 11. جدول الفيديوهات (videos) - محسّن
-- =====================================================
DROP TABLE IF EXISTS `videos`;
CREATE TABLE `videos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `presenter` VARCHAR(150) DEFAULT NULL COMMENT 'المقدم / الشيخ',
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(18,2) DEFAULT 0.00 COMMENT '0 = مجاني',
    `old_price` DECIMAL(18,2) DEFAULT NULL,
    `video_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط مباشر أو YouTube',
    `video_type` ENUM('youtube','vimeo','direct','embed') DEFAULT 'youtube' COMMENT 'نوع الفيديو',
    `video_id` VARCHAR(50) DEFAULT NULL COMMENT 'معرف YouTube مثل dQw4w9WgXcQ',
    `thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'صورة المصغرة',
    `video_duration` INT DEFAULT NULL COMMENT 'المدة بالثواني',
    `category_id` INT DEFAULT NULL,
    `view_count` INT DEFAULT 0,
    `like_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_free` TINYINT(1) DEFAULT 1,
    `is_premium` TINYINT(1) DEFAULT 0 COMMENT 'محتوى مميز',
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_video_category` (`category_id`),
    INDEX `idx_video_active` (`is_active`),
    INDEX `idx_video_featured` (`is_featured`, `is_active`),
    INDEX `idx_video_free` (`is_free`, `is_active`),
    INDEX `idx_video_slug` (`slug`),
    INDEX `idx_video_presenter` (`presenter`),
    INDEX `idx_video_views` (`view_count` DESC),
    INDEX `idx_video_type` (`video_type`),
    CONSTRAINT `fk_video_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الفيديوهات والدروس المرئية';

-- =====================================================
-- 12. جدول الباقات (packages)
-- =====================================================
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `original_total_price` DECIMAL(18,2) NOT NULL COMMENT 'مجموع أسعار العناصر قبل الخصم',
    `package_price` DECIMAL(18,2) NOT NULL COMMENT 'سعر الباقة بعد الخصم',
    `discount_percentage` DECIMAL(5,2) DEFAULT 0 COMMENT 'نسبة الخصم',
    `image_url` VARCHAR(500) DEFAULT NULL,
    `category_id` INT DEFAULT NULL COMMENT 'قسم الباقة',
    `validity_days` INT DEFAULT NULL COMMENT 'مدة صلاحية الباقة بالأيام (NULL = دائمة)',
    `max_downloads` INT DEFAULT NULL COMMENT 'الحد الأقصى للتحميلات (NULL = غير محدود)',
    `sales_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `starts_at` DATETIME DEFAULT NULL COMMENT 'بداية العرض',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'نهاية العرض',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pkg_active` (`is_active`),
    INDEX `idx_pkg_featured` (`is_featured`, `is_active`),
    INDEX `idx_pkg_slug` (`slug`),
    INDEX `idx_pkg_category` (`category_id`),
    INDEX `idx_pkg_sales` (`sales_count` DESC),
    INDEX `idx_pkg_dates` (`starts_at`, `expires_at`),
    CONSTRAINT `fk_pkg_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='باقات العروض المجمعة';

-- =====================================================
-- 13. جدول عناصر الباقة (package_items)
-- =====================================================
DROP TABLE IF EXISTS `package_items`;
CREATE TABLE `package_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `item_type` ENUM('product','audio','video') NOT NULL COMMENT 'نوع العنصر',
    `item_id` INT NOT NULL COMMENT 'معرف العنصر حسب النوع',
    `item_name` VARCHAR(200) NOT NULL COMMENT 'نسخة من الاسم لتجنب الاعتماد الكلي على FK',
    `item_price` DECIMAL(18,2) NOT NULL COMMENT 'السعر الفردي للعنصر',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pki_package` (`package_id`),
    INDEX `idx_pki_type` (`item_type`),
    INDEX `idx_pki_item` (`item_type`, `item_id`),
    CONSTRAINT `fk_pki_package` FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='عناصر الباقة (منتجات + صوتيات + فيديوهات)';

-- =====================================================
-- 14. جدول الإعلانات والسلايدر (advertisements)
-- =====================================================
DROP TABLE IF EXISTS `advertisements`;
CREATE TABLE `advertisements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(300) NOT NULL COMMENT 'العنوان (يدعم HTML)',
    `subtitle` VARCHAR(300) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `link_url` VARCHAR(500) DEFAULT NULL,
    `link_text` VARCHAR(100) DEFAULT 'تصفح الآن',
    `link_target` ENUM('_self','_blank') DEFAULT '_self',
    `position` TINYINT DEFAULT 0 COMMENT '0=Hero Slider, 1=بانر جانبي, 2=بانر وسط الصفحة, 3=نافذة منبثقة',
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `click_count` INT DEFAULT 0,
    `impression_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ad_position` (`position`),
    INDEX `idx_ad_active` (`is_active`, `position`),
    INDEX `idx_ad_order` (`position`, `display_order`),
    INDEX `idx_ad_dates` (`starts_at`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الإعلانات والسلايدر';

-- =====================================================
-- 15. جدول الأوامر / الطلبات (orders)
-- =====================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'رقم الطلب الفريد',
    `user_id` INT NOT NULL,
    `sub_total` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'المجموع الفرعي',
    `discount_amount` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'مبلغ الخصم من الكوبون',
    `coupon_id` INT DEFAULT NULL,
    `shipping_cost` DECIMAL(18,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'الضريبة (إن وجدت)',
    `total_amount` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'الإجمالي النهائي',
    `status` ENUM('Pending','Processing','Shipped','Delivered','Cancelled','Refunded','Failed') DEFAULT 'Pending',
    `status_note` TEXT DEFAULT NULL COMMENT 'ملاحظات على الحالة',
    `shipping_full_name` VARCHAR(100) DEFAULT NULL,
    `shipping_phone` VARCHAR(20) DEFAULT NULL,
    `shipping_city` VARCHAR(100) DEFAULT NULL,
    `shipping_district` VARCHAR(100) DEFAULT NULL,
    `shipping_address` VARCHAR(500) DEFAULT NULL,
    `shipping_landmark` VARCHAR(255) DEFAULT NULL,
    `shipping_notes` TEXT DEFAULT NULL,
    `payment_method` ENUM('CashOnDelivery','CreditCard','BankTransfer','Wallet','ApplePay','STCPay') DEFAULT 'CashOnDelivery',
    `payment_status` ENUM('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
    `paid_at` DATETIME DEFAULT NULL,
    `transaction_id` VARCHAR(200) DEFAULT NULL COMMENT 'معرف عملية الدفع من البوابة',
    `user_notes` TEXT DEFAULT NULL COMMENT 'ملاحظات العميل',
    `admin_notes` TEXT DEFAULT NULL COMMENT 'ملاحظات الأدمن',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_order_user` (`user_id`),
    INDEX `idx_order_status` (`status`),
    INDEX `idx_order_payment` (`payment_status`),
    INDEX `idx_order_date` (`created_at` DESC),
    INDEX `idx_order_coupon` (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الطلبات';

-- =====================================================
-- 16. جدول عناصر الطلب (order_items)
-- =====================================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `item_type` ENUM('product','audio','video','package') DEFAULT 'product' COMMENT 'نوع العنصر',
    `item_id` INT DEFAULT NULL COMMENT 'معرف العنصر الأصلي',
    `item_name` VARCHAR(200) NOT NULL COMMENT 'اسم العنصر (نسخة)',
    `item_image` VARCHAR(500) DEFAULT NULL COMMENT 'صورة العنصر (نسخة)',
    `unit_price` DECIMAL(18,2) NOT NULL COMMENT 'السعر عند الطلب',
    `quantity` INT NOT NULL DEFAULT 1,
    `total_price` DECIMAL(18,2) NOT NULL,
    `digital_file_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط التحميل للمنتجات الرقمية',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_oi_order` (`order_id`),
    INDEX `idx_oi_type` (`item_type`),
    INDEX `idx_oi_item` (`item_type`, `item_id`),
    CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='عناصر الطلب (منتجات + صوتيات + فيديوهات + باقات)';

-- =====================================================
-- 17. جدول سجل حالات الطلب (order_status_history)
-- =====================================================
DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE `order_status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `note` TEXT DEFAULT NULL,
    `changed_by` INT DEFAULT NULL COMMENT 'المستخدم الذي غيّر الحالة',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_osh_order` (`order_id`),
    INDEX `idx_osh_date` (`created_at` DESC),
    CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_osh_user` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سجل تتبع حالات الطلب';

-- =====================================================
-- 18. جدول كوبونات الخصم (coupons)
-- =====================================================
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'كود الكوبون',
    `description` VARCHAR(255) DEFAULT NULL COMMENT 'وصف الكوبون',
    `discount_type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'نسبة مئوية أو مبلغ ثابت',
    `discount_value` DECIMAL(18,2) NOT NULL COMMENT 'قيمة الخصم',
    `max_discount` DECIMAL(18,2) DEFAULT NULL COMMENT 'الحد الأقصى للخصم (للنسب المئوية)',
    `min_order_amount` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'الحد الأدنى لسعر الطلب',
    `max_uses_total` INT DEFAULT NULL COMMENT 'الحد الأقصى للاستخدام الكلي',
    `max_uses_per_user` INT DEFAULT 1 COMMENT 'الحد الأقصى للاستخدام لكل مستخدم',
    `used_count` INT DEFAULT 0 COMMENT 'عدد مرات الاستخدام الفعلي',
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `applies_to` ENUM('all','products','audios','videos','packages') DEFAULT 'all' COMMENT 'ينطبق على',
    `category_ids` JSON DEFAULT NULL COMMENT 'أقسام محددة إن وجدت',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_coupon_code` (`code`),
    INDEX `idx_coupon_active` (`is_active`),
    INDEX `idx_coupon_dates` (`starts_at`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='كوبونات الخصم';

-- =====================================================
-- 19. جدول استخدام الكوبونات (coupon_usage)
-- =====================================================
DROP TABLE IF EXISTS `coupon_usage`;
CREATE TABLE `coupon_usage` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `order_id` INT NOT NULL,
    `discount_applied` DECIMAL(18,2) NOT NULL COMMENT 'مبلغ الخصم الفعلي المطبق',
    `used_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cu_coupon` (`coupon_id`),
    INDEX `idx_cu_user` (`user_id`),
    INDEX `idx_cu_order` (`order_id`),
    CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سجل استخدام الكوبونات';

-- =====================================================
-- 20. جدول التقييمات والمراجعات (reviews)
-- =====================================================
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `reviewable_type` ENUM('product','audio','video','package') NOT NULL COMMENT 'نوع العنصر المُقيَّم',
    `reviewable_id` INT NOT NULL COMMENT 'معرف العنصر',
    `rating` TINYINT UNSIGNED NOT NULL COMMENT 'التقييم من 1 إلى 5',
    `title` VARCHAR(200) DEFAULT NULL COMMENT 'عنوان المراجعة',
    `review_text` TEXT DEFAULT NULL COMMENT 'نص المراجعة',
    `is_approved` TINYINT(1) DEFAULT 0 COMMENT '1=معتمد 0=بانتظار الموافقة',
    `is_verified_purchase` TINYINT(1) DEFAULT 0 COMMENT 'شراء موثق',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rev_type` (`reviewable_type`, `reviewable_id`),
    INDEX `idx_rev_user` (`user_id`),
    INDEX `idx_rev_approved` (`is_approved`),
    INDEX `idx_rev_rating` (`rating`),
    UNIQUE INDEX `idx_rev_unique` (`user_id`, `reviewable_type`, `reviewable_id`),
    CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تقييمات ومراجعات المستخدمين';

-- =====================================================
-- 21. جدول المفضلة (wishlists)
-- =====================================================
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `wishlistable_type` ENUM('product','audio','video','package') NOT NULL,
    `wishlistable_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_wl_unique` (`user_id`, `wishlistable_type`, `wishlistable_id`),
    INDEX `idx_wl_user` (`user_id`),
    INDEX `idx_wl_type` (`wishlistable_type`, `wishlistable_id`),
    CONSTRAINT `fk_wl_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='قائمة المفضلة';

-- =====================================================
-- 22. جدول رسائل التواصل (contact_messages)
-- =====================================================
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(200) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('New','Read','Replied','Closed','Spam') DEFAULT 'New',
    `user_id` INT DEFAULT NULL COMMENT 'NULL إذا لم يكن مسجلاً',
    `admin_reply` TEXT DEFAULT NULL,
    `replied_at` DATETIME DEFAULT NULL,
    `replied_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cm_status` (`status`),
    INDEX `idx_cm_email` (`email`),
    INDEX `idx_cm_date` (`created_at` DESC),
    INDEX `idx_cm_user` (`user_id`),
    CONSTRAINT `fk_cm_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_cm_replier` FOREIGN KEY (`replied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='رسائل التواصل من الزوار';

-- =====================================================
-- 23. جدول الإشعارات (notifications)
-- =====================================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `body` TEXT DEFAULT NULL,
    `type` ENUM('order','system','promo','info') DEFAULT 'info',
    `link_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط الانتقال عند الضغط',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`user_id`, `is_read`),
    INDEX `idx_notif_date` (`created_at` DESC),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='إشعارات المستخدمين';

-- =====================================================
-- 24. جدول مناطق الشحن (shipping_zones)
-- =====================================================
DROP TABLE IF EXISTS `shipping_zones`;
CREATE TABLE `shipping_zones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `zone_name` VARCHAR(100) NOT NULL COMMENT 'مثل: الرياض، جدة، المنطقة الشرقية',
    `cities` JSON DEFAULT NULL COMMENT 'قائمة المدن التابعة للمنطقة',
    `base_cost` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'تكلفة الشحن الأساسية',
    `extra_cost_per_item` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'تكلفة إضافية لكل عنصر',
    `free_shipping_threshold` DECIMAL(18,2) DEFAULT NULL COMMENT 'شحن مجاني فوق هذا المبلغ',
    `estimated_days_min` INT DEFAULT 1 COMMENT 'أقل مدة تسليم بالأيام',
    `estimated_days_max` INT DEFAULT 3 COMMENT 'أقصى مدة تسليم بالأيام',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sz_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مناطق الشحن وتكاليفها';

-- =====================================================
-- 25. جدول الصفحات التعريفية / CMS (pages)
-- =====================================================
DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `content` LONGTEXT DEFAULT NULL COMMENT 'المحتوى بال HTML',
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cms_slug` (`slug`),
    INDEX `idx_cms_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الصفحات التعريفية (من نحن، الشروط، الخصوصية)';

-- =====================================================
-- =================== زرع البيانات ==================
-- =====================================================

-- ---------- الإعدادات ----------
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `label_ar`) VALUES
('site_name', 'تشافي للرقية الشرعية', 'text', 'general', 'اسم الموقع'),
('site_description', 'متجر تشافي الإلكتروني - منتجات وصوتيات وفيديوهات الرقية الشرعية', 'text', 'general', 'وصف الموقع'),
('site_logo', 'https://picsum.photos/seed/chafi-logo/200/200', 'image', 'general', 'شعار الموقع'),
('site_favicon', 'https://picsum.photos/seed/chafi-fav/64/64', 'image', 'general', 'أيقونة الموقع'),
('currency', 'SAR', 'text', 'general', 'العملة الافتراضية'),
('currency_name_ar', 'ريال سعودي', 'text', 'general', 'اسم العملة بالعربي'),
('currency_symbol', 'ر.س', 'text', 'general', 'رمز العملة'),
('phone', '+966 50 123 4567', 'text', 'general', 'رقم الهاتف'),
('email', 'info@tashafi.net', 'text', 'general', 'البريد الإلكتروني'),
('address', 'المملكة العربية السعودية', 'text', 'general', 'العنوان'),
('free_shipping_threshold', '200', 'number', 'general', 'حد الشحن المجاني (ريال)'),
('default_shipping_cost', '25', 'number', 'general', 'تكلفة الشحن الافتراضية'),
('whatsapp', '+966501234567', 'text', 'social', 'رقم واتساب'),
('twitter', 'https://twitter.com/tashafi', 'text', 'social', 'رابط تويتر'),
('instagram', 'https://instagram.com/tashafi', 'text', 'social', 'رابط انستقرام'),
('youtube', 'https://youtube.com/@tashafi', 'text', 'social', 'رابط يوتيوب'),
('tiktok', 'https://tiktok.com/@tashafi', 'text', 'social', 'رابط تيك توك'),
('primary_color', '#1a582a', 'color', 'general', 'اللون الأساسي'),
('accent_color', '#c8a020', 'color', 'general', 'اللون المميز'),
('enable_reviews', '1', 'boolean', 'general', 'تفعيل التقييمات'),
('enable_wishlist', '1', 'boolean', 'general', 'تفعيل المفضلة'),
('auto_approve_reviews', '0', 'boolean', 'general', 'اعتماد التقييمات تلقائياً'),
('products_per_page', '12', 'number', 'general', 'عدد المنتجات في الصفحة');

-- ---------- المستخدمين ----------
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `is_active`) VALUES
(1, 'مدير النظام', 'admin@tashafi.net', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966501234567', 'SuperAdmin', 1),
(2, 'أحمد محمد', 'ahmed@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966509876543', 'User', 1),
(3, 'فاطمة علي', 'fatima@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966507654321', 'User', 1),
(4, 'خالد عبدالله', 'khaled@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966505432167', 'User', 1);

-- ---------- الأقسام المتداخلة (Nested Categories) ----------
INSERT INTO `categories` (`id`, `name`, `slug`, `short_description`, `description`, `icon_class`, `color_hex`, `parent_id`, `level`, `path`, `sort_order`, `show_in_menu`, `show_on_home`, `content_type`) VALUES
(1, 'منتجات الرقية', 'ruqya-products', 'منتجات طبيعية مقروء عليها آيات الشفاء', 'تشمل هذه الفئة جميع المنتجات الطبيعية المستخدمة في الرقية الشرعية، بدءاً من العسل والزيت والماء المقروء عليها، مروراً بالمسك والبخور والسدر.', 'fas fa-box-open', '#1a582a', NULL, 0, '1', 1, 1, 1, 'products'),
(2, 'الصوتيات', 'audios', 'رقيات صوتية وقراءات شافية', 'قسم الصوتيات يجمع أجمل وأبلغ الرقيات الشرعية المسجلة.', 'fas fa-headphones', '#c8a020', NULL, 0, '2', 2, 1, 1, 'audios'),
(3, 'الفيديوهات', 'videos', 'دروس ومحاضرات مرئية عن الرقية الشرعية', 'قسم الفيديوهات يقدم محتوى مرئياً غنياً يشمل دروساً تعليمية حول أحكام الرقية الشرعية.', 'fas fa-video', '#5a463c', NULL, 0, '3', 3, 1, 1, 'videos'),
(4, 'الباقات والعروض', 'packages', 'باقات مجمعة بأسعار مخفضة', 'قسم الباقات يقدم تجميعات ذكية من المنتجات والصوتيات والفيديوهات بأسعار توفيرية مميزة.', 'fas fa-gift', '#0e2f18', NULL, 0, '4', 4, 1, 1, 'mixed'),
(5, 'المكتبة العلمية', 'library', 'مقالات وفتاوى وكتب عن الرقية', 'قسم المكتبة العلمية يضم مقالات متخصصة وفتاوى شرعية معتمدة من علماء الأمة.', 'fas fa-book-open', '#3f834d', NULL, 0, '5', 0, 1, 0, 'products'),
(6, 'العسل المقروء', 'ruqya-honey', 'عسل طبيعي مقروء عليه آيات الشفاء', 'يضم هذا القسم أنواع العسل الطبيعي الأصلي الذي تمت قراءة آيات الرقية الشرعية عليه.', 'fas fa-jar', '#d4a017', 1, 1, '1,6', 1, 1, 0, 'products'),
(7, 'الزيوت المقروءة', 'ruqya-oils', 'زيت زيتون وحبة البركة مقروء عليها', 'قسم الزيوت يشمل زيت الزيتون البكر الممتاز وزيت حبة البركة.', 'fas fa-oil-can', '#2d6a4f', 1, 1, '1,7', 2, 1, 0, 'products'),
(8, 'المياه المقروءة', 'ruqya-water', 'ماء زمزم وماء مقروء عليه', 'يشمل ماء زمزم المبارك وماء مقروء عليه آيات الشفاء.', 'fas fa-tint', '#1a759f', 1, 1, '1,8', 3, 1, 0, 'products'),
(9, 'المسك والبخور', 'musk-incense', 'مسك أسود وبخور مقروء عليه', 'قسم المسك والبخور يضم المسك الأسود الأصلي والبخور العودي واللباني الذكر.', 'fas fa-fire', '#8b5e3c', 1, 1, '1,9', 4, 1, 0, 'products'),
(10, 'السدر والحناء', 'sidr-henna', 'ورق سدر وحناء طبيعية للاغتسال', 'ورق السدر من أهم المواد المستخدمة في الرقية الشرعية.', 'fas fa-leaf', '#52b788', 1, 1, '1,10', 5, 1, 0, 'products'),
(11, 'الرقية العامة', 'general-ruqya-audio', 'رقية شرعية شاملة لجميع الأمراض', 'الرقية العامة هي الرقية التي يمكن لأي مسلم أن يقرأها على نفسه أو على غيره.', 'fas fa-pray', '#c8a020', 2, 1, '2,11', 1, 1, 0, 'audios'),
(12, 'رقية السحر', 'magic-ruqya-audio', 'رقية متخصصة لفك السحر', 'رقية السحر هي قراءات متخصصة تستهدف فك السحر بأنواعه المختلفة.', 'fas fa-magic', '#9b2226', 2, 1, '2,12', 2, 1, 0, 'audios'),
(13, 'رقية المس والصرع', 'possession-ruqya-audio', 'رقية متخصصة للمس والصرع', 'هذا القسم يخصص لرقية المس والصرع الشرعي.', 'fas fa-brain', '#6d597a', 2, 1, '2,13', 3, 0, 0, 'audios'),
(14, 'رقية العين والحسد', 'evil-eye-ruqya-audio', 'رقية متخصصة للعين والحسد', 'هذا القسم يقدم رقيات متخصصة للعين والحسد.', 'fas fa-eye', '#e76f51', 2, 1, '2,14', 4, 0, 0, 'audios'),
(15, 'دروس تعليمية', 'educational-videos', 'تعلم أحكام الرقية الشرعية', 'قسم الدروس التعليمية يقدم سلسلة مقاطع مرتبة.', 'fas fa-chalkboard-teacher', '#3f834d', 3, 1, '3,15', 1, 1, 0, 'videos'),
(16, 'شهادات و تجارب', 'testimonials-videos', 'قصص حقيقية لمن شفاهم الله', 'قسم الشهادات يضم مقاطع فيديو حقيقية لأشخاص شفاهم الله تعالى.', 'fas fa-heart', '#c8a020', 3, 1, '3,16', 2, 0, 0, 'videos'),
(17, 'تنبيهات ومحاذير', 'warnings-videos', 'تحذيرات من البدع والنصب', 'هذا القسم المهم ينبه على الأخطاء الشائعة والبدع المنتشرة.', 'fas fa-exclamation-triangle', '#c62828', 3, 1, '3,17', 3, 0, 0, 'videos'),
(18, 'عسل السدر', 'sidr-honey', 'عسل سدر يمني أصلي مقروء عليه', 'أجود أنواع العسل السدري اليمني الأصلي.', 'fas fa-jar', '#b8860b', 6, 2, '1,6,18', 1, 0, 0, 'products'),
(19, 'عسل الزهور البرية', 'wildflower-honey', 'عسل زهور برية طبيعي مقروء عليه', 'عسل طبيعي من رحيق الزهور البرية المتنوعة.', 'fas fa-jar', '#daa520', 6, 2, '1,6,19', 2, 0, 0, 'products'),
(20, 'عسل السمرة', 'samra-honey', 'عسل سمرة طبيعي فاخر مقروء عليه', 'عسل السمرة من أندر أنواع العسل الطبيعي.', 'fas fa-jar', '#8b4513', 6, 2, '1,6,20', 3, 0, 0, 'products');

-- ---------- المنتجات ----------
INSERT INTO `products` (`id`, `name`, `slug`, `short_description`, `description`, `price`, `old_price`, `cost_price`, `stock_quantity`, `low_stock_threshold`, `manage_stock`, `sku`, `weight`, `category_id`, `image_url`, `is_active`, `is_featured`, `is_digital`, `sales_count`) VALUES
(1, 'عسل سدر يمني فاخر - 500 جرام', 'sidr-honey-500g', 'عسل سدر أصلي من وديان اليمن مقروء عليه الرقية الشرعية الكاملة', '<p>عسل سدر يمني أصلي 100% من أجود المناطق في اليمن. تمت قراءة الرقية الشرعية الكاملة عليه بما يشمل آيات الشفاء من القرآن الكريم والأدعية النبوية المأثورة.</p><p><strong>المميزات:</strong></p><ul><li>أصلي ومضمون بشهادة تحليل</li><li>مقروء عليه الرقية الشرعية الكاملة</li><li>عبوة زجاجية محكمة الإغلاق</li><li>صالح لمدة سنتين من الإنتاج</li></ul>', 150.00, 200.00, 80.00, 50, 5, 1, 'CHF-HON-001', 0.600, 6, 'https://picsum.photos/seed/honey1/600/600', 1, 1, 0, 127),
(2, 'عسل سدر يمني - 1 كيلو', 'sidr-honey-1kg', 'عبوة كبيرة من عسل السدر اليمني الأصلي المقروء عليه', '<p>عبوة عائلية كبيرة من عسل السدر اليمني الأصلي بسعة 1 كيلوجرام. مثالي للعائلات التي ترغب في الاستمرار على العلاج لفترة طويلة. مقروء عليه الرقية الشرعية الكاملة.</p>', 275.00, 350.00, 150.00, 35, 5, 1, 'CHF-HON-002', 1.150, 6, 'https://picsum.photos/seed/honey2/600/600', 1, 1, 0, 89),
(3, 'زيت زيتون بكر ممتاز - 250 مل', 'olive-oil-250ml', 'زيت زيتون بكر ممتاز معصور على البارد مقروء عليه', '<p>زيت زيتون بكر ممتاز (Extra Virgin) معصور على البارد، من أجود أنواع الزيتون. تمت قراءة الرقية الشرعية عليه. يُستخدم للشرب والأكل والدهان كما كان يفعل السلف الصالح.</p>', 85.00, NULL, 40.00, 100, 10, 1, 'CHF-OIL-001', 0.300, 7, 'https://picsum.photos/seed/olive1/600/600', 1, 1, 0, 203),
(4, 'زيت زيتون بكر ممتاز - 500 مل', 'olive-oil-500ml', 'عبوة متوسطة من زيت الزيتون البكر المقروء عليه', '<p>عبوة 500 مل من زيت الزيتون البكر الممتاز المقروء عليه. مثالية للاستخدام اليومي المنتظم كجزء من برنامج الرقية الشرعية.</p>', 150.00, 180.00, 70.00, 60, 10, 1, 'CHF-OIL-002', 0.550, 7, 'https://picsum.photos/seed/olive2/600/600', 1, 0, 0, 145),
(5, 'زيت حبة البركة البارد - 250 مل', 'blackseed-oil-250ml', 'زيت حبة البركة المعصور على البارد مقروء عليه', '<p>زيت حبة البركة الأصلي المعصور على البارد من بذور النبي (حبة البركة). قال النبي ﷺ: (عليكم بهذه الحبة السوداء فإن فيها شفاء من كل داء إلا السام). مقروء عليه الرقية الشرعية.</p>', 95.00, 120.00, 45.00, 80, 10, 1, 'CHF-OIL-003', 0.300, 7, 'https://picsum.photos/seed/blackseed1/600/600', 1, 1, 0, 176),
(6, 'ماء زمزم مقروء عليه - 5 لتر', 'zamzam-water-5l', 'ماء زمزم مبارك عبوة 5 لتر مقروء عليه الرقية الشرعية', '<p>ماء زمزم مبارك في عبوة محكمة الإغلاق بسعة 5 لتر. تمت قراءة الرقية الشرعية الكاملة عليه. ماء زمزم لما شرب له.</p>', 45.00, NULL, 20.00, 200, 20, 1, 'CHF-WAT-001', 5.000, 8, 'https://picsum.photos/seed/zamzam/600/600', 1, 1, 0, 312),
(7, 'مسك أسود أصلي', 'black-musk-original', 'للاستخدام الخارجي قبل النوم', '<p>مسك أسود أصلي نقي. يستخدم كوقاية وعلاج.</p>', 120.00, 150.00, 60.00, 30, 5, 1, 'CHF-MSK-001', 0.100, 9, 'https://picsum.photos/seed/musk/600/600', 1, 1, 0, 85);

-- ---------- الصوتيات ----------
INSERT INTO `audios` (`id`, `title`, `slug`, `narrator`, `description`, `price`, `audio_url`, `audio_duration`, `thumbnail_url`, `category_id`, `listen_count`, `is_active`, `is_featured`, `is_free`) VALUES
(1, 'الرقية الشرعية الشاملة', 'general-ruqya-1', 'الشيخ عبدالرحمن السديس', 'رقية شرعية شاملة للعين والحسد والسحر.', 0.00, '#', 3600, 'https://picsum.photos/seed/audio1/400/400', 11, 1500, 1, 1, 1),
(2, 'رقية السحر والمس', 'magic-ruqya-1', 'الشيخ ماهر المعيقلي', 'رقية قوية ومزلزلة للسحر والمس.', 50.00, '#', 2400, 'https://picsum.photos/seed/audio2/400/400', 12, 850, 1, 1, 0);

-- ---------- الفيديوهات ----------
INSERT INTO `videos` (`id`, `title`, `slug`, `presenter`, `description`, `price`, `video_url`, `video_type`, `thumbnail_url`, `video_duration`, `category_id`, `view_count`, `is_active`, `is_featured`, `is_free`) VALUES
(1, 'كيف تحصن بيتك وأهلك؟', 'protect-home-video', 'د. محمد العريفي', 'شرح مفصل لطرق تحصين البيت والأبناء من العين والسحر.', 0.00, '#', 'youtube', 'https://picsum.photos/seed/vid1/600/340', 1200, 15, 3200, 1, 1, 1),
(2, 'علامات الشفاء من السحر', 'healing-signs-video', 'الشيخ صالح المغامسي', 'أهم العلامات التي تدل على خروج السحر من الجسد.', 100.00, '#', 'youtube', 'https://picsum.photos/seed/vid2/600/340', 1800, 16, 1400, 1, 1, 0);

-- ---------- الباقات ----------
INSERT INTO `packages` (`id`, `name`, `slug`, `description`, `short_description`, `original_total_price`, `package_price`, `discount_percentage`, `image_url`, `category_id`, `is_active`, `is_featured`) VALUES
(1, 'باقة الشفاء المتكاملة', 'healing-package-full', 'تحتوي هذه الباقة على العسل وزيت الزيتون والمسك بخصم خاص جداً لتكون عوناً لك في رحلة العلاج.', 'باقة متكاملة للرقية', 400.00, 320.00, 20.00, 'https://picsum.photos/seed/package/600/600', 4, 1, 1);

-- ---------- عناصر الباقات ----------
INSERT INTO `package_items` (`package_id`, `item_type`, `item_id`, `item_name`, `item_price`, `sort_order`) VALUES
(1, 'product', 1, 'عسل سدر يمني فاخر - 500 جرام', 150.00, 1),
(1, 'product', 3, 'زيت زيتون بكر ممتاز - 250 مل', 85.00, 2),
(1, 'product', 7, 'مسك أسود أصلي', 120.00, 3);

-- ---------- الإعلانات والسلايدر ----------
INSERT INTO `advertisements` (`id`, `title`, `subtitle`, `image_url`, `link_url`, `position`, `is_active`) VALUES 
(1, 'نقاء الجسد والروح<br>مع منتجات تشافي', 'أفضل المنتجات الطبيعية', 'https://picsum.photos/seed/slider1/1920/600', 'index.php?page=products', 0, 1),
(2, 'خصم 20% على الباقات<br>لفترة محدودة', 'عروض حصرية', 'https://picsum.photos/seed/slider2/1920/600', 'index.php?page=packages', 0, 1);

SET FOREIGN_KEY_CHECKS = 1;
SQL;

    $pdo->exec($sql);
    
    echo "<div style='background: #d1fae5; color: #047857; padding: 20px; border-radius: 10px; margin-top: 20px;'>
            <h3 style='margin-top: 0;'>🎉 تمت العملية بنجاح أسطوري!</h3>
            <p>قاعدة البيانات الآن مطابقة لنسخة نظام الـ ERP الاحترافي بالكامل، وتم إنشاء جميع الجداول الـ 25 وزرع الإعدادات والمحتوى والمستخدمين وتم حل جميع الأخطاء.</p>
            <a href='index.php' style='display:inline-block; margin-top:10px; padding:10px 20px; background:#1a582a; color:#fff; text-decoration:none; border-radius:5px;'>الانتقال للموقع الآن</a>
          </div>";

} catch (PDOException $e) {
    echo "<p style='color:red; font-weight: bold;'>❌ حدث خطأ في قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>