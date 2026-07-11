<?php
// مسار الملف: setup_db.php
// المكان: يُحفظ في المجلد الرئيسي للمشروع (chafi/setup_db.php)
// الوظيفة: إعداد قاعدة البيانات الشاملة بأمان (بدون حذف) وزرع المحتوى الاحترافي المنسق كـ HTML

require_once 'config.php';

echo "<div style='font-family: Tahoma, Arial; padding: 40px; max-width: 800px; margin: 40px auto; text-align: right; background: #f8fafc; border-radius: 20px; border: 2px solid #e2e8f0; direction: rtl;'>";
echo "<h2 style='color: #1a582a; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;'>جاري فحص وتحديث قاعدة البيانات وزرع المحتوى بأمان... ⏳</h2>";

try {
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- =====================================================
-- 1. جدول الإعدادات العامة (settings)
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text','number','boolean','json','color','image') DEFAULT 'text',
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `label_ar` VARCHAR(200) DEFAULT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. جدول الوسائط / المكتبة الرقمية (media)
-- =====================================================
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_url` VARCHAR(500) NOT NULL,
    `file_type` ENUM('image','video','audio','document','other') NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `file_size` BIGINT UNSIGNED DEFAULT 0,
    `dimensions` VARCHAR(50) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `uploaded_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_file_type` (`file_type`),
    INDEX `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. جدول المستخدمين (users)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `avatar_url` VARCHAR(500) DEFAULT NULL,
    `role` ENUM('User','Admin','SuperAdmin') DEFAULT 'User',
    `is_active` TINYINT(1) DEFAULT 1,
    `email_verified_at` DATETIME DEFAULT NULL,
    `last_login_at` DATETIME DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_user_email` (`email`),
    INDEX `idx_user_role` (`role`),
    INDEX `idx_user_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. جدول عناوين المستخدمين (user_addresses)
-- =====================================================
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_label` VARCHAR(50) DEFAULT 'المنزل',
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) DEFAULT NULL,
    `street` VARCHAR(255) DEFAULT NULL,
    `building_no` VARCHAR(50) DEFAULT NULL,
    `floor_no` VARCHAR(20) DEFAULT NULL,
    `apartment_no` VARCHAR(20) DEFAULT NULL,
    `landmark` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_addr_user` (`user_id`),
    CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. جدول الأقسام (categories)
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `short_description` VARCHAR(300) DEFAULT NULL,
    `description` LONGTEXT DEFAULT NULL,
    `icon_class` VARCHAR(100) DEFAULT NULL,
    `color_hex` VARCHAR(7) DEFAULT '#1a582a',
    `image_url` VARCHAR(500) DEFAULT NULL,
    `banner_url` VARCHAR(500) DEFAULT NULL,
    `parent_id` INT DEFAULT NULL,
    `level` TINYINT UNSIGNED DEFAULT 0,
    `path` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `show_in_menu` TINYINT(1) DEFAULT 1,
    `show_on_home` TINYINT(1) DEFAULT 1,
    `content_type` ENUM('products','videos','audios','mixed') DEFAULT 'products',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cat_parent` (`parent_id`),
    INDEX `idx_cat_slug` (`slug`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. جدول المنتجات (products)
-- =====================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `old_price` DECIMAL(18,2) DEFAULT NULL,
    `cost_price` DECIMAL(18,2) DEFAULT NULL,
    `currency` VARCHAR(5) DEFAULT 'SAR',
    `stock_quantity` INT DEFAULT 0,
    `low_stock_threshold` INT DEFAULT 5,
    `manage_stock` TINYINT(1) DEFAULT 1,
    `sku` VARCHAR(100) DEFAULT NULL,
    `weight` DECIMAL(10,3) DEFAULT NULL,
    `length` DECIMAL(10,2) DEFAULT NULL,
    `width` DECIMAL(10,2) DEFAULT NULL,
    `height` DECIMAL(10,2) DEFAULT NULL,
    `free_shipping` TINYINT(1) DEFAULT 0,
    `category_id` INT DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_digital` TINYINT(1) DEFAULT 0,
    `digital_file_url` VARCHAR(500) DEFAULT NULL,
    `sales_count` INT DEFAULT 0,
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_prod_category` (`category_id`),
    INDEX `idx_prod_slug` (`slug`),
    CONSTRAINT `fk_prod_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. جدول صور المنتجات (product_images)
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_primary` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. جدول الوسوم (tags)
-- =====================================================
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `usage_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. جدول ربط المنتجات بالوسوم (product_tags)
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    UNIQUE INDEX `idx_pt_unique` (`product_id`, `tag_id`),
    CONSTRAINT `fk_pt_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. جدول الصوتيات (audios)
-- =====================================================
CREATE TABLE IF NOT EXISTS `audios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `narrator` VARCHAR(150) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(18,2) DEFAULT 0.00,
    `old_price` DECIMAL(18,2) DEFAULT NULL,
    `audio_url` VARCHAR(500) DEFAULT NULL,
    `audio_duration` INT DEFAULT NULL,
    `file_size_mb` DECIMAL(10,2) DEFAULT NULL,
    `thumbnail_url` VARCHAR(500) DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `listen_count` INT DEFAULT 0,
    `download_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_free` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audio_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. جدول الفيديوهات (videos)
-- =====================================================
CREATE TABLE IF NOT EXISTS `videos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `presenter` VARCHAR(150) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(18,2) DEFAULT 0.00,
    `old_price` DECIMAL(18,2) DEFAULT NULL,
    `video_url` VARCHAR(500) DEFAULT NULL,
    `video_type` ENUM('youtube','vimeo','direct','embed') DEFAULT 'youtube',
    `video_id` VARCHAR(50) DEFAULT NULL,
    `thumbnail_url` VARCHAR(500) DEFAULT NULL,
    `video_duration` INT DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `view_count` INT DEFAULT 0,
    `like_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_free` TINYINT(1) DEFAULT 1,
    `is_premium` TINYINT(1) DEFAULT 0,
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_video_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. جدول الباقات (packages)
-- =====================================================
CREATE TABLE IF NOT EXISTS `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `original_total_price` DECIMAL(18,2) NOT NULL,
    `package_price` DECIMAL(18,2) NOT NULL,
    `discount_percentage` DECIMAL(5,2) DEFAULT 0,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `validity_days` INT DEFAULT NULL,
    `max_downloads` INT DEFAULT NULL,
    `sales_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pkg_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. عناصر الباقة (package_items)
-- =====================================================
CREATE TABLE IF NOT EXISTS `package_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `item_type` ENUM('product','audio','video') NOT NULL,
    `item_id` INT NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `item_price` DECIMAL(18,2) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pki_package` FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. الإعلانات (advertisements)
-- =====================================================
CREATE TABLE IF NOT EXISTS `advertisements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(300) NOT NULL,
    `subtitle` VARCHAR(300) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `link_url` VARCHAR(500) DEFAULT NULL,
    `link_text` VARCHAR(100) DEFAULT 'تصفح الآن',
    `link_target` ENUM('_self','_blank') DEFAULT '_self',
    `position` TINYINT DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `click_count` INT DEFAULT 0,
    `impression_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 25. الصفحات التعريفية (cms_pages)
-- =====================================================
CREATE TABLE IF NOT EXISTS `cms_pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 26. طلبات حجز الجلسات (appointments)
-- =====================================================
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(150) NOT NULL,
    `age` INT DEFAULT NULL,
    `gender` ENUM('ذكر','أنثى') NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    `preferred_time` VARCHAR(150) NOT NULL,
    `symptoms` TEXT NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `whatsapp` VARCHAR(30) NOT NULL,
    `transfer_receipt_url` VARCHAR(500) NOT NULL,
    `status` ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- زرع البيانات الأساسية والمستخدمين (INSERT IGNORE) للحماية من التكرار
-- =====================================================
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `label_ar`) VALUES
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

INSERT IGNORE INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `is_active`) VALUES
(1, 'مدير النظام', 'admin@tashafi.net', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966501234567', 'SuperAdmin', 1),
(2, 'أحمد محمد', 'ahmed@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966509876543', 'User', 1),
(3, 'فاطمة علي', 'fatima@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966507654321', 'User', 1),
(4, 'خالد عبدالله', 'khaled@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966505432167', 'User', 1);

-- الأقسام الأساسية (INSERT IGNORE للحماية)
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `short_description`, `description`, `icon_class`, `color_hex`, `parent_id`, `level`, `path`, `sort_order`, `show_in_menu`, `show_on_home`, `content_type`) VALUES
(1, 'منتجات الرقية', 'ruqya-products', 'منتجات طبيعية مقروء عليها آيات الشفاء', 'تشمل هذه الفئة جميع المنتجات الطبيعية المستخدمة في الرقية الشرعية، بدءاً من العسل والزيت والماء المقروء عليها، مروراً بالمسك والبخور والسدر.', 'fas fa-box-open', '#1a582a', NULL, 0, '1', 1, 0, 1, 'products'),
(2, 'الصوتيات', 'audios', 'رقيات صوتية وقراءات شافية', 'قسم الصوتيات يجمع أجمل وأبلغ الرقيات الشرعية المسجلة.', 'fas fa-headphones', '#c8a020', NULL, 0, '2', 2, 0, 1, 'audios'),
(3, 'الفيديوهات', 'videos', 'دروس ومحاضرات مرئية عن الرقية الشرعية', 'قسم الفيديوهات يقدم محتوى مرئياً غنياً يشمل دروساً تعليمية حول أحكام الرقية الشرعية.', 'fas fa-video', '#5a463c', NULL, 0, '3', 3, 0, 1, 'videos'),
(4, 'الباقات والعروض', 'packages', 'باقات مجمعة بأسعار مخفضة', 'قسم الباقات يقدم تجميعات ذكية من المنتجات والصوتيات والفيديوهات بأسعار توفيرية مميزة.', 'fas fa-gift', '#0e2f18', NULL, 0, '4', 4, 0, 1, 'mixed'),
(5, 'المكتبة العلمية', 'library', 'مقالات وفتاوى وكتب عن الرقية', 'قسم المكتبة العلمية يضم مقالات متخصصة وفتاوى شرعية معتمدة من علماء الأمة.', 'fas fa-book-open', '#3f834d', NULL, 0, '5', 0, 0, 0, 'products'),
(6, 'العسل المقروء', 'ruqya-honey', 'عسل طبيعي مقروء عليه آيات الشفاء', 'يضم هذا القسم أنواع العسل الطبيعي الأصلي الذي تمت قراءة آيات الرقية الشرعية عليه.', 'fas fa-jar', '#d4a017', 1, 1, '1,6', 1, 0, 0, 'products'),
(7, 'الزيوت المقروءة', 'ruqya-oils', 'زيت زيتون وحبة البركة مقروء عليها', 'قسم الزيوت يشمل زيت الزيتون البكر الممتاز وزيت حبة البركة.', 'fas fa-oil-can', '#2d6a4f', 1, 1, '1,7', 2, 0, 0, 'products'),
(8, 'المياه المقروءة', 'ruqya-water', 'ماء زمزم وماء مقروء عليه', 'يشمل ماء زمزم المبارك وماء مقروء عليه آيات الشفاء.', 'fas fa-tint', '#1a759f', 1, 1, '1,8', 3, 0, 0, 'products'),
(9, 'المسك والبخور', 'musk-incense', 'مسك أسود وبخور مقروء عليه', 'قسم المسك والبخور يضم المسك الأسود الأصلي والبخور العودي واللباني الذكر.', 'fas fa-fire', '#8b5e3c', 1, 1, '1,9', 4, 0, 0, 'products'),
(10, 'السدر والحناء', 'sidr-henna', 'ورق سدر وحناء طبيعية للاغتسال', 'ورق السدر من أهم المواد المستخدمة في الرقية الشرعية.', 'fas fa-leaf', '#52b788', 1, 1, '1,10', 5, 0, 0, 'products'),
(11, 'الرقية العامة', 'general-ruqya-audio', 'رقية شرعية شاملة لجميع الأمراض', 'الرقية العامة هي الرقية التي يمكن لأي مسلم أن يقرأها على نفسه أو على غيره.', 'fas fa-pray', '#c8a020', 2, 1, '2,11', 1, 0, 0, 'audios'),
(12, 'رقية السحر', 'magic-ruqya-audio', 'رقية متخصصة لفك السحر', 'رقية السحر هي قراءات متخصصة تستهدف فك السحر بأنواعه المختلفة.', 'fas fa-magic', '#9b2226', 2, 1, '2,12', 2, 0, 0, 'audios'),
(13, 'رقية المس والصرع', 'possession-ruqya-audio', 'رقية متخصصة للمس والصرع', 'هذا القسم يخصص لرقية المس والصرع الشرعي.', 'fas fa-brain', '#6d597a', 2, 1, '2,13', 3, 0, 0, 'audios'),
(14, 'رقية العين والحسد', 'evil-eye-ruqya-audio', 'رقية متخصصة للعين والحسد', 'هذا القسم يقدم رقيات متخصصة للعين والحسد.', 'fas fa-eye', '#e76f51', 2, 1, '2,14', 4, 0, 0, 'audios'),
(15, 'دروس تعليمية', 'educational-videos', 'تعلم أحكام الرقية الشرعية', 'قسم الدروس التعليمية يقدم سلسلة مقاطع مرتبة.', 'fas fa-chalkboard-teacher', '#3f834d', 3, 1, '3,15', 1, 0, 0, 'videos'),
(16, 'شهادات و تجارب', 'testimonials-videos', 'قصص حقيقية لمن شفاهم الله', 'قسم الشهادات يضم مقاطع فيديو حقيقية لأشخاص شفاهم الله تعالى.', 'fas fa-heart', '#c8a020', 3, 1, '3,16', 2, 0, 0, 'videos'),
(17, 'تنبيهات ومحاذير', 'warnings-videos', 'تحذيرات من البدع والنصب', 'هذا القسم المهم ينبه على الأخطاء الشائعة والبدع المنتشرة.', 'fas fa-exclamation-triangle', '#c62828', 3, 1, '3,17', 3, 0, 0, 'videos'),
(18, 'عسل السدر', 'sidr-honey', 'عسل سدر يمني أصلي مقروء عليه', 'أجود أنواع العسل السدري اليمني الأصلي.', 'fas fa-jar', '#b8860b', 6, 2, '1,6,18', 1, 0, 0, 'products'),
(19, 'عسل الزهور البرية', 'wildflower-honey', 'عسل زهور برية طبيعي مقروء عليه', 'عسل طبيعي من رحيق الزهور البرية المتنوعة.', 'fas fa-jar', '#daa520', 6, 2, '1,6,19', 2, 0, 0, 'products'),
(20, 'عسل السمرة', 'samra-honey', 'عسل سمرة طبيعي فاخر مقروء عليه', 'عسل السمرة من أندر أنواع العسل الطبيعي.', 'fas fa-jar', '#8b4513', 6, 2, '1,6,20', 3, 0, 0, 'products');

-- المنتجات الأساسية
INSERT IGNORE INTO `products` (`id`, `name`, `slug`, `short_description`, `description`, `price`, `old_price`, `cost_price`, `stock_quantity`, `low_stock_threshold`, `manage_stock`, `sku`, `weight`, `category_id`, `image_url`, `is_active`, `is_featured`, `is_digital`, `sales_count`) VALUES
(1, 'عسل سدر يمني فاخر - 500 جرام', 'sidr-honey-500g', 'عسل سدر أصلي من وديان اليمن مقروء عليه الرقية الشرعية الكاملة', '<p>عسل سدر يمني أصلي 100% من أجود المناطق في اليمن. تمت قراءة الرقية الشرعية الكاملة عليه بما يشمل آيات الشفاء من القرآن الكريم والأدعية النبوية المأثورة.</p><p><strong>المميزات:</strong></p><ul><li>أصلي ومضمون بشهادة تحليل</li><li>مقروء عليه الرقية الشرعية الكاملة</li><li>عبوة زجاجية محكمة الإغلاق</li><li>صالح لمدة سنتين من الإنتاج</li></ul>', 150.00, 200.00, 80.00, 50, 5, 1, 'CHF-HON-001', 0.600, 6, 'https://picsum.photos/seed/honey1/600/600', 1, 1, 0, 127),
(2, 'عسل سدر يمني - 1 كيلو', 'sidr-honey-1kg', 'عبوة كبيرة من عسل السدر اليمني الأصلي المقروء عليه', '<p>عبوة عائلية كبيرة من عسل السدر اليمني الأصلي بسعة 1 كيلوجرام. مثالي للعائلات التي ترغب في الاستمرار على العلاج لفترة طويلة. مقروء عليه الرقية الشرعية الكاملة.</p>', 275.00, 350.00, 150.00, 35, 5, 1, 'CHF-HON-002', 1.150, 6, 'https://picsum.photos/seed/honey2/600/600', 1, 1, 0, 89),
(3, 'زيت زيتون بكر ممتاز - 250 مل', 'olive-oil-250ml', 'زيت زيتون بكر ممتاز معصور على البارد مقروء عليه', '<p>زيت زيتون بكر ممتاز (Extra Virgin) معصور على البارد، من أجود أنواع الزيتون. تمت قراءة الرقية الشرعية عليه. يُستخدم للشرب والأكل والدهان كما كان يفعل السلف الصالح.</p>', 85.00, NULL, 40.00, 100, 10, 1, 'CHF-OIL-001', 0.300, 7, 'https://picsum.photos/seed/olive1/600/600', 1, 1, 0, 203),
(4, 'زيت زيتون بكر ممتاز - 500 مل', 'olive-oil-500ml', 'عبوة متوسطة من زيت الزيتون البكر المقروء عليه', '<p>عبوة 500 مل من زيت الزيتون البكر الممتاز المقروء عليه. مثالية للاستخدام اليومي المنتظم كجزء من برنامج الرقية الشرعية.</p>', 150.00, 180.00, 70.00, 60, 10, 1, 'CHF-OIL-002', 0.550, 7, 'https://picsum.photos/seed/olive2/600/600', 1, 0, 0, 145),
(5, 'زيت حبة البركة البارد - 250 مل', 'blackseed-oil-250ml', 'زيت حبة البركة المعصور على البارد مقروء عليه', '<p>زيت حبة البركة الأصلي المعصور على البارد من بذور النبي (حبة البركة). قال النبي ﷺ: (عليكم بهذه الحبة السوداء فإن فيها شفاء من كل داء إلا السام). مقروء عليه الرقية الشرعية.</p>', 95.00, 120.00, 45.00, 80, 10, 1, 'CHF-OIL-003', 0.300, 7, 'https://picsum.photos/seed/blackseed1/600/600', 1, 1, 0, 176),
(6, 'ماء زمزم مقروء عليه - 5 لتر', 'zamzam-water-5l', 'ماء زمزم مبارك عبوة 5 لتر مقروء عليه الرقية الشرعية', '<p>ماء زمزم مبارك في عبوة محكمة الإغلاق بسعة 5 لتر. تمت قراءة الرقية الشرعية الكاملة عليه. ماء زمزم لما شرب له.</p>', 45.00, NULL, 20.00, 200, 20, 1, 'CHF-WAT-001', 5.000, 8, 'https://picsum.photos/seed/zamzam/600/600', 1, 1, 0, 312),
(7, 'مسك أسود أصلي', 'black-musk-original', 'للاستخدام الخارجي قبل النوم', '<p>مسك أسود أصلي نقي. يستخدم كوقاية وعلاج.</p>', 120.00, 150.00, 60.00, 30, 5, 1, 'CHF-MSK-001', 0.100, 9, 'https://picsum.photos/seed/musk/600/600', 1, 1, 0, 85);

-- الصوتيات الأساسية
INSERT IGNORE INTO `audios` (`id`, `title`, `slug`, `narrator`, `description`, `price`, `audio_url`, `audio_duration`, `thumbnail_url`, `category_id`, `listen_count`, `is_active`, `is_featured`, `is_free`) VALUES
(1, 'الرقية العامة', 'general-ruqya-1', 'الشيخ أحــمد المبارك', 'الرقية الشرعية الشاملة.', 0.00, 'https://www.youtube.com/watch?v=i4tggpG4rFI', 2400, 'https://picsum.photos/seed/audio1/400/400', 11, 9999, 1, 1, 1),
(2, 'رقية المس', 'possession-ruqya-1', 'الشيخ محمــد اسحــاق', 'رقية متخصصة لعلاج المس.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio2/400/400', 13, 9999, 1, 1, 1),
(3, 'رقية السحر', 'magic-ruqya-1', 'الشيخ أحــمد المبارك', 'رقية فك السحر.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio3/400/400', 12, 9999, 1, 1, 1),
(4, 'رقية العين والحسد', 'evil-eye-ruqya-1', 'الشيخ محمــد اسحــاق', 'رقية متخصصة للعين والحسد.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio4/400/400', 14, 9999, 1, 1, 1),
(5, 'رقية الامراض المستعصية', 'severe-diseases-ruqya-1', 'الشيخ أحــمد المبارك', 'رقية الامراض المستعصية.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio5/400/400', 11, 9999, 1, 1, 1),
(6, 'رقية القلق والوسوسة', 'anxiety-ruqya-1', 'الشيخ محمــد اسحــاق', 'رقية القلق والوسوسة.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio6/400/400', 11, 9999, 1, 1, 1),
(7, 'رقية المنزل', 'home-ruqya-1', 'الشيخ أحــمد المبارك', 'رقية تحصين المنزل.', 0.00, '#', 2400, 'https://picsum.photos/seed/audio7/400/400', 11, 9999, 1, 1, 1);

-- الفيديوهات الأساسية
INSERT IGNORE INTO `videos` (`id`, `title`, `slug`, `presenter`, `description`, `price`, `video_url`, `video_type`, `thumbnail_url`, `video_duration`, `category_id`, `view_count`, `is_active`, `is_featured`, `is_free`) VALUES
(1, 'كيف تحصن بيتك وأهلك؟', 'protect-home-video', 'د. محمد العريفي', 'شرح مفصل لطرق تحصين البيت والأبناء من العين والسحر.', 0.00, '#', 'youtube', 'https://picsum.photos/seed/vid1/600/340', 1200, 15, 3200, 1, 1, 1),
(2, 'علامات الشفاء من السحر', 'healing-signs-video', 'الشيخ صالح المغامسي', 'أهم العلامات التي تدل على خروج السحر من الجسد.', 100.00, '#', 'youtube', 'https://picsum.photos/seed/vid2/600/340', 1800, 16, 1400, 1, 1, 0);

-- الباقات
INSERT IGNORE INTO `packages` (`id`, `name`, `slug`, `description`, `short_description`, `original_total_price`, `package_price`, `discount_percentage`, `image_url`, `category_id`, `is_active`, `is_featured`) VALUES
(1, 'باقة الشفاء المتكاملة', 'healing-package-full', 'تحتوي هذه الباقة على العسل وزيت الزيتون والمسك بخصم خاص جداً لتكون عوناً لك في رحلة العلاج.', 'باقة متكاملة للرقية', 400.00, 320.00, 20.00, 'https://picsum.photos/seed/package/600/600', 4, 1, 1);

-- الإعلانات
INSERT IGNORE INTO `advertisements` (`id`, `title`, `subtitle`, `image_url`, `link_url`, `position`, `is_active`) VALUES 
(1, 'نقاء الجسد والروح<br>مع منتجات تشافي', 'أفضل المنتجات الطبيعية', 'https://picsum.photos/seed/slider1/1920/600', 'index.php?page=products', 0, 1),
(2, 'خصم 20% على الباقات<br>لفترة محدودة', 'عروض حصرية', 'https://picsum.photos/seed/slider2/1920/600', 'index.php?page=packages', 0, 1);

SQL;

    $pdo->exec($sql);
    
    // التحقق من الأعمدة الناقصة وإضافتها في الجداول
    try {
        $pdo->query("SELECT transfer_receipt_url FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN transfer_receipt_url VARCHAR(500) DEFAULT NULL AFTER payment_method");
    }

    try {
        $pdo->query("SELECT transfer_receipt_url FROM appointments LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN transfer_receipt_url VARCHAR(500) NOT NULL AFTER whatsapp");
    }

    // =====================================================
    // إدراج الصفحات المستقلة (CMS) المنسقة بالـ HTML (مع الحماية INSERT IGNORE)
    // =====================================================
    $cmsPages = [
        [
            'title' => 'عن الموقع',
            'slug' => 'about',
            'content' => <<<HTML
<div class="space-y-6">
    <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">نبذة تعريفية تشافي للرقية الشرعية</h3>
    <p class="text-lg leading-loose text-brk-600 mb-4">يضم فريق تشافي نخبة من المشايخ المتخصصين في الرقية الشرعية، ممن لديهم خبرة عملية في الرقية وفق الكتاب والسنة، وسنوات من الممارسة في تقديم الرقية الشرعية والإرشاد، مع تجارب متنوعة على المستوى المحلي والخليجي.</p>
    <p class="text-lg leading-loose text-brk-600 mb-4">يعتمد الفريق في عمله على الرقية الشرعية المأثورة من القرآن الكريم والسنة النبوية الصحيحة، مع الالتزام بالضوابط الشرعية، والابتعاد عن كل ما يخالف العقيدة الإسلامية من الشعوذة أو الدجل أو الممارسات غير المشروعة.</p>
    
    <h3 class="text-xl font-bold text-pri-900 mt-8 mb-4">كما يقدم تشافي مجموعة من المنتجات التي يُقرأ عليها القرآن والأدعية الشرعية، مثل:</h3>
    <ul class="list-disc list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
        <li>ماء زمزم.</li>
        <li>الماء المقروء عليه.</li>
        <li>زيت الزيتون.</li>
        <li>الحبة السوداء.</li>
        <li>العسل الطبيعي.</li>
        <li>السدر.</li>
        <li>الزيوت الطبيعية.</li>
        <li>منتجات أخرى مباحة يتم إعدادها وفق الضوابط الشرعية.</li>
    </ul>

    <p class="text-lg leading-loose text-brk-600 mb-4">ويؤمن فريق تشافي بأن هذه المنتجات هي أسباب مباحة، وأن الشفاء بيد الله وحده، امتثالاً لقوله تعالى:</p>
    <blockquote class="bg-pri-50 border-r-4 border-pri-600 p-6 text-xl font-amiri text-pri-800 font-bold my-6 rounded-l-2xl shadow-sm">
        ﴿وَإِذَا مَرِضْتُ فَهُوَ يَشْفِينِ﴾ <span class="text-sm text-brk-400 font-normal block mt-2">[الشعراء: 80]</span>
    </blockquote>

    <p class="text-lg leading-loose text-brk-600 mb-6">ويحرص الفريق على تقديم التوجيه الشرعي الصحيح، وتعليم الأذكار والتحصينات اليومية، مع التأكيد على أهمية الأخذ بالأسباب الطبية ومراجعة الأطباء المختصين عند الحاجة، وأن الرقية الشرعية لا تغني عن العلاج الطبي فيما يتطلبه.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <div class="bg-gld-50 p-8 rounded-3xl border border-gld-200 text-center shadow-sm">
            <div class="w-16 h-16 bg-white text-gld-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 shadow"><i class="fas fa-bullseye"></i></div>
            <h4 class="font-black text-pri-900 text-xl mb-3">شعارنا</h4>
            <p class="text-lg text-gld-800 font-amiri font-bold">"بإذن الله... شفاء ورحمة"</p>
        </div>
        
        <div class="bg-pri-50 p-8 rounded-3xl border border-pri-200 text-center shadow-sm">
            <div class="w-16 h-16 bg-white text-pri-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 shadow"><i class="fas fa-paper-plane"></i></div>
            <h4 class="font-black text-pri-900 text-xl mb-3">رسالتنا</h4>
            <p class="text-base text-pri-800 leading-relaxed">نشر الرقية الشرعية الصحيحة، وتعزيز الطمأنينة والإيمان، وتقديم منتجات مباحة تُستخدم في إطار الرقية الشرعية، مع الالتزام بالمصداقية والأمانة، وأن الشفاء أولاً وآخراً من الله سبحانه وتعالى.</p>
        </div>
    </div>
</div>
HTML
        ],
        [
            'title' => 'مناقشات وردود',
            'slug' => 'discussions-replies',
            'content' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 border-b-2 border-gld-200 pb-3">الرقية الشرعية بين النقد والإنصاف</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">الحمد لله رب العالمين، والصلاة والسلام على نبينا محمد، وعلى آله وصحبه أجمعين. أما بعد:</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">فقد كثرت في الآونة الأخيرة الأصوات التي تنتقد الرقية الشرعية والرقاة، بل تجاوز بعض المنتقدين حدود النقد العلمي إلى الطعن والتشكيك في كثير من الممارسات التي أجازها الشرع، وأفتى بها علماء أهل السنة والجماعة. والمؤسف أن كثيرًا من هذه الانتقادات تصدر ممن لم يمارس الرقية الشرعية، ولم يعايش واقعها، ولم يطلع على ما يكتنفها من أحوال المرضى وتجارب العلاج، وإنما يتحدث عن أمر لم يدرسه ولم يخبره.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ومن القواعد العقلية والعلمية المقررة أن الحكم على الشيء فرع عن تصوره، فلا يصح لأحد أن يحكم على علم أو فن أو تخصص دون معرفة حقيقته وأصوله. ولهذا قال شيخ الإسلام ابن تيمية رحمه الله:</p>
        <blockquote class="bg-gray-50 border-r-4 border-pri-500 p-4 text-xl font-amiri text-pri-800 font-bold my-4 rounded-l-lg shadow-sm">
            «لا يفتي في مسائل الجهاد إلا أهل الثغور.»
        </blockquote>
        <p class="text-lg leading-loose text-brk-600 mb-4">والمقصود أن أهل الاختصاص هم الأعلم بواقع المسائل التي يباشرونها ويعايشون تفاصيلها، وإن كان الحكم الشرعي النهائي مرجعه إلى العلماء الراسخين.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">والرقية الشرعية علمٌ عملي قائم على نصوص الكتاب والسنة، وتجارب معتبرة لا تخرج عن الضوابط الشرعية، وليست قائمة على الأهواء أو الممارسات المبتدعة كما يصورها بعض الناس. فالراقي الملتزم لا يبتدع في دين الله، وإنما يجتهد في تطبيق النصوص الشرعية وفق ما دلت عليه الأدلة، وما أقره أهل العلم.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ومن المسائل التي يكثر حولها الجدل: تخصيص الراقي لآيات معينة يكررها في علاج مرض معين، سواء كان المرض عضويًا أو نفسيًا أو روحيًا. والحقيقة أن هذا التخصيص ليس تشريعًا جديدًا، ولا ادعاءً بأن تلك الآيات لا تصلح إلا لهذا المرض، وإنما هو مبني على الخبرة العملية، وما ظهر بالتجربة من نفع بعض الآيات في حالات معينة، مع الاعتقاد الجازم بأن القرآن كله شفاء، وأن النفع والضر بيد الله سبحانه وتعالى.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">وقد قرر الإمام ابن القيم رحمه الله هذا الأصل العظيم فقال:</p>
        <blockquote class="bg-gray-50 border-r-4 border-pri-500 p-4 text-lg font-amiri text-pri-800 font-bold my-4 rounded-l-lg shadow-sm">
            "فالقرآن هو الشفاء التام من جميع الأمراض القلبية والبدنية، وأدواء الدنيا والآخرة، وما كل أحد يؤهل ولا يوفق للاستشفاء به... فما من مرض من أمراض القلوب والأبدان إلا وفي القرآن سبيل الدلالة على دوائه وسببه، والحمية منه، لمن رزقه الله فهمًا في كتابه." <br><span class="text-sm text-brk-400 font-normal mt-2 inline-block">(زاد المعاد، 4/352)</span>
        </blockquote>
        <p class="text-lg leading-loose text-brk-600 mb-4">فإذا كان القرآن كله شفاء، فلا مانع أن يلاحظ الراقي بالتجربة أن بعض الآيات يظهر أثرها أكثر في حالات معينة، كما يلاحظ الطبيب أن دواءً معينًا هو الأنسب لمرض معين، مع بقاء الأصل أن الشفاء من الله وحده.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ومع ذلك، فإن الراقي الشرعي ليس مستقلًا في اجتهاده، ولا يجعل التجربة مصدرًا للتشريع، وإنما يقف عند حدود الكتاب والسنة، ويرجع فيما يشكل عليه إلى أهل العلم الراسخين، امتثالًا لقوله تعالى:</p>
        <blockquote class="bg-gray-50 border-r-4 border-gld-500 p-4 text-xl font-amiri text-gld-800 font-bold my-4 rounded-l-lg shadow-sm">
            ﴿فَاسْأَلُوا أَهْلَ الذِّكْرِ إِنْ كُنْتُمْ لَا تَعْلَمُونَ﴾
        </blockquote>
        <p class="text-lg leading-loose text-brk-600 mb-4">فالمرجع في تقرير الأحكام الشرعية هم العلماء، أما الراقي فوظيفته تطبيق ما دلت عليه النصوص، والاستفادة من الخبرة العملية فيما لا يخالف الشرع.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ومن الخطأ أن يُطعن في الرقاة الملتزمين أو تُرفض خبراتهم بمجرد أنها لم تُعرف عند غيرهم، فإن التجربة المعتبرة لها وزنها إذا انضبطت بالشرع، وقد قرر العلماء أن الأصل في العادات والتجارب الإباحة ما لم تتضمن محظورًا شرعيًا أو اعتقادًا فاسدًا.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">كما ينبغي التفريق بين الراقي الشرعي الملتزم الذي يجعل الكتاب والسنة أساس عمله، وبين من يمارس الدجل أو الشعوذة أو يستعين بالجن أو يخالف العقيدة الصحيحة؛ فهؤلاء لا يمثلون الرقية الشرعية، ولا يجوز أن تُنسب أخطاؤهم إلى هذا الباب المبارك من أبواب العلاج.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">إن الإنصاف يقتضي ألا يُحكم على الرقية الشرعية من خلال ممارسات خاطئة لبعض المنتسبين إليها، كما لا يُحكم على أي علم من خلال أخطاء بعض أفراده. بل الواجب رد المسائل إلى الكتاب والسنة، وفهم السلف الصالح، وأقوال أهل العلم المعتبرين.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">وسيأتي – بإذن الله – عرض عدد من فتاوى كبار العلماء في المسائل المتعلقة بالرقية الشرعية، ليتبين للقارئ أن كثيرًا مما يُنتقد اليوم قد سبق بيانه وإقراره من قبل أهل العلم، وحتى يكون النقاش مبنيًا على الدليل والإنصاف، بعيدًا عن الانطباعات الشخصية أو الأحكام المسبقة.</p>
        <p class="text-lg leading-loose text-brk-600 mb-8">ونسأل الله تعالى أن يجعل القرآن العظيم شفاءً ورحمة للمؤمنين، وأن يرزقنا العلم النافع، والعمل الصالح، والإخلاص في القول والعمل، وصلى الله وسلم على نبينا محمد وعلى آله وصحبه أجمعين.</p>
        <div class="bg-pri-50 border border-pri-100 p-4 rounded-xl text-pri-900 font-bold mb-8">
            أخـوكم : الشيخ القارئ أحمـد المبارك ، ونسأل الله أن يغفر للشيخ خــالد الحبشي وجعله في ميزان حسناته ووالديه.
        </div>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">حكم اخذ الأجرة على الرقية والتفرغ للناس</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">نرى ونسمع كثير من المرضى الذين يذهبون للرقاة ومما نسمع في مجالسنا ومجتمعنا من طلاب العلم وعامة الناس عند حديثهم عن الرقاة، ورقيتهم للمرضى، والمبتلين وما يأخذوه من أموال. كذلك اتهامهم وكأنهم يسرقون الناس ويبتزُّوهم ويستغلوهم.</p>
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">ومن العبارات التي نسمعها:</h4>
        <ul class="list-disc list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>إن الرقاة يسرقون الناس ويخدعونهم</li>
            <li>يستغلونَ ضعف المرضى وحاجتهم للرقية</li>
            <li>لا يجوز للراقي أخذ مقابل على رقيته وقراءته</li>
            <li>يجب على الراقي الصحيح أن لا يأخذ على رقيته إلا إذا أعطاه المريض بطيب نفس</li>
            <li>أنَّ لا يُعطى إلا بعد الشفاء</li>
            <li>الرقاة لا يقرؤون لله بل لأجل المال</li>
            <li>فلان يأخذ المال مقابل رقيته لذلك رقيته لا تنفع... وغيرها من العبارات.</li>
        </ul>
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">سبب هذه الأقوال:</h4>
        <ol class="list-decimal list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>جهل الناس بحكم أخذ الأجرة عن الرقية.</li>
            <li>ما يقع من بعض الرقاة من المبالغة في الأجر.</li>
            <li>حسد البعض على ما يأخذه الراقي.</li>
            <li>بعض الفتاوى التي لم تُوِّح هذه المسألة.</li>
        </ol>
        <p class="text-lg leading-loose text-brk-600 mb-4 font-bold">ولا نقول إلا اتقوا الله في أعراض الناس ولا تحكموا على أحد حتى تعلمُوا حُكم أخذ الأجرة على الرقية.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">إننا نَحتاج إلى وقفة في هذا الموضوع.. والمزيد تجده في كتاب أجرة الراقي ومهنته في المكتبة.</p>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">أثر الرقية المسجلة على المريض</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">كم هو عجيب أن يعيب البعض على من يتكلم بدون علم في مسألة ثم نجده يقع في نفس الأمر ويتكلم بدون علم ولايتراجع عن رأيه الخاطئ إلى الحق والصواب، ولأن أمر الرقية أمر حساس ويحتاج إلى ضبط وتقنين فإنك تجد بعض المخلصين نحسبهم كذلك والله حسيبهم يرفضون ويردون كل جديد في أمر الرقية خوفاً من الوقوع في محظور وهذا أمر واجب ومطلوب ومحمود ولكن ليس لدرجة تحريم ماأحل الله وتفسيق وتبديع من فتح الله عليهم في هذا الباب وحرصوا كل الحرص ألا يقعوا في المحذور وألا يوقعوا غيرهم في أمر محرم أو منهي عنه، ومن هذه الأمور الرقية المسجّلة على أشرطة الكاسيت أو الكمبيوتر أو بالأصح كل ماهو مسموع حتى إن كان عبر الهاتف.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">وللأسف الشديد أنك تجد من بعض المرضى وغيرهم من الناقدين بمجرد أن يرى مثل هذه الأمور وغيرها يلجأ مباشرة إلى عالم أو مفتي ويطرح عليه السؤال بطريقة غير صحيحة وسليمة تجعل المفتي يفتي بغير علم وأضرب مثال في موضوعنا فبعد ان سأل السائل عن حكم استخدام الأشرطة ومكبرات الصوت في الرقية كان الجواب : لاأرى أنها تنفع والصحيح أن يرقيه مباشرة وبالطبع الجواب في مثل هذه الفتاوى يحتاج لتروى وبحث وتدقيق وسؤال لكثير من الرقاة عن جدوى وتأثير هذا النوع من الرقية ولا يكتفى بسؤال راقٍ واحد فكل له طريقته وكل له أشرطته والآيات التي اختارها ووضعها فيها ولأهمية هذا الموضوع وشدة الحاجة الماسة إليه وتبييناً لكل من تكلم في أمر الرقية ولأنه لايجوز القول والقطع في مسألة إلا بعد مناقشتها أو يكون من الأفضل السكوت وعدم الفتوى أحببت أن أفصل هذا الموضوع قدر الإستطاعة والله الموفق.</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">فكرة إنشاء أشرطة الرقية؟</h4>
        <p class="text-lg leading-loose text-brk-600 mb-4">إذا تساءلنا كيف نشأت فكرة أشرطة الرقية أجبنا عن ذلك بالآتي:</p>
        <ol class="list-decimal list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>تأثر المرضى بأشرطة القرآن المسجلة فبعضهم يقول كنت استمع لشريط قرآن وفجاة وجدت حالي تغير وغبت عن وعيي أو شعرت برعشة ورعدة.</li>
            <li>أن مافي الأشرطة عبارة عن نقل نفس الايات ولكن بطريق آخر وكلا الطريقتين بالسماع.</li>
            <li>التسهيل لطلب الكثير من الناس للرقية الشرعية لقلة وندرة المعالجين الشرعيين.</li>
            <li>تسهيل الأمر على الناس ودرءاً لظاهرة تعلق المرضى بالرقاة وتنقلهم من بلد إلى بلد من أجل الراقي فلان وفلان.</li>
        </ol>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">ما حكم سماع أشرطة القرآن المسجلة؟</h4>
        <p class="text-lg leading-loose text-brk-600 mb-6">سماع أشرطة القرآن المسجلة جائزة كأشرطة القراء وهي موجودة في التسجيلات الإسلامية مثلها مثل الخطب والمحاضرات.</p>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">ما تأثير الأشرطة المسجلة؟</h4>
        <p class="text-lg leading-loose text-brk-600 mb-4">تأثير الأشرطة تأثير واضح ولا ينكره إلا جاهل بهذا الأمر ويظهر جلياً من خلال:</p>
        <ol class="list-decimal list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>كلام المرضى أنفسهم بنسبة كبيرة فكل مصاب سمع تأثر سواء كان التأثر كثيراً أو قليلاً.</li>
            <li>النتائج التي تحدث عند الإستماع للرقية فبعضهم قد يستفرغ السحر (كما هو ظاهر في مكتبة المرئيات) وبعضهم يخرج السحر من اسفل وبعضهم يشعر بخروج الجان منه أو حرارة أو يسمع صراخهم في داخله وبعضهم يرتعد ويرتعش وبعضهم يفيق من غيبوبته كحال المرضى في العنايات المركزة.</li>
            <li>للأسف الشديد أننا صدقنا وفرحنا كثيرا عندما قرانا وسمعنا عن تأثير القرآن على المرضى عن طريق سماعهم لشريط مسجل وأنه يقوي جهاز المناعة عندهم من خلال أبحاث في امريكا ولكن عند أمر الرقية نحجم مع أن الأصل في أمر التداوي الإباحة.</li>
            <li>إعتماد بعض المستشفيات في بلادنا الحبيبة خاصة على تشغيل القرآن حتى في العناية المركزة في بعض المستشفيات الحكومية والأهلية ومافعلوه إلا لعلمهم بجدوى ذلك.</li>
        </ol>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">سماع القرآن الكريم يقوي جهاز المناعة (حقائق علمية وطبية في القرآن)</h4>
        <p class="text-lg leading-loose text-brk-600 mb-4">ثبت علمياً أن سماع الإنسان للقرآن الكريم يعمل على تنشيط الجهاز المناعي سواء كان هذا الإنسان مسلماً أو غير مسلم ، كيف كان ذلك؟!!</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">للإجابة على هذا السؤال قدم د. أحمد القاضي “رئيس مجلس إدارة معهد الطب الإسلامي للتعليم والبحوث في أمريكا وأستاذ القلب المصري ” دارسة في مؤتمر طبي عقد في القاهرة مؤخرًا عن: “كيفية تنشيط جهاز المناعة بالجسم للتخلص من اخطر الأمراض المستعصية والمزمنة ” ويقول أن (79% ) ممن أجريت عليهم البحوث بسماعهم لكلمات القرآن الكريم سواء كانوا مسلمين أو غير مسلمين وسواء كانوا يعرفون العربية أو لا يعرفونها ظهرت عليهم تغيرات وظيفية تدل على تخفيف درجـة التـوتـر العصبي التلقائـي ،وقـد أمكن تسجيـل ذلك كله بأحدث الأجهزة العلمية وأدقها ..</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ويضيف د. أحمد القاضي :أنه من المعروف أن التوتر يؤدي إلى نقص مستوى المناعة في الجسم وهذا يظهر عن طريق إفراز بعض المواد داخل الجسم أو ربما حدوث ردود فعل بين الجهاز العصبي والغدد الصماء ،ويتسبب ذلك في إحداث خلل في التوازن الوظيفي الداخلي بالجسم ،ولذلك فإن الأثر القرآني المهدئ للتوتر يؤدي إلى تنشيط وظائف المناعة لمقاومة الأمراض والشفاء منها.</p>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">ما حكم سماع أشرطة الرقية؟</h4>
        <p class="text-lg leading-loose text-brk-600 mb-4">جاءت بعض الفتاوى في حكم سماع أشرطة الرقية بالجواز وبعضها بأن الرقية لاتتحقق لعدم وجود النية.</p>
        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-6">
            <h5 class="font-bold text-pri-900 mb-2">س: هل تتحقق الرقية بالأشرطة المسجلة؟</h5>
            <p class="text-sm text-brk-400 mb-4">تاريخ الفتوى: 27 شوال 1423 / 01-01-2003</p>
            <p class="text-lg font-bold text-pri-800 mb-2">السؤال:</p>
            <p class="text-lg leading-loose text-brk-600 mb-4">هل تجوز الرقية الشرعية بفتح شريط قرآن كريم مسجل وجعل الماء بجانبه؟</p>
            <p class="text-lg font-bold text-pri-800 mb-2">الفتوى:</p>
            <p class="text-lg leading-loose text-brk-600 mb-4">الحمد لله والصلاة والسلام على رسول الله وعلى آله وصحبه أما بعد: فسماع القرآن والرقى الشرعية من الشرائط المسجلة وإن كان خيراً، إلا أن الرقية الشرعية لا تتحقق بذلك دون أن يقرأها إنسان بنفسه. وهذا هو الأصل الذي وردت به الأدلة وسار عليه عمل العلماء، فالرقية أركانها ثلاثة: الراقي وما يرقي به والمرقي. وبقدر صلاح الراقي وتقواه بقدر ما تؤثر رقيته، وهذا لا يتأتى في الشرائط المسجلة، سواء كانت الرقية على شخص أو على ماء أو على زيت ونحو ذلك. والله أعلم.</p>
        </div>
        <p class="text-lg leading-loose text-brk-600 mb-4"><span class="font-bold text-pri-900">تعليق:</span> قولهم الراقي ومايرقي به والمرقي ، وقولهم وهذا لايتأتى في الشرائط المسجلة إنما هو اجتهاد لأن الواقع يخالف هذه الأقوال فقد ثبت وظهر ووقع وحصل المقصود وحصلت فوائد كثيرة من الأشرطة المسجلة على العرب والعجم بل حتى على الجن فبعضهم أسلم بعد سماعه لأشرطة الرقية وشريط نصيحة للجن ونطق بالشهادتين ودلّ على مكان السحر وخرج من الجسد.</p>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">متى نحتاج للرقية المسجلة؟</h4>
        <ol class="list-decimal list-inside space-y-2 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>عند انعدام الرقاة وقلتهم.</li>
            <li>لمن لايحسن القراءة سواء كان غير متعلم أوأعجمي أو صغير أو مجنون.</li>
            <li>لتكثيف أمر الرقية لعدم قدرة المريض على القراءة المستمرة.</li>
            <li>عند النوم لها تأثير عجيب وأيضاً لعدم استطاعة المريض على رقية نفسه حال النوم.</li>
            <li>لتسهيل مهمة الراقي في كثير من الحالات.</li>
            <li>المستشفيات والمصحات العقلية وهو حاصل وموجود.</li>
            <li>البيوت المسكونة بالجن سواء بسحر أو أذى فللقراءة المستمرة عبر المسجلات نتائج مرضية.</li>
        </ol>
    </section>
</div>
HTML
        ],
        [
            'title' => 'حكم اختيار الايات فالعلاج',
            'slug' => 'rulings-verses',
            'content' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 border-b-2 border-gld-200 pb-3">مقـدمـة</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">كم نرى ونسمع في كل حين من ردود وتهجمات على الرقية والرقاة وخاصة فيما أباحه الشارع وأفتى فيه أهل العلم من أهل السنة. والعجب العجاب أن المعارض ليس من أهل الرقية، ولم يدخل عالم الجن والعلاج وما يحدث فيه وإنما حاله حال الذي يعيب الشيء وهو لا يعرفه. كمثال المهندس الذي ينكر ويعيب على الطبيب وهو جاهل بصنعته.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">وقد قال شيخ الإسلام ابن تيمية: [لا يفتي في مسائل الجهاد إلا أهل الثغور]. لماذا؟؟ لأنهم يرون ما يحدث.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">ومن هذا المنطلق، ومن باب بيان منهج العلاج بالقرآن والرقية الشرعية أحببت أن أوضح أن اختيار الراقي لآيات معينة يرقي بها ويرددها على المرضى سواء كان المرض عضويًا أو نفسيًا أو روحيًا إنما هو حصيلة خبرته والحالات التي مرت عليه.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">وكما قال ابن القيم: [فالقرآن هو الشفاء التام من جميع الأمراض القلبية والبدنية وامراض الدنيا والآخرة وما كلّ أحد يؤهل ولايوفق للاستشفاء به، … إلى أن قال: فما من مرض من أمراض القلوب والأبدان إلا وفي القرآن سبيل الدلالة على دوائه وسببه، والحمية منه لمن رزقه الله فهمًا في كتابه] [زاد المعاد: ج4].</p>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">تخصيص آيات معينة بأعداد محددة لأمراض معينة</h3>
        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-6">
            <h4 class="font-bold text-pri-800 text-xl mb-2">سؤال:</h4>
            <p class="text-lg leading-loose text-brk-600 mb-6">ما حكم تخصيص آيات معينة وتكرارها بأعداد محددة لعلاج أمراض معينة مثال أن يقرأ آيات معينة من سورة معينة ويكررها بأعداد محددة لمرض السرطان مثلا، وغيرها لمرض آخر إلى غير ذلك؟</p>
            <h4 class="font-bold text-pri-800 text-xl mb-2">الجواب:</h4>
            <p class="text-lg leading-loose text-brk-600 mb-4">قال الله تعالى: “وَنُنَزِّلُ مِنَ الْقُرْآنِ مَا هُوَ شِفَاءٌ وَرَحْمَةٌ لِلْمُؤْمِنِينَ” فظاهر الآية أن من القرآن آيات تكون قراءتها سببًا للشفاء والرحمة، وقيل: إن (من) لبيان الجنس؛ أي: إن جنس القرآن شفاء ورحمة، ولا شك أن هناك آيات ورد فيها ما يدل على الاستشفاء بها، وقد ثبت في حديث أبي سعيد قراءة سورة الفاتحة كعلاج للديغ، فأقر ذلك النبي – صلى الله عليه وسلم – وقال: (وما أدراك أنها رقية) وفي حديث آخر: (فاتحة الكتاب شفاء من كل داء). وثبت أن آية الكرسي سبب للحفظ من وسوسة الشيطان ورويت آثار عن السلف من الصحابة والتابعين في العلاج ببعض الآيات القرآنية والأدعية النبوية وجربت آيات السحر الثلاث في سورة الأعراف ويونس وطه، فوجدت مؤثرة في حل السحر وفي علاج المحبوس عن أهله، وكذا قراءة المعوذتين، ولا بأس بتكرار القراءة والاستعاذة، كما ورد: أن النبي صلى الله عليه وسلم كان ينفث في يديه بعد جمعهما، ويقرأ آية الكرسي وسورتي الإخلاص والمعوذتين، ويمسح بهما ما أقبل من جسده فلا إنكار على من فعل ذلك أو نحوه، والله أعلم. [ابن جبرين الفتاوى الذهبية].</p>
        </div>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">تكرار بعض الآيات لأمراض معينه دون اعتقاد فيها</h3>
        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-6">
            <h4 class="font-bold text-pri-800 text-xl mb-2">سؤال:</h4>
            <p class="text-lg leading-loose text-brk-600 mb-6">هناك من القراء من يخصص بعض الآيات لأمراض معينة مع تكرارها بأعداد معينة، مع عدم اعتقادهم بأن العدد هو السبب في الشفاء، فما حكم هذا التخصيص؟ وما حكم التكرار؟</p>
            <h4 class="font-bold text-pri-800 text-xl mb-2">الجواب:</h4>
            <p class="text-lg leading-loose text-brk-600 mb-4">لا شك أن القرآن شفاء كما أخبر الله تعالى بقوله تعالى: “قُلْ هُوَ لِلَّذِينَ آمَنُوا هُدًى وَشِفَاءٌ وقوله: يَا أَيُّهَا النَّاسُ قَدْ جَاءَتْكُمْ مَوْعِظَةٌ مِنْ رَبِّكُمْ وَشِفَاءٌ لِمَا فِي الصُّدُورِ وَهُدًى وَرَحْمَةٌ لِلْمُؤْمِنِينَ” فأما قوله تعالى: “وَنُنَزِّلُ مِنَ الْقُرْآنِ مَا هُوَ شِفَاءٌ وَرَحْمَةٌ لِلْمُؤْمِنِينَ” فقال كثير من العلماء: [ إن (مِن) ليست للتبعيض، وإنما هي لبيان الجنس، أي جنس القرآن، ومع ذلك فإن في القرآن آيات لها خاصية في العلاج بها، ولها تأثير في المرقى بها، ومن ذلك فاتحة الكتاب؛ ففي حديث أبي سعيد أن النبي صلى الله عليه وسلم قال للذي رقى بها: (وما أدراك أنها رقية)].</p>
            <p class="text-lg leading-loose text-brk-600 mb-4">وقد ورد فضل آيات خاصة، كآية الكرسي ونحوها، وسورتي المعوذتين؛ فقد قال النبي صلى الله عليه وسلم: (ما تعوّذ الناس بمثلهما وكذا سورة الإخلاص)، والآيتان من آخر سورة البقرة، فأما تكرارها ثلاثًا أو نحو ذلك فلا بأس، فإن القراءة مفيدة، سواء تكررت أو أفردت، لكن التكرار والإكثار أقوى تأثيرًا.</p>
            <p class="text-sm font-bold text-brk-500 mt-2">المصدر: [الفتاوى الذهبية الجبرين]</p>
        </div>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">لماذا يختار الراقي آيات لكل مرض؟</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">القرآن كله شفاء ولكن مع كثرة القراءة واختلاف الحالات وتسجيل الملاحظات يبدأ الراقي في اختيار آيات تناسب حالة المريض وليس هذا تشريع بل من باب قوله تعالى وننزل من القرآن ماهو شفاء ومن قول المصطفى علمه من علمه وجهله من جهله ومن أقوال الصحابة والعلماء واختيارهم بعض الآيات في العلاج ويقع اختيار الراقي للآيات لأسباب أذكر بعضها:</p>
        <ol class="list-decimal list-inside space-y-4 text-lg text-brk-600 mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
            <li>نظرة الراقي في آيات الرقية الشرعية نفسها وأنها مختلفة توضح أن الأمر واسع.</li>
            <li>تأثر الجان مثلاً عند قراءة بداية الصافات توضح أنهم يعذبون بآيات العذاب والنار والشهب ومنه يبدأ الراقي في قراءة بعض هذه الآيات فيرى شدة تأثيرها فيجمع كل الآيات التي فيها معنى العذاب أو النار ونحوها.</li>
            <li>بحث الراقي في هذا الباب يوصله إلى بعض ما فعله الصحابة وإلى ما فعله ائمة أهل السنة كالإمام أحمد وشيخ الإسلام ابن تيمية وابن القيم وغيرهم.</li>
            <li>بعض المرضى يسأل عن أمور يراها فمثلاً يقول دعوت الله كثيراً وألححت في الدعاء وسألت الله أن يريني دائي ودوائي فيقول رأيت أني مصاب بالعين ورأيتني وأنا أقرأ قوله تعالى: “وَإِن يَكَادُ الَّذِينَ كَفَرُوا لَيُزْلِقُونَكَ بِأَبْصَارِهِمْ لَمَّا سَمِعُوا الذِّكْرَ وَيَقُولُونَ إِنَّهُ لَمَجْنُونٌ ”.</li>
            <li>الراقي الذي لا يلزم نفسه بآيات الرقية فقط بل يقرأ من كل مكان يرى العجب العجاب فمرة قرأت آيات عذاب وكان منها “ثُمَّ فِي سِلْسِلَةٍ ذَرْعُهَا سَبْعُونَ ذِرَاعاً فَاسْلُكُوهُ” [الحاقة: 32] فإذا بإحدى المريضات تصرخ وتتشحط كأنها مربوطة...</li>
            <li>في الرقية الجماعية بالذات يفتح على الراقي عند رقيته ومروره على كثير من الآيات فمرة كنت أقرأ ومررت على آية تحث على الصدقة فإذا أحدهم يتحرك ثم قرأت آية أخرى في الصدقة حتى زادت الحركة وآية أخرى: “وَأَمَّا السَّائِلَ فَلَا تَنْهَرْ” فصرخ وشق ثوبه.</li>
            <li>من أسباب اختيار الآيات وفوائدها أنها تقرأ في الحالات النفسية والحزن وهذا لابد أن يتفطن له المعالج والطبيب.</li>
        </ol>
    </section>

    <section>
        <h3 class="text-2xl font-black text-pri-900 mb-4 mt-12 border-b-2 border-gld-200 pb-3">منهج اختيار الآيـات من موقع لقط المرجان</h3>
        <p class="text-lg leading-loose text-brk-600 mb-4">لعل الذي يقرأ في السطور التالية يظن أن لكل مرضٍ آية معينة وهذا التخصيص الذي لا أعنيه أبدًا، ويكفي للقارئ أن يقرأ سورة الفاتحة على أي مرض فهي رقية كما ورد عن المصطفى صلى الله عليه وسلم، أو يقرأ سورة البقرة فإن أخذها بركة، أو يقرأ آية الكرسي فهي أعظم آية في القرآن، أو يقرأ المعوذات فما استعاذ مستعيذ بمثلهما؛ ولكنني عندما أذكر بعض الأمراض وما يقرأ عليها من آيات مع آيات الرقية فهو من قبيل التبرك فيما تتضمنه الآية من معنى يتناسب مع حال المرض.</p>
        <p class="text-lg leading-loose text-brk-600 mb-4">يقول ابن القيم: [هنا أمور ثلاثة موافقة الدواء للداء، وبذل الطبيب له، وقبول طبيعة العليل، فمتى تخلف واحد منها لم يحصل الشفاء، وإذا اجتمع حصل الشفاء ولا بد بإذن الله سبحانه وتعالى، ومن عرف هذا كما ينبغي تبين له أسرار الرقى، وميز بين النافع منها وغيره].</p>
    </section>
</div>
HTML
        ]
    ];

    foreach ($cmsPages as $page) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO cms_pages (title, slug, content, is_active, sort_order) VALUES (?, ?, ?, 1, 1)");
        $stmt->execute([$page['title'], $page['slug'], $page['content']]);
    }

    // =====================================================
    // إدراج الأقسام المتداخلة المنسقة (Categories) (مع الحماية INSERT IGNORE)
    // =====================================================
    $categoriesHtml = [
        [
            'id' => 21,
            'name' => 'الجن والشياطين',
            'slug' => 'jinn-and-demons',
            'icon' => 'fas fa-fire',
            'color' => '#e76f51',
            'order' => 1,
            'desc' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">من هم الجن وحقيقتهم</h3>
        <p class="text-lg leading-loose mb-4">قال تعالى: <span class="font-amiri text-xl text-gld-600 font-bold">“وَمَا خَلَقْتُ الْجِنَّ وَالإِنسَ إِلاَّ لِيَعْبُدُون”</span> [سورة الذاريات:56].</p>
        <p class="text-lg leading-loose mb-4">خلق الله تعالى الجن والإنس لعبادته سبحانه والتعلق به وحده دون سواه، لذلك لابد على المسلم أن يتعرف على عالم الجن كمعرفته بعالم الإنس؛ للصلة القوية بين الإنس والجان في كثير من مجالات الحياة.</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">الجن لغةً:</h4>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li>يعني الستر، يقال: جَنَّ الشيء جنًا أي ستره، ومن ذلك سُمي الجنون جنونًا؛ لأنه يجن العقل، أي يستره ويغطيه.</li>
            <li>وسُمي القبر جننًا؛ لأنه يستر الميت.</li>
            <li>وسُمي الكفن جننًا، للمعنى نفسه.</li>
            <li>وسُمي الجنين، لكونه مستورًا داخل بطن أمه.</li>
            <li>وسمي القلب جنانًا، لأنه مستور في الصدر، ويستر داخله أخباراً وأسرارًا.</li>
            <li>وسميت الروح جنانًا، لأن الجسم يسترها.</li>
            <li>والمجن: الترس، لأنه يقي صاحبه، ويستره عن العدو، وكذلك الدرع.</li>
            <li>ويقال للبستان: جنة. وجمعها جنان، لأنه يستر ما بداخله ومنه أطلق اسم (الجَنَّةَ).</li>
            <li>و(الجن) سموا بذلك لاجتنانهم عن الأبصار، أي استتارهم واختفائهم.</li>
            <li>ويقال لهم (جِنَّة) أيضاً ومفرده: جني.</li>
        </ul>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">الجن اصطلاحًا:</h4>
        <p class="text-lg leading-loose">هم كائنات خفيَّة، لها القدرة على أن تتخذ أشكالاً متعددة. وتسمى هذه الكائنات، كذلك: الجان والمردة. والجِنَّة والجن عالم آخر غير عالم الإنسان، وإن كان يشترك مع الإنسان في صفة العقل والاختيار لطريق الخير أو الشر.</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">أصل كلمة الجن:</h4>
        <p class="text-lg leading-loose">ذهب بعض المستشرقين إلى أنّ كلمة (الجن) من الكلمات المعرَّبة، وذهب بعض آخر إلى أنها عربية. ويرى بعضهم أنها من الكلمات السامية القديمة؛ لأن الإيمان بالجن من العقائد القديمة المعروفة عند قدماء الساميين وعند غيرهم، كذلك.</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">اثبات وجود الجن</h3>
        <p class="text-lg leading-loose mb-4">ما أنزل الله تعالى آيةً في كتابه (القرآن الكريم) أو علَّمه رسوله الأمين في سنته عليه أفضل الصلاة وأتم التسليم إلا وكان إثباتًا كافيًا لنا. فلا نلجأ لنتثبت أو البحث بعده عن تصديق أو تكذيب ما ورد فيهما.</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">الجن في القرآن:</h4>
        <p class="text-lg leading-loose mb-4">لقد ورِدَ ذكر الجن في القرآن (الجن، والجان، والجِنَّة) في تسع وعشرين آية من القرآن الكريم. كما أُنزلَت سورة كاملة باسم (الجن) تتحدث السورة عن تصديق نزول القرآن وأنه من عند الله من خلال إيمان الجن به، وإبطال مزاعم المشركين فيهم.</p>
        <ul class="space-y-4 text-lg text-brk-600">
            <li><strong>1- أصل خلق الجن:</strong> ذكر الله تعالى أن أصل خلقهم من نار السموم. قال تعالى: “وَالْجَانَّ خَلَقْنَاهُ مِن قَبْلُ مِن نَّارِ السَّمُوم” [سورة الحجر:27].</li>
            <li><strong>2- عداوة بعض الجن للأنبياء:</strong> قال تعالى: “وَكَذَلِكَ جَعَلْنَا لِكُلِّ نِبِيٍّ عَدُوًّا شَيَاطِينَ الإِنسِ وَالْجِنِّ يُوحِي بَعْضُهُمْ إِلَى بَعْضٍ زُخْرُفَ الْقَوْلِ غُرُورًا” [سورة الأنعام:112].</li>
            <li><strong>3- إرسال الرسل إليهم:</strong> قال تعالى: “يَا مَعْشَرَ الْجِنِّ وَالإِنسِ أَلَمْ يَأْتِكُمْ رُسُلٌ مِّنكُمْ يَقُصُّونَ عَلَيْكُمْ آيَاتِي وَيُنذِرُونَكُمْ لِقَاء يَوْمِكُمْ هَـذَا...” [سورة الأنعام:130].</li>
            <li><strong>4- عجزهم عن إتيان مثل هذا القرآن:</strong> قال تعالى: “قُل لَّئِنِ اجْتَمَعَتِ الإِنسُ وَالْجِنُّ عَلَى أَن يَأْتُواْ بِمِثْلِ هَـذَا الْقُرْآنِ لاَ يَأْتُونَ بِمِثْلِهِ...” [سورة الإسراء:88].</li>
            <li><strong>5- صرف بعض الجن إلى النبي يستمعون القرآن:</strong> قال تعالى: “وَإِذْ صَرَفْنَا إِلَيْكَ نَفَرًا مِّنَ الْجِنِّ يَسْتَمِعُونَ الْقُرْآنَ...” [سورة الأحقاف:29].</li>
            <li><strong>6- جعلت قريش بين الله وبين الجنِّ نسبًا:</strong> قال تعالى: “وَجَعَلُوا بَيْنَهُ وَبَيْنَ الْجِنَّةِ نَسَبًا...” [سورة الصافات:158].</li>
            <li><strong>7- كما أنها جعلت الجن شركاء له:</strong> قال تعالى: “وَجَعَلُواْ لِلّهِ شُرَكَاء الْجِنَّ...” [سورة الأنعام:100].</li>
        </ul>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">الجن في السّنة:</h4>
        <p class="text-lg leading-loose mb-4">زعم كفار قريش أن الله تزوج الجن وأنّ الملائكة هم بناته من هذا الزواج – تعالى الله عن ذلك علوًا كبيرًا- فقد روي أن كفار قريش قالوا: الملائكة بنات الله. فقال لهم أبو بكر الصديق: فمن أمهاتهم؟ قالوا: بنات سراة الجن.</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">خلق الجن وحقيقتهم</h3>
        <p class="text-lg leading-loose mb-4">الجن مخلوقون من النار، كما في قوله تعالى: “وَالْجَانَّ خَلَقْنَاهُ مِن قَبْلُ مِن نَّارِ السَّمُوم”. والمارج: الشعلة الساطعة ذات اللهب الشديد.</p>
        <p class="text-lg leading-loose mb-4">وفي الحديث عن عائشة عن النبي صلى الله عليه وسلم، قالت: قال رسول الله صلى الله عليه وسلم: (خُلِقَت الملائكةَ من نورٍ وخُلِقَ آدمُ ممَّا وُصِفَ لكُم). [رواه مسلم]</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">تكليف الجن:</h4>
        <p class="text-lg leading-loose">أخبر الله عز وجل أنه بعث نفراً من الجن إلى النبي، ليستمعوا القرآن وينذروا قومهم... فهذا دليل على أنهم مأمورون بالإيمان برسالة محمد صلى الله علية وسلم ، مثل: الإنس.</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">حياة الجن واصنافهم</h3>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600 mb-6">
            <li><strong>أصناف الجن:</strong> صنف يطير في الهواء، صنف حيّات وكلاب، صنف يحلون ويظعنون، وهم السعالي.</li>
            <li><strong>تشكلهم وقدراتهم:</strong> الجن لهم قدرة خارقة على التشكُّل بأشكالٍ مختلفة وكثيرة لا تحصر، من الإنسان والحيوان بل حتى الجمادات.</li>
            <li><strong>مساكن الجن:</strong> يسكنون الأماكن الخربة وأماكن المعاصي والصحاري والكهوف والمقابر والمواضع المظلمة، وباطن الأرض.</li>
            <li><strong>طعام الجن وشرابهم:</strong> يأكلون أي طعام وخاصةً مما لم يُذكرُ اسم الله عليه، وكذلك العظم والروث كما ورد في الأحاديث الصحيحة.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">عقــيدة الجـــن ودياناتهم</h3>
        <p class="text-lg leading-loose">منهم الجن المسلم ومنهم النصراني واليهودي والبوذي والهندوسي والشيعي والملحد و الكافر والعاصي وقد أخبر الله عنهم أنهم قالوا: “وَأَنَّا مِنَّا الصَّالِحُونَ وَمِنَّا دُونَ ذَلِكَ كُنَّا طَرَائِقَ قِدَدًا” [سورة الجن:11].</p>
    </section>
</div>
HTML
        ],
        [
            'id' => 22,
            'name' => 'العين والحسد',
            'slug' => 'evil-eye-envy',
            'icon' => 'fas fa-eye',
            'color' => '#2a9d8f',
            'order' => 2,
            'desc' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">مفهوم العين وحقيقته</h3>
        <p class="text-lg leading-loose">هو نظر باستحسان أو بغض فتنبعث جواهر لطيفة غير مرئية من عين العائن لتتصل بالمعيون فيحصل الضرر ونفس العائن لا يتوقف تأثيرها على الرؤية، بل قد يكون أعمى، فيوصف له الشيء، فتؤثر نفسه فيه، وإن لم يره.</p>
        <p class="text-lg leading-loose mt-4">العين مأخوذة من عان يعين إذا أصابه بعينه، وأصلها من إعجاب العائن بالشيء ثم تتبعه كيفية نفسه الخبيثة ثم تستعين على تنفيذ سمها بنظرها إلى المعين وقد أمر الله نبيه محمدًا صلى الله عليه وسلم بالاستعاذة من الحاسد فقال تعالى: “وَمِن شَرِّ حَاسِدٍ إِذَا حَسَد” [سورة الفلق:5].</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">حقيقة العين والحسد والأدلة عليهما</h3>
        <p class="text-lg leading-loose">للعين والحسد حقيقة ووجود وأدلة ثابتة من القرآن والسنّة وعند الأمم السابقة وفي واقعنا أدلة كثيرة على ذلك.</p>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600 mt-4">
            <li><strong>الأدلة من القرآن:</strong> قال تعالى: “وَإِن يَكَادُ الَّذِينَ كَفَرُوا لَيُزْلِقُونَكَ بِأَبْصَارِهِمْ لَمَّا سَمِعُوا الذِّكْرَ وَيَقُولُونَ إِنَّهُ لَمَجْنُون”</li>
            <li><strong>الأدلة من السنّة:</strong> قول المصطفى عليه الصلاة والسلام: (استعيذوا بالله من العين فإن العين حقّ). وقوله عليه الصلاة والسلام: (العين حقّ ولو كان شيء سابق القدر سبقته العين وإذا استُغسِلتُم فاغسلوا) [مسلم: 5666].</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">انواع العين</h3>
        <ul class="space-y-4 text-lg text-brk-600">
            <li><strong>أولاً: العين الإنسية:</strong> تقع من أعين الناس بالنظر وإبصارهم للشيء، أو بالوصف.</li>
            <li><strong>ثانيًا: العين الجنّية:</strong> العين والحسد اللذان تقعان من الجـن، ففي الحديث: (استرقوا لها، فإن بها النظرة) أي من الجن.</li>
            <li><strong>ثالثًا: أعين الحيوانات:</strong> بعض الحيوانات تصيب الناس بأعينها ونظرها، كأنواع معينة من الحيات والكلاب والقطط.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">قوة العين ومجالات التأثير</h3>
        <p class="text-lg leading-loose mb-4">للعين والحسد قوة خارقة لا يصدّقها كثير من الناس فقد تهد الجبل. قال رسول الله صلى الله عليه وسلم: (العين حقّ، تستنزل الحالقَ) أي تسقط الجبل العالي.</p>
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">المجالات التي تؤثر فيها العين والحسد:</h4>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li>تؤثر في العقول: الذكاء- الحفظ- التركيز- الفهم.</li>
            <li>تؤثر في الرزق: المال- طرق الكسب- الوظيفة.</li>
            <li>تؤثر في الجمال: الشعر- الوجه- اللون والبشرة.</li>
            <li>تؤثر في الدين: القرآن- العبادات- الخشوع.</li>
            <li>تؤثر في الحياة الزوجية والأسرية.</li>
            <li>تؤثر في الأجساد والأعمار والجمادات والحيوانات.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">اعراض وعلامات العين</h3>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 list-disc pr-6 text-lg text-brk-600">
            <li>التثاؤب المتتابع بدون نعاس.</li>
            <li>الجشاء (التكريع) الكثير.</li>
            <li>الهرش (الحكّة) وظهور دمامل.</li>
            <li>الحرارة أو البرودة بدون سبب.</li>
            <li>ضيق الصدر والخمول والأرق.</li>
            <li>تساقط الشعر وشحوب الوجه.</li>
            <li>الصداع الدائم.</li>
            <li>تسلخ مفاجئ ورغبة في الخروج من المنزل.</li>
            <li>أمراض متنقلة لا يشخصها الطب.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">نصائح وتوجيهات للمحسود والمعيون</h3>
        <p class="text-lg leading-loose mb-4">ذكر الإمام ابن القيم عشرة أسباب يندفع بها شر الحاسد عن المحسود، نلخصها:</p>
        <ol class="list-decimal pr-6 space-y-2 text-lg text-brk-600 mb-6">
            <li>التعوذ بالله من شره والتحصن به.</li>
            <li>تقوى الله وحفظه عند أمره ونهيه.</li>
            <li>الصبر على عدوه وأن لا يقاتله ولا يشكوه.</li>
            <li>التوكل على الله فمن يتوكل على الله فهو حسبه.</li>
            <li>فراغ القلب من الاشتغال به والفكر فيه.</li>
            <li>الإقبال على الله والإخلاص له وجعل محبته وترضيه في قلبه.</li>
            <li>تجريد التوبة إلى الله من الذنوب.</li>
            <li>الصدقة والإحسان ما أمكنه.</li>
            <li>طفي نار الحاسد والباغي والمؤذي بالإحسان إليه.</li>
            <li>تجريد التوحيد والترحل بالفكر في الأسباب إلى الله.</li>
        </ol>
    </section>
</div>
HTML
        ],
        [
            'id' => 23,
            'name' => 'السحر',
            'slug' => 'magic',
            'icon' => 'fas fa-magic',
            'color' => '#9b2226',
            'order' => 3,
            'desc' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">مفهوم السحر وحقيقته</h3>
        <p class="text-lg leading-loose mb-4"><strong>السحر لغةً:</strong> كل ما لطف مأخذه ودق، ومنه قول النبي صلى الله عليه وسلم: (إن من البيان لسحرًا) وسحره: أي خدعه. يطلق السحر على عمل تقرب به إلى الشيطان وبمعونة منه.</p>
        <p class="text-lg leading-loose mb-4"><strong>السحر اصطلاحًا:</strong> المراد بالسحر ما يستعان في تحصيله بالتقرب إلى الشيطان مما لا يستقل به الإنسان، وعقَد ورقى وكلام يتكلم به، أو يكتبه، أو يعمل شيئًا يؤثِّر في بدن المسحور أو قلبه أو عقله.</p>
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">الأدلة من القرآن والسنة:</h4>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li>قال تعالى: “وَمَا كَفَرَ سُلَيْمَانُ وَلَـكِنَّ الشَّيْاطِينَ كَفَرُواْ يُعَلِّمُونَ النَّاسَ السِّحْرَ” [البقرة: 102].</li>
            <li>سورة الفلق: “وَمِن شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ” وهنَّ السَّواحِر من النساء.</li>
            <li>حديث عائشة رضي الله عنها في الصحيحين حينما سُحر النبي صلى الله عليه وسلم من قِبَل لبيد بن الأعصم اليهودي.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">فك الأسحار وإبطالها</h3>
        <p class="text-lg leading-loose mb-4">ماذا تفعل إذا وجدتَ سحرًا؟ أنت على حالتين:</p>
        <ol class="list-decimal pr-6 space-y-2 text-lg text-brk-600 mb-6">
            <li><strong>الحالة الأولى:</strong> إحالته لمن يتولى ذلك بعد أن تتحصن بالمعوذات وآية الكرسي وتأخذه لراقي من أهل السنة.</li>
            <li><strong>الحالة الثانية:</strong> تتولى فكه وإبطاله بنفسك محتسباً الأجر. كن على يقين ولا تخف أحداً إلا الله.</li>
        </ol>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">خطوات فك السحر:</h4>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li>أحضر وعاء به ماء واقرأ عليه آيات إبطال السحر وانفث فيه.</li>
            <li>استحضر النية واليقين بأن الشفاء من الله.</li>
            <li>ضع السحر في الماء وابدأ في فكه وحله وتقطيعه وأنت تردد المعوذات.</li>
            <li>إذا انتهيت، أخرج البقايا وادفنها أو ألقها في القمامة وصب الماء خارج المنزل.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">مجالات تأثير السحر</h3>
        <p class="text-lg leading-loose mb-4">يؤثر السحر بإذن الله في أمور كثيرة منها:</p>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 list-disc pr-6 text-lg text-brk-600">
            <li>تفريق الأزواج بالبغض والكره.</li>
            <li>ربط الرجل عن زوجته والعكس.</li>
            <li>إسقاط الحمل وتعطيل الإنجاب.</li>
            <li>المحبة والتسخير للوقوع في المحرمات.</li>
            <li>الفرقة بين أفراد الأسرة.</li>
            <li>تعذيب المسحور بالأمراض الجسدية والنفسية.</li>
            <li>الخسارة في التجارة وتعطيل الرزق.</li>
            <li>التخييل وخدع العيون (سحر التخييل).</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">الوقاية من السحر</h3>
        <p class="text-lg leading-loose mb-4">درهم وقاية خير من قنطار علاج، وتتم الوقاية من خلال:</p>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li>العقيدة الصحيحة والتوحيد الخالص.</li>
            <li>المحافظة على الصلاة في وقتها والنوافل.</li>
            <li>قراءة القرآن وخاصة سورة البقرة التي لا تستطيعها البطلة (السحرة).</li>
            <li>الأدعية والتحصينات اليومية (أذكار الصباح والمساء).</li>
            <li>التصبح بسبع تمرات عجوة.</li>
            <li>البعد عن الذنوب والمعاصي التي تفتح منافذ الشياطين.</li>
        </ul>
    </section>
</div>
HTML
        ],
        [
            'id' => 24,
            'name' => 'المس والصرع',
            'slug' => 'possession-epilepsy',
            'icon' => 'fas fa-brain',
            'color' => '#6d597a',
            'order' => 4,
            'desc' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">الصرع مفهومه وأنواعه</h3>
        <p class="text-lg leading-loose mb-4"><strong>الصرع:</strong> هو اضطراب تشنّجي وخلل وظيفي مؤقت بالمخ يؤثر على الجهاز العصبي ينتج عنه تشنجات. وقد يكون عضوياً أو روحياً.</p>
        <p class="text-lg leading-loose mb-4">يقول ابن القيم: [لو كشف الغطاء لرأيت أكثر النفوس البشرية صرعى مع هذه الارواح الخبيثة ، وهي في أسرها وقبضتها تسوقها حيث شاءت].</p>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">النوع الأول: صرع الأخـلاط (العضوي):</h4>
        <p class="text-lg leading-loose mb-4">نشاط كهربائي وتهيج في بعض خلايا المخ. يستجيب لعلاج الأطباء وتتحسن حالته إذا داوم على الأدوية. لا يستطيع المريض أن يحرك أي عضو من جسده بإرادته وقت الصرع لأنه لا يعي شيئًا.</p>

        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">النوع الثاني: صرع الأرواح الخبيثة (الجن):</h4>
        <p class="text-lg leading-loose mb-4">عبارة عن تسلُّط الشيطان على مخ المصروع. المريض يعاني في الغالب من أعراض المس ويمكن الكشف عليه بقراءة الرقية الشرعية. لا يستفيد من علاج الأطباء إلا من باب الاستدراج والمكر من الجني.</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">حقيقة المس وأسبابه</h3>
        <p class="text-lg leading-loose mb-4">قال تعالى: “الَّذِينَ يَأْكُلُونَ الرِّبَا لاَ يَقُومُونَ إِلاَّ كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ”. المس حقيقة واقعة ثابتة بالقرآن والسنة وإجماع أئمة المسلمين.</p>
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-3">أسباب وقوع المس:</h4>
        <ul class="list-disc pr-6 space-y-2 text-lg text-brk-600">
            <li><strong>الانتقام:</strong> يقترن الشيطان بالإنسان لينتقم منه (بسبب صب ماء حار، البول في شقوق، إيذاء حيوانات دون تسمية).</li>
            <li><strong>الظلم:</strong> ظلم الجن للإنس لغفلتهم عن ذكر الله.</li>
            <li><strong>العشق:</strong> إعجاب الجني بالإنسي.</li>
            <li><strong>السحر والعين:</strong> قد يقترن الجني كخادم سحر أو يدخل مع العين.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">أنواع المس</h3>
        <ul class="space-y-4 text-lg text-brk-600">
            <li><strong>المس الطائف:</strong> وساوس وضيق عارض يزول بذكر الله.</li>
            <li><strong>المس العارض:</strong> يتلبس الجني لبعض الوقت ثم يخرج.</li>
            <li><strong>الاقتران الدائم:</strong> سكن الجني في الجسد بشكل دائم.</li>
            <li><strong>المس الخارجي:</strong> تسلط من الخارج كالجاثوم والمضايقات أثناء النوم.</li>
            <li><strong>المس الوهمي والتمثيلي:</strong> إيحاء نفسي أو تمثيل من المريض لأسباب اجتماعية ونفسية.</li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">المس العاشق: علاماته وعلاجه</h3>
        <p class="text-lg leading-loose mb-4">من أشد أنواع المس أذى، حيث يعشق الجني الإنسية (أو العكس) ويزين له الفاحشة ويصرفه عن الزواج والحياة الطبيعية.</p>
        <h4 class="text-xl font-bold text-pri-800 mt-4 mb-2">علامات المس العاشق:</h4>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 list-disc pr-6 text-lg text-brk-600">
            <li>كثرة الاحتلام المفرط.</li>
            <li>النفور من الزوج أو الزوجة والميل للعزلة.</li>
            <li>الشعور بحركات ومضايقات في العورات.</li>
            <li>الشعور بوجود شخص آخر في السرير.</li>
            <li>تعطيل الزواج للشباب والفتيات بدون مبرر.</li>
            <li>بكاء الفتاة فجأة عند الخطبة.</li>
        </ul>
        
        <h4 class="text-xl font-bold text-pri-800 mt-6 mb-2">طرق العلاج من المس العاشق:</h4>
        <ol class="list-decimal pr-6 space-y-2 text-lg text-brk-600">
            <li>قراءة آيات ذم الفاحشة وسورة النور ويوسف.</li>
            <li>الدعاء المستمر على المعتدي في أوقات الإجابة.</li>
            <li>استخدام المسك الأسود دهناً وشمّاً لتنفير المعتدي.</li>
            <li>المحافظة التامة على الأذكار والصلاة في وقتها.</li>
            <li>دهان الجسد بالزيت المقروء.</li>
        </ol>
    </section>
</div>
HTML
        ],
        [
            'id' => 25,
            'name' => 'التداوي والعلاج',
            'slug' => 'healing-treatment',
            'icon' => 'fas fa-leaf',
            'color' => '#52b788',
            'order' => 5,
            'desc' => <<<HTML
<div class="space-y-8">
    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">مفهوم الطب الشرعي والتداوي</h3>
        <p class="text-lg leading-loose mb-4">المقصود بالطب لشرعي كل ما ورد به الدليل في كتاب أو سنة. وقد أذِن الشارع بالتداوي عمومًا كما في قوله صلى الله عليه وسلم: (تداووا عباد الله، فإن الله لم يضع داء إلا وضع له دواء، غير داء واحد، الهرم).</p>
        <p class="text-lg leading-loose mb-4">وفي الصحيحين: (ما أنزل الله من داء إلا أنزل له شفاء).</p>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">أقسام الطب الشرعي وما ورد به الدليل</h3>
        <ul class="space-y-4 text-lg text-brk-600">
            <li><strong>الاستشفاء بكلام الله:</strong> (وننزل من القرآن ما هو شفاء ورحمة للمؤمنين).</li>
            <li><strong>التداوي بالدعاء:</strong> الدعاء مفتاح أبواب الرحمة ويرفع البلاء.</li>
            <li><strong>التداوي بالأطعمة والأعشاب المباحة (الطب النبوي):</strong>
                <ul class="list-disc pr-6 mt-2 space-y-1">
                    <li><strong>ماء زمزم:</strong> (طعام طعمٍ، وشفاء سقم).</li>
                    <li><strong>العسل:</strong> (يخرج من بطونها شراب فيه شفاء للناس).</li>
                    <li><strong>الحبة السوداء:</strong> (شفاء من كل داء إلا السام).</li>
                    <li><strong>زيت الزيتون:</strong> (كلوا الزيت وادهنوا به فإنه من شجرة مباركة).</li>
                    <li><strong>السنا والسنوت، القسط الهندي، والعجوة.</strong></li>
                </ul>
            </li>
        </ul>
    </section>

    <section>
        <h3 class="text-2xl font-bold text-pri-900 mb-4 border-b pb-2">شروط التداوي والعلاج</h3>
        <ol class="list-decimal pr-6 space-y-2 text-lg text-brk-600">
            <li><strong>التداوي بالمباح:</strong> ألا نتداوى بمحرّم أو باستخدام طرق السحرة والمشعوذين.</li>
            <li><strong>عدم التعلق بالسبب:</strong> الاعتقاد الجازم بأن الشافي هو الله وأن الدواء مجرد سبب مباح.</li>
            <li><strong>المتابعة واليقين:</strong> الاستمرار على الرقية والعلاج بيقين وصبر.</li>
        </ol>
    </section>
</div>
HTML
        ]
    ];

    $stmtCat = $pdo->prepare("INSERT IGNORE INTO categories (id, name, slug, icon_class, color_hex, sort_order, description, show_in_menu, show_on_home, content_type) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, 'mixed')");
    
    foreach ($categoriesHtml as $cat) {
        $stmtCat->execute([$cat['id'], $cat['name'], $cat['slug'], $cat['icon'], $cat['color'], $cat['order'], $cat['desc']]);
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "<div style='background: #d1fae5; color: #047857; padding: 20px; border-radius: 10px; margin-top: 20px;'>
            <h3 style='margin-top: 0;'>🎉 تمت العملية بنجاح أسطوري!</h3>
            <p>تم زرع جميع المحتويات التي أرسلتها (عن الموقع، الجن، العين، السحر، المس، التداوي، المناقشات، الآيات) منسقة بشكل احترافي رائع، وتم حماية الجداول من الحذف لتبقى بياناتك آمنة.</p>
            <a href='index.php' style='display:inline-block; margin-top:10px; padding:10px 20px; background:#1a582a; color:#fff; text-decoration:none; border-radius:5px;'>الانتقال للموقع الآن</a>
          </div>";

} catch (PDOException $e) {
    echo "<p style='color:red; font-weight: bold;'>❌ حدث خطأ في قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>